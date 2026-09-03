<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Application\Actions;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;

/**
 * عضوية مؤسسة = صفّين مش صف واحد:
 *   ١) tenant_user       — «هو عضو»
 *   ٢) model_has_roles   — «دوره **جوه** المؤسسة دي» (teams بتاعة spatie)
 *
 * ⚠️ الصف التاني هو اللي بيتنسي. User::canAccessTenant() بيطلب
 * الاتنين: العضوية **و** صلاحية access.panel.* في سياق المؤسسة.
 * من غير الدور، اللي بيعمل المؤسسة بيلاقي /admin/t/{slug} مقفول
 * في وشّه — وهو اللي عملها. (docs/03 بند ٥)
 */
final readonly class GrantTenantMembershipAction
{
    public function handle(User $user, Tenant $tenant, ?string $role = null): string
    {
        $role ??= $this->defaultRoleFor($user);

        $user->tenants()->syncWithoutDetaching([
            $tenant->getKey() => ['joined_at' => now()],
        ]);

        $this->assignRoleWithinTenant($user, $tenant, $role);

        return $role;
    }

    /**
     * المدير العام بيفضل مدير عام في أي مؤسسة جديدة — لو نزّلناه لـ admin
     * هيفقد قدرته على إدارة المؤسسات من جوه المؤسسة اللي لسه عاملها.
     * غير كده، اللي بيعمل المؤسسة بياخد دور المنشئ من الكونفيج.
     */
    private function defaultRoleFor(User $user): string
    {
        $superAdmin = (string) config('authorization.super_admin_role');

        return $this->holdsRoleAnywhere($user, $superAdmin)
            ? $superAdmin
            : (string) config('authorization.tenant_creator_role');
    }

    /**
     * الأدوار مخزّنة لكل مؤسسة، فـ «هل هو مدير عام؟» سؤال عابر للمؤسسات.
     * بنسأل جدول الربط مباشرة بدل hasRole() عشان الدالة دي بره طبقة
     * التفويض — ودي قراءة انتماء، مش قرار تفويض.
     */
    private function holdsRoleAnywhere(User $user, string $role): bool
    {
        $pivot = (string) config('permission.table_names.model_has_roles');
        $roles = (string) config('permission.table_names.roles');

        return DB::table($pivot)
            ->join($roles, "{$roles}.id", '=', "{$pivot}.role_id")
            ->where("{$pivot}.model_type", $user->getMorphClass())
            ->where("{$pivot}.model_id", $user->getKey())
            ->where("{$roles}.name", $role)
            ->exists();
    }

    /**
     * ⚠️ assignRole() بيكتب tenant_id من الـ registrar مش من الموديل —
     * فمن غير setPermissionsTeamId() الدور بيتسجّل على المؤسسة الحالية
     * (أو null) بدل الجديدة. والعلاقات مكاشة لكل فريق، فلازم تتفضّى.
     */
    private function assignRoleWithinTenant(User $user, Tenant $tenant, string $role): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previousTeam = $registrar->getPermissionsTeamId();

        try {
            $registrar->setPermissionsTeamId($tenant->getKey());
            $registrar->forgetCachedPermissions();
            $user->unsetRelation('roles')->unsetRelation('permissions');

            $exists = Role::query()
                ->where('name', $role)
                ->where('guard_name', (string) config('authorization.guard'))
                ->exists();

            if ($exists) {
                $user->assignRole($role);
            }
        } finally {
            $registrar->setPermissionsTeamId($previousTeam);
            $registrar->forgetCachedPermissions();
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }
}
