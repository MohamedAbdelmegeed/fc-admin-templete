<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\AuthorizationServiceProvider;
use App\Providers\DynamicConfigServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\HorizonServiceProvider;
use Src\Contexts\Audit\AuditServiceProvider;
use Src\Contexts\Identity\IdentityServiceProvider;
use Src\Contexts\Media\MediaServiceProvider;
use Src\Contexts\Notifications\NotificationsServiceProvider;
use Src\Contexts\Settings\SettingsServiceProvider;
use Src\Contexts\Tenancy\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    AuthorizationServiceProvider::class,
    DynamicConfigServiceProvider::class,
    HorizonServiceProvider::class,

    // السياقات **قبل** مزوّد اللوحة — الموارد بتتسجّل عبر
    // Panel::configureUsing اللي بيتطبّق وقت بناء الـ Panel.
    TenancyServiceProvider::class,
    IdentityServiceProvider::class,
    SettingsServiceProvider::class,
    MediaServiceProvider::class,
    NotificationsServiceProvider::class,
    AuditServiceProvider::class,

    AdminPanelProvider::class,
];
