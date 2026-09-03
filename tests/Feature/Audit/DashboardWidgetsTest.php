<?php

declare(strict_types=1);

use Src\Contexts\Audit\Domain\Models\Activity;
use Src\Contexts\Audit\Presentation\Filament\Widgets\OnboardingChecklistWidget;
use Src\Contexts\Audit\Presentation\Filament\Widgets\RecentActivityWidget;
use Src\Contexts\Audit\Presentation\Filament\Widgets\SystemHealthWidget;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Exceptions\MissingTenantContextException;

/*
|--------------------------------------------------------------------------
| ودجتس لوحة التحكم
|--------------------------------------------------------------------------
| الكونفيج كان معرّف ٤ صلاحيات ودجتس ومفيش غير ودجت واحد متعمول.
| التلاتة الباقيين هنا — وكل واحد وراه Gate من الكونفيج.
*/

it('يخفي كل ودجت عن دور مالوش صلاحيته', function (): void {
    actingAsRole('viewer');

    expect(RecentActivityWidget::canView())->toBeFalse()
        ->and(SystemHealthWidget::canView())->toBeFalse();
});

it('يظهر النشاط والصحة للمدير', function (): void {
    actingAsRole('admin');

    expect(RecentActivityWidget::canView())->toBeTrue()
        ->and(SystemHealthWidget::canView())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| العزل — أخطر جزء في الودجت ده
|--------------------------------------------------------------------------
*/

it('لا يعرض نشاط مؤسسة في لوحة مؤسسة تانية', function (): void {
    $acme = currentTenant();
    $beta = Tenant::factory()->create();

    switchTenant($acme);
    Tenant::factory()->create(['slug' => 'acme-child']);

    switchTenant($beta);
    Tenant::factory()->create(['slug' => 'beta-child']);

    switchTenant($acme);
    $acmeRows = Activity::query()->pluck('tenant_id')->unique()->all();

    expect($acmeRows)->not->toContain($beta->getKey())
        ->and(Activity::query()->count())->toBeGreaterThan(0);

    switchTenant($beta);
    expect(Activity::query()->pluck('tenant_id')->unique()->all())
        ->not->toContain($acme->getKey());
});

it('يرفض قراءة النشاط بدون سياق مؤسسة بدل ما يرجّع الكل', function (): void {
    app(TenantContext::class)->set(null);

    expect(fn () => Activity::query()->count())
        ->toThrow(MissingTenantContextException::class);
});

it('يسجّل النشاط خارج سياق المؤسسة بدل ما يرمي', function (): void {
    app(TenantContext::class)->set(null);

    // سيدرز وأوامر الكونسول بتعمل كده — لازم تعدّي.
    $tenant = Tenant::factory()->create();

    expect($tenant->exists)->toBeTrue();

    switchTenant($tenant);
});

/*
|--------------------------------------------------------------------------
| قائمة التجهيز
|--------------------------------------------------------------------------
*/

it('يعلّم خطوة الفريق كمكتملة بعد إضافة زميل', function (): void {
    $tenant = currentTenant();
    actingAsRole('super_admin', $tenant);

    $stepsBefore = collect((new OnboardingChecklistWidget)->steps())
        ->firstWhere('key', 'team');

    expect($stepsBefore['done'])->toBeFalse();

    $mate = User::factory()->create();
    $mate->tenants()->attach($tenant);

    $stepsAfter = collect((new OnboardingChecklistWidget)->steps())
        ->firstWhere('key', 'team');

    expect($stepsAfter['done'])->toBeTrue();
});

it('لا يعطي رابطاً لصفحة المستخدم ممنوع منها', function (): void {
    actingAsRole('editor');

    $mail = collect((new OnboardingChecklistWidget)->steps())
        ->firstWhere('key', 'mail');

    // المحرّر مالوش manageMail — يبقى مفيش لينك بيودّيه لـ ٤٠٣.
    expect($mail['url'])->toBeNull();
});
