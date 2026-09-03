<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Src\Contexts\Settings\Application\Actions\SendTestEmail;
use Src\Contexts\Settings\Application\DTOs\MailConfiguration;
use Src\Contexts\Settings\Domain\Settings\MailSettings;
use Src\Contexts\Settings\Presentation\Filament\Support\ManagedSettingsPage;
use Src\Contexts\Settings\Presentation\Filament\Support\SupportedLocales;
use Throwable;

final class ManageMail extends ManagedSettingsPage
{
    protected static string $settings = MailSettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $slug = 'settings/mail';

    protected static ?int $navigationSort = 40;

    protected static function ability(): string
    {
        return 'manageMail';
    }

    /** اسم التبويب — مش اسم مدخل القائمة؛ المدخل واحد اسمه «الإعدادات». */
    public static function tabLabel(): string
    {
        return __('settings::settings.pages.mail');
    }

    public function getTitle(): string
    {
        return __('settings::settings.pages.mail');
    }

    public function getSubheading(): string
    {
        return __('settings::settings.pages.mail_help');
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTest')
                ->label(__('settings::settings.mail.send_test'))
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->visible(fn (): bool => $this->canEdit())
                ->schema([
                    TextInput::make('recipient')
                        ->label(__('settings::settings.mail.test_recipient'))
                        ->email()
                        ->required()
                        ->default(fn (): string => (string) auth()->user()?->email),
                ])
                ->action(function (array $data): void {
                    $this->sendTestEmail((string) $data['recipient']);
                }),
        ];
    }

    /**
     * بتستخدم قيم الفورم الحالية — عشان تجرّب **قبل** ما تحفظ. لو حفظنا
     * الأول عشان نجرّب، إعدادات غلط بتوقّف كل رسائل النظام لحد ما حد
     * ياخد باله.
     */
    private function sendTestEmail(string $recipient): void
    {
        try {
            app(SendTestEmail::class)->handle(
                MailConfiguration::fromFormData(
                    $this->form->getState(),
                    (string) config('app.name'),
                ),
                $recipient,
            );

            Notification::make()
                ->success()
                ->title(__('settings::settings.mail.test_sent'))
                ->body(__('settings::settings.mail.test_sent_body', ['email' => $recipient]))
                ->send();
        } catch (Throwable $exception) {
            // الرسالة الخام من SMTP هي المفيدة هنا فعلاً — «فشل الإرسال»
            // لوحدها بتخلّي الواحد يقعد يخمّن.
            Notification::make()
                ->danger()
                ->title(__('settings::settings.mail.test_failed'))
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('settings::settings.sections.transport'))
                ->description(__('settings::settings.sections.transport_help'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('driver')
                        ->label(__('settings::settings.fields.mail_driver'))
                        ->options(self::driverOptions())
                        ->native(false)
                        ->required()
                        ->live()
                        ->helperText(__('settings::settings.help.mail_driver')),

                    Select::make('encryption')
                        ->label(__('settings::settings.fields.encryption'))
                        ->options(self::encryptionOptions())
                        ->native(false)
                        ->required()
                        ->visible(fn (callable $get): bool => $get('driver') === 'smtp')
                        // بتفضل تتحفظ حتى وهي مخفية — من غير كده تبديل
                        // وسيلة الإرسال بيمسح بيانات SMTP المحفوظة.
                        ->dehydratedWhenHidden(),

                    TextInput::make('host')
                        ->label(__('settings::settings.fields.host'))
                        ->required(fn (callable $get): bool => $get('driver') === 'smtp')
                        ->visible(fn (callable $get): bool => $get('driver') === 'smtp')
                        // بتفضل تتحفظ حتى وهي مخفية — من غير كده تبديل
                        // وسيلة الإرسال بيمسح بيانات SMTP المحفوظة.
                        ->dehydratedWhenHidden()
                        ->maxLength(180),

                    TextInput::make('port')
                        ->label(__('settings::settings.fields.port'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(65535)
                        ->required(fn (callable $get): bool => $get('driver') === 'smtp')
                        ->visible(fn (callable $get): bool => $get('driver') === 'smtp')
                        // بتفضل تتحفظ حتى وهي مخفية — من غير كده تبديل
                        // وسيلة الإرسال بيمسح بيانات SMTP المحفوظة.
                        ->dehydratedWhenHidden(),

                    TextInput::make('username')
                        ->label(__('settings::settings.fields.username'))
                        ->visible(fn (callable $get): bool => $get('driver') === 'smtp')
                        // بتفضل تتحفظ حتى وهي مخفية — من غير كده تبديل
                        // وسيلة الإرسال بيمسح بيانات SMTP المحفوظة.
                        ->dehydratedWhenHidden()
                        ->maxLength(180),

                    TextInput::make('password')
                        ->label(__('settings::settings.fields.password'))
                        ->password()
                        ->revealable()
                        ->visible(fn (callable $get): bool => $get('driver') === 'smtp')
                        // بتفضل تتحفظ حتى وهي مخفية — من غير كده تبديل
                        // وسيلة الإرسال بيمسح بيانات SMTP المحفوظة.
                        ->dehydratedWhenHidden()
                        // مخزّنة مشفّرة في جدول الإعدادات (MailSettings::encrypted).
                        ->helperText(__('settings::settings.help.mail_password'))
                        ->maxLength(180),
                ]),

            Section::make(__('settings::settings.sections.sender'))
                ->description(__('settings::settings.sections.sender_help'))
                ->columnSpanFull()
                ->schema([
                    TextInput::make('from_address')
                        ->label(__('settings::settings.fields.from_address'))
                        ->email()
                        ->required()
                        ->maxLength(180)
                        ->helperText(__('settings::settings.help.from_address')),

                    Tabs::make('from_name_translations')
                        ->columnSpanFull()
                        ->tabs(SupportedLocales::tabs(fn (string $locale): array => [
                            TextInput::make("from_name.{$locale}")
                                ->label(__('settings::settings.fields.from_name'))
                                ->required($locale === config('app.fallback_locale'))
                                ->maxLength(120),
                        ])),
                ]),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function driverOptions(): array
    {
        $drivers = array_values(array_intersect(
            (array) config('mail.configurable_mailers', []),
            array_keys((array) config('mail.mailers', [])),
        ));

        return array_combine(
            $drivers,
            array_map(static fn (string $d): string => __("settings::settings.mail_drivers.{$d}"), $drivers),
        );
    }

    /**
     * @return array<string, string>
     */
    private static function encryptionOptions(): array
    {
        $values = (array) config('mail.encryptions', []);

        return array_combine(
            $values,
            array_map(static fn (string $v): string => __("settings::settings.encryptions.{$v}"), $values),
        );
    }
}
