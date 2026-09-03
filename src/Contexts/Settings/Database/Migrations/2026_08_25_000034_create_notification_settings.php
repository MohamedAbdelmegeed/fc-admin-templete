<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('notifications.broadcast_enabled', false);
        $this->migrator->add('notifications.database_polling_seconds', 30);
        $this->migrator->add('notifications.prune_read_after_days', 90);
        $this->migrator->add('notifications.globally_disabled_channels', []);
    }
};
