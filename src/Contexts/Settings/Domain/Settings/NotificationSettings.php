<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class NotificationSettings extends Settings
{
    public bool $broadcast_enabled;

    public int $database_polling_seconds;

    public int $prune_read_after_days;

    /** @var list<string> */
    public array $globally_disabled_channels;

    public static function group(): string
    {
        return 'notifications';
    }
}
