<?php

declare(strict_types=1);

namespace Src\Contexts\Identity;

use Src\Support\Presentation\ContextServiceProvider;

final class IdentityServiceProvider extends ContextServiceProvider
{
    protected function contextName(): string
    {
        return 'Identity';
    }

    protected function contextPath(): string
    {
        return __DIR__;
    }
}
