<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | الحارس (Guard)
    |--------------------------------------------------------------------------
    */
    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | فاصل المفتاح
    |--------------------------------------------------------------------------
    | الشكل النهائي للصلاحية: {action}.{resource}  مثال: view_any.users
    */
    'separator' => '.',

    /*
    |--------------------------------------------------------------------------
    | الأفعال القياسية لكل مورد
    |--------------------------------------------------------------------------
    */
    'default_actions' => [
        'view_any', 'view', 'create', 'update', 'delete',
        'delete_any', 'restore', 'force_delete', 'export',
    ],

    /*
    |--------------------------------------------------------------------------
    | الموارد
    |--------------------------------------------------------------------------
    | group   : مجموعة العرض في شاشة الأدوار (مفتاح ترجمة)
    | actions : لو null → default_actions. لو محددة → دي بس.
    | extra   : أفعال مخصصة إضافية
    */
    'resources' => [

        'users' => [
            'group' => 'identity',
            'actions' => null,
            'extra' => ['impersonate', 'reset_password', 'force_logout', 'assign_roles'],
        ],

        'roles' => [
            'group' => 'identity',
            'actions' => ['view_any', 'view', 'create', 'update', 'delete'],
        ],

        'tenants' => [
            'group' => 'tenancy',
            'actions' => null,
        ],

        'media' => [
            'group' => 'content',
            'actions' => ['view_any', 'view', 'create', 'delete'],
            'extra' => ['download', 'change_disk'],
        ],

        'settings' => [
            'group' => 'system',
            'actions' => ['view_any', 'update'],
            // صلاحية منفصلة لكل مجموعة إعدادات — مين يقدر يغيّر البريد
            // مش لازم يقدر يغيّر الأمان. (docs/05 بند ٧)
            'extra' => [
                'manage_general', 'manage_storage', 'manage_mail',
                'manage_appearance', 'manage_security', 'manage_notifications',
            ],
        ],

        'activity_logs' => [
            'group' => 'system',
            'actions' => ['view_any', 'view'],
            'extra' => ['prune'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | صلاحيات الصفحات المخصصة (مش مرتبطة بمورد)
    |--------------------------------------------------------------------------
    | بتتحوّل لـ Gates معرّفة في AuthorizationServiceProvider — مش نصوص
    | صلاحيات بتتفحص مباشرة. (docs/19 بند ٧)
    */
    'pages' => [
        'access.panel.admin' => 'system',
        'access.dashboard' => 'system',
        'access.system' => 'system',
        'access.tenancy' => 'tenancy',
        'access.horizon' => 'system',
        'access.pulse' => 'system',
        'access.health' => 'system',
        'access.log_viewer' => 'system',
        'access.backups' => 'system',
        'access.failed_jobs' => 'system',
        'access.onboarding_analytics' => 'system',
    ],

    /*
    |--------------------------------------------------------------------------
    | صلاحيات الودجتس
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'widget.stats_overview' => 'dashboard',
        'widget.recent_activity' => 'dashboard',
        'widget.system_health' => 'dashboard',
        'widget.onboarding_checklist' => 'dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | الأدوار الافتراضية
    |--------------------------------------------------------------------------
    | '*' = كل الصلاحيات. الأدوار دي بتتزامن مع كل تشغيل للأمر.
    */
    'roles' => [
        'super_admin' => ['*'],

        'admin' => [
            'users.*', 'roles.*', 'media.*', 'settings.*', 'activity_logs.*',
            'access.panel.admin', 'access.dashboard', 'access.system', 'access.health',
            'widget.*',
        ],

        'editor' => [
            'view_any.users', 'view.users',
            'media.*',
            'access.panel.admin', 'access.dashboard',
            'widget.stats_overview', 'widget.onboarding_checklist',
        ],

        'viewer' => [
            'view_any.*', 'view.*',
            'access.panel.admin', 'access.dashboard',
            'widget.stats_overview',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | الدور الخارق
    |--------------------------------------------------------------------------
    | بيتجاوز فحوصات الصلاحية عبر Gate::before — لكن **مش** قواعد السلامة
    | المعرّفة في invariants() بتاعة كل Policy. (docs/19 بند ٥)
    */
    'super_admin_role' => 'super_admin',

    /*
    |--------------------------------------------------------------------------
    | دور منشئ المؤسسة
    |--------------------------------------------------------------------------
    | اللي بيعمل مؤسسة جديدة بياخد الدور ده **جواها** تلقائياً. من غيره
    | هيعمل المؤسسة ومايقدرش يفتحها، لأن canAccessTenant() بيطلب صلاحية
    | access.panel.* في سياق المؤسسة نفسها. (docs/03)
    | المدير العام مستثنى — بيفضل super_admin في أي مؤسسة يعملها.
    */
    'tenant_creator_role' => env('AUTH_TENANT_CREATOR_ROLE', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | حماية من الحذف
    |--------------------------------------------------------------------------
    | أدوار ممنوع حذفها أو تعديل صلاحياتها من الواجهة
    */
    'protected_roles' => ['super_admin'],
];
