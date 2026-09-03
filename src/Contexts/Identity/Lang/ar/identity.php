<?php

declare(strict_types=1);

return [
    'auth' => [
        'login_field' => 'اسم المستخدم أو البريد الإلكتروني',
    ],

    'user' => [
        'singular' => 'مستخدم',
        'plural' => 'المستخدمون',

        'sections' => [
            'account' => 'بيانات الحساب',
            'access' => 'الصلاحيات والوصول',
            'preferences' => 'التفضيلات',
            'security' => 'الأمان',
        ],

        'fields' => [
            'name' => 'الاسم',
            'username' => 'اسم المستخدم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الموبايل',
            'password' => 'كلمة المرور',
            'avatar' => 'الصورة الشخصية',
            'roles' => 'الأدوار',
            'tenants' => 'المؤسسات',
            'locale' => 'لغة الواجهة',
            'timezone' => 'المنطقة الزمنية',
            'status' => 'الحالة',
            'last_login' => 'آخر دخول',
            'two_factor' => 'المصادقة الثنائية',
        ],

        'status' => [
            'invited' => 'مدعو',
            'active' => 'نشط',
            'suspended' => 'موقوف',
        ],

        'actions' => [
            'invite' => 'دعوة مستخدم',
            'activate' => 'تفعيل',
            'suspend' => 'إيقاف',
            'impersonate' => 'انتحال الشخصية',
            'reset_password' => 'إعادة تعيين كلمة المرور',
            'force_logout' => 'إنهاء كل الجلسات',
            'bulk_activate' => 'تفعيل المختار',
        ],

        'confirm' => [
            'suspend_heading' => 'إيقاف المستخدم؟',
            'suspend_body' => 'مش هيقدر يدخل اللوحة لحد ما تفعّله تاني.',
            'impersonate_heading' => 'الدخول بحساب المستخدم ده؟',
            'impersonate_body' => 'هتشوف اللوحة بعينه. العملية مسجّلة، وبتنتهي تلقائياً بعد :minutes.',
            'force_logout_heading' => 'إنهاء كل جلسات المستخدم؟',
            'force_logout_body' => 'هيتسجّل خروجه من كل أجهزته فوراً.',
        ],

        'notifications' => [
            'invited' => 'الدعوة اتبعتت',
            'activated' => 'المستخدم اتفعّل',
            'suspended' => 'المستخدم اتوقف',
            'logged_out' => 'الجلسات اتنهت',
            'password_reset_sent' => 'رابط إعادة التعيين اتبعت',
        ],

        'help' => [
            'username' => 'اختياري — بيقدر يدخل بيه بدل البريد. حروف إنجليزي وأرقام وشرطات.',
            'roles' => 'الأدوار دي بتسري على المؤسسة الحالية بس.',
            'locale' => 'الإشعارات والبريد بتوصله باللغة دي.',
        ],

        'empty' => [
            'heading' => 'مفيش مستخدمين في المؤسسة دي',
            'description' => 'ابدأ بدعوة أول عضو في الفريق.',
            'cta' => 'دعوة أول مستخدم',
        ],

        'badges' => [
            'invited_users' => 'مستخدمون مدعوون لسه ما دخلوش',
        ],
    ],

    'role' => [
        'singular' => 'دور',
        'plural' => 'الأدوار',

        'sections' => [
            'details' => 'بيانات الدور',
            'permissions' => 'الصلاحيات',
        ],

        'fields' => [
            'name' => 'اسم الدور',
            'guard' => 'الحارس',
            'permissions' => 'الصلاحيات',
            'users_count' => 'عدد المستخدمين',
        ],

        'actions' => [
            'select_all' => 'تحديد الكل',
        ],

        'help' => [
            'name' => 'الاسم بالإنجليزي وبـ snake_case — العرض بيتم من ملف الترجمة.',
            'protected' => 'الدور ده محمي ومش قابل للتعديل من الواجهة.',
        ],

        'empty' => [
            'heading' => 'مفيش أدوار',
            'description' => 'شغّل php artisan authorization:sync عشان الأدوار الافتراضية تتعمل.',
        ],
    ],
];
