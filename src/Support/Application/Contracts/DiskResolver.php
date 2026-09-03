<?php

declare(strict_types=1);

namespace Src\Support\Application\Contracts;

/**
 * الديسك اللي بنخزّن عليه قرار وقت التشغيل، مش وقت الكتابة.
 * الكود مش بيعرف S3 ولا local — بيسأل هنا وخلاص. (docs/04)
 */
interface DiskResolver
{
    /** الديسك المناسب لمجموعة وسائط معيّنة */
    public function for(string $collection): string;

    /** ديسك التحويلات (المصغّرات) */
    public function forConversions(string $collection): string;

    /** هل المجموعة دي خاصة (تحتاج رابط مؤقت)؟ */
    public function isPrivate(string $collection): bool;
}
