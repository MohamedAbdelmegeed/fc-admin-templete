<?php

declare(strict_types=1);

namespace Src\Contexts\Settings;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Gate;
use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Contexts\Settings\Domain\Settings\GeneralSettings;
use Src\Contexts\Settings\Domain\Settings\MailSettings;
use Src\Contexts\Settings\Domain\Settings\NotificationSettings;
use Src\Contexts\Settings\Domain\Settings\SecuritySettings;
use Src\Contexts\Settings\Domain\Settings\StorageSettings;
use Src\Contexts\Settings\Infrastructure\Policies\SettingsPolicy;
use Src\Contexts\Settings\Presentation\Filament\Support\SettingsTabs;
use Src\Support\Presentation\ContextServiceProvider;

final class SettingsServiceProvider extends ContextServiceProvider
{
    /**
     * كلاسات الإعدادات اللي السياسة بتتسجّل عليها.
     *
     * @var list<class-string>
     */
    private const SETTINGS = [
        GeneralSettings::class,
        AppearanceSettings::class,
        StorageSettings::class,
        MailSettings::class,
        NotificationSettings::class,
        SecuritySettings::class,
    ];

    protected function contextName(): string
    {
        return 'Settings';
    }

    protected function contextPath(): string
    {
        return __DIR__;
    }

    public function boot(): void
    {
        parent::boot();

        $this->registerPolicies();
        $this->registerSettingsTabs();
    }

    /**
     * شريط التبويبات بيتحقن بـ render hook مقصور على صفحات الإعدادات —
     * من غير ما نعدّل Blade بتاعة Filament ولا نكرّر الشريط في كل صفحة.
     *
     * ⚠️ الـ hook مابياخدش الصفحة كوسيط، فبنستنتجها من مسار الطلب:
     * كل صفحة إعدادات ليها slug ثابت، والمقارنة بتبقى على المسار بعد
     * بادئة اللوحة والمؤسسة.
     */
    private function registerSettingsTabs(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            function (): string {
                $current = SettingsTabs::currentPage();

                if ($current === null) {
                    return '';
                }

                return view('settings.tabs', [
                    'tabs' => SettingsTabs::visible($current),
                ])->render();
            },
            scopes: SettingsTabs::all(),
        );
    }

    /**
     * الاكتشاف بالاصطلاح بيدوّر في Domain\Models — والإعدادات في
     * Domain\Settings، فالتسجيل هنا صريح. من غير ده أي can() على كلاس
     * إعدادات هيرجع false من غير سبب واضح.
     */
    private function registerPolicies(): void
    {
        foreach (self::SETTINGS as $settings) {
            Gate::policy($settings, SettingsPolicy::class);
        }
    }
}
