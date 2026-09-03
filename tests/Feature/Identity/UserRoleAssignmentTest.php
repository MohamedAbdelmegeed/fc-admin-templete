<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Src\Contexts\Identity\Application\Actions\SyncUserRolesAction;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Exceptions\MissingTenantContextException;

/*
|--------------------------------------------------------------------------
| إسناد الأدوار لمستخدم جوه مؤسسة
|--------------------------------------------------------------------------
| model_has_roles.tenant_id عمود NOT NULL. حفظ العلاقة المجرّد بـ sync()
| بيكتب الصف من غيره ويقع بـ 23502 — والعطل ده كان بيظهر وقت تعديل
| أدوار مستخدم من لوحة مؤسسة.
*/

it('يكتب المؤسسة في صف الدور بدل ما يقع بـ null', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $target = User::factory()->create();
    $target->tenants()->attach($tenant);

    $admin = Role::query()->where('name', 'admin')->sole();

    app(SyncUserRolesAction::class)->handle($target, [$admin->getKey()], $tenant->getKey());

    expect(DB::table('model_has_roles')
        ->where('model_id', $target->getKey())
        ->where('role_id', $admin->getKey())
        ->whereNull('tenant_id')
        ->exists())->toBeFalse();

    expect(DB::table('model_has_roles')
        ->where('model_id', $target->getKey())
        ->where('role_id', $admin->getKey())
        ->where('tenant_id', $tenant->getKey())
        ->exists())->toBeTrue();
});

it('يقبل أسماء الأدوار مثلما يقبل المفاتيح', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $target = User::factory()->create();
    $target->tenants()->attach($tenant);

    // 'editor' نص جوه عمود id بتاعه integer — لازم ميوصلش لـ whereKey.
    app(SyncUserRolesAction::class)->handle($target, ['editor'], $tenant->getKey());

    expect(DB::table('model_has_roles')
        ->where('model_id', $target->getKey())
        ->where('tenant_id', $tenant->getKey())
        ->count())->toBe(1);
});

it('يستبدل أدوار المؤسسة الحالية ولا يلمس مؤسسة تانية', function (): void {
    $acme = currentTenant();
    $beta = Tenant::factory()->create();

    actingAsRole('super_admin', $acme);

    $target = User::factory()->create();
    $target->tenants()->attach([$acme->getKey(), $beta->getKey()]);

    $action = app(SyncUserRolesAction::class);
    $action->handle($target, ['admin'], $acme->getKey());
    $action->handle($target, ['viewer'], $beta->getKey());

    // تغيير دور acme مايمسّش beta.
    $action->handle($target, ['editor'], $acme->getKey());

    $rows = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_has_roles.model_id', $target->getKey())
        ->pluck('roles.name', 'model_has_roles.tenant_id');

    expect($rows[$acme->getKey()])->toBe('editor')
        ->and($rows[$beta->getKey()])->toBe('viewer');
});

it('يرفض الإسناد بلا سياق مؤسسة بدل ما يكتب صفاً بلا مالك', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $target = User::factory()->create();

    app(TenantContext::class)->set(null);

    expect(fn () => app(SyncUserRolesAction::class)->handle($target, ['admin']))
        ->toThrow(MissingTenantContextException::class);
});

it('يمسح كل الأدوار لما التحديد يبقى فاضي', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $target = User::factory()->create();
    $target->tenants()->attach($tenant);

    $action = app(SyncUserRolesAction::class);
    $action->handle($target, ['admin'], $tenant->getKey());
    $action->handle($target, [], $tenant->getKey());

    expect(DB::table('model_has_roles')
        ->where('model_id', $target->getKey())
        ->where('tenant_id', $tenant->getKey())
        ->count())->toBe(0);
});
