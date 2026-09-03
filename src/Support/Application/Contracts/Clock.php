<?php

declare(strict_types=1);

namespace Src\Support\Application\Contracts;

use Carbon\CarbonImmutable;

/** الوقت كاعتمادية — عشان الاختبارات تقدر تجمّده. */
interface Clock
{
    public function now(): CarbonImmutable;
}
