<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.app_name', [
            'ar' => 'لوحة تحكم كود المستقبل',
            'en' => 'Future Code Admin',
        ]);
        $this->migrator->add('general.app_description', [
            'ar' => 'قالب لوحة تحكم جاهز للإنتاج',
            'en' => 'A production-ready admin panel template',
        ]);
        $this->migrator->add('general.support_email', 'support@futurecode.test');
        $this->migrator->add('general.support_phone', '+20 100 000 0000');
        $this->migrator->add('general.default_locale', 'ar');
        $this->migrator->add('general.timezone', 'Africa/Cairo');
        $this->migrator->add('general.maintenance_mode', false);
        $this->migrator->add('general.maintenance_message', [
            'ar' => 'النظام تحت الصيانة دلوقتي. هنرجع قريب.',
            'en' => 'The system is under maintenance. We will be back shortly.',
        ]);
    }
};
