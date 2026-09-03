<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Presentation\Filament\Resources\TenantResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Application\Actions\GrantTenantMembershipAction;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Contexts\Tenancy\Presentation\Filament\Resources\TenantResource;

final class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * اللي بيعمل المؤسسة بيبقى عضو فيها **وليه دور جواها** — وإلا هيعملها
     * ومايقدرش يدخلها (/admin/t/{slug} بيرجع مقفول).
     */
    protected function afterCreate(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->record;

        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        app(GrantTenantMembershipAction::class)->handle($user, $tenant);
    }
}
