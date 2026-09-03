<?php

declare(strict_types=1);

namespace Src\Contexts\Notifications;

use Src\Support\Presentation\ContextServiceProvider;

final class NotificationsServiceProvider extends ContextServiceProvider
{
    protected function contextName(): string
    {
        return 'Notifications';
    }

    protected function contextPath(): string
    {
        return __DIR__;
    }
}
