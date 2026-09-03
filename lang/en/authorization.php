<?php

declare(strict_types=1);

return [

    'actions' => [
        'view_any' => 'View list',
        'view' => 'View details',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'delete_any' => 'Bulk delete',
        'restore' => 'Restore',
        'force_delete' => 'Permanently delete',
        'export' => 'Export',
        'impersonate' => 'Impersonate',
        'reset_password' => 'Reset password',
        'force_logout' => 'Terminate sessions',
        'assign_roles' => 'Assign roles',
        'download' => 'Download',
        'change_disk' => 'Change storage disk',
        'prune' => 'Prune records',
        'manage_general' => 'Manage general settings',
        'manage_storage' => 'Manage storage',
        'manage_mail' => 'Manage mail',
        'manage_appearance' => 'Manage appearance',
        'manage_security' => 'Manage security',
        'manage_notifications' => 'Manage notifications',
    ],

    'resources' => [
        'users' => 'Users',
        'roles' => 'Roles',
        'tenants' => 'Organizations',
        'media' => 'Media',
        'settings' => 'Settings',
        'activity_logs' => 'Activity log',
    ],

    'groups' => [
        'identity' => 'Identity & access',
        'tenancy' => 'Organizations',
        'content' => 'Content',
        'system' => 'System',
        'dashboard' => 'Dashboard',
    ],

    'sections' => [
        'pages' => 'Pages',
        'widgets' => 'Widgets',
    ],

    'pages' => [
        'access.panel.admin' => 'Access the admin panel',
        'access.dashboard' => 'Access the dashboard',
        'access.system' => 'System section',
        'access.tenancy' => 'Organizations section',
        'access.horizon' => 'Access Horizon',
        'access.pulse' => 'Access Pulse',
        'access.health' => 'System health page',
        'access.log_viewer' => 'Log viewer',
        'access.backups' => 'Backups',
        'access.failed_jobs' => 'Failed jobs',
        'access.onboarding_analytics' => 'Onboarding analytics',
    ],

    'widgets' => [
        'widget.stats_overview' => 'Stats overview widget',
        'widget.recent_activity' => 'Recent activity widget',
        'widget.system_health' => 'System health widget',
        'widget.onboarding_checklist' => 'Onboarding checklist widget',
    ],

    'roles' => [
        'super_admin' => 'Super admin',
        'admin' => 'Admin',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
    ],

    'denied' => [
        'missing_permission' => 'You do not have the ":permission" permission.',
        'record_trashed' => 'This record is deleted. Restore it first.',
        'record_not_found' => 'This record does not exist.',
        'maintenance_mode' => 'The system is currently in maintenance mode.',
        'self_target' => 'You cannot do that to your own account.',
        'while_impersonating' => 'This operation is blocked while impersonating.',
        'inactive_tenant' => 'This organization is suspended.',

        'user' => [
            'cannot_delete_self' => 'You cannot delete your own account.',
            'cannot_suspend_self' => 'You cannot suspend your own account.',
            'last_admin' => 'This is the last admin in the organization — they cannot be removed.',
            'cannot_impersonate_super_admin' => 'Super admins cannot be impersonated.',
            'cannot_impersonate_self' => 'You are already signed in as yourself.',
            'already_suspended' => 'This user is already suspended.',
            'already_active' => 'This user is already active.',
            'not_in_tenant' => 'This user is not a member of the current organization.',
            'cannot_grant_super_admin' => 'The super admin role can only be granted from the command line.',
        ],

        'role' => [
            'protected' => 'This role is protected — it cannot be edited or deleted from the UI.',
            'in_use' => 'This role is assigned to :count user(s). Unassign them first.',
        ],

        'tenant' => [
            'cannot_delete_current' => 'You cannot delete the organization you are currently working in.',
            'has_users' => 'This organization still has :count user(s). Move them first.',
            'last_active' => 'This is the last active organization — it cannot be suspended.',
            'already_active' => 'This organization is already active.',
            'already_inactive' => 'This organization is already suspended.',
        ],

        'media' => [
            'not_owner' => 'This file does not belong to the current organization.',
            'private_collection' => 'This collection is private — a download permission is required.',
        ],

        'settings' => [
            'contrast_failed' => 'This color has a :ratio:1 contrast ratio on white; the minimum is 4.5:1. Pick a darker color.',
        ],
    ],

    'sync' => [
        'operation' => 'Operation',
        'count' => 'Count',
        'add' => 'Add',
        'prunable' => 'Prunable',
        'done' => 'Synced and cache cleared.',
    ],
];
