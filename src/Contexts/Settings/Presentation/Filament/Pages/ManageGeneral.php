<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Src\Contexts\Settings\Domain\Settings\GeneralSettings;
use Src\Contexts\Settings\Presentation\Filament\Support\ManagedSettingsPage;
use Src\Contexts\Settings\Presentation\Filament\Support\SupportedLocales;

final class ManageGeneral extends ManagedSettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $slug = 'settings/general';

    protected static ?int $navigationSort = 10;

    protected static function ability(): string
    {
        return 'manageGeneral';
    }

    /** اسم التبويب — مش اسم مدخل القائمة؛ المدخل واحد اسمه «الإعدادات». */
    public static function tabLabel(): string
    {
        return __('settings::settings.pages.general');
    }

    public function getTitle(): string
    {
        return __('settings::settings.pages.general');
    }

    public function getSubheading(): string
    {
        return __('settings::settings.pages.general_help');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('settings::settings.sections.identity'))
                ->description(__('settings::settings.sections.identity_help'))
                ->columnSpanFull()
                ->schema([
                    Tabs::make('translations')
                        ->columnSpanFull()
                        ->tabs(SupportedLocales::tabs(fn (string $locale): array => [
                            TextInput::make("app_name.{$locale}")
                                ->label(__('settings::settings.fields.app_name'))
                                ->required($locale === config('app.fallback_locale'))
                                ->maxLength(120),

                            Textarea::make("app_description.{$locale}")
                                ->label(__('settings::settings.fields.app_description'))
                                ->rows(2)
                                ->maxLength(500),
                        ])),
                ]),

            Section::make(__('settings::settings.sections.contact'))
                ->description(__('settings::settings.sections.contact_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('support_email')
                        ->label(__('settings::settings.fields.support_email'))
                        ->email()
                        ->required()
                        ->maxLength(180),

                    TextInput::make('support_phone')
                        ->label(__('settings::settings.fields.support_phone'))
                        ->tel()
                        ->maxLength(40),
                ]),

            Section::make(__('settings::settings.sections.regional'))
                ->description(__('settings::settings.sections.regional_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    SupportedLocales::select('default_locale')
                        ->label(__('settings::settings.fields.default_locale'))
                        ->required()
                        ->helperText(__('settings::settings.help.default_locale')),

                    SupportedLocales::timezone('timezone')
                        ->label(__('settings::settings.fields.timezone'))
                        ->required(),
                ]),

            Section::make(__('settings::settings.sections.maintenance'))
                ->description(__('settings::settings.sections.maintenance_help'))
                ->columnSpanFull()
                ->schema([
                    Toggle::make('maintenance_mode')
                        ->label(__('settings::settings.fields.maintenance_mode'))
                        ->helperText(__('settings::settings.help.maintenance_mode'))
                        ->live(),

                    Tabs::make('maintenance_translations')
                        ->columnSpanFull()
                        ->visible(fn (callable $get): bool => (bool) $get('maintenance_mode'))
                        ->tabs(SupportedLocales::tabs(fn (string $locale): array => [
                            Textarea::make("maintenance_message.{$locale}")
                                ->label(__('settings::settings.fields.maintenance_message'))
                                ->rows(3)
                                ->maxLength(500),
                        ])),
                ]),
        ]);
    }
}
