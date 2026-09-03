<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | كتالوج الإشعارات
    |--------------------------------------------------------------------------
    | كل إشعار في التطبيق لازم يكون هنا — فيه اختبار بيفشل لو إشعار
    | مش مسجّل. (docs/09 بند ٨)
    |
    | channels : القنوات المتاحة للمستخدم يختار منها
    | default  : الافتراضي قبل ما يغيّر حاجة
    | required : قنوات مايقدرش يوقفها (أمنية غالباً) — بتعدّي حتى لو قفل الإشعار
    | group    : مجموعة العرض في شاشة التفضيلات (مفتاح ترجمة)
    */

    'catalog' => [

        'user_invited' => [
            'group' => 'identity',
            'channels' => ['database', 'mail'],
            'default' => ['database', 'mail'],
            'required' => ['database'],
        ],

        'user_suspended' => [
            'group' => 'identity',
            'channels' => ['database', 'mail'],
            'default' => ['database', 'mail'],
            'required' => ['mail'],
        ],

        'password_changed' => [
            'group' => 'security',
            'channels' => ['database', 'mail'],
            'default' => ['database', 'mail'],
            'required' => ['mail'],       // أمني — إجباري
        ],

        'new_device_signed_in' => [
            'group' => 'security',
            'channels' => ['database', 'mail'],
            'default' => ['database', 'mail'],
            'required' => ['mail'],
        ],

        'two_factor_disabled' => [
            'group' => 'security',
            'channels' => ['database', 'mail'],
            'default' => ['database', 'mail'],
            'required' => ['mail'],
        ],

        'export_ready' => [
            'group' => 'system',
            'channels' => ['database', 'broadcast', 'mail'],
            'default' => ['database', 'broadcast'],
            'required' => [],
        ],

        'export_failed' => [
            'group' => 'system',
            'channels' => ['database', 'broadcast', 'mail'],
            'default' => ['database', 'broadcast'],
            'required' => [],
        ],

        'backup_failed' => [
            'group' => 'system',
            'channels' => ['database', 'mail'],
            'default' => ['database', 'mail'],
            'required' => ['mail'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | الطابور
    |--------------------------------------------------------------------------
    | طابور منفصل عشان إشعار ما يتأخرش ورا Job تقيل. (docs/13)
    */

    'queue' => env('NOTIFICATIONS_QUEUE', 'notifications'),

    /*
    |--------------------------------------------------------------------------
    | التنظيف
    |--------------------------------------------------------------------------
    | الإشعارات المقروءة الأقدم من المدة دي بتتمسح بأمر مجدول.
    */

    'prune_read_after_days' => env('NOTIFICATIONS_PRUNE_DAYS', 90),
];
