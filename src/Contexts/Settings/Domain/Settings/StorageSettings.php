<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class StorageSettings extends Settings
{
    public string $default_disk;

    public string $private_disk;

    public string $conversions_disk;

    /** @var list<string> */
    public array $private_collections;

    /** @var array<string, string> */
    public array $collection_disks;

    public int $max_upload_size_kb;

    /** @var list<string> */
    public array $allowed_mimes;

    public int $temporary_url_minutes;

    public static function group(): string
    {
        return 'storage';
    }
}
