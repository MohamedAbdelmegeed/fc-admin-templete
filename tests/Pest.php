<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Tests\Support\TenantRegistry;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
uses(TestCase::class)->in('Architecture');

// كل اختبار بيبدأ بمستأجر نظيف — من غير كده حالة اختبار بتسرّب للي بعده.
uses()->beforeEach(function (): void {
    TenantRegistry::reset();
})->in('Feature');

/*
|--------------------------------------------------------------------------
| مساعدات مشتركة
|--------------------------------------------------------------------------
*/

/** المستأجر الحالي بتاع الاختبار — بيتعمل مرة واحدة والسياق بيتضبط عليه. */
function currentTenant(): Tenant
{
    $tenant = TenantRegistry::current();

    switchTenant($tenant);

    return $tenant;
}

/** بيبدّل سياق المستأجر **و** فريق الصلاحيات معاً — الاتنين لازم يتزامنوا. */
function switchTenant(Tenant $tenant): void
{
    app(TenantContext::class)->set($tenant->getKey());
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

/**
 * بيزامن الصلاحيات من الكونفيج.
 *
 * ⚠️ الأمر بيصفّر فريق الصلاحيات (الأدوار عامة)، فأي استدعاء له لازم
 * يتبعه switchTenant() تاني.
 */
function syncAuthorization(): void
{
    Artisan::call('authorization:sync');
}

function userWithRole(string $role, ?Tenant $tenant = null): User
{
    syncAuthorization();

    $tenant ??= currentTenant();
    switchTenant($tenant);

    $user = User::factory()->create();
    $user->tenants()->attach($tenant);
    $user->assignRole($role);

    return $user->fresh();
}

function actingAsRole(string $role, ?Tenant $tenant = null): User
{
    $user = userWithRole($role, $tenant);

    test()->actingAs($user);

    return $user;
}

/**
 * بيحطّ الاختبار جوه اللوحة ومؤسسة محددة — زي الطلب الحقيقي بالظبط.
 *
 * ⚠️ الترتيب مهم: Filament::setTenant() بيطلق حدث بيتطلب مستخدم
 * مصادَق عليه، فلازم actingAs() **قبلها**.
 */
function actAsIn(User $user, Tenant $tenant): void
{
    switchTenant($tenant);

    test()->actingAs($user);

    Filament::setCurrentPanel('admin');

    // ⚠️ boot() إلزامي: Filament بيسجّل الـ global scope بتاع عزل
    // المستأجر جوه Panel::boot()، واللي بيتنادى من ميدلوير SetUpPanel
    // في الطلب الحقيقي. من غيره الاختبار بيشوف بيانات كل المؤسسات
    // ويوهمك بوجود تسريب مش موجود فعلاً.
    Filament::getPanel('admin')->boot();

    Filament::setTenant($tenant);
}
