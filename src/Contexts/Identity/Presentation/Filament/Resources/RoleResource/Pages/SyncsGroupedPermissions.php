<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Resources\RoleResource\Pages;

use Src\Contexts\Identity\Domain\Models\Role;
use Src\Support\Infrastructure\Authorization\PermissionBuilder;

/**
 * الصلاحيات معروضة في الفورم مقسّمة على حقول لكل مجموعة، لكنها في
 * قاعدة البيانات علاقة واحدة. الـ trait ده بيترجم بين الشكلين.
 *
 * الحقول معلّمة dehydrated(false) عشان ما تحاولش تتحفظ كأعمدة على
 * جدول roles — المزامنة بتحصل هنا بعد الحفظ.
 */
trait SyncsGroupedPermissions
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function fillGroupedPermissions(array $data, ?Role $role): array
    {
        $selected = $role?->permissions->pluck('name')->all() ?? [];

        foreach (app(PermissionBuilder::class)->groups() as $group => $subjects) {
            foreach ($subjects as $subject => $permissions) {
                $data["permissions_{$group}_{$subject}"] = array_values(
                    array_intersect($permissions, $selected),
                );
            }
        }

        return $data;
    }

    protected function syncGroupedPermissions(Role $role): void
    {
        $selected = [];

        foreach (app(PermissionBuilder::class)->groups() as $group => $subjects) {
            foreach (array_keys($subjects) as $subject) {
                $selected = [
                    ...$selected,
                    ...(array) ($this->data["permissions_{$group}_{$subject}"] ?? []),
                ];
            }
        }

        // فلترة أخيرة على الكونفيج: أي اسم جه من الطلب ومش معرّف عندنا
        // بيتشال — مفيش صلاحية بتتولد من مدخل مستخدم.
        $allowed = app(PermissionBuilder::class)->allPermissionNames();

        $role->syncPermissions(array_values(array_intersect(array_unique($selected), $allowed)));
    }
}
