<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Resources\RoleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Presentation\Filament\Resources\RoleResource;

final class EditRole extends EditRecord
{
    use SyncsGroupedPermissions;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Role $role */
        $role = $this->record;

        return $this->fillGroupedPermissions($data, $role);
    }

    protected function afterSave(): void
    {
        /** @var Role $role */
        $role = $this->record;

        // الدور المحمي صلاحياته بتتزامن من الكونفيج بس — مش من الواجهة.
        if ($role->isProtected()) {
            return;
        }

        $this->syncGroupedPermissions($role);
    }
}
