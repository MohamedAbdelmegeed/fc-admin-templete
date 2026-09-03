<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Domain\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasColor, HasIcon, HasLabel
{
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';

    public function getLabel(): string
    {
        return __("identity::identity.user.status.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Invited => 'warning',
            self::Active => 'success',
            self::Suspended => 'danger',
        };
    }

    /** اللون ما يقفش لوحده — دايماً معاه أيقونة. (docs/08 بند ٢) */
    public function getIcon(): string
    {
        return match ($this) {
            self::Invited => 'heroicon-o-envelope',
            self::Active => 'heroicon-o-check-circle',
            self::Suspended => 'heroicon-o-no-symbol',
        };
    }

    public function canSignIn(): bool
    {
        return $this === self::Active;
    }
}
