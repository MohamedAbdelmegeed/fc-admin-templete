<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Src\Contexts\Settings\Domain\Settings\StorageSettings;
use Src\Contexts\Settings\Presentation\Filament\Support\ManagedSettingsPage;

final class ManageStorage extends ManagedSettingsPage
{
    protected static string $settings = StorageSettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $slug = 'settings/storage';

    protected static ?int $navigationSort = 30;

    protected static function ability(): string
    {
        return 'manageStorage';
    }

    /** اسم التبويب — مش اسم مدخل القائمة؛ المدخل واحد اسمه «الإعدادات». */
    public static function tabLabel(): string
    {
        return __('settings::settings.pages.storage');
    }

    public function getTitle(): string
    {
        return __('settings::settings.pages.storage');
    }

    public function getSubheading(): string
    {
        return __('settings::settings.pages.storage_help');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('settings::settings.sections.disks'))
                ->description(__('settings::settings.sections.disks_help'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    self::diskSelect('default_disk')
                        ->label(__('settings::settings.fields.default_disk'))
                        ->helperText(__('settings::settings.help.default_disk')),

                    self::diskSelect('private_disk')
                        ->label(__('settings::settings.fields.private_disk'))
                        ->helperText(__('settings::settings.help.private_disk')),

                    self::diskSelect('conversions_disk')
                        ->label(__('settings::settings.fields.conversions_disk'))
                        ->helperText(__('settings::settings.help.conversions_disk')),
                ]),

            Section::make(__('settings::settings.sections.collections'))
                ->description(__('settings::settings.sections.collections_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TagsInput::make('private_collections')
                        ->label(__('settings::settings.fields.private_collections'))
                        ->helperText(__('settings::settings.help.private_collections'))
                        ->reorderable(),

                    KeyValue::make('collection_disks')
                        ->label(__('settings::settings.fields.collection_disks'))
                        ->keyLabel(__('settings::settings.fields.collection'))
                        ->valueLabel(__('settings::settings.fields.disk'))
                        ->helperText(__('settings::settings.help.collection_disks')),
                ]),

            Section::make(__('settings::settings.sections.uploads'))
                ->description(__('settings::settings.sections.uploads_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('max_upload_size_kb')
                        ->label(__('settings::settings.fields.max_upload_size_kb'))
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->suffix(__('settings::settings.units.kb'))
                        // الحد ده لازم يفضل أقل من upload_max_filesize في
                        // php.ini و client_max_body_size في nginx — رفعه هنا
                        // لوحده بيدّي المستخدم خطأ 413 مبهم. (docs/20 بند ٧)
                        ->helperText(__('settings::settings.help.max_upload_size_kb')),

                    TextInput::make('temporary_url_minutes')
                        ->label(__('settings::settings.fields.temporary_url_minutes'))
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->suffix(__('settings::settings.units.minutes'))
                        ->helperText(__('settings::settings.help.temporary_url_minutes')),

                    TagsInput::make('allowed_mimes')
                        ->label(__('settings::settings.fields.allowed_mimes'))
                        ->columnSpanFull()
                        ->helperText(__('settings::settings.help.allowed_mimes')),
                ]),
        ]);
    }

    /** الديسكات من config/filesystems — مفيش قائمة مكتوبة بالإيد تروح تقدم. */
    private static function diskSelect(string $name): Select
    {
        $disks = array_keys((array) config('filesystems.disks', []));

        return Select::make($name)
            ->options(array_combine($disks, $disks))
            ->native(false)
            ->required();
    }
}
