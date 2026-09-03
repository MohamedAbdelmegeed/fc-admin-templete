<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Theming;

use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Support\Application\Contracts\TenantContext;
use Throwable;

/**
 * طبقتان (docs/06 بند ١):
 *   طبقة العلامة  — بتتغيّر لكل مستأجر (لون واحد: درجة 600)
 *   طبقة المنتج   — ثابتة (المحايدات وألوان الحالة)
 *
 * ممنوع استخدام لون العلامة كلون حالة أو العكس: فيه مستأجر لونه أحمر،
 * ولو الأحمر عندك معناه «خطر» هتبقى كارثة.
 */
final class ThemeColorResolver
{
    /** المحايدات البترولية — طبقة منتج، متتغيّرش لأي مستأجر. */
    private const BRAND_NEUTRALS = [
        50 => '#F7F9F9',
        100 => '#F2F5F4',
        200 => '#E8EEED',
        300 => '#D8E1E0',
        400 => '#A9B7B6',
        500 => '#7E9291',
        600 => '#667877',
        700 => '#4E6260',
        800 => '#2A3D3F',
        900 => '#16292B',
        950 => '#0C1415',
    ];

    /** ألوان الحالة — قيم الوضع الفاتح، مفحوصة التباين. (docs/06 بند ٣) */
    private const STATUS = [
        'success' => '#0F7A3D',
        'warning' => '#96550A',
        'danger' => '#B4232E',
        'info' => '#0E6E8C',
    ];

    public function __construct(private readonly ColorScaleGenerator $generator) {}

    /**
     * @return array<string, array<int, string>>
     */
    public function palette(): array
    {
        return [
            'primary' => $this->scale(),
            'gray' => self::BRAND_NEUTRALS,
            ...array_map(
                fn (string $hex): array => $this->generator->generate($hex),
                self::STATUS,
            ),
        ];
    }

    /**
     * سُلَّم لون العلامة الحالي — مكاش بمفتاح فيه المستأجر واللون، عشان
     * ما نحسبوش في كل طلب. (docs/06 بند ٦)
     *
     * @return array<int, string>
     */
    public function scale(): array
    {
        $color = $this->primaryColor();
        $tenantId = app(TenantContext::class)->id() ?? 'global';

        return cache()->remember(
            "theme:scale:{$tenantId}:".md5($color),
            now()->addDay(),
            fn (): array => $this->generator->generate($color),
        );
    }

    public function primaryColor(): string
    {
        try {
            return app(AppearanceSettings::class)->primary_color;
        } catch (Throwable) {
            // الإعدادات لسه ماتعملتش — بنرجع للبترولي الافتراضي.
            return '#12454F';
        }
    }

    /** @return array<string, string> */
    public function statusColors(): array
    {
        return self::STATUS;
    }
}
