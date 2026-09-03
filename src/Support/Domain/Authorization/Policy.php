<?php

declare(strict_types=1);

namespace Src\Support\Domain\Authorization;

/**
 * الكلاس الأساسي لكل السياسات.
 *
 * السياسة مش «مكان نفحص فيه الصلاحية» — هي المكان اللي فيه الصلاحية
 * وقواعد الأعمال معاً، عشان الإجابة تبقى واحدة سواء الطلب جه من الواجهة
 * أو API أو Job. التفاصيل في docs/19-policies.md.
 */
abstract class Policy
{
    /** اسم المورد كما هو في config/authorization.php */
    abstract protected function resource(): string;

    /**
     * قدرات لا يتجاوزها المدير العام.
     *
     * دي مش صلاحيات — دي قواعد سلامة. مثال: «مينفعش تحذف نفسك».
     * لازم تفضل شغالة حتى للـ super_admin، وإلا هيقفل على نفسه.
     *
     * @return list<string>
     */
    public function invariants(): array
    {
        return [];
    }

    public function isInvariant(string $ability): bool
    {
        return in_array($ability, $this->invariants(), true);
    }

    /** يبدأ سلسلة فحص جديدة */
    protected function decide(): Decision
    {
        return new Decision;
    }

    /** يبني اسم الصلاحية الكامل: publish + announcements → publish.announcements */
    public function permissionFor(string $action): string
    {
        return $action.config('authorization.separator').$this->resource();
    }

    /** اسم المورد متاح للفحوصات الخارجية (الاختبارات المعمارية مثلاً) */
    public function resourceName(): string
    {
        return $this->resource();
    }
}
