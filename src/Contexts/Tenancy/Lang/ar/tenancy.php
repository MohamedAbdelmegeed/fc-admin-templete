<?php

declare(strict_types=1);

return [
    'tenant' => [
        'singular' => 'مؤسسة',
        'plural' => 'المؤسسات',

        'sections' => [
            'details' => 'بيانات المؤسسة',
            'branding' => 'الهوية البصرية',
            'subscription' => 'الاشتراك',
        ],

        'fields' => [
            'name' => 'الاسم',
            'description' => 'الوصف',
            'slug' => 'المعرّف',
            'domain' => 'النطاق',
            'primary_color' => 'اللون الأساسي',
            'logo' => 'الشعار',
            'is_active' => 'نشطة',
            'trial_ends_at' => 'انتهاء التجربة',
            'users_count' => 'عدد المستخدمين',
        ],

        'actions' => [
            'activate' => 'تفعيل',
            'deactivate' => 'إيقاف',
            'switch' => 'الدخول للمؤسسة',
        ],

        'confirm' => [
            'deactivate_heading' => 'إيقاف المؤسسة؟',
            'deactivate_body' => 'كل أعضائها مش هيقدروا يدخلوا لحد ما تتفعّل تاني.',
        ],

        'notifications' => [
            'activated' => 'المؤسسة اتفعّلت',
            'deactivated' => 'المؤسسة اتوقفت',
        ],

        'help' => [
            'slug' => 'بيظهر في الرابط: /admin/t/{slug}. حروف إنجليزي صغيرة وشرطات بس.',
            'primary_color' => 'درجة واحدة بس (600) — باقي السُّلَّم بيتولّد تلقائياً.',
            'domain' => 'اختياري. لو اتحدد، المؤسسة بتتعرّف من النطاق ده كمان.',
        ],

        'empty' => [
            'heading' => 'مفيش مؤسسات',
            'description' => 'المؤسسة هي الوحدة اللي بيتعزل عندها كل شيء — ابدأ بواحدة.',
            'cta' => 'إضافة أول مؤسسة',
        ],

        'menu' => [
            'settings' => 'إعدادات المؤسسة',
        ],

        'trial' => [
            'active' => 'تجربة لحد :date',
            'expired' => 'التجربة انتهت',
            'none' => 'مفيش تجربة',
        ],
    ],
];
