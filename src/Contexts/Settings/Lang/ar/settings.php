<?php

declare(strict_types=1);

return [

    'pages' => [
        // اسم المدخل الوحيد في القائمة — الباقي تبويبات جوّاه.
        'index' => 'الإعدادات',
        'general' => 'عام',
        'general_help' => 'الاسم وبيانات التواصل واللغة ووضع الصيانة.',
        'appearance' => 'المظهر',
        'appearance_help' => 'لون العلامة واللوجو والثيم والفوتر — بيتطبّقوا على اللوحة كلها.',
        'storage' => 'التخزين',
        'storage_help' => 'كل نوع ملف بيتخزّن على أنهي ديسك، وإيه المسموح رفعه.',
        'mail' => 'البريد',
        'mail_help' => 'إعدادات الإرسال وهوية المُرسِل. جرّب قبل ما تحفظ.',
        'notifications' => 'الإشعارات',
        'notifications_help' => 'قنوات الوصول ومعدّل التحديث ومدة الاحتفاظ.',
        'security' => 'الأمان',
        'security_help' => 'كلمات المرور والمصادقة الثنائية وحدود الجلسات.',
    ],

    'sections' => [
        'identity' => 'الهوية',
        'identity_help' => 'الاسم والوصف اللي بيظهروا للمستخدمين وفي الرسائل.',
        'contact' => 'التواصل',
        'contact_help' => 'المستخدم بيتوجّه لفين لما يحتاج مساعدة.',
        'regional' => 'اللغة والوقت',
        'regional_help' => 'الافتراضي للمستخدمين اللي لسه ما اختاروش.',
        'maintenance' => 'وضع الصيانة',
        'maintenance_help' => 'بيقفل اللوحة على الكل ما عدا المديرين العامين.',

        'brand' => 'العلامة',
        'brand_help' => 'درجة واحدة بس — باقي السُّلَّم بيتولّد منها.',
        'logos' => 'الشعارات',
        'logos_help' => 'سيبها فاضية فيرجع Filament لاسم التطبيق.',
        'layout' => 'التخطيط',
        'layout_help' => 'اللوحة بتفتح إزاي قبل ما المستخدم يغيّر حاجة.',
        'footer' => 'الفوتر',
        'footer_help' => 'بيظهر تحت في كل صفحة.',

        'disks' => 'الديسكات',
        'disks_help' => 'معرّفة في config/filesystems.php. تغيير الديسك مش بينقل الملفات القديمة.',
        'collections' => 'المجموعات',
        'collections_help' => 'المجموعات الخاصة بتتقدّم بروابط مؤقتة، مش برابط عام أبداً.',
        'uploads' => 'الرفع',
        'uploads_help' => 'حدود بتتطبّق على كل رفع في اللوحة.',

        'transport' => 'وسيلة الإرسال',
        'transport_help' => 'الرسائل بتخرج من التطبيق إزاي.',
        'sender' => 'المُرسِل',
        'sender_help' => 'العنوان والاسم اللي المستقبِل هيشوفهم.',

        'delivery' => 'التوصيل',
        'delivery_help' => 'الإشعارات بتوصل إزاي والمستخدم فاتح اللوحة.',
        'channels' => 'القنوات',
        'channels_help' => 'قفل قناة من هنا بيتخطّى تفضيلات كل المستخدمين.',
        'retention' => 'مدة الاحتفاظ',
        'retention_help' => 'الإشعارات المقروءة بتتمسح بأمر مجدول.',

        'passwords' => 'كلمات المرور',
        'passwords_help' => 'بتتطبّق عند أول تغيير جاي، مش بأثر رجعي.',
        'two_factor' => 'المصادقة الثنائية',
        'two_factor_help' => 'المستخدمون في الأدوار دي لازم يفعّلوها خلال المهلة.',
        'sessions' => 'الجلسات',
        'sessions_help' => 'الجلسة بتفضل صالحة قد إيه.',
    ],

    'fields' => [
        'app_name' => 'اسم التطبيق',
        'app_description' => 'الوصف',
        'support_email' => 'بريد الدعم',
        'support_phone' => 'هاتف الدعم',
        'default_locale' => 'اللغة الافتراضية',
        'timezone' => 'المنطقة الزمنية',
        'maintenance_mode' => 'وضع الصيانة',
        'maintenance_message' => 'الرسالة اللي بتظهر للمستخدم',

        'primary_color' => 'اللون الأساسي',
        'font' => 'الخط',
        'logo_light' => 'اللوجو (فاتح)',
        'logo_dark' => 'اللوجو (داكن)',
        'favicon' => 'أيقونة الموقع',
        'default_theme' => 'الثيم الافتراضي',
        'sidebar_default' => 'الشريط الجانبي',
        'allow_theme_switch' => 'السماح بتبديل الثيم',
        'footer_text' => 'نص الفوتر',

        'default_disk' => 'الديسك الافتراضي',
        'private_disk' => 'الديسك الخاص',
        'conversions_disk' => 'ديسك التحويلات',
        'private_collections' => 'المجموعات الخاصة',
        'collection_disks' => 'ديسك لكل مجموعة',
        'collection' => 'المجموعة',
        'disk' => 'الديسك',
        'max_upload_size_kb' => 'أقصى حجم للرفع',
        'temporary_url_minutes' => 'عمر الرابط المؤقت',
        'allowed_mimes' => 'أنواع الملفات المسموحة',

        'mail_driver' => 'وسيلة الإرسال',
        'encryption' => 'التشفير',
        'host' => 'المضيف',
        'port' => 'المنفذ',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'from_address' => 'عنوان المُرسِل',
        'from_name' => 'اسم المُرسِل',

        'broadcast_enabled' => 'الإشعارات الفورية',
        'database_polling_seconds' => 'مدة التحديث',
        'globally_disabled_channels' => 'القنوات المقفولة',
        'prune_read_after_days' => 'مسح الإشعارات المقروءة بعد',

        'password_min_length' => 'أقل طول',
        'password_history_count' => 'كلمات المرور المحفوظة',
        'password_expires_days' => 'انتهاء كلمة المرور بعد',
        'password_require_uncompromised' => 'رفض كلمات المرور المسرّبة',
        'two_factor_required_roles' => 'إجبارية للأدوار',
        'two_factor_grace_period_days' => 'مهلة التفعيل',
        'session_lifetime_minutes' => 'عمر الجلسة',
        'impersonation_max_minutes' => 'حد الانتحال',
        'force_https' => 'فرض HTTPS',
    ],

    'help' => [
        'default_locale' => 'بتُستخدم للمستخدمين الجدد وللرسائل اللي مالهاش مستقبِل محدد.',
        'maintenance_mode' => 'المدير العام بيفضل داخل عادي عشان يقدر يصلّح اللي باظ.',

        'primary_color' => 'لازم يوصل تباين ٤٫٥:١ على الأبيض — أي لون أفتح بيتترفض.',
        'font' => 'القائمة في config/branding.php. كل الخطوط فيها بتدعم العربية.',
        'logo_light' => 'بيظهر على الخلفيات الفاتحة. PNG أو SVG، لحد :size كيلوبايت.',
        'logo_dark' => 'لو فاضي بيرجع للوجو الفاتح.',
        'favicon' => 'مربّع. بيظهر في تبويب المتصفح.',
        'allow_theme_switch' => 'لو مقفول، الكل بيفضل على الثيم الافتراضي.',

        'default_disk' => 'الملفات العامة بتروح فين.',
        'private_disk' => 'عمره ما بيتقدّم برابط عام.',
        'conversions_disk' => 'المصغّرات والمقاسات المولّدة.',
        'private_collections' => 'ملفات المجموعات دي محتاجة صلاحية تنزيل.',
        'collection_disks' => 'بيتخطّى الديسك الافتراضي لمجموعة واحدة بس.',
        'max_upload_size_kb' => 'لازم يفضل أقل من upload_max_filesize في php.ini و client_max_body_size في nginx، وإلا الرفع بيقع بخطأ ٤١٣ مبهم.',
        'temporary_url_minutes' => 'الرابط الموقّع لملف خاص بيفضل صالح قد إيه.',
        'allowed_mimes' => 'أنواع MIME، مثال image/png. أي حاجة تانية بتترفض وقت الرفع.',

        'mail_driver' => 'استخدم «log» محلياً عشان الرسايل تتكتب في اللوج بدل ما تتبعت.',
        'mail_password' => 'مخزّنة مشفّرة. سيبها زي ما هي عشان تحتفظ بالحالية.',
        'from_address' => 'لازم يكون عنوان مسموح للوسيلة ترسل منه، وإلا الرسالة بتتزنق من غير ما حد ياخد باله.',

        'broadcast_enabled' => 'محتاج سيرفر Reverb شغّال.',
        'database_polling_seconds' => 'كل لوحة مفتوحة بتعمل طلب بالمعدّل ده — الرقم الصغير بيتضاعف مع كل مستخدم داخل.',
        'globally_disabled_channels' => 'الإشعارات الأمنية المعلّمة «إجبارية» في الكتالوج بتوصل برضه.',
        'prune_read_after_days' => 'الإشعارات غير المقروءة عمرها ما بتتمسح.',

        'password_min_length' => 'مينفعش يقل عن :floor، وهو الأرضية الصلبة في config/security.php.',
        'password_history_count' => 'كام كلمة مرور سابقة ممنوع تتكرر. صفر بيقفل الفحص.',
        'password_expires_days' => 'صفر بيقفل الانتهاء.',
        'password_require_uncompromised' => 'بيفحص كلمة المرور في قوائم التسريبات المعروفة.',
        'two_factor_required_roles' => 'المستخدمون في الأدوار دي مش هيقدروا يدخلوا بعد المهلة من غير مصادقة ثنائية.',
        'two_factor_grace_period_days' => 'بتتحسب من اليوم اللي الشرط بدأ ينطبق فيه على المستخدم.',
        'session_lifetime_minutes' => 'مدة السكون قبل ما المستخدم يتسجّل خروجه.',
        'impersonation_max_minutes' => 'جلسة الانتحال بتنتهي لوحدها بعد المدة دي.',
        'force_https' => 'بيحوّل كل الطلبات لـ HTTPS. اقفله بس للتطوير المحلي على HTTP.',
    ],

    'themes' => [
        'light' => 'فاتح',
        'dark' => 'داكن',
        'system' => 'حسب الجهاز',
    ],

    'sidebar' => [
        'expanded' => 'مفتوح',
        'collapsed' => 'مطوي',
    ],

    'channels' => [
        'database' => 'داخل اللوحة',
        'mail' => 'بريد إلكتروني',
        'broadcast' => 'فوري',
    ],

    'mail_drivers' => [
        'smtp' => 'SMTP',
        'ses' => 'Amazon SES',
        'postmark' => 'Postmark',
        'resend' => 'Resend',
        'sendmail' => 'Sendmail',
        'log' => 'كتابة في اللوج (من غير إرسال)',
        'array' => 'تجاهل (للاختبار)',
    ],

    'encryptions' => [
        'tls' => 'TLS',
        'ssl' => 'SSL',
        'none' => 'بدون',
    ],

    'units' => [
        'kb' => 'كيلوبايت',
        'minutes' => 'دقيقة',
        'seconds' => 'ثانية',
        'days' => 'يوم',
    ],

    'mail' => [
        'send_test' => 'إرسال رسالة تجريبية',
        'test_recipient' => 'إرسال إلى',
        'test_subject' => 'رسالة تجريبية',
        'test_body' => 'لو انت بتقرا ده، يبقى إعدادات البريد شغّالة.',
        'test_sent' => 'الرسالة التجريبية اتبعتت',
        'test_sent_body' => 'اتبعتت لـ :email بالإعدادات اللي في الفورم دلوقتي.',
        'test_failed' => 'الرسالة التجريبية ما اتبعتتش',
    ],

    'notifications' => [
        'saved' => 'الإعدادات اتحفظت',
    ],
];
