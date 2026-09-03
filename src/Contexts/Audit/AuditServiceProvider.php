<?php

declare(strict_types=1);

namespace Src\Contexts\Audit;

use Src\Support\Presentation\ContextServiceProvider;

final class AuditServiceProvider extends ContextServiceProvider
{
    protected function contextName(): string
    {
        return 'Audit';
    }

    protected function contextPath(): string
    {
        return __DIR__;
    }
}
