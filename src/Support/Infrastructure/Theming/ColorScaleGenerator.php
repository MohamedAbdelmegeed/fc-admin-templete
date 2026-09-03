<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Theming;

/**
 * يولّد سُلَّم 50→950 من درجة 600 باستخدام OKLCH.
 *
 * المؤسسة بتضبط درجة 600 بس؛ الباقي مُشتَق برمجياً عشان أي لون علامة
 * يبقى ليه سُلَّم متسق. القيم معايرة على البترولي — راجعها لو غيّرنا
 * الأساس. (docs/06 بند ٢)
 */
final class ColorScaleGenerator
{
    /** @var array<int, float> */
    private const CHROMA_FALLOFF = [
        50 => 0.18, 100 => 0.32, 200 => 0.52, 300 => 0.72,
        400 => 0.92, 500 => 1.0, 600 => 1.0,
        700 => 0.94, 800 => 0.86, 900 => 0.74, 950 => 0.62,
    ];

    /**
     * @return array<int, string>
     */
    public function generate(string $hex): array
    {
        $base = Oklch::fromHex($hex);

        $lightness = [
            50 => 0.96, 100 => 0.90, 200 => 0.80, 300 => 0.68,
            400 => 0.55, 500 => 0.46, 600 => $base['l'],
            700 => $base['l'] * 0.83,
            800 => $base['l'] * 0.66,
            900 => $base['l'] * 0.48,
            950 => $base['l'] * 0.36,
        ];

        $scale = [];

        foreach ($lightness as $shade => $l) {
            // درجة 600 بترجع زي ما هي بالظبط — من غير أي تقريب في التحويل،
            // عشان اللي المستخدم اختاره يظهر حرفياً.
            $scale[$shade] = $shade === 600
                ? strtoupper('#'.ltrim(trim($hex), '#'))
                : Oklch::toHex($l, $base['c'] * self::CHROMA_FALLOFF[$shade], $base['h']);
        }

        return $scale;
    }

    /**
     * الدرجات 600 و700 لازم يعدّوا تباين ≥ 4.5:1 على أبيض — وإلا النص
     * الأبيض فوق الأزرار هيبقى غير مقروء. (معيار قبول docs/06 بند ٢)
     */
    public function passesContrast(string $hex): bool
    {
        return $this->contrastFailures($hex) === [];
    }

    /**
     * @return array<int, float> الدرجات اللي رسبت ونسبة تباينها
     */
    public function contrastFailures(string $hex): array
    {
        $scale = $this->generate($hex);
        $failures = [];

        foreach ([600, 700] as $shade) {
            $ratio = Oklch::contrast($scale[$shade], '#FFFFFF');

            if ($ratio < 4.5) {
                $failures[$shade] = round($ratio, 2);
            }
        }

        return $failures;
    }
}
