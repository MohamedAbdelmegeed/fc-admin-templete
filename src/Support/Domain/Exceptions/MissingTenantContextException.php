<?php

declare(strict_types=1);

namespace Src\Support\Domain\Exceptions;

/**
 * بترتمي لما استعلام أو إنشاء يحصل على موديل تابع لمستأجر من غير سياق.
 *
 * الافتراضي = الرفض. الاستعلام من غير سياق **بيرمي**، مش بيرجّع كل الصفوف —
 * لأن التسريب الصامت أخطر من الخطأ الصريح. (docs/20 بند ٣)
 */
final class MissingTenantContextException extends DomainException
{
    public function __construct(public readonly string $model)
    {
        parent::__construct("لا يوجد سياق مستأجر أثناء التعامل مع الموديل [{$model}].");
    }
}
