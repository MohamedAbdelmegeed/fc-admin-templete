<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Src\Contexts\Identity\Presentation\Filament\Pages\Login;
use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Contexts\Settings\Domain\Settings\GeneralSettings;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Infrastructure\Theming\BrandLogoResolver;
use Src\Support\Infrastructure\Theming\ThemeColorResolver;
use Src\Support\Presentation\Filament\Navigation\NavigationGroup;
use Src\Support\Presentation\Http\Middleware\AssignRequestContext;
use Src\Support\Presentation\Http\Middleware\InitializeTenantContext;
use Src\Support\Presentation\Http\Middleware\SetLocale;
use Throwable;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->profile(isSimple: false)

            // ══════════════════ تعدد المستأجرين ══════════════════
            ->tenant(Tenant::class, slugAttribute: 'slug')
            ->tenantRoutePrefix('t')
            ->tenantMenu()
            ->searchableTenantMenu()

            // ══════════════════ الهوية البصرية ══════════════════
            ->colors(fn (): array => app(ThemeColorResolver::class)->palette())
            // الخط إعداد مش ثابت — بيتغيّر من صفحة المظهر من غير ديبلوي.
            ->font(fn (): string => $this->fontFamily(), provider: GoogleFontProvider::class)
            ->monoFont('IBM Plex Mono', provider: GoogleFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName(fn (): string => $this->brandName())
            ->brandLogo(fn (): ?string => app(BrandLogoResolver::class)->url())
            ->darkModeBrandLogo(fn (): ?string => app(BrandLogoResolver::class)->url(dark: true))
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => app(BrandLogoResolver::class)->faviconUrl())
            ->darkMode(fn (): bool => (bool) $this->appearanceValue('allow_theme_switch', true))
            ->defaultThemeMode($this->defaultThemeMode())

            // ══════════════════ التنقّل ══════════════════
            ->navigationGroups($this->navigationGroups())
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups()
            ->sidebarWidth('17rem')
            ->collapsedSidebarWidth('4.5rem')
            ->maxContentWidth(Width::Full)
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce('400ms')
            ->unsavedChangesAlerts()
            ->spa()

            // ══════════════════ الإشعارات ══════════════════
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')

            // ══════════════════ الاكتشاف ══════════════════
            // الموارد بتتسجّل من مزوّد كل سياق (docs/01) — مفيش
            // discoverResources من app/ هنا عن قصد.
            ->pages([Dashboard::class])

            // ══════════════════ الميدلوير ══════════════════
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
                AssignRequestContext::class,
            ])
            // ⚠️ ممنوع isPersistent: true على القائمة دي. Livewire بيعيد
            // تشغيل الميدلوير الـ persistent جوه طلب /livewire/update على
            // «طلب وهمي» كوكيزه **متفكوكة التشفير** بالفعل، فـ EncryptCookies
            // بيفشل في فكّها تاني ويعتبرها null، وبعدها StartSession بيفتح
            // جلسة **جديدة فاضية** على نفس الـ Store (سينجلتون) — فالجلسة
            // الأصلية بتتبدّل، والرد بيرجّع كوكي جلسة فاضية، والمستخدم
            // بيتسجّل خروجه من أول تنقّل بعد كده.
            // SetLocale و AssignRequestContext شغّالين أصلاً على طلبات
            // Livewire لأنهم مضافين على مجموعة web في bootstrap/app.php.
            ->authMiddleware([
                Authenticate::class,
            ])
            // persistent عشان يشتغل على طلبات Livewire كمان — من غير كده
            // سياق المستأجر بيضيع في نص التفاعل. (docs/03 بند ٤)
            ->tenantMiddleware([
                InitializeTenantContext::class,
            ], isPersistent: true);
    }

    public function boot(): void
    {
        $this->registerRenderHooks();
    }

    /**
     * الـ render hooks هي اللي بتخلّي اللوحة «بتاعتنا» من غير ما نعدّل
     * أي Blade من Filament. (docs/07 بند ٩)
     */
    private function registerRenderHooks(): void
    {
        // متغيّرات لون العلامة — بتتغيّر لكل مستأجر، فمينفعش تبقى في ملف الثيم.
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('theme.brand-variables', [
                'scale' => app(ThemeColorResolver::class)->scale(),
            ])->render(),
        );

        // الفوتر والحقوق — النص من الإعدادات ومترجم، مش مكتوب في الـ Blade.
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => view('theme.footer', [
                'appearance' => $this->appearance(),
                'locale' => app()->getLocale(),
            ])->render(),
        );

        // مؤشر البيئة — عشان محدش يعدّل في الإنتاج وهو فاكر نفسه على staging.
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_FOOTER,
            fn (): string => view('theme.environment', [
                'environment' => app()->environment(),
            ])->render(),
        );

        // مبدّل اللغة في التوب-بار.
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn (): string => view('theme.locale-switcher', [
                'locales' => (array) config('app.supported_locales', []),
                'current' => app()->getLocale(),
            ])->render(),
        );
    }

    /**
     * المجموعات من الـ Enum — مرتّبة ومفلترة بالصلاحية، فمحدش بيشوف
     * مجموعة فاضية. (docs/07 بند ٢)
     *
     * @return list<FilamentNavigationGroup>
     */
    private function navigationGroups(): array
    {
        return collect(NavigationGroup::cases())
            ->filter(fn (NavigationGroup $group): bool => $group->ability() === null
                || $this->canAccessNavigationGroup($group))
            ->sortBy(fn (NavigationGroup $group): int => $group->sort())
            ->map(fn (NavigationGroup $group): FilamentNavigationGroup => FilamentNavigationGroup::make()
                ->label($group->getLabel())
                ->icon($group->getIcon())
                ->collapsible())
            ->values()
            ->all();
    }

    /**
     * Gate::allows() بينفّذ وقت تسجيل اللوحة — يعني في أي أمر artisan
     * مش بس طلبات HTTP، بما فيها composer dump-autoload وقت بناء
     * الصورة، لما الـ APP_KEY لسه مش موجود عمداً (docker/production
     * مالوش .env). من غير الـ try/catch ده، الاستثناء بيوقف البناء كله.
     */
    private function canAccessNavigationGroup(NavigationGroup $group): bool
    {
        try {
            return Gate::allows($group->ability());
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * خط مش في القائمة البيضاء = خط ممكن ما يدعمش العربية، فبنرجع
     * للافتراضي بدل ما نص اللوحة كلها يتكسر.
     */
    private function fontFamily(): string
    {
        $family = (string) $this->appearanceValue('font_family', '');
        $allowed = (array) config('branding.fonts', []);

        return in_array($family, $allowed, true)
            ? $family
            : (string) config('branding.fallback_font');
    }

    private function brandName(): string
    {
        try {
            return app(GeneralSettings::class)->name();
        } catch (Throwable) {
            return (string) config('app.name');
        }
    }

    private function appearance(): ?AppearanceSettings
    {
        try {
            return app(AppearanceSettings::class);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * ⚠️ spatie/settings بيحمّل الخصائص **كسول** — يعني app(Settings::class)
     * بينجح والاستعلام بيحصل عند أول قراءة لخاصية. فأي try/catch لازم
     * يلف **قراءة الخاصية** مش إنشاء الكائن، وإلا الاستثناء بيهرب.
     * ده بيحصل فعلاً في الاختبارات وقبل أول migrate.
     */
    private function appearanceValue(string $property, mixed $default): mixed
    {
        try {
            return app(AppearanceSettings::class)->{$property} ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }

    private function defaultThemeMode(): ThemeMode
    {
        return match ($this->appearanceValue('default_theme', 'system')) {
            'light' => ThemeMode::Light,
            'dark' => ThemeMode::Dark,
            default => ThemeMode::System,
        };
    }
}
