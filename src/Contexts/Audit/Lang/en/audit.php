<?php

declare(strict_types=1);

return [
    'widgets' => [
        'recent_activity' => 'Recent activity',
        'onboarding' => 'Setup checklist',
    ],

    'fields' => [
        'event' => 'Event',
        'description' => 'Description',
        'causer' => 'User',
        'log_name' => 'Log',
    ],

    'events' => [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'restored' => 'Restored',
    ],

    'system' => 'System',

    'empty' => [
        'activity' => 'No activity recorded for this organization yet.',
    ],

    'health' => [
        'database' => 'Database',
        'cache' => 'Cache',
        'queue' => 'Queue',
        'storage' => 'Storage',
        'up' => 'Healthy',
        'down' => 'Down',
    ],

    'onboarding' => [
        'help' => 'Each item reflects real state — the list disappears on its own once everything is done.',
        'go' => 'Set it up',
        'steps' => [
            'brand_name' => 'Set the organization name',
            'brand_logo' => 'Upload the organization logo',
            'mail' => 'Configure the sending address',
            'team' => 'Invite your first teammate',
        ],
    ],
];
