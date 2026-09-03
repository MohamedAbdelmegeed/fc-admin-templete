<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Tenancy;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * وصول منخفض المستوى لجدول المستأجرين.
 *
 * بيستخدم الـ Query Builder مش الموديل عن قصد: الكلاس ده بيتنادى من جوه
 * CurrentTenantContext، واستدعاء الموديل هناك بيعمل حلقة اعتماد
 * (الموديل بيسأل السياق، والسياق بيسأل الموديل).
 */
final class TenantRegistry
{
    /** @return list<int> */
    public static function activeTenantIds(): array
    {
        try {
            return DB::table('tenants')
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();
        } catch (Throwable) {
            // الجدول لسه ماتعملش (أول migrate) — مفيش مستأجرين.
            return [];
        }
    }
}
