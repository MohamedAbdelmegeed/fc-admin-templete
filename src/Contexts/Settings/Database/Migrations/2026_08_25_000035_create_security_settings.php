<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('security.password_min_length', 12);
        $this->migrator->add('security.password_require_uncompromised', true);
        $this->migrator->add('security.password_history_count', 5);
        $this->migrator->add('security.password_expires_days', 0);
        $this->migrator->add('security.two_factor_required_roles', ['super_admin', 'admin']);
        $this->migrator->add('security.two_factor_grace_period_days', 7);
        $this->migrator->add('security.session_lifetime_minutes', 120);
        $this->migrator->add('security.impersonation_max_minutes', 30);
        $this->migrator->add('security.force_https', true);
    }
};
