<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | كلمات المرور
    |--------------------------------------------------------------------------
    | القيم دي حدود صلبة على مستوى الكود. النسخة القابلة للتغيير من
    | لوحة الإعدادات في SecuritySettings.
    */

    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 12),
        'history_count' => env('PASSWORD_HISTORY_COUNT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | المصادقة الثنائية
    |--------------------------------------------------------------------------
    */

    'two_factor' => [
        'required_for_roles' => ['super_admin', 'admin'],
        'grace_period_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | انتحال الشخصية
    |--------------------------------------------------------------------------
    | العمليات دي مقفولة تماماً أثناء الانتحال — حتى لو المنتحِل معاه
    | الصلاحية. (docs/12 بند ٣)
    */

    'impersonation' => [
        'max_minutes' => env('IMPERSONATION_MAX_MINUTES', 30),
        'blocked_abilities' => [
            'delete',
            'forceDelete',
            'impersonate',
            'resetPassword',
            'updatePassword',
            'assignRoles',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | تنقية HTML
    |--------------------------------------------------------------------------
    | قائمة **بيضاء** — مش سوداء. أي وسم مش هنا بيتشال. (docs/20 بند ٥)
    */

    'html' => [
        'allowed_tags' => [
            'p', 'br', 'strong', 'em', 'u', 's', 'ul', 'ol', 'li',
            'h2', 'h3', 'h4', 'blockquote', 'a', 'code', 'pre', 'span',
        ],
        'allowed_link_schemes' => ['http', 'https', 'mailto'],
        'allowed_link_hosts' => null,      // null = أي مضيف (الروابط الخارجية بتاخد rel)
        'allowed_media_hosts' => [],       // فاضية = مفيش صور خارجية
        'max_input_length' => 500_000,
    ],

    /*
    |--------------------------------------------------------------------------
    | سياسة أمن المحتوى (CSP)
    |--------------------------------------------------------------------------
    | ابدأ في وضع التقرير، اقرأ المخالفات، وبعدين فعّل الفرض.
    | متفرضش من غير المرحلة دي — هتقضي يومين تدوّر على اللي كسر.
    */

    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'enforce' => env('CSP_ENFORCE', false),
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://fonts.bunny.net'],
            'font-src' => ["'self'", 'data:', 'https://fonts.gstatic.com', 'https://fonts.bunny.net'],
            'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
            'connect-src' => ["'self'", 'ws:', 'wss:'],
            'frame-ancestors' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | تنقية اللوج
    |--------------------------------------------------------------------------
    | ممنوع تسجيل أي من دول — لا في اللوج ولا في Sentry. (docs/20 بند ١٣)
    */

    'redact' => [
        'password', 'password_confirmation', 'current_password',
        'token', 'api_key', 'secret', 'authorization',
        'two_factor_secret', 'two_factor_recovery_codes', 'code',
    ],
];
