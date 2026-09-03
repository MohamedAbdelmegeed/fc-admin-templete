<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Domain\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * بنورّث دور spatie عشان (١) الاكتشاف التلقائي للـ Policy يلاقيه في
 * Infrastructure/Policies، و(٢) تغيير الأدوار يتسجّل في سجل النشاط —
 * وده من أهم اللي بيتسجّل. (docs/11 بند ٤)
 */
final class Role extends SpatieRole
{
    use LogsActivity;

    public function isProtected(): bool
    {
        return in_array($this->name, (array) config('authorization.protected_roles', []), true);
    }

    public function label(): string
    {
        $key = "authorization.roles.{$this->name}";
        $translated = __($key);

        return $translated === $key ? $this->name : $translated;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('security');
    }
}
