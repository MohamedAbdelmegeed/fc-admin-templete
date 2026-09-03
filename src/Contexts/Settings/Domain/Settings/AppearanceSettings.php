<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class AppearanceSettings extends Settings
{
    /** درجة 600 بس — باقي السُّلَّم مُشتَق برمجياً. (docs/06 بند ٢) */
    public string $primary_color;

    public ?string $logo_light_path;

    public ?string $logo_dark_path;

    public ?string $favicon_path;

    public string $default_theme;        // light | dark | system

    public bool $allow_theme_switch;

    public string $sidebar_default;      // expanded | collapsed

    /** @var array<string, string> */
    public array $footer_text;

    public string $font_family;

    public static function group(): string
    {
        return 'appearance';
    }

    public function footer(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->footer_text[$locale]
            ?? $this->footer_text[config('app.fallback_locale')]
            ?? '';
    }
}
