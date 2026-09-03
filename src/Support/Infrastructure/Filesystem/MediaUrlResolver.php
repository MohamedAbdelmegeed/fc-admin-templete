<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Filesystem;

use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Src\Support\Application\Contracts\DiskResolver;
use Throwable;

/** الملف الخاص رابطه مؤقت دايماً — مفيش رابط دائم لمحتوى خاص. (docs/20 بند ٧) */
final class MediaUrlResolver
{
    public function __construct(private readonly DiskResolver $disks) {}

    public function url(Media $media, string $conversion = ''): string
    {
        if (! $this->disks->isPrivate($media->collection_name)) {
            return $conversion !== '' ? $media->getUrl($conversion) : $media->getUrl();
        }

        $minutes = (int) config('media-library.temporary_url_minutes', 10);

        try {
            return $media->getTemporaryUrl(now()->addMinutes($minutes), $conversion);
        } catch (Throwable) {
            // getTemporaryUrl بيشتغل على S3 بس — للديسك المحلي بنستخدم
            // route موقّع كبديل.
            return URL::temporarySignedRoute(
                'fc.media.private',
                now()->addMinutes($minutes),
                ['media' => $media->getKey(), 'conversion' => $conversion],
            );
        }
    }
}
