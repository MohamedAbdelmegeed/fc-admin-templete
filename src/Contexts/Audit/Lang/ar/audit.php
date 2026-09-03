<?php

declare(strict_types=1);

return [
    'widgets' => [
        'recent_activity' => 'آخر النشاط',
        'onboarding' => 'خطوات التجهيز',
    ],

    'fields' => [
        'event' => 'الحدث',
        'description' => 'الوصف',
        'causer' => 'المستخدم',
        'log_name' => 'السجل',
    ],

    'events' => [
        'created' => 'إضافة',
        'updated' => 'تعديل',
        'deleted' => 'حذف',
        'restored' => 'استرجاع',
    ],

    'system' => 'النظام',

    'empty' => [
        'activity' => 'مفيش نشاط لسه في المؤسسة دي.',
    ],

    'health' => [
        'database' => 'قاعدة البيانات',
        'cache' => 'الكاش',
        'queue' => 'الطابور',
        'storage' => 'التخزين',
        'up' => 'شغّال',
        'down' => 'واقع',
    ],

    'onboarding' => [
        'help' => 'البنود دي بتتقري من حالة المؤسسة الفعلية — القائمة بتختفي لوحدها لما تخلص.',
        'go' => 'يلا نظبطها',
        'steps' => [
            'brand_name' => 'حدّد اسم المؤسسة',
            'brand_logo' => 'ارفع لوجو المؤسسة',
            'mail' => 'ظبّط بريد الإرسال',
            'team' => 'ادعُ أول زميل للفريق',
        ],
    ],
];
