<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Src\Contexts\Settings\Domain\Settings\GeneralSettings;
use Src\Contexts\Settings\Domain\Settings\MailSettings;
use Src\Contexts\Settings\Domain\Settings\SecuritySettings;
use Throwable;

/**
 * بيحقن الإعدادات الديناميكية في كونفيج Laravel وقت التشغيل.
 *
 * ⚠️ مصيدتان (docs/05 بند ٦):
 *  ١. المزوّد ده بيعمل استعلام DB في كل طلب — الكاش على Redis هو اللي
 *     بيخلّيه مقبول. تأكد إن settings.cache.enabled = true.
 *  ٢. لو الجدول لسه ماتعملش (أول migrate) المزوّد هيكسر كل أمر artisan.
 *     الحارس تحت إلزامي.
 */
final class DynamicConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->settingsAreAvailable()) {
            return;
        }

        $this->applyMailSettings();
        $this->applyGeneralSettings();
        $this->applySecuritySettings();
    }

    private function settingsAreAvailable(): bool
    {
        try {
            return Schema::hasTable(config('settings.repositories.database.table') ?? 'settings');
        } catch (Throwable) {
            return false;
        }
    }

    private function applyMailSettings(): void
    {
        try {
            $mail = app(MailSettings::class);
        } catch (Throwable) {
            return;
        }

        config([
            'mail.default' => $mail->driver,
            'mail.mailers.smtp.host' => $mail->host,
            'mail.mailers.smtp.port' => $mail->port,
            'mail.mailers.smtp.username' => $mail->username,
            'mail.mailers.smtp.password' => $mail->password,
            'mail.mailers.smtp.encryption' => $mail->encryption,
            'mail.from.address' => $mail->from_address,
            'mail.from.name' => $mail->fromName(),
        ]);
    }

    private function applyGeneralSettings(): void
    {
        try {
            $general = app(GeneralSettings::class);
        } catch (Throwable) {
            return;
        }

        config([
            'app.name' => $general->name(),
            'app.timezone' => $general->timezone,
        ]);

        date_default_timezone_set($general->timezone);
    }

    private function applySecuritySettings(): void
    {
        try {
            $security = app(SecuritySettings::class);
        } catch (Throwable) {
            return;
        }

        config([
            'session.lifetime' => $security->session_lifetime_minutes,
            'security.two_factor.required_for_roles' => $security->two_factor_required_roles,
            'security.two_factor.grace_period_days' => $security->two_factor_grace_period_days,
            'security.impersonation.max_minutes' => $security->impersonation_max_minutes,
        ]);
    }
}
