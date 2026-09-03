<?php

declare(strict_types=1);

namespace Src\Contexts\Media;

use Src\Support\Presentation\ContextServiceProvider;

final class MediaServiceProvider extends ContextServiceProvider
{
    protected function contextName(): string
    {
        return 'Media';
    }

    protected function contextPath(): string
    {
        return __DIR__;
    }
}
