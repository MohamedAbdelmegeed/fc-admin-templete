<?php

declare(strict_types=1);

use Src\Support\Infrastructure\Theming\ColorScaleGenerator;
use Src\Support\Infrastructure\Theming\Oklch;

it('يولّد سُلَّم كامل من درجة 600', function (): void {
    $scale = (new ColorScaleGenerator)->generate('#12454F');

    expect($scale)->toHaveKeys([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950])
        ->and($scale[600])->toBe('#12454F')
        ->and($scale[50])->toMatch('/^#[0-9A-F]{6}$/');
});

it('يفتّح تدريجياً كل ما الدرجة تقل', function (): void {
    $scale = (new ColorScaleGenerator)->generate('#12454F');

    $previous = 0.0;

    foreach ([900, 800, 700, 600, 500, 400, 300, 200, 100, 50] as $shade) {
        $luminance = Oklch::relativeLuminance($scale[$shade]);

        expect($luminance)->toBeGreaterThan($previous, "الدرجة {$shade} مش أفتح من اللي قبلها");

        $previous = $luminance;
    }
});

it('يقبل لون العلامة الافتراضي في فحص التباين', function (): void {
    expect((new ColorScaleGenerator)->passesContrast('#12454F'))->toBeTrue();
});

it('يرفض لوناً تباينه أقل من 4.5:1 على الأبيض', function (): void {
    $generator = new ColorScaleGenerator;

    expect($generator->passesContrast('#FF0000'))->toBeFalse()
        ->and($generator->contrastFailures('#FF0000'))->toHaveKey(600);
});

it('يحسب نسبة التباين حسب WCAG', function (): void {
    expect(round(Oklch::contrast('#000000', '#FFFFFF'), 1))->toBe(21.0)
        ->and(round(Oklch::contrast('#FFFFFF', '#FFFFFF'), 1))->toBe(1.0);
});
