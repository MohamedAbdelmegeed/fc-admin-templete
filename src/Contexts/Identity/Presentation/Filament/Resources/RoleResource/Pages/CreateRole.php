<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Resources\RoleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Presentation\Filament\Resources\RoleResource;

final class CreateRole extends CreateRecord
{
    use SyncsGroupedPermissions;

    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        /** @var Role $role */
        $role = $this->record;

        $this->syncGroupedPermissions($role);
    }
}
