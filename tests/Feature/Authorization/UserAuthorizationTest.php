<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;

/*
|--------------------------------------------------------------------------
| مصفوفة الأدوار × القدرات
|--------------------------------------------------------------------------
*/

dataset('user_matrix', [
    'super_admin' => ['super_admin', ['viewAny' => true, 'create' => true, 'export' => true, 'assignRoles' => true]],
    'admin' => ['admin', ['viewAny' => true, 'create' => true, 'export' => true, 'assignRoles' => true]],
    'editor' => ['editor', ['viewAny' => true, 'create' => false, 'export' => false, 'assignRoles' => false]],
    'viewer' => ['viewer', ['viewAny' => true, 'create' => false, 'export' => false, 'assignRoles' => false]],
]);

it('يطبّق مصفوفة الصلاحيات على المستخدمين', function (string $role, array $expected): void {
    $user = userWithRole($role);

    foreach ($expected as $ability => $allowed) {
        expect($user->can($ability, User::class))
            ->toBe($allowed, "الدور {$role} — القدرة {$ability}");
    }
})->with('user_matrix');

/*
|--------------------------------------------------------------------------
| قواعد السلامة — المدير العام مابيتخطاهاش
|--------------------------------------------------------------------------
*/

it('لا يستطيع أي مستخدم حذف نفسه', function (): void {
    foreach (['super_admin', 'admin'] as $role) {
        $user = userWithRole($role);

        expect($user->can('delete', $user))->toBeFalse("الدور {$role} قدر يحذف نفسه");
    }
});

it('يوضّح سبب الرفض بدل ما يرفض بصمت', function (): void {
    $user = userWithRole('super_admin');

    $response = Gate::forUser($user)->inspect('delete', $user);

    expect($response->denied())->toBeTrue()
        ->and($response->message())->toBe(__('authorization.denied.user.cannot_delete_self'));
});

it('يمنع انتحال شخصية المدير العام حتى من مدير عام آخر', function (): void {
    $actor = userWithRole('super_admin');
    $target = userWithRole('super_admin');

    // إنشاء مستخدم تاني بيعيد مزامنة الصلاحيات ويمسح الكاش — لازم
    // نفضّي العلاقة المكاشة على الأول عشان الفحص يقرا من جديد.
    $actor->unsetRelation('roles')->unsetRelation('permissions');
    $target->unsetRelation('roles')->unsetRelation('permissions');

    $response = Gate::forUser($actor)->inspect('impersonate', $target);

    expect($response->denied())->toBeTrue()
        ->and($response->message())->toBe(__('authorization.denied.user.cannot_impersonate_super_admin'));
});

it('يتجاوز المدير العام الصلاحيات العادية', function (): void {
    $super = userWithRole('super_admin');

    expect($super->can('viewAny', User::class))->toBeTrue()
        ->and($super->can('viewAny', Tenant::class))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| إخفاء الوجود بدل الرفض
|--------------------------------------------------------------------------
*/

it('يخفي مستخدم مؤسسة أخرى بـ 404 لا 403', function (): void {
    $acme = Tenant::factory()->create();
    $beta = Tenant::factory()->create();

    $actor = userWithRole('admin', $acme);
    $foreigner = userWithRole('admin', $beta);

    switchTenant($acme);
    $actor->unsetRelation('roles');

    $response = Gate::forUser($actor)->inspect('view', $foreigner);

    expect($response->denied())->toBeTrue()
        ->and($response->status())->toBe(404);
});

/*
|--------------------------------------------------------------------------
| رفض بغير رسالة = إخفاء الزرار (docs/19 بند ٨)
|--------------------------------------------------------------------------
*/

it('يرفض نقص الصلاحية بدون رسالة عشان الزرار يختفي', function (): void {
    $viewer = userWithRole('viewer');

    $response = Gate::forUser($viewer)->inspect('create', User::class);

    expect($response->denied())->toBeTrue()
        ->and($response->message())->toBeNull();
});
