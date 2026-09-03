<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Theming;

use Illuminate\Support\Facades\Storage;
use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Support\Application\Contracts\DiskResolver;
use Throwable;

/** بيرجّع رابط اللوجو حسب الوضع (فاتح/داكن) — أو null فيرجع Filament للاسم. */
final class BrandLogoResolver
{
    public function __construct(private readonly DiskResolver $disks) {}

    public function url(bool $dark = false): ?string
    {
        try {
            $settings = app(AppearanceSettings::class);
        } catch (Throwable) {
            return null;
        }

        $path = $dark
            ? ($settings->logo_dark_path ?? $settings->logo_light_path)
            : $settings->logo_light_path;

        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk($this->disks->for('branding'))->url($path);
    }

    public function faviconUrl(): ?string
    {
        try {
            $path = app(AppearanceSettings::class)->favicon_path;
        } catch (Throwable) {
            return null;
        }

        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk($this->disks->for('branding'))->url($path);
    }
}
