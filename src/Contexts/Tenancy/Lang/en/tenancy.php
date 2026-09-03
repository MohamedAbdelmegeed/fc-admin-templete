<?php

declare(strict_types=1);

return [
    'tenant' => [
        'singular' => 'Organization',
        'plural' => 'Organizations',

        'sections' => [
            'details' => 'Organization details',
            'branding' => 'Branding',
            'subscription' => 'Subscription',
        ],

        'fields' => [
            'name' => 'Name',
            'description' => 'Description',
            'slug' => 'Slug',
            'domain' => 'Domain',
            'primary_color' => 'Primary color',
            'logo' => 'Logo',
            'is_active' => 'Active',
            'trial_ends_at' => 'Trial ends at',
            'users_count' => 'Users',
        ],

        'actions' => [
            'activate' => 'Activate',
            'deactivate' => 'Suspend',
            'switch' => 'Open organization',
        ],

        'confirm' => [
            'deactivate_heading' => 'Suspend this organization?',
            'deactivate_body' => 'None of its members will be able to sign in until it is activated again.',
        ],

        'notifications' => [
            'activated' => 'Organization activated',
            'deactivated' => 'Organization suspended',
        ],

        'help' => [
            'slug' => 'Appears in the URL: /admin/t/{slug}. Lowercase letters and dashes only.',
            'primary_color' => 'One shade only (600) — the rest of the scale is generated.',
            'domain' => 'Optional. If set, the organization is also resolved from this domain.',
        ],

        'empty' => [
            'heading' => 'No organizations yet',
            'description' => 'The organization is the unit everything is isolated by — start with one.',
            'cta' => 'Add the first organization',
        ],

        'menu' => [
            'settings' => 'Organization settings',
        ],

        'trial' => [
            'active' => 'Trial until :date',
            'expired' => 'Trial expired',
            'none' => 'No trial',
        ],
    ],
];
