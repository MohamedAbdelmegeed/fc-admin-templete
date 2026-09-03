<?php

declare(strict_types=1);

use Carbon\CarbonInterface;

/*
|--------------------------------------------------------------------------
| مسارات
|--------------------------------------------------------------------------
*/

if (! function_exists('src_path')) {
    function src_path(string $path = ''): string
    {
        return base_path('src'.($path !== '' ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

/*
|--------------------------------------------------------------------------
| التفويض
|--------------------------------------------------------------------------
*/

if (! function_exists('permission_label')) {
    /**
     * اسم الصلاحية بشكل مقروء ومترجم.
     *
     * 'view_any.users'        → «عرض القائمة — المستخدمون»
     * 'access.health'         → «صفحة صحة النظام»
     * 'widget.stats_overview' → «ودجت الإحصائيات»
     */
    function permission_label(string $permission): string
    {
        $separator = (string) config('authorization.separator', '.');

        if (array_key_exists($permission, (array) config('authorization.pages', []))) {
            return __("authorization.pages.{$permission}");
        }

        if (array_key_exists($permission, (array) config('authorization.widgets', []))) {
            return __("authorization.widgets.{$permission}");
        }

        if (! str_contains($permission, $separator)) {
            return $permission;
        }

        [$action, $resource] = explode($separator, $permission, 2);

        return __("authorization.actions.{$action}").' — '.__("authorization.resources.{$resource}");
    }
}

/*
|--------------------------------------------------------------------------
| الأرقام والتواريخ (docs/10 بند ٦)
|--------------------------------------------------------------------------
| أرقام لاتينية (0–9) في الواجهة دايماً — حتى بالعربي. الأرقام الهندية
| للمستندات المطبوعة بس.
*/

if (! function_exists('fc_number')) {
    function fc_number(int|float $value, int $decimals = 0): string
    {
        return number_format($value, $decimals, '.', ',');
    }
}

if (! function_exists('fc_currency')) {
    function fc_currency(int|float $amount, ?string $currency = null): string
    {
        $currency ??= (string) config('app.currency', 'EGP');

        return __("common.currency.{$currency}", ['amount' => fc_number($amount, 2)]);
    }
}

if (! function_exists('fc_date')) {
    function fc_date(?CarbonInterface $date, string $format = 'd M Y'): string
    {
        return $date?->locale(app()->getLocale())->translatedFormat($format) ?? '—';
    }
}

if (! function_exists('fc_datetime')) {
    function fc_datetime(?CarbonInterface $date, string $format = 'd M Y — H:i'): string
    {
        return fc_date($date, $format);
    }
}
