<?php

declare(strict_types=1);

use function Pest\Livewire\livewire;

use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Identity\Presentation\Filament\Resources\UserResource;
use Src\Contexts\Identity\Presentation\Filament\Resources\UserResource\Pages\ListUsers;
use Src\Contexts\Tenancy\Domain\Models\Tenant;

/*
|--------------------------------------------------------------------------
| تحميل الصفحات فعلياً
|--------------------------------------------------------------------------
| الاختبارات دي بتفتح الصفحات زي المتصفح بالظبط. من غيرها الأخطاء
| اللي بتظهر وقت الرسم بس (زي علاقة المستأجر الناقصة على المورد)
| مابتتمسكش غير لما مستخدم حقيقي يقع فيها.
*/

// actAsIn() في tests/Pest.php — بتتشارك مع اختبارات الصفحات التانية.

it('يفتح صفحة المستخدمين ويعرض أعضاء المؤسسة فقط', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    $otherTenant = Tenant::factory()->create();
    $outsider = userWithRole('admin', $otherTenant);

    actAsIn($admin, $tenant);

    livewire(ListUsers::class)
        ->assertOk()
        // deferLoading() مفعّلة عالمياً — الصفوف بتتحمّل في طلب تاني
        // زي المتصفح بالظبط، فلازم نحمّل الجدول قبل ما نتحقق.
        ->loadTable()
        ->assertCanSeeTableRecords([$admin])
        ->assertCanNotSeeTableRecords([$outsider]);
});

it('يمنع المشاهد من الوصول لصفحة الإنشاء', function (): void {
    $tenant = currentTenant();
    $viewer = userWithRole('viewer', $tenant);

    actAsIn($viewer, $tenant);

    test()->get(UserResource::getUrl('create', tenant: $tenant))->assertForbidden();
});

it('يسمح للمدير بالوصول لصفحة الإنشاء', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    test()->get(UserResource::getUrl('create', tenant: $tenant))->assertOk();
});

it('يفتح قائمة المستخدمين للمشاهد بدون إجراءات محظورة', function (): void {
    $tenant = currentTenant();
    $viewer = userWithRole('viewer', $tenant);
    $target = userWithRole('editor', $tenant);

    actAsIn($viewer, $tenant);

    livewire(ListUsers::class)
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords([$target]);

    expect($viewer->can('delete', $target))->toBeFalse();
});

it('لا يملك المحرّر صلاحية إسناد الأدوار', function (): void {
    $tenant = currentTenant();
    $editor = userWithRole('editor', $tenant);

    actAsIn($editor, $tenant);

    expect($editor->can('assignRoles', User::class))->toBeFalse();
});
