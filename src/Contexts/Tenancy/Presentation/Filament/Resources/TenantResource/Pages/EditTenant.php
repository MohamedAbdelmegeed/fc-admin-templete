<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Presentation\Filament\Resources\TenantResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Src\Contexts\Tenancy\Presentation\Filament\Resources\TenantResource;

final class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /** تغيير لون المؤسسة لازم يبطّل كاش السُّلَّم وإلا اللوحة تفضل باللون القديم. */
    protected function afterSave(): void
    {
        cache()->forget('theme:scale:'.$this->record->getKey().':'.md5((string) $this->record->primary_color));
    }
}
