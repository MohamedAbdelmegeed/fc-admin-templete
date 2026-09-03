<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('storage.default_disk', 's3');
        $this->migrator->add('storage.private_disk', 's3-private');
        $this->migrator->add('storage.conversions_disk', 's3');
        $this->migrator->add('storage.private_collections', ['documents', 'contracts']);
        $this->migrator->add('storage.collection_disks', []);
        $this->migrator->add('storage.max_upload_size_kb', 10240);
        $this->migrator->add('storage.allowed_mimes', [
            'image/jpeg', 'image/png', 'image/webp', 'application/pdf',
        ]);
        $this->migrator->add('storage.temporary_url_minutes', 10);
    }
};
