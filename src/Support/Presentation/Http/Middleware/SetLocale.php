<?php

declare(strict_types=1);

namespace Src\Support\Presentation\Http\Middleware;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Src\Contexts\Settings\Domain\Settings\GeneralSettings;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** ترتيب الأولوية: تفضيل المستخدم ← السيشن ← إعدادات النظام ← الكونفيج. */
final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        $supported = (array) config('app.supported_locales', ['en']);

        foreach ([$request->user()?->locale, $request->session()->get('locale'), $this->systemDefault()] as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        return (string) config('app.locale');
    }

    private function systemDefault(): ?string
    {
        try {
            return app(GeneralSettings::class)->default_locale;
        } catch (Throwable) {
            return null;
        }
    }
}
