<?php

declare(strict_types=1);

return [

    'actions' => [
        'view_any' => 'عرض القائمة',
        'view' => 'عرض التفاصيل',
        'create' => 'إضافة',
        'update' => 'تعديل',
        'delete' => 'حذف',
        'delete_any' => 'حذف جماعي',
        'restore' => 'استرجاع',
        'force_delete' => 'حذف نهائي',
        'export' => 'تصدير',
        'impersonate' => 'انتحال الشخصية',
        'reset_password' => 'إعادة تعيين كلمة المرور',
        'force_logout' => 'إنهاء الجلسات',
        'assign_roles' => 'إسناد الأدوار',
        'download' => 'تنزيل',
        'change_disk' => 'تغيير مكان التخزين',
        'prune' => 'تنظيف السجلات',
        'manage_general' => 'إدارة الإعدادات العامة',
        'manage_storage' => 'إدارة التخزين',
        'manage_mail' => 'إدارة البريد',
        'manage_appearance' => 'إدارة المظهر',
        'manage_security' => 'إدارة الأمان',
        'manage_notifications' => 'إدارة الإشعارات',
    ],

    'resources' => [
        'users' => 'المستخدمون',
        'roles' => 'الأدوار',
        'tenants' => 'المؤسسات',
        'media' => 'الوسائط',
        'settings' => 'الإعدادات',
        'activity_logs' => 'سجل النشاط',
    ],

    'groups' => [
        'identity' => 'الهوية والوصول',
        'tenancy' => 'المؤسسات',
        'content' => 'المحتوى',
        'system' => 'النظام',
        'dashboard' => 'لوحة المعلومات',
    ],

    'sections' => [
        'pages' => 'الصفحات',
        'widgets' => 'الودجتس',
    ],

    'pages' => [
        'access.panel.admin' => 'الدخول للوحة التحكم',
        'access.dashboard' => 'الدخول للوحة المعلومات',
        'access.system' => 'قسم النظام',
        'access.tenancy' => 'قسم المؤسسات',
        'access.horizon' => 'الدخول لـ Horizon',
        'access.pulse' => 'الدخول لـ Pulse',
        'access.health' => 'صفحة صحة النظام',
        'access.log_viewer' => 'عارض السجلات',
        'access.backups' => 'النسخ الاحتياطي',
        'access.failed_jobs' => 'المهام الفاشلة',
        'access.onboarding_analytics' => 'مقاييس الأونبوردنج',
    ],

    'widgets' => [
        'widget.stats_overview' => 'ودجت الإحصائيات',
        'widget.recent_activity' => 'ودجت آخر النشاطات',
        'widget.system_health' => 'ودجت صحة النظام',
        'widget.onboarding_checklist' => 'ودجت قائمة المهام',
    ],

    'roles' => [
        'super_admin' => 'مدير عام',
        'admin' => 'مدير',
        'editor' => 'محرّر',
        'viewer' => 'مشاهد',
    ],

    /*
    |--------------------------------------------------------------------------
    | رسائل الرفض
    |--------------------------------------------------------------------------
    | الرسالة دي بتظهر للمستخدم — فهي جزء من المنتج مش من الكود.
    | قول السبب مش «ممنوع»، وقول الحل لو فيه، ومتسربش معلومات.
    */

    'denied' => [
        'missing_permission' => 'مش معاك صلاحية «:permission».',
        'record_trashed' => 'السجل ده محذوف. استرجعه الأول.',
        'record_not_found' => 'السجل ده مش موجود.',
        'maintenance_mode' => 'النظام في وضع الصيانة دلوقتي.',
        'self_target' => 'مينفعش تعمل كده على حسابك.',
        'while_impersonating' => 'العملية دي مقفولة أثناء انتحال الشخصية.',
        'inactive_tenant' => 'المؤسسة دي موقوفة.',

        'user' => [
            'cannot_delete_self' => 'مينفعش تحذف حسابك بنفسك.',
            'cannot_suspend_self' => 'مينفعش توقف حسابك بنفسك.',
            'last_admin' => 'ده آخر مدير في المؤسسة — مينفعش تشيله.',
            'cannot_impersonate_super_admin' => 'مينفعش تنتحل شخصية مدير عام.',
            'cannot_impersonate_self' => 'إنت بالفعل داخل بحسابك.',
            'already_suspended' => 'المستخدم ده موقوف بالفعل.',
            'already_active' => 'المستخدم ده نشط بالفعل.',
            'not_in_tenant' => 'المستخدم ده مش عضو في المؤسسة الحالية.',
            'cannot_grant_super_admin' => 'دور المدير العام بيتسند من سطر الأوامر بس.',
        ],

        'role' => [
            'protected' => 'الدور ده محمي — مينفعش يتعدّل أو يتحذف من الواجهة.',
            'in_use' => 'الدور ده مسند لـ :count مستخدم. شيله منهم الأول.',
        ],

        'tenant' => [
            'cannot_delete_current' => 'مينفعش تحذف المؤسسة اللي إنت شغّال فيها دلوقتي.',
            'has_users' => 'المؤسسة دي فيها :count مستخدم. انقلهم الأول.',
            'last_active' => 'دي آخر مؤسسة نشطة — مينفعش توقفها.',
            'already_active' => 'المؤسسة دي نشطة بالفعل.',
            'already_inactive' => 'المؤسسة دي موقوفة بالفعل.',
        ],

        'media' => [
            'not_owner' => 'الملف ده مش تابع للمؤسسة الحالية.',
            'private_collection' => 'المجموعة دي خاصة — محتاج صلاحية تنزيل.',
        ],

        'settings' => [
            'contrast_failed' => 'اللون ده تباينه :ratio:1 على الأبيض، والحد الأدنى 4.5:1. اختار لون أغمق.',
        ],
    ],

    'sync' => [
        'operation' => 'العملية',
        'count' => 'العدد',
        'add' => 'إضافة',
        'prunable' => 'حذف محتمل',
        'done' => 'تمت المزامنة ومسح الكاش.',
    ],
];
