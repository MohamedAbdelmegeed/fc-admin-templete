<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class SecuritySettings extends Settings
{
    public int $password_min_length;

    public bool $password_require_uncompromised;

    public int $password_history_count;

    public int $password_expires_days;          // 0 = معطّل

    /** @var list<string> */
    public array $two_factor_required_roles;

    public int $two_factor_grace_period_days;

    public int $session_lifetime_minutes;

    public int $impersonation_max_minutes;

    public bool $force_https;

    public static function group(): string
    {
        return 'security';
    }
}
