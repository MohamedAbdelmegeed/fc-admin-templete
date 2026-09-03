<?php

declare(strict_types=1);

namespace Src\Support\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * الرؤوس الأمنية. (docs/12 بند ٦)
 *
 * ملاحظة: CSP بيتبعت في وضع **التقرير** بس افتراضياً — Filament و
 * Livewire بيستخدموا سكربتات inline، ففرض السياسة من غير مرحلة تقرير
 * هيكسر اللوحة. شغّل CSP_ENFORCE=true بعد ما تقارير المخالفات تنضف.
 * (docs/20 بند ٥)
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        // HSTS في الإنتاج بس — لو اتحط محلياً على HTTP هيكسر التطوير.
        if (app()->isProduction() && $request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        if (($policy = $this->contentSecurityPolicy()) !== null) {
            $header = (bool) config('security.csp.enforce', false)
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only';

            $headers[$header] = $policy;
        }

        $response->headers->add($headers);

        return $response;
    }

    private function contentSecurityPolicy(): ?string
    {
        if (! (bool) config('security.csp.enabled', true)) {
            return null;
        }

        $directives = (array) config('security.csp.directives', []);

        if ($directives === []) {
            return null;
        }

        return implode('; ', array_map(
            static fn (string $name, array $values): string => $name.' '.implode(' ', $values),
            array_keys($directives),
            array_values($directives),
        ));
    }
}
