<?php

declare(strict_types=1);

namespace Tests\Support;

use Src\Contexts\Tenancy\Domain\Models\Tenant;

/**
 * حامل المستأجر الحالي أثناء الاختبار.
 *
 * ⚠️ متستخدمش `test()->tenant` — الخاصية دي مابتفضلش بين استدعاءات
 * الدوال المساعدة في Pest، فكل استدعاء كان بينشئ مستأجر جديد وبيكسر
 * أي اختبار بيعتمد على إن المستخدمين في نفس المؤسسة.
 */
final class TenantRegistry
{
    private static ?Tenant $tenant = null;

    public static function current(): Tenant
    {
        return self::$tenant ??= Tenant::factory()->create();
    }

    public static function set(?Tenant $tenant): void
    {
        self::$tenant = $tenant;
    }

    public static function reset(): void
    {
        self::$tenant = null;
    }
}
