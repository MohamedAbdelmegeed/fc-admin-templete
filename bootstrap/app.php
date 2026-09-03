<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Src\Support\Presentation\Http\Middleware\AssignRequestContext;
use Src\Support\Presentation\Http\Middleware\SecurityHeaders;
use Src\Support\Presentation\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // أشهر باگ في الإنتاج: من غير ده Laravel بيشوف الطلب HTTP ورا
        // الـ load balancer، فكل الروابط والأصول بتتكسر. (docs/14 بند ١)
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->web(append: [
            SetLocale::class,
            AssignRequestContext::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // ممنوع تسجيل الأسرار — لا في اللوج ولا في Sentry. (docs/20 بند ١٣)
        $exceptions->dontFlash(config('security.redact', []));
    })->create();
