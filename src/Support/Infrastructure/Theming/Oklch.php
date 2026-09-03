<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Theming;

use InvalidArgumentException;

/**
 * تحويلات لون بين sRGB و OKLCH.
 *
 * ليه OKLCH مش HSL؟ لأن HSL مش متجانس إدراكياً — تفتيح لون فيه بيغيّر
 * السطوع المُدرَك بشكل غير متوقع، فالسُّلَّم بيطلع غير متساوي. OKLCH
 * بيحافظ على السطوع المُدرَك، فالسُّلَّم بيبقى متدرّج بانتظام.
 */
final class Oklch
{
    /** @return array{l: float, c: float, h: float} */
    public static function fromHex(string $hex): array
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        $lr = self::toLinear($r / 255);
        $lg = self::toLinear($g / 255);
        $lb = self::toLinear($b / 255);

        $l = 0.4122214708 * $lr + 0.5363325363 * $lg + 0.0514459929 * $lb;
        $m = 0.2119034982 * $lr + 0.6806995451 * $lg + 0.1073969566 * $lb;
        $s = 0.0883024619 * $lr + 0.2817188376 * $lg + 0.6299787005 * $lb;

        $l = self::cbrt($l);
        $m = self::cbrt($m);
        $s = self::cbrt($s);

        $labL = 0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s;
        $labA = 1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s;
        $labB = 0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s;

        $hue = rad2deg(atan2($labB, $labA));

        return [
            'l' => $labL,
            'c' => sqrt($labA ** 2 + $labB ** 2),
            'h' => $hue < 0 ? $hue + 360 : $hue,
        ];
    }

    public static function toHex(float $lightness, float $chroma, float $hue): string
    {
        $labA = $chroma * cos(deg2rad($hue));
        $labB = $chroma * sin(deg2rad($hue));

        $l = ($lightness + 0.3963377774 * $labA + 0.2158037573 * $labB) ** 3;
        $m = ($lightness - 0.1055613458 * $labA - 0.0638541728 * $labB) ** 3;
        $s = ($lightness - 0.0894841775 * $labA - 1.2914855480 * $labB) ** 3;

        $r = 4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
        $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
        $b = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

        return sprintf(
            '#%02X%02X%02X',
            self::toChannel($r),
            self::toChannel($g),
            self::toChannel($b),
        );
    }

    /** نسبة التباين حسب WCAG 2.1 — بترجّع رقم بين 1 و 21. */
    public static function contrast(string $hexA, string $hexB): float
    {
        $a = self::relativeLuminance($hexA);
        $b = self::relativeLuminance($hexB);

        [$light, $dark] = $a > $b ? [$a, $b] : [$b, $a];

        return ($light + 0.05) / ($dark + 0.05);
    }

    public static function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        return 0.2126 * self::toLinear($r / 255)
            + 0.7152 * self::toLinear($g / 255)
            + 0.0722 * self::toLinear($b / 255);
    }

    /** @return array{int, int, int} */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            throw new InvalidArgumentException("لون غير صالح: {$hex}");
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function toLinear(float $channel): float
    {
        return $channel <= 0.04045
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }

    private static function toChannel(float $linear): int
    {
        $srgb = $linear <= 0.0031308
            ? 12.92 * $linear
            : 1.055 * ($linear ** (1 / 2.4)) - 0.055;

        return (int) max(0, min(255, (int) round($srgb * 255)));
    }

    private static function cbrt(float $value): float
    {
        return $value < 0 ? -((-$value) ** (1 / 3)) : $value ** (1 / 3);
    }
}
