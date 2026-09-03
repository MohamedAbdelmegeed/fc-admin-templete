<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Pages;

use BackedEnum;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Contexts\Settings\Presentation\Filament\Support\ManagedSettingsPage;
use Src\Contexts\Settings\Presentation\Filament\Support\SupportedLocales;
use Src\Support\Application\Contracts\DiskResolver;
use Src\Support\Infrastructure\Theming\ColorScaleGenerator;

final class ManageAppearance extends ManagedSettingsPage
{
    protected static string $settings = AppearanceSettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $slug = 'settings/appearance';

    protected static ?int $navigationSort = 20;

    protected static function ability(): string
    {
        return 'manageAppearance';
    }

    /** اسم التبويب — مش اسم مدخل القائمة؛ المدخل واحد اسمه «الإعدادات». */
    public static function tabLabel(): string
    {
        return __('settings::settings.pages.appearance');
    }

    public function getTitle(): string
    {
        return __('settings::settings.pages.appearance');
    }

    public function getSubheading(): string
    {
        return __('settings::settings.pages.appearance_help');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('settings::settings.sections.brand'))
                ->description(__('settings::settings.sections.brand_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    ColorPicker::make('primary_color')
                        ->label(__('settings::settings.fields.primary_color'))
                        ->hex()
                        ->required()
                        ->helperText(__('settings::settings.help.primary_color'))
                        // لون تباينه ضعيف بيخلّي النص الأبيض فوق الأزرار غير
                        // مقروء — بنرفضه وقت الحفظ مش بعد ما اللوحة تبوظ.
                        ->rule(self::contrastRule()),

                    Select::make('font_family')
                        ->label(__('settings::settings.fields.font'))
                        ->options(self::fontOptions())
                        ->native(false)
                        ->required()
                        ->helperText(__('settings::settings.help.font')),
                ]),

            Section::make(__('settings::settings.sections.logos'))
                ->description(__('settings::settings.sections.logos_help'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    self::imageUpload('logo_light_path', 'logo')
                        ->label(__('settings::settings.fields.logo_light'))
                        ->helperText(__('settings::settings.help.logo_light', [
                            'size' => (string) config('branding.logo.max_size_kb'),
                        ])),

                    self::imageUpload('logo_dark_path', 'logo')
                        ->label(__('settings::settings.fields.logo_dark'))
                        ->helperText(__('settings::settings.help.logo_dark')),

                    self::imageUpload('favicon_path', 'favicon')
                        ->label(__('settings::settings.fields.favicon'))
                        ->helperText(__('settings::settings.help.favicon')),
                ]),

            Section::make(__('settings::settings.sections.layout'))
                ->description(__('settings::settings.sections.layout_help'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('default_theme')
                        ->label(__('settings::settings.fields.default_theme'))
                        ->options(self::keyedOptions('branding.themes', 'themes'))
                        ->native(false)
                        ->required(),

                    Select::make('sidebar_default')
                        ->label(__('settings::settings.fields.sidebar_default'))
                        ->options(self::keyedOptions('branding.sidebar_states', 'sidebar'))
                        ->native(false)
                        ->required(),

                    Toggle::make('allow_theme_switch')
                        ->label(__('settings::settings.fields.allow_theme_switch'))
                        ->helperText(__('settings::settings.help.allow_theme_switch')),
                ]),

            Section::make(__('settings::settings.sections.footer'))
                ->description(__('settings::settings.sections.footer_help'))
                ->columnSpanFull()
                ->schema([
                    Tabs::make('footer_translations')
                        ->columnSpanFull()
                        ->tabs(SupportedLocales::tabs(fn (string $locale): array => [
                            TextInput::make("footer_text.{$locale}")
                                ->label(__('settings::settings.fields.footer_text'))
                                ->maxLength(200),
                        ])),
                ]),
        ]);
    }

    /**
     * الرفع بيروح للديسك اللي DiskResolver بيقرره وقت التشغيل — الصفحة
     * مش عارفة s3 من local. (docs/04)
     */
    private static function imageUpload(string $name, string $kind): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->directory(config('branding.media_collection'))
            ->disk(fn (): string => app(DiskResolver::class)->for((string) config('branding.media_collection')))
            ->acceptedFileTypes((array) config("branding.{$kind}.accepted_types"))
            ->maxSize((int) config("branding.{$kind}.max_size_kb"))
            ->imageEditor()
            ->downloadable()
            ->openable();
    }

    /**
     * @return array<string, string>
     */
    private static function fontOptions(): array
    {
        $fonts = (array) config('branding.fonts', []);

        return array_combine($fonts, $fonts);
    }

    /**
     * القيم من الكونفيج والتسميات من ملفات الترجمة — مفيش نص مكتوب هنا.
     *
     * @return array<string, string>
     */
    private static function keyedOptions(string $configKey, string $translationKey): array
    {
        $values = (array) config($configKey, []);

        return array_combine(
            $values,
            array_map(
                static fn (string $value): string => __("settings::settings.{$translationKey}.{$value}"),
                $values,
            ),
        );
    }

    private static function contrastRule(): Closure
    {
        return static function (): Closure {
            return static function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $failures = app(ColorScaleGenerator::class)->contrastFailures($value);

                if ($failures !== []) {
                    $fail(__('authorization.denied.settings.contrast_failed', [
                        'ratio' => (string) reset($failures),
                    ]));
                }
            };
        };
    }
}
