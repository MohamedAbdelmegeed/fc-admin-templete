<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy;

use Src\Support\Presentation\ContextServiceProvider;

final class TenancyServiceProvider extends ContextServiceProvider
{
    protected function contextName(): string
    {
        return 'Tenancy';
    }

    protected function contextPath(): string
    {
        return __DIR__;
    }
}
