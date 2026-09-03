<?php

declare(strict_types=1);

use Src\Contexts\Settings\Presentation\Filament\Pages\ManageGeneral;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageMail;
use Src\Contexts\Settings\Presentation\Filament\Support\SettingsTabs;

/*
|--------------------------------------------------------------------------
| الإعدادات مدخل واحد وجوّاه تبويبات
|--------------------------------------------------------------------------
| ٦ مداخل منفصلة كانت بتاكل نص السايدبار وتكسر قاعدة ٧±٢ (docs/07).
| الصفحات فضلت زي ما هي بمساراتها وسياساتها — التبويب تجربة استخدام بس.
*/

it('يعرض مدخلاً واحداً للإعدادات في القائمة', function (): void {
    $user = actingAsRole('super_admin');
    $tenant = currentTenant();

    $html = $this->actingAs($user->fresh())
        ->get("/admin/t/{$tenant->slug}")
        ->getContent();

    preg_match_all('/fi-sidebar-item-label[^>]*>\s*([^<]+)/', (string) $html, $matches);
    $labels = array_map('trim', $matches[1]);

    expect($labels)->toContain(__('settings::settings.pages.index'))
        ->and($labels)->not->toContain(__('settings::settings.pages.mail'))
        ->and($labels)->not->toContain(__('settings::settings.pages.security'));
});

it('يرسم شريط التبويبات فوق كل صفحة إعدادات ويعلّم على الحالية', function (): void {
    $user = actingAsRole('super_admin');
    $tenant = currentTenant();

    $html = (string) $this->actingAs($user->fresh())
        ->get("/admin/t/{$tenant->slug}/settings/mail")
        ->assertSuccessful()
        ->getContent();

    preg_match_all('/fc-settings-tabs__item[^>]*>\s*([^<]+)/', $html, $matches);

    expect(array_map('trim', $matches[1]))->toHaveCount(6);

    preg_match('/fc-settings-tabs__item--active[^>]*>\s*([^<]+)/', $html, $active);

    expect(trim($active[1] ?? ''))->toBe(__('settings::settings.pages.mail'));
});

it('يخفي التبويبات الممنوعة بدل ما تودّي لصفحة مرفوضة', function (): void {
    $viewer = actingAsRole('viewer');

    $labels = array_column(SettingsTabs::visible(ManageGeneral::class), 'label');

    // الـ viewer قراءة فقط — مالوش أي قدرة manage* على الإعدادات.
    expect($labels)->toBeEmpty();
});

it('ينقل مدخل القائمة لأول صفحة مسموح بيها', function (): void {
    actingAsRole('super_admin');

    expect(SettingsTabs::navigationPage())->toBe(ManageGeneral::class);
    expect(ManageGeneral::shouldRegisterNavigation())->toBeTrue();
    expect(ManageMail::shouldRegisterNavigation())->toBeFalse();
});
