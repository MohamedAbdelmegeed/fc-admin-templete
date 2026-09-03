<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Application\DTOs;

/**
 * الإعدادات اللي الصفحة شايلاها في الفورم، في شكل يعرف يحوّل نفسه
 * لكونفيج بريد. الـ Action مش بيقرا من الفورم ولا من الإعدادات المحفوظة —
 * بياخد ده وخلاص، فينفع يتنادى من أي مكان.
 */
final readonly class MailConfiguration
{
    public function __construct(
        public string $driver,
        public string $host,
        public int $port,
        public string $username,
        public string $password,
        public string $encryption,
        public string $fromAddress,
        public string $fromName,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromFormData(array $data, string $fallbackFromName): self
    {
        $fromName = $data['from_name'] ?? [];

        return new self(
            driver: (string) ($data['driver'] ?? 'smtp'),
            host: (string) ($data['host'] ?? ''),
            port: (int) ($data['port'] ?? 587),
            username: (string) ($data['username'] ?? ''),
            password: (string) ($data['password'] ?? ''),
            encryption: (string) ($data['encryption'] ?? 'tls'),
            fromAddress: (string) ($data['from_address'] ?? ''),
            fromName: is_array($fromName)
                ? (string) ($fromName[app()->getLocale()] ?? $fromName[config('app.fallback_locale')] ?? $fallbackFromName)
                : (string) ($fromName ?: $fallbackFromName),
        );
    }

    /**
     * @param  array<string, mixed>  $mailConfig
     * @return array<string, mixed>
     */
    public function applyTo(array $mailConfig): array
    {
        $mailConfig['default'] = $this->driver;
        $mailConfig['from'] = ['address' => $this->fromAddress, 'name' => $this->fromName];

        $mailConfig['mailers']['smtp'] = [
            ...($mailConfig['mailers']['smtp'] ?? []),
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            // 'none' في الواجهة معناها من غير تشفير — Symfony بيتوقع null.
            'encryption' => $this->encryption === 'none' ? null : $this->encryption,
        ];

        return $mailConfig;
    }
}
