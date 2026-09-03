<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Resources\UserResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Identity\Presentation\Filament\Resources\UserResource;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * المستخدم مش تابع لمستأجر بعمود — عضويته بتتسجّل في tenant_user.
     * Filament بيربط تلقائياً لما يكون فيه tenantRelationshipName، بس
     * بنتأكد هنا كمان عشان الإنشاء من غير لوحة يبقى صحيح.
     */
    protected function afterCreate(): void
    {
        $tenant = Filament::getTenant();

        if ($tenant === null) {
            return;
        }

        /** @var User $user */
        $user = $this->record;

        $user->tenants()->syncWithoutDetaching([$tenant->getKey()]);
    }
}
