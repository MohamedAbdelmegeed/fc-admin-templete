<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Filesystem;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Src\Support\Application\Contracts\DiskResolver;
use Src\Support\Application\Contracts\TenantContext;

/**
 * الديسك مايتكتبش بإيد في أي مكان. الـ trait ده بيضمن إن كل رفع بيمرّ
 * على DiskResolver وبيحمل tenant_id عشان الـ PathGenerator يعزل المسار.
 */
trait InteractsWithResolvedMedia
{
    public function attachMedia(string|UploadedFile $file, string $collection): Media
    {
        $resolver = app(DiskResolver::class);

        return $this->addMedia($file)
            ->withCustomProperties(['tenant_id' => app(TenantContext::class)->id()])
            ->storingConversionsOnDisk($resolver->forConversions($collection))
            ->toMediaCollection($collection, $resolver->for($collection));
    }
}
