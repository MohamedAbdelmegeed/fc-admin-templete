<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Src\Support\Infrastructure\Authorization\InvariantRegistry;
use Src\Support\Infrastructure\Authorization\PermissionBuilder;

/**
 * المكان الوحيد — مع الكلاس الأساسي Policy — اللي مسموح فيه فحص صلاحية
 * مباشر (hasRole / hasPermissionTo). فيه اختبار معماري بيفرض ده.
 */
final class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionBuilder::class);
        $this->app->singleton(InvariantRegistry::class);
    }

    public function boot(): void
    {
        $this->discoverPolicies();
        $this->definePageAndWidgetGates();
        $this->grantSuperAdminAccess();
    }

    /**
     * الموديلات بتاعتنا مش في App\Models، فالاكتشاف التلقائي مش هيلاقيها.
     * الاصطلاح: Domain\Models\X → Infrastructure\Policies\XPolicy.
     */
    private function discoverPolicies(): void
    {
        Gate::guessPolicyNamesUsing(static function (string $model): string {
            return Str::of($model)
                ->replace('\\Domain\\Models\\', '\\Infrastructure\\Policies\\')
                ->append('Policy')
                ->toString();
        });
    }

    /**
     * الصفحات والودجتس مالهاش موديل — بنعرّفها كـ Gates من الكونفيج بدل
     * ما الكود يسأل عن نص صلاحية مباشرة. (docs/19 بند ٧)
     */
    private function definePageAndWidgetGates(): void
    {
        $abilities = [
            ...array_keys((array) config('authorization.pages', [])),
            ...array_keys((array) config('authorization.widgets', [])),
        ];

        foreach ($abilities as $ability) {
            Gate::define($ability, function (Authenticatable $user) use ($ability): Response {
                return $this->hasPermission($user, $ability)
                    ? Response::allow()
                    : Response::deny(__('authorization.denied.missing_permission', [
                        'permission' => permission_label($ability),
                    ]));
            });
        }
    }

    /**
     * فحص صلاحية بيشتغل حتى **قبل** اختيار المؤسسة.
     *
     * ⚠️ مصيدة حقيقية: الأدوار مربوطة بالمؤسسة (teams)، لكن Filament
     * بينادي canAccessPanel() بعد تسجيل الدخول مباشرة — وقتها لسه مفيش
     * مؤسسة مختارة، ففريق الصلاحيات = null وأي فحص بيرجع false،
     * فالمستخدم مايقدرش يدخل أصلاً عشان يختار مؤسسته.
     *
     * الحل: لو مفيش سياق مؤسسة، نسأل «هل معاه الصلاحية دي في أي مؤسسة
     * هو عضو فيها؟». ده مش تخفيف للعزل — العزل بيتطبّق بعد الاختيار.
     */
    private function hasPermission(Authenticatable $user, string $ability): bool
    {
        $guard = (string) config('authorization.guard');
        $registrar = app(PermissionRegistrar::class);

        if ($registrar->getPermissionsTeamId() !== null) {
            return $user->hasPermissionTo($ability, $guard);
        }

        return $this->inEachTenant(
            $user,
            static fn (): bool => $user->hasPermissionTo($ability, $guard),
        );
    }

    private function hasRole(Authenticatable $user, string $role): bool
    {
        if (! method_exists($user, 'hasRole')) {
            return false;
        }

        $registrar = app(PermissionRegistrar::class);

        if ($registrar->getPermissionsTeamId() !== null) {
            return $user->hasRole($role);
        }

        return $this->inEachTenant($user, static fn (): bool => $user->hasRole($role));
    }

    /**
     * بيشغّل الفحص داخل كل مؤسسة المستخدم عضو فيها، وبيقف عند أول نجاح.
     * بيرجّع الفريق الأصلي دايماً — حتى لو الفحص رمى استثناء.
     */
    private function inEachTenant(Authenticatable $user, callable $check): bool
    {
        if (! method_exists($user, 'tenants')) {
            return false;
        }

        $registrar = app(PermissionRegistrar::class);
        $previousTeam = $registrar->getPermissionsTeamId();

        try {
            foreach ($user->tenants()->pluck('tenants.id') as $tenantId) {
                $registrar->setPermissionsTeamId($tenantId);

                // العلاقة مكاشة لكل فريق — لازم تتفضّى مع كل تبديل.
                $user->unsetRelation('roles')->unsetRelation('permissions');

                if ($check()) {
                    return true;
                }
            }

            return false;
        } finally {
            $registrar->setPermissionsTeamId($previousTeam);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    /**
     * المدير العام بيتجاوز **الصلاحيات** — مش **قواعد السلامة**.
     *
     * لو رجّعنا true على طول، الـ Policy مش بتتنفّذ أصلاً، فالمدير العام
     * يقدر يحذف نفسه أو ينتحل مدير عام تاني. القدرات المحمية بـ invariants()
     * بترجّع null عشان الـ Policy تكمّل — والـ permission() جواها هتعدّي
     * عادي لأنه معاه كل الصلاحيات. (docs/19 بند ٥)
     */
    private function grantSuperAdminAccess(): void
    {
        Gate::before(function (Authenticatable $user, string $ability, array $arguments = []): ?bool {
            if (! $this->hasRole($user, (string) config('authorization.super_admin_role'))) {
                return null;   // ← null مش false، عشان الـ Policy تكمّل
            }

            if (app(InvariantRegistry::class)->guards($ability, $arguments[0] ?? null)) {
                return null;
            }

            return true;
        });
    }
}
