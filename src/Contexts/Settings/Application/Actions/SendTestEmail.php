<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Application\Actions;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Src\Contexts\Settings\Application\DTOs\MailConfiguration;

/**
 * بيبعت رسالة تجريبية بإعدادات **لسه ماتحفظتش**.
 *
 * السبب: لو التجربة اشتغلت بالمحفوظ بس، المستخدم لازم يحفظ إعدادات غلط
 * الأول عشان يكتشف إنها غلط — وساعتها كل رسائل النظام بتقع. فالإعدادات
 * بتتحقن في الكونفيج لطلب واحد، والرسالة بتتبعت، وبنرجّع الكونفيج زي
 * ما كان مهما حصل.
 */
final class SendTestEmail
{
    public function handle(MailConfiguration $configuration, string $recipient): void
    {
        $original = Config::get('mail');

        try {
            Config::set('mail', $configuration->applyTo($original));

            // Laravel بيكاش المرسِل بعد أول استخدام — من غير المسح ده
            // التجربة بتستخدم الإعدادات القديمة وتقول «تمام» وهي غلط.
            Mail::purge($configuration->driver);
            Mail::forgetMailers();

            Mail::raw(
                __('settings::settings.mail.test_body'),
                fn ($message) => $message
                    ->to($recipient)
                    ->subject(__('settings::settings.mail.test_subject')),
            );
        } finally {
            Config::set('mail', $original);
            Mail::forgetMailers();
        }
    }
}
