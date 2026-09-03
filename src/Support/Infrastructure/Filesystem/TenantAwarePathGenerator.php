<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Filesystem;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * كل ملفات المستأجر تحت tenants/{id}/ — عزل على مستوى نظام الملفات
 * كمان، مش بس على مستوى الاستعلام. (docs/04 بند ٤)
 */
final class TenantAwarePathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $tenantId = $media->getCustomProperty('tenant_id') ?? 'global';

        return "tenants/{$tenantId}/{$media->collection_name}/{$media->getKey()}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive/';
    }
}
