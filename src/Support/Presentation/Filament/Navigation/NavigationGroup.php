<?php

declare(strict_types=1);

namespace Src\Support\Presentation\Filament\Navigation;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * المجموعات كـ Enum مش strings — عشان الترتيب والأيقونات والترجمة
 * والصلاحية في مكان واحد. أقصى ٧±٢ مجموعة ظاهرة. (docs/07 بند ٢)
 */
enum NavigationGroup: string implements HasIcon, HasLabel
{
    case Dashboard = 'dashboard';
    case Identity = 'identity';
    case Content = 'content';
    case Operations = 'operations';
    case Reports = 'reports';
    case Tenancy = 'tenancy';
    case System = 'system';

    public function getLabel(): string
    {
        return __("navigation.groups.{$this->value}");
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Dashboard => 'heroicon-o-home',
            self::Identity => 'heroicon-o-users',
            self::Content => 'heroicon-o-document-text',
            self::Operations => 'heroicon-o-cog-6-tooth',
            self::Reports => 'heroicon-o-chart-bar',
            self::Tenancy => 'heroicon-o-building-office-2',
            self::System => 'heroicon-o-server-stack',
        };
    }

    public function sort(): int
    {
        return match ($this) {
            self::Dashboard => 0,
            self::Identity => 10,
            self::Content => 20,
            self::Operations => 30,
            self::Reports => 40,
            self::Tenancy => 80,
            self::System => 90,
        };
    }

    /** القدرة المطلوبة لظهور المجموعة كلها — Gate معرّف، مش نص صلاحية. */
    public function ability(): ?string
    {
        return match ($this) {
            self::System => 'access.system',
            self::Tenancy => 'access.tenancy',
            default => null,
        };
    }
}
