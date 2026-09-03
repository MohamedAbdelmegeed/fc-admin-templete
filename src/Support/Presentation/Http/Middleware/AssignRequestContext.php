<?php

declare(strict_types=1);

namespace Src\Support\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Support\Application\Contracts\TenantContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * أي سطر لوج من غير سياق = سطر ضايع.
 *
 * الـ request_id بيرجع في الهيدر كمان، فالمستخدم لما يبلّغ عن مشكلة
 * نقدر نلاقي كل سطور اللوج بتاعة الطلب ده في ثانية. (docs/11 بند ٢)
 */
final class AssignRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-Id') ?? (string) Str::uuid();

        Log::shareContext([
            'request_id' => $requestId,
            'user_id' => $request->user()?->getKey(),
            'tenant_id' => app(TenantContext::class)->id(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'locale' => app()->getLocale(),
        ]);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
