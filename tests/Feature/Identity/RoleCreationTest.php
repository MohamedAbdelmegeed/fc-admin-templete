<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Src\Contexts\Identity\Application\Actions\SyncUserRolesAction;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Domain\Models\User;

/*
|--------------------------------------------------------------------------
| إنشاء دور جوه مؤسسة
|--------------------------------------------------------------------------
| role_has_permissions مافيهوش tenant_id (الصلاحيات على الدور مش على
| المؤسسة)، فمزامنة الصلاحيات آمنة. اللي ليه tenant_id هو ربط الدور
| بالمستخدم — وده اللي كان بيقع.
*/

it('ينشئ دوراً ويزامن صلاحياته بدون عمود مؤسسة', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $role = Role::query()->create([
        'name' => 'coordinator',
        'guard_name' => (string) config('authorization.guard'),
    ]);

    $role->syncPermissions(['access.panel.admin', 'access.dashboard']);

    expect($role->permissions()->count())->toBe(2);

    // مفيش عمود مؤسسة على الجدول ده أصلاً — لو اتضاف يوم، الاختبار ده
    // هو اللي هيقول إن المزامنة محتاجة سياق.
    expect(DB::getSchemaBuilder()->hasColumn('role_has_permissions', 'tenant_id'))->toBeFalse();
});

it('يسند الدور الجديد لمستخدم داخل المؤسسة', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $role = Role::query()->create([
        'name' => 'coordinator',
        'guard_name' => (string) config('authorization.guard'),
    ]);

    $target = User::factory()->create();
    $target->tenants()->attach($tenant);

    app(SyncUserRolesAction::class)->handle($target, [$role->getKey()], $tenant->getKey());

    expect(DB::table('model_has_roles')
        ->where('model_id', $target->getKey())
        ->where('role_id', $role->getKey())
        ->where('tenant_id', $tenant->getKey())
        ->exists())->toBeTrue();
});
