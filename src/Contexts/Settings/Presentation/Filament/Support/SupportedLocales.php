<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Support;

use DateTimeZone;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * الحقول المترجمة بتتكرّر في كل صفحة إعدادات. القائمة نفسها بتيجي من
 * app.supported_locales — إضافة لغة جديدة للكونفيج بتظهر في كل الصفحات
 * من غير ما نلمس ولا صفحة. (docs/10 بند ٣)
 */
final class SupportedLocales
{
    /** @return list<string> */
    public static function all(): array
    {
        return array_values((array) config('app.supported_locales', ['en']));
    }

    /**
     * تبويب لكل لغة، ومحتواه بيتبني بالكولباك — عشان كل صفحة تحدد
     * حقولها من غير ما تكرّر منطق التبويبات.
     *
     * @param  callable(string): array<int, mixed>  $fields
     * @return list<Tab>
     */
    public static function tabs(callable $fields): array
    {
        return array_map(
            static fn (string $locale): Tab => Tab::make($locale)
                ->label(__("common.locales.{$locale}"))
                ->schema($fields($locale)),
            self::all(),
        );
    }

    /** قائمة اللغات المدعومة كحقل اختيار. */
    public static function select(string $name): Select
    {
        return Select::make($name)
            ->options(array_combine(
                self::all(),
                array_map(static fn (string $l): string => __("common.locales.{$l}"), self::all()),
            ))
            ->native(false);
    }

    /** كل المناطق الزمنية — القائمة طويلة، فالبحث إلزامي مش تحسين. */
    public static function timezone(string $name): Select
    {
        return Select::make($name)
            ->options(array_combine(
                DateTimeZone::listIdentifiers(),
                DateTimeZone::listIdentifiers(),
            ))
            ->searchable()
            ->native(false);
    }
}
