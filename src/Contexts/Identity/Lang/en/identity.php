<?php

declare(strict_types=1);

return [
    'auth' => [
        'login_field' => 'Username or email',
    ],

    'user' => [
        'singular' => 'User',
        'plural' => 'Users',

        'sections' => [
            'account' => 'Account details',
            'access' => 'Access & permissions',
            'preferences' => 'Preferences',
            'security' => 'Security',
        ],

        'fields' => [
            'name' => 'Name',
            'username' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone',
            'password' => 'Password',
            'avatar' => 'Avatar',
            'roles' => 'Roles',
            'tenants' => 'Organizations',
            'locale' => 'Interface language',
            'timezone' => 'Timezone',
            'status' => 'Status',
            'last_login' => 'Last sign-in',
            'two_factor' => 'Two-factor authentication',
        ],

        'status' => [
            'invited' => 'Invited',
            'active' => 'Active',
            'suspended' => 'Suspended',
        ],

        'actions' => [
            'invite' => 'Invite user',
            'activate' => 'Activate',
            'suspend' => 'Suspend',
            'impersonate' => 'Impersonate',
            'reset_password' => 'Reset password',
            'force_logout' => 'Terminate all sessions',
            'bulk_activate' => 'Activate selected',
        ],

        'confirm' => [
            'suspend_heading' => 'Suspend this user?',
            'suspend_body' => 'They will not be able to sign in until you activate them again.',
            'impersonate_heading' => 'Sign in as this user?',
            'impersonate_body' => 'You will see the panel through their eyes. This is logged and ends automatically after :minutes.',
            'force_logout_heading' => 'Terminate all sessions?',
            'force_logout_body' => 'They will be signed out of every device immediately.',
        ],

        'notifications' => [
            'invited' => 'Invitation sent',
            'activated' => 'User activated',
            'suspended' => 'User suspended',
            'logged_out' => 'Sessions terminated',
            'password_reset_sent' => 'Password reset link sent',
        ],

        'help' => [
            'username' => 'Optional — can be used instead of the email to sign in. Letters, numbers and dashes.',
            'roles' => 'These roles apply to the current organization only.',
            'locale' => 'Notifications and emails are sent in this language.',
        ],

        'empty' => [
            'heading' => 'No users in this organization',
            'description' => 'Start by inviting your first team member.',
            'cta' => 'Invite the first user',
        ],

        'badges' => [
            'invited_users' => 'Invited users who have not signed in yet',
        ],
    ],

    'role' => [
        'singular' => 'Role',
        'plural' => 'Roles',

        'sections' => [
            'details' => 'Role details',
            'permissions' => 'Permissions',
        ],

        'fields' => [
            'name' => 'Role name',
            'guard' => 'Guard',
            'permissions' => 'Permissions',
            'users_count' => 'Users',
        ],

        'actions' => [
            'select_all' => 'Select all',
        ],

        'help' => [
            'name' => 'Use English snake_case — the display label comes from the translation file.',
            'protected' => 'This role is protected and cannot be edited from the UI.',
        ],

        'empty' => [
            'heading' => 'No roles yet',
            'description' => 'Run php artisan authorization:sync to create the default roles.',
        ],
    ],
];
