<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class GeneralSettings extends Settings
{
    /** @var array<string, string> */
    public array $app_name;

    /** @var array<string, string> */
    public array $app_description;

    public string $support_email;

    public string $support_phone;

    public string $default_locale;

    public string $timezone;

    public bool $maintenance_mode;

    /** @var array<string, string> */
    public array $maintenance_message;

    public static function group(): string
    {
        return 'general';
    }

    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->app_name[$locale]
            ?? $this->app_name[config('app.fallback_locale')]
            ?? (string) config('app.name');
    }
}
