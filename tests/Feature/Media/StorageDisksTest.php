<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use Src\Support\Application\Contracts\DiskResolver;

/*
|--------------------------------------------------------------------------
| الديسكات المعرّفة لازم تطابق اللي الإعدادات بتطلبه
|--------------------------------------------------------------------------
| الإعدادات كانت بتشاور على 's3-private' وهو مش معرّف في filesystems،
| فأي رفع لمجموعة خاصة كان بيقع بـ
| «Disk [s3-private] does not have a config».
| الاختبار ده بيقفل الفجوة دي بشكل عام: أي اسم ديسك تختاره الإعدادات
| لازم يكون له تعريف.
*/

it('كل ديسك تطلبه الإعدادات له تعريف في filesystems', function (): void {
    $resolver = app(DiskResolver::class);
    $disks = array_keys((array) config('filesystems.disks'));

    $required = collect(['documents', 'contracts', 'avatar', 'logo', 'default'])
        ->flatMap(fn (string $collection): array => [
            $resolver->for($collection),
            $resolver->forConversions($collection),
        ])
        ->unique()
        ->values();

    $missing = $required->reject(fn (string $disk): bool => in_array($disk, $disks, true));

    expect($missing->all())->toBeEmpty(
        'ديسكات مطلوبة وغير معرّفة: '.$missing->implode(', '),
    );
});

it('المجموعات الخاصة تروح على ديسك خاص والعامة على العام', function (): void {
    $resolver = app(DiskResolver::class);

    expect($resolver->isPrivate('documents'))->toBeTrue()
        ->and($resolver->isPrivate('contracts'))->toBeTrue()
        ->and($resolver->isPrivate('avatar'))->toBeFalse();

    expect($resolver->for('documents'))->not->toBe($resolver->for('avatar'));
});

it('الديسك الخاص بلا رابط دائم', function (): void {
    $private = app(DiskResolver::class)->for('documents');

    // وجود 'url' بيخلي Storage::url() يرجّع رابط دائم لملف سري.
    expect(config("filesystems.disks.{$private}.url"))->toBeNull()
        ->and(config("filesystems.disks.{$private}.visibility"))->toBe('private');
});

it('الديسك الخاص مش نفس باكت الديسك العام', function (): void {
    $resolver = app(DiskResolver::class);

    $publicBucket = config('filesystems.disks.'.$resolver->for('avatar').'.bucket');
    $privateBucket = config('filesystems.disks.'.$resolver->for('documents').'.bucket');

    // الباكت العام عليه anonymous download محلياً — لو الملفات الخاصة
    // اتحطّت فيه بتبقى مقروءة بلينك مباشر لأي حد.
    expect($privateBucket)->not->toBe($publicBucket);
});

it('محوّل الرؤية بتاع S3 متثبّت', function (): void {
    // من غير league/flysystem-aws-s3-v3 أي كتابة على s3 بتقع بـ
    // «Class League\Flysystem\AwsS3V3\PortableVisibilityConverter not found».
    expect(class_exists(PortableVisibilityConverter::class))->toBeTrue();
});

it('يكتب ويقرا فعلاً من الديسكين', function (): void {
    foreach (['avatar', 'documents'] as $collection) {
        $disk = Storage::disk(app(DiskResolver::class)->for($collection));
        $path = '.fc-test-'.bin2hex(random_bytes(4));

        $disk->put($path, 'hello');

        expect($disk->get($path))->toBe('hello');

        $disk->delete($path);
    }
})->group('storage');
