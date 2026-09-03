<?php

declare(strict_types=1);

namespace Src\Support\Domain\Exceptions;

use RuntimeException;

/** الأب لكل استثناءات الدومين — بيسمح بمسك النوع ده وحده في الواجهة. */
abstract class DomainException extends RuntimeException {}
