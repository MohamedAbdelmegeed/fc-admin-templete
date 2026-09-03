<?php

declare(strict_types=1);

return [

    'pages' => [
        // اسم المدخل الوحيد في القائمة — الباقي تبويبات جوّاه.
        'index' => 'Settings',
        'general' => 'General',
        'general_help' => 'Name, contact details, language and maintenance mode.',
        'appearance' => 'Appearance',
        'appearance_help' => 'Brand color, logos, theme and footer — applied across the panel.',
        'storage' => 'Storage',
        'storage_help' => 'Which disk each kind of file lives on, and what may be uploaded.',
        'mail' => 'Mail',
        'mail_help' => 'Outgoing mail transport and sender identity. Test before you save.',
        'notifications' => 'Notifications',
        'notifications_help' => 'Delivery channels, polling rate and retention.',
        'security' => 'Security',
        'security_help' => 'Passwords, two-factor authentication and session limits.',
    ],

    'sections' => [
        'identity' => 'Identity',
        'identity_help' => 'The name and description shown to users and in emails.',
        'contact' => 'Contact',
        'contact_help' => 'Where users are pointed when they need help.',
        'regional' => 'Language & time',
        'regional_help' => 'Defaults for users who have not chosen their own.',
        'maintenance' => 'Maintenance mode',
        'maintenance_help' => 'Closes the panel to everyone except super admins.',

        'brand' => 'Brand',
        'brand_help' => 'One shade only — the rest of the scale is generated from it.',
        'logos' => 'Logos',
        'logos_help' => 'Leave empty to fall back to the application name.',
        'layout' => 'Layout',
        'layout_help' => 'How the panel opens before the user changes anything.',
        'footer' => 'Footer',
        'footer_help' => 'Shown at the bottom of every page.',

        'disks' => 'Disks',
        'disks_help' => 'Defined in config/filesystems.php. Changing a disk does not move existing files.',
        'collections' => 'Collections',
        'collections_help' => 'Private collections are served through temporary URLs, never a public link.',
        'uploads' => 'Uploads',
        'uploads_help' => 'Limits applied to every upload in the panel.',

        'transport' => 'Transport',
        'transport_help' => 'How mail leaves the application.',
        'sender' => 'Sender',
        'sender_help' => 'The address and name recipients will see.',

        'delivery' => 'Delivery',
        'delivery_help' => 'How notifications reach users while they are in the panel.',
        'channels' => 'Channels',
        'channels_help' => 'Turning a channel off here overrides every user preference.',
        'retention' => 'Retention',
        'retention_help' => 'Read notifications are pruned by a scheduled command.',

        'passwords' => 'Passwords',
        'passwords_help' => 'Applied at the next password change, not retroactively.',
        'two_factor' => 'Two-factor authentication',
        'two_factor_help' => 'Users in the selected roles must enrol within the grace period.',
        'sessions' => 'Sessions',
        'sessions_help' => 'How long a signed-in session stays valid.',
    ],

    'fields' => [
        'app_name' => 'Application name',
        'app_description' => 'Description',
        'support_email' => 'Support email',
        'support_phone' => 'Support phone',
        'default_locale' => 'Default language',
        'timezone' => 'Timezone',
        'maintenance_mode' => 'Maintenance mode',
        'maintenance_message' => 'Message shown to users',

        'primary_color' => 'Primary color',
        'font' => 'Font',
        'logo_light' => 'Logo (light)',
        'logo_dark' => 'Logo (dark)',
        'favicon' => 'Favicon',
        'default_theme' => 'Default theme',
        'sidebar_default' => 'Sidebar',
        'allow_theme_switch' => 'Let users switch theme',
        'footer_text' => 'Footer text',

        'default_disk' => 'Default disk',
        'private_disk' => 'Private disk',
        'conversions_disk' => 'Conversions disk',
        'private_collections' => 'Private collections',
        'collection_disks' => 'Per-collection disks',
        'collection' => 'Collection',
        'disk' => 'Disk',
        'max_upload_size_kb' => 'Maximum upload size',
        'temporary_url_minutes' => 'Temporary URL lifetime',
        'allowed_mimes' => 'Allowed file types',

        'mail_driver' => 'Transport',
        'encryption' => 'Encryption',
        'host' => 'Host',
        'port' => 'Port',
        'username' => 'Username',
        'password' => 'Password',
        'from_address' => 'From address',
        'from_name' => 'From name',

        'broadcast_enabled' => 'Real-time notifications',
        'database_polling_seconds' => 'Polling interval',
        'globally_disabled_channels' => 'Disabled channels',
        'prune_read_after_days' => 'Prune read notifications after',

        'password_min_length' => 'Minimum length',
        'password_history_count' => 'Remembered passwords',
        'password_expires_days' => 'Password expires after',
        'password_require_uncompromised' => 'Reject breached passwords',
        'two_factor_required_roles' => 'Required for roles',
        'two_factor_grace_period_days' => 'Grace period',
        'session_lifetime_minutes' => 'Session lifetime',
        'impersonation_max_minutes' => 'Impersonation limit',
        'force_https' => 'Force HTTPS',
    ],

    'help' => [
        'default_locale' => 'Used for new users and for emails sent to nobody in particular.',
        'maintenance_mode' => 'Super admins keep full access so you can fix what broke.',

        'primary_color' => 'Must reach 4.5:1 contrast on white — anything lighter is rejected.',
        'font' => 'The list is in config/branding.php. Every font there supports Arabic.',
        'logo_light' => 'Shown on light backgrounds. PNG or SVG, up to :size KB.',
        'logo_dark' => 'Falls back to the light logo when empty.',
        'favicon' => 'Square. Shown in the browser tab.',
        'allow_theme_switch' => 'When off, everyone stays on the default theme.',

        'default_disk' => 'Where public files go.',
        'private_disk' => 'Never served by a public URL.',
        'conversions_disk' => 'Thumbnails and generated sizes.',
        'private_collections' => 'Files in these collections require a download permission.',
        'collection_disks' => 'Overrides the default disk for one collection only.',
        'max_upload_size_kb' => 'Must stay below upload_max_filesize in php.ini and client_max_body_size in nginx, or uploads fail with an unhelpful 413.',
        'temporary_url_minutes' => 'How long a signed link to a private file stays valid.',
        'allowed_mimes' => 'MIME types, e.g. image/png. Anything else is rejected on upload.',

        'mail_driver' => 'Use "log" locally to write mail to the log instead of sending it.',
        'mail_password' => 'Stored encrypted. Leave as-is to keep the current password.',
        'from_address' => 'Must be an address the transport is allowed to send from, or mail is silently dropped.',

        'broadcast_enabled' => 'Requires the Reverb server to be running.',
        'database_polling_seconds' => 'Every open panel polls at this rate — a low value multiplies across all signed-in users.',
        'globally_disabled_channels' => 'Security notifications marked required in the catalog are still delivered.',
        'prune_read_after_days' => 'Unread notifications are never pruned.',

        'password_min_length' => 'Cannot go below :floor, the hard floor in config/security.php.',
        'password_history_count' => 'How many previous passwords cannot be reused. 0 disables the check.',
        'password_expires_days' => '0 disables expiry.',
        'password_require_uncompromised' => 'Checks the password against known breach lists.',
        'two_factor_required_roles' => 'Users in these roles cannot use the panel after the grace period without 2FA.',
        'two_factor_grace_period_days' => 'Counted from the day the requirement starts applying to the user.',
        'session_lifetime_minutes' => 'Idle time before a user is signed out.',
        'impersonation_max_minutes' => 'An impersonation session ends automatically after this.',
        'force_https' => 'Redirects every request to HTTPS. Turn off only for local HTTP development.',
    ],

    'themes' => [
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'Follow the device',
    ],

    'sidebar' => [
        'expanded' => 'Expanded',
        'collapsed' => 'Collapsed',
    ],

    'channels' => [
        'database' => 'In-panel',
        'mail' => 'Email',
        'broadcast' => 'Real-time',
    ],

    'mail_drivers' => [
        'smtp' => 'SMTP',
        'ses' => 'Amazon SES',
        'postmark' => 'Postmark',
        'resend' => 'Resend',
        'sendmail' => 'Sendmail',
        'log' => 'Write to log (no sending)',
        'array' => 'Discard (testing)',
    ],

    'encryptions' => [
        'tls' => 'TLS',
        'ssl' => 'SSL',
        'none' => 'None',
    ],

    'units' => [
        'kb' => 'KB',
        'minutes' => 'minutes',
        'seconds' => 'seconds',
        'days' => 'days',
    ],

    'mail' => [
        'send_test' => 'Send a test email',
        'test_recipient' => 'Send to',
        'test_subject' => 'Test email',
        'test_body' => 'If you are reading this, the mail settings work.',
        'test_sent' => 'Test email sent',
        'test_sent_body' => 'Sent to :email using the settings currently in the form.',
        'test_failed' => 'The test email could not be sent',
    ],

    'notifications' => [
        'saved' => 'Settings saved',
    ],
];
