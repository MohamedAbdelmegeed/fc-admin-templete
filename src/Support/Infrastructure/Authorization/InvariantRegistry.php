<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Authorization;

use Illuminate\Support\Facades\Gate;
use Src\Support\Domain\Authorization\Policy;
use Throwable;

/**
 * بيجاوب على سؤال واحد: هل القدرة دي محمية بقاعدة سلامة على الموديل ده؟
 *
 * Gate::before بيستخدمه عشان يعرف امتى **مايتخطاش** الـ Policy للمدير العام.
 * (docs/19 بند ٥)
 */
final class InvariantRegistry
{
    public function guards(string $ability, mixed $argument): bool
    {
        if ($argument === null) {
            return false;
        }

        try {
            $policy = Gate::getPolicyFor($argument);
        } catch (Throwable) {
            return false;
        }

        return $policy instanceof Policy && $policy->isInvariant($ability);
    }
}
