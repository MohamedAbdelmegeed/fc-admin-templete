<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.driver', 'log');
        $this->migrator->add('mail.host', 'mailpit');
        $this->migrator->add('mail.port', 1025);
        $this->migrator->add('mail.username', '');
        $this->migrator->addEncrypted('mail.password', '');
        $this->migrator->add('mail.encryption', 'tls');
        $this->migrator->add('mail.from_address', 'admin@fc-admin.test');
        $this->migrator->add('mail.from_name', [
            'ar' => 'كود المستقبل',
            'en' => 'Future Code',
        ]);
    }
};
