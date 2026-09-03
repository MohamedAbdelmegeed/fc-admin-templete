<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Src\Support\Infrastructure\Authorization\PermissionBuilder;

it('كل مفتاح ترجمة عربي له مقابل إنجليزي والعكس', function (): void {
    foreach (File::files(lang_path('ar')) as $file) {
        $englishPath = lang_path('en/'.$file->getFilename());

        expect(File::exists($englishPath))->toBeTrue("ناقص: en/{$file->getFilename()}");

        $arabicKeys = array_keys(Arr::dot(require $file->getPathname()));
        $englishKeys = array_keys(Arr::dot(require $englishPath));

        $missing = array_diff($arabicKeys, $englishKeys);
        $extra = array_diff($englishKeys, $arabicKeys);

        expect($missing)->toBeEmpty("ناقص في en/{$file->getFilename()}: ".implode(', ', $missing));
        expect($extra)->toBeEmpty("زيادة في en/{$file->getFilename()}: ".implode(', ', $extra));
    }
});

it('كل ملفات ترجمة السياقات متطابقة بين اللغتين', function (): void {
    foreach (File::directories(base_path('src/Contexts')) as $context) {
        $arabicDirectory = $context.'/Lang/ar';

        if (! File::isDirectory($arabicDirectory)) {
            continue;
        }

        foreach (File::files($arabicDirectory) as $file) {
            $englishPath = $context.'/Lang/en/'.$file->getFilename();

            expect(File::exists($englishPath))->toBeTrue("ناقص: {$englishPath}");

            $missing = array_diff(
                array_keys(Arr::dot(require $file->getPathname())),
                array_keys(Arr::dot(require $englishPath)),
            );

            expect($missing)->toBeEmpty("ناقص في {$englishPath}: ".implode(', ', $missing));
        }
    }
});

it('كل صلاحية في الكونفيج لها تسمية مترجمة في اللغتين', function (): void {
    $permissions = app(PermissionBuilder::class)->allPermissionNames();

    expect($permissions)->not->toBeEmpty();

    foreach (['ar', 'en'] as $locale) {
        app()->setLocale($locale);

        foreach ($permissions as $permission) {
            $label = permission_label($permission);

            expect($label)
                ->not->toContain('authorization.', "الصلاحية {$permission} غير مترجمة في {$locale}")
                ->not->toBeEmpty();
        }
    }
});

it('يتعامل مع الجمع العربي بصيغه الستة', function (): void {
    app()->setLocale('ar');

    $expected = [
        0 => 'مفيش نتائج',
        1 => 'نتيجة واحدة',
        2 => 'نتيجتان',
        5 => '5 نتائج',
        11 => '11 نتيجة',
        100 => '100 نتيجة',
    ];

    foreach ($expected as $count => $text) {
        expect(trans_choice('common.results', $count, ['count' => fc_number($count)]))->toBe($text);
    }
});

it('يعرض أرقاماً لاتينية في الواجهة حتى بالعربي', function (): void {
    app()->setLocale('ar');

    expect(fc_number(1234567))->toBe('1,234,567')
        ->and(fc_number(1234.5, 2))->toBe('1,234.50');
});
