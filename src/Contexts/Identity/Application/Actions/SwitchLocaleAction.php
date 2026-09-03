<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Application\Actions;

use Src\Contexts\Identity\Domain\Models\User;

/**
 * تبديل اللغة بيتخزّن في مكانين: السيشن (للطلب الجاي فوراً)
 * وعمود المستخدم (عشان الإشعارات والطوابير تعرف لغته وهي بره الطلب).
 */
final readonly class SwitchLocaleAction
{
    public function handle(string $locale, ?User $user = null): string
    {
        $supported = (array) config('app.supported_locales', ['en']);

        abort_unless(in_array($locale, $supported, true), 400);

        $user?->forceFill(['locale' => $locale])->save();
        session()->put('locale', $locale);
        app()->setLocale($locale);

        return $locale;
    }
}
