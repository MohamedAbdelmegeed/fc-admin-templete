<?php

declare(strict_types=1);

namespace Src\Support\Application\Contracts;

/** المصدر الوحيد لمعرفة المستأجر الحالي من أي طبقة. */
interface TenantContext
{
    public function id(): ?int;

    public function set(?int $tenantId): void;

    public function isBypassed(): bool;

    /** للأوامر والتقارير عبر كل المستأجرين — استخدمه بحذر شديد. */
    public function withoutScope(callable $callback): mixed;

    public function forEachTenant(callable $callback): void;
}
