<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Src\Contexts\Settings\Domain\Settings\SecuritySettings;
use Src\Contexts\Settings\Presentation\Filament\Support\ManagedSettingsPage;

final class ManageSecurity extends ManagedSettingsPage
{
    protected static string $settings = SecuritySettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $slug = 'settings/security';

    protected static ?int $navigationSort = 60;

    protected static function ability(): string
    {
        return 'manageSecurity';
    }

    /** اسم التبويب — مش اسم مدخل القائمة؛ المدخل واحد اسمه «الإعدادات». */
    public static function tabLabel(): string
    {
        return __('settings::settings.pages.security');
    }

    public function getTitle(): string
    {
        return __('settings::settings.pages.security');
    }

    public function getSubheading(): string
    {
        return __('settings::settings.pages.security_help');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('settings::settings.sections.passwords'))
                ->description(__('settings::settings.sections.passwords_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('password_min_length')
                        ->label(__('settings::settings.fields.password_min_length'))
                        ->numeric()
                        // الحد الأدنى في config/security هو أرضية صلبة —
                        // الإعداد يقدر يشدّ مش يرخّي. (docs/12)
                        ->minValue((int) config('security.password.min_length'))
                        ->maxValue(128)
                        ->required()
                        ->helperText(__('settings::settings.help.password_min_length', [
                            'floor' => (string) config('security.password.min_length'),
                        ])),

                    TextInput::make('password_history_count')
                        ->label(__('settings::settings.fields.password_history_count'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(24)
                        ->required()
                        ->helperText(__('settings::settings.help.password_history_count')),

                    TextInput::make('password_expires_days')
                        ->label(__('settings::settings.fields.password_expires_days'))
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->suffix(__('settings::settings.units.days'))
                        ->helperText(__('settings::settings.help.password_expires_days')),

                    Toggle::make('password_require_uncompromised')
                        ->label(__('settings::settings.fields.password_require_uncompromised'))
                        ->helperText(__('settings::settings.help.password_require_uncompromised')),
                ]),

            Section::make(__('settings::settings.sections.two_factor'))
                ->description(__('settings::settings.sections.two_factor_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('two_factor_required_roles')
                        ->label(__('settings::settings.fields.two_factor_required_roles'))
                        ->options(self::roleOptions())
                        ->columns(2)
                        ->helperText(__('settings::settings.help.two_factor_required_roles')),

                    TextInput::make('two_factor_grace_period_days')
                        ->label(__('settings::settings.fields.two_factor_grace_period_days'))
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->suffix(__('settings::settings.units.days'))
                        ->helperText(__('settings::settings.help.two_factor_grace_period_days')),
                ]),

            Section::make(__('settings::settings.sections.sessions'))
                ->description(__('settings::settings.sections.sessions_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('session_lifetime_minutes')
                        ->label(__('settings::settings.fields.session_lifetime_minutes'))
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(20160)
                        ->required()
                        ->suffix(__('settings::settings.units.minutes'))
                        ->helperText(__('settings::settings.help.session_lifetime_minutes')),

                    TextInput::make('impersonation_max_minutes')
                        ->label(__('settings::settings.fields.impersonation_max_minutes'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(480)
                        ->required()
                        ->suffix(__('settings::settings.units.minutes'))
                        ->helperText(__('settings::settings.help.impersonation_max_minutes')),

                    Toggle::make('force_https')
                        ->label(__('settings::settings.fields.force_https'))
                        ->helperText(__('settings::settings.help.force_https')),
                ]),
        ]);
    }

    /**
     * الأدوار من الكونفيج — نفس مصدر المزامنة، فمفيش دور بيظهر هنا
     * ومش موجود في النظام.
     *
     * @return array<string, string>
     */
    private static function roleOptions(): array
    {
        $roles = array_keys((array) config('authorization.roles', []));

        return array_combine(
            $roles,
            array_map(static fn (string $role): string => __("authorization.roles.{$role}"), $roles),
        );
    }
}
