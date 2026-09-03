<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Tenancy;

use Filament\Facades\Filament;
use Filament\FilamentManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;
use Src\Support\Application\Contracts\TenantContext;
use Throwable;

/**
 * خدمة واحدة تعرف المستأجر الحالي من أي مكان — وبتزامن كل اللي بيعتمد عليه:
 * فرق spatie/permission، بادئة الكاش، وسياق اللوج. (docs/03 بند ٣)
 */
final class CurrentTenantContext implements TenantContext
{
    private ?int $tenantId = null;

    private bool $bypassed = false;

    public function id(): ?int
    {
        return $this->tenantId ?? $this->tenantIdFromPanel();
    }

    public function set(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->syncDependents($tenantId);
    }

    public function isBypassed(): bool
    {
        return $this->bypassed;
    }

    /** للأوامر والتقارير عبر كل المستأجرين — استخدمه بحذر شديد. */
    public function withoutScope(callable $callback): mixed
    {
        $previous = $this->bypassed;
        $this->bypassed = true;

        try {
            return $callback();
        } finally {
            $this->bypassed = $previous;
        }
    }

    public function forEachTenant(callable $callback): void
    {
        $previous = $this->tenantId;

        // بنجمع الـ IDs الأول بره الحلقة — عشان تبديل السياق جوه الحلقة
        // ما يأثرش على الـ cursor المفتوح.
        $tenantIds = $this->withoutScope(
            fn (): array => TenantRegistry::activeTenantIds(),
        );

        try {
            foreach ($tenantIds as $tenantId) {
                $this->set($tenantId);
                $callback($tenantId);
            }
        } finally {
            $this->set($previous);
        }
    }

    private function tenantIdFromPanel(): ?int
    {
        if (! app()->bound(FilamentManager::class)) {
            return null;
        }

        try {
            $key = Filament::getTenant()?->getKey();
        } catch (Throwable) {
            // بره سياق لوحة (CLI، طابور، اختبار) — مفيش مستأجر من الواجهة.
            return null;
        }

        return $key === null ? null : (int) $key;
    }

    private function syncDependents(?int $tenantId): void
    {
        // ١. فرق spatie/permission — لازم كمان نمسح الكاش، لأن المزوّد بيقراه
        //    أثناء boot قبل ما ميدلوير المستأجر يشتغل. (docs/03 بند ٤)
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenantId);
        $registrar->forgetCachedPermissions();

        // ٢. بادئة الكاش. تغيير الكونفيج لوحده مش كفاية — الستور المحلول
        //    بيبقى ماسك البادئة القديمة، فلازم purge عشان يتبني من جديد.
        config(['cache.prefix' => 'fc_t'.($tenantId ?? 'global')]);

        foreach (array_unique(array_filter([
            config('cache.default'),
            config('permission.cache.store'),
        ])) as $store) {
            if ($store === 'default') {
                continue;
            }

            try {
                Cache::purge($store);
            } catch (Throwable) {
                // ستور مش معرّف في بيئة الاختبار — مش مشكلة.
            }
        }

        // ٣. سياق اللوج — كل سطر لوج بيبقى فيه tenant_id. (docs/11 بند ٢)
        Log::shareContext(['tenant_id' => $tenantId]);
    }
}
