<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class MailSettings extends Settings
{
    public string $driver;

    public string $host;

    public int $port;

    public string $username;

    public string $password;

    public string $encryption;

    public string $from_address;

    /** @var array<string, string> */
    public array $from_name;

    public static function group(): string
    {
        return 'mail';
    }

    /**
     * أي إعداد فيه سر لازم يكون هنا. (docs/05 بند ٢)
     *
     * @return list<string>
     */
    public static function encrypted(): array
    {
        return ['password'];
    }

    public function fromName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->from_name[$locale]
            ?? $this->from_name[config('app.fallback_locale')]
            ?? (string) config('app.name');
    }
}
