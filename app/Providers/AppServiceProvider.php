<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Tables\Table;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Src\Contexts\Settings\Domain\Settings\SecuritySettings;
use Src\Support\Application\Contracts\DiskResolver;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Infrastructure\Filesystem\SettingsDrivenDiskResolver;
use Src\Support\Infrastructure\Security\HtmlSanitizer;
use Src\Support\Infrastructure\Tenancy\CurrentTenantContext;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, CurrentTenantContext::class);
        $this->app->singleton(DiskResolver::class, SettingsDrivenDiskResolver::class);
        $this->app->singleton(HtmlSanitizer::class);

        $this->registerTelescope();
    }

    /**
     * Telescope في التطوير بس. في الإنتاج = تسريب بيانات + بطء،
     * عشان كده هو في dont-discover والتسجيل يدوي هنا. (docs/11 بند ٨)
     */
    private function registerTelescope(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        if (! class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            return;
        }

        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureUrls();
        $this->configurePasswords();
        $this->configureRateLimiting();
        $this->configureTables();

        Event::listen(Logout::class, function ($event): void {
            Log::warning('Auth Logout Event Fired', [
                'user' => $event->user?->getKey(),
                'url' => request()->url(),
                'session' => session()->all(),
                'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))->pluck('function', 'class')->toArray(),
            ]);
        });

        Event::listen(CurrentDeviceLogout::class, function ($event): void {
            Log::warning('CurrentDeviceLogout Event Fired', [
                'user' => $event->user?->getKey(),
                'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))->pluck('function', 'class')->toArray(),
            ]);
        });
    }

    private function configureModels(): void
    {
        // بيمنع mass assignment صامت ويكشف أي علاقة ناقصة الـ eager load
        // بدل ما تعدّي على شكل N+1 في الإنتاج.
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard(false);
    }

    private function configureUrls(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    private function configurePasswords(): void
    {
        Password::defaults(function (): Password {
            $minLength = (int) config('security.password.min_length', 12);
            $uncompromised = true;

            try {
                $settings = app(SecuritySettings::class);
                $minLength = $settings->password_min_length;
                $uncompromised = $settings->password_require_uncompromised;
            } catch (Throwable) {
                // الإعدادات لسه ماتعملتش — بنكمّل بقيم الكونفيج.
            }

            $rule = Password::min($minLength)->letters()->mixedCase()->numbers()->symbols();

            // الفحص ضد قواعد التسريبات بيعمل نداء شبكة — في الإنتاج بس.
            return $this->app->isProduction() && $uncompromised
                ? $rule->uncompromised()
                : $rule;
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request): array => [
            Limit::perMinute(5)->by($request->input('email').'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);

        RateLimiter::for('two-factor', fn (Request $request): Limit => Limit::perMinute(5)
            ->by((string) $request->session()->get('login.id')));

        RateLimiter::for('exports', fn (Request $request): Limit => Limit::perHour(10)
            ->by((string) ($request->user()?->getKey() ?? $request->ip())));

        RateLimiter::for('media-upload', fn (Request $request): Limit => Limit::perMinute(20)
            ->by((string) ($request->user()?->getKey() ?? $request->ip())));

        RateLimiter::for('search', fn (Request $request): Limit => Limit::perMinute(60)
            ->by((string) ($request->user()?->getKey() ?? $request->ip())));

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)
            ->by((string) ($request->user()?->getKey() ?? $request->ip())));
    }

    /**
     * الإعدادات الافتراضية الممتازة لكل الجداول — مرة واحدة بدل ما
     * تتكرر في ٦٠ مورد. متغيّرهاش في مورد فردي من غير تعليق بالسبب.
     * (docs/08 بند ١)
     */
    private function configureTables(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->defaultPaginationPageOption(25)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->persistFiltersInSession()
                ->persistSortInSession()
                ->persistSearchInSession()
                ->persistColumnSearchesInSession()
                ->deferLoading()
                ->deferFilters()
                ->searchOnBlur()
                ->striped()
                ->emptyStateHeading(fn (): string => __('table.empty.heading'))
                ->emptyStateDescription(fn (): string => __('table.empty.description'))
                ->emptyStateIcon('heroicon-o-inbox');
        });
    }
}
