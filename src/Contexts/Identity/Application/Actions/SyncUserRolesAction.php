<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Application\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Spatie\Permission\PermissionRegistrar;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Exceptions\MissingTenantContextException;

/**
 * إسناد الأدوار **جوه مؤسسة محددة**.
 *
 * ⚠️ ليه Action مش ->relationship('roles') وخلاص؟ لأن Filament بيحفظ
 * العلاقة بـ sync() المجرّد، وde بيكتب صف في model_has_roles من غير
 * tenant_id — والعمود NOT NULL، فالحفظ بيقع بـ 23502. spatie نفسه
 * بيحطّ الـ tenant_id يدوي في assignRole() (بيبعته كـ pivot values)،
 * فلازم نعدّي من عنده مش من حوالين العلاقة.
 *
 * وكمان: syncRoles() بيقرا الفريق من الـ registrar وقت التنفيذ، فلو
 * السياق مش مظبوط الأدوار بتتكتب على المؤسسة الغلط بدل ما تفشل — وده
 * أسوأ من الخطأ. عشان كده بنضبط الفريق صراحةً هنا.
 */
final readonly class SyncUserRolesAction
{
    /**
     * @param  list<int|string>  $roles  مفاتيح أو أسماء أدوار
     */
    public function handle(User $user, array $roles, ?int $tenantId = null): void
    {
        $tenantId ??= app(TenantContext::class)->id();

        if ($tenantId === null) {
            // العمود NOT NULL، فالبديل عن الرمي هو كتابة صف بمؤسسة غلط.
            throw new MissingTenantContextException(User::class);
        }

        $registrar = app(PermissionRegistrar::class);
        $previousTeam = $registrar->getPermissionsTeamId();

        try {
            $registrar->setPermissionsTeamId($tenantId);
            $registrar->forgetCachedPermissions();
            $user->unsetRelation('roles')->unsetRelation('permissions');

            $user->syncRoles($this->resolve($roles));
        } finally {
            $registrar->setPermissionsTeamId($previousTeam);
            $registrar->forgetCachedPermissions();
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    /**
     * Filament بيبعت مفاتيح، والسيدر والاختبارات بيبعتوا أسماء — وخلط
     * الاتنين في whereKey واحد بيكسر Postgres ('admin' جوه عمود integer).
     *
     * @param  list<int|string>  $roles
     * @return EloquentCollection<int, Role>
     */
    private function resolve(array $roles): EloquentCollection
    {
        $isKey = static fn (int|string $role): bool => is_int($role) || ctype_digit((string) $role);

        $keys = array_values(array_filter($roles, $isKey));
        $names = array_values(array_filter($roles, static fn (int|string $role): bool => ! $isKey($role)));

        return Role::query()
            ->where('guard_name', (string) config('authorization.guard'))
            ->where(function (Builder $query) use ($keys, $names): void {
                $query->whereIn('id', $keys === [] ? [0] : $keys);

                if ($names !== []) {
                    $query->orWhereIn('name', $names);
                }
            })
            ->get();
    }
}
