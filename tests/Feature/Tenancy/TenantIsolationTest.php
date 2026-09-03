<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Src\Contexts\Audit\Domain\Models\Activity;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Notifications\Domain\Models\NotificationPreference;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Exceptions\MissingTenantContextException;
use Src\Support\Infrastructure\Persistence\Concerns\BelongsToTenant;

it('لا يسرّب بيانات بين المستأجرين', function (): void {
    $acme = Tenant::factory()->create();
    $beta = Tenant::factory()->create();

    switchTenant($acme);
    NotificationPreference::factory()->count(2)->create();

    switchTenant($beta);
    NotificationPreference::factory()->count(5)->create();

    switchTenant($acme);
    expect(NotificationPreference::query()->count())->toBe(2);

    switchTenant($beta);
    expect(NotificationPreference::query()->count())->toBe(5);
});

it('يرفض الاستعلام بدون سياق مستأجر بدل ما يرجّع كل الصفوف', function (): void {
    app(TenantContext::class)->set(null);

    expect(fn () => NotificationPreference::query()->count())
        ->toThrow(MissingTenantContextException::class);
});

it('يرفض إنشاء سجل بدون سياق مستأجر', function (): void {
    app(TenantContext::class)->set(null);

    expect(fn () => NotificationPreference::query()->create([
        'user_id' => 1,
        'notification_key' => 'user_invited',
        'channels' => ['database'],
    ]))->toThrow(MissingTenantContextException::class);
});

it('يسمح بتجاوز صريح عبر withoutScope فقط', function (): void {
    $acme = Tenant::factory()->create();
    $beta = Tenant::factory()->create();

    switchTenant($acme);
    NotificationPreference::factory()->count(2)->create();

    switchTenant($beta);
    NotificationPreference::factory()->count(3)->create();

    $context = app(TenantContext::class);
    $context->set(null);

    $total = $context->withoutScope(fn (): int => NotificationPreference::query()->count());

    expect($total)->toBe(5);
});

it('لا يسرّب الأدوار بين المستأجرين في نفس الطلب', function (): void {
    $acme = Tenant::factory()->create();
    $beta = Tenant::factory()->create();

    $user = userWithRole('admin', $acme);
    $user->tenants()->attach($beta);

    switchTenant($beta);

    expect($user->fresh()->hasRole('admin'))->toBeFalse();

    switchTenant($acme);

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| الاختبار اللي بيمسك الموديل الجديد اللي نسي الـ trait
|--------------------------------------------------------------------------
| ده أخطر باگ ممكن يحصل في التطبيق — تسريب صامت. الاختبار بيمرّ على
| كل الموديلات تلقائياً فمحدش محتاج يفتكر. (docs/03 بند ٥)
*/

it('كل موديل فيه tenant_id عليه BelongsToTenant', function (): void {
    // استثناء واحد موثّق: Role فيه tenant_id لأن spatie/permission
    // بيستخدمه كعمود «فريق»، والأدوار عندنا **عامة** (tenant_id = null)
    // والربط بالمؤسسة بيحصل في model_has_roles. لو حطينا الـ trait عليه
    // هيبقى مستحيل نقرا الأدوار وإحنا بره سياق مؤسسة. (docs/02 بند ١)
    // والاستثناء التاني: Activity. الـ trait بيرمي على الكتابة لما مفيش
    // سياق، والنشاط بيتسجّل من الكونسول والسيدرز والطوابير. الموديل
    // بيركّب TenantScope يدوي فالقراءة متعزولة زي أي موديل تاني.
    $allowed = [
        Role::class,
        Activity::class,
    ];

    $violations = [];

    foreach (allDomainModels() as $model) {
        if (in_array($model, $allowed, true)) {
            continue;
        }

        $instance = new $model;

        if (! Schema::hasColumn($instance->getTable(), 'tenant_id')) {
            continue;
        }

        if (! in_array(BelongsToTenant::class, class_uses_recursive($model), true)) {
            $violations[] = $model;
        }
    }

    expect($violations)->toBeEmpty('موديلات ناقصها BelongsToTenant: '.implode(', ', $violations));
});

it('المستخدم ليس تابعاً لمستأجر واحد بل عضو في عدة مؤسسات', function (): void {
    $acme = Tenant::factory()->create();
    $beta = Tenant::factory()->create();

    switchTenant($acme);
    $user = User::factory()->create();
    $user->tenants()->attach([$acme->getKey(), $beta->getKey()]);

    expect($user->tenants()->count())->toBe(2)
        ->and(Schema::hasColumn('users', 'tenant_id'))->toBeFalse();
});

/**
 * @return list<class-string>
 */
function allDomainModels(): array
{
    $models = [];

    foreach (File::allFiles(base_path('src/Contexts')) as $file) {
        if (! str_contains($file->getPathname(), 'Domain'.DIRECTORY_SEPARATOR.'Models')) {
            continue;
        }

        $class = classFromPath($file->getPathname());

        if (class_exists($class) && is_subclass_of($class, Model::class)) {
            $models[] = $class;
        }
    }

    return $models;
}

function classFromPath(string $path): string
{
    $relative = str_replace([base_path('src').DIRECTORY_SEPARATOR, '.php'], '', $path);

    return 'Src\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
}
