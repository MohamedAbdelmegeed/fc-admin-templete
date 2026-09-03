<?php

declare(strict_types=1);

namespace Src\Support\Presentation;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * الأب لكل مزوّدات السياقات.
 *
 * كل سياق بيسجّل ميجريشناته وترجماته وموارد Filament بتاعته من مساره
 * الخاص — عشان تفتح فولدر واحد وتفهم ميزة كاملة. (docs/01)
 */
abstract class ContextServiceProvider extends ServiceProvider
{
    /** اسم السياق زي ما هو في الـ namespace — مثال: Identity */
    abstract protected function contextName(): string;

    /** مسار مجلد السياق — دايماً __DIR__ من المزوّد نفسه */
    abstract protected function contextPath(): string;

    /**
     * اللوحات اللي السياق ده بيسجّل عليها موارده.
     *
     * @return list<string>
     */
    protected function panels(): array
    {
        return ['admin'];
    }

    /**
     * الـ Panel بيتبني جوه register() بتاع AdminPanelProvider، و
     * Panel::make() بيطبّق كولباكات configureUsing وقت الإنشاء — يعني
     * لو سجّلناها في boot() بتتأخر والموارد ماتظهرش. عشان كده هنا،
     * ومزوّدات السياقات مرتّبة قبل مزوّد اللوحة في bootstrap/providers.php.
     */
    public function register(): void
    {
        $this->registerFilamentComponents();
    }

    public function boot(): void
    {
        $this->bootMigrations();
        $this->bootTranslations();
        $this->bootRoutes();
    }

    protected function translationNamespace(): string
    {
        return strtolower($this->contextName());
    }

    protected function bootMigrations(): void
    {
        $path = $this->contextPath().'/Database/Migrations';

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    protected function bootTranslations(): void
    {
        $path = $this->contextPath().'/Lang';

        if (is_dir($path)) {
            $this->loadTranslationsFrom($path, $this->translationNamespace());
        }
    }

    protected function bootRoutes(): void
    {
        $path = $this->contextPath().'/Routes/web.php';

        if (is_file($path)) {
            $this->loadRoutesFrom($path);
        }
    }

    protected function registerFilamentComponents(): void
    {
        $base = $this->contextPath().'/Presentation/Filament';

        // الـ namespace مشتق من مكان المزوّد نفسه — مفيش نص مكرّر يتنسى
        // تحديثه لو السياق اتنقل.
        $namespace = (new ReflectionClass(static::class))->getNamespaceName().'\Presentation\Filament';

        $panels = $this->panels();

        Panel::configureUsing(function (Panel $panel) use ($base, $namespace, $panels): void {
            if (! in_array($panel->getId(), $panels, true)) {
                return;
            }

            if (is_dir($base.'/Resources')) {
                $panel->discoverResources(in: $base.'/Resources', for: $namespace.'\Resources');
            }

            if (is_dir($base.'/Pages')) {
                $panel->discoverPages(in: $base.'/Pages', for: $namespace.'\Pages');
            }

            if (is_dir($base.'/Widgets')) {
                $panel->discoverWidgets(in: $base.'/Widgets', for: $namespace.'\Widgets');
            }
        });
    }
}
