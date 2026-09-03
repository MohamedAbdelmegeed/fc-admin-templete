<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Src\Contexts\Settings\Domain\Settings\NotificationSettings;
use Src\Contexts\Settings\Presentation\Filament\Support\ManagedSettingsPage;

final class ManageNotifications extends ManagedSettingsPage
{
    protected static string $settings = NotificationSettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $slug = 'settings/notifications';

    protected static ?int $navigationSort = 50;

    protected static function ability(): string
    {
        return 'manageNotifications';
    }

    /** اسم التبويب — مش اسم مدخل القائمة؛ المدخل واحد اسمه «الإعدادات». */
    public static function tabLabel(): string
    {
        return __('settings::settings.pages.notifications');
    }

    public function getTitle(): string
    {
        return __('settings::settings.pages.notifications');
    }

    public function getSubheading(): string
    {
        return __('settings::settings.pages.notifications_help');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('settings::settings.sections.delivery'))
                ->description(__('settings::settings.sections.delivery_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('broadcast_enabled')
                        ->label(__('settings::settings.fields.broadcast_enabled'))
                        ->helperText(__('settings::settings.help.broadcast_enabled')),

                    TextInput::make('database_polling_seconds')
                        ->label(__('settings::settings.fields.database_polling_seconds'))
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(600)
                        ->required()
                        ->suffix(__('settings::settings.units.seconds'))
                        // كل مستخدم مفتوح عنده اللوحة بيعمل طلب كل المدة دي.
                        // ٥ ثواني × ٢٠٠ مستخدم = ٤٠ طلب/ثانية على الفاضي.
                        ->helperText(__('settings::settings.help.database_polling_seconds')),
                ]),

            Section::make(__('settings::settings.sections.channels'))
                ->description(__('settings::settings.sections.channels_help'))
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('globally_disabled_channels')
                        ->label(__('settings::settings.fields.globally_disabled_channels'))
                        ->options(self::channelOptions())
                        ->columns(3)
                        // القنوات الإجبارية في الكتالوج بتعدّي غصب عن ده —
                        // إشعار «اتغيّرت كلمة مرورك» لازم يوصل مهما حصل.
                        ->helperText(__('settings::settings.help.globally_disabled_channels')),
                ]),

            Section::make(__('settings::settings.sections.retention'))
                ->description(__('settings::settings.sections.retention_help'))
                ->columnSpanFull()
                ->schema([
                    TextInput::make('prune_read_after_days')
                        ->label(__('settings::settings.fields.prune_read_after_days'))
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->suffix(__('settings::settings.units.days'))
                        ->helperText(__('settings::settings.help.prune_read_after_days')),
                ]),
        ]);
    }

    /**
     * القنوات مشتقّة من كتالوج الإشعارات نفسه — قناة جديدة في الكتالوج
     * بتظهر هنا لوحدها. (docs/09 بند ٨)
     *
     * @return array<string, string>
     */
    private static function channelOptions(): array
    {
        $channels = collect((array) config('notifications.catalog', []))
            ->flatMap(fn (array $notification): array => $notification['channels'] ?? [])
            ->unique()
            ->sort()
            ->values()
            ->all();

        return array_combine(
            $channels,
            array_map(static fn (string $c): string => __("settings::settings.channels.{$c}"), $channels),
        );
    }
}
