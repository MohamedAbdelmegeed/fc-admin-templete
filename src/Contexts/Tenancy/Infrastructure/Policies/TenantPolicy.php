<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Infrastructure\Policies;

use Illuminate\Auth\Access\Response;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Authorization\Policy;

final class TenantPolicy extends Policy
{
    protected function resource(): string
    {
        return 'tenants';
    }

    public function invariants(): array
    {
        return ['delete', 'forceDelete', 'deactivate'];
    }

    public function viewAny(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'view_any')->response();
    }

    public function view(User $user, Tenant $tenant): Response
    {
        return $this->decide()->permission($user, $this, 'view')->response();
    }

    public function create(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'create')->response();
    }

    public function update(User $user, Tenant $tenant): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->rule(! $tenant->trashed(), 'record_trashed')
            ->response();
    }

    public function delete(User $user, Tenant $tenant): Response
    {
        return $this->decide()
            ->permission($user, $this, 'delete')
            ->rule(! $tenant->trashed(), 'record_trashed')
            // مينفعش تحذف الفرع اللي إنت واقف عليه.
            ->rule($tenant->getKey() !== app(TenantContext::class)->id(), 'tenant.cannot_delete_current')
            ->ruleUsing(
                fn (): bool => $tenant->users()->doesntExist(),
                'tenant.has_users',
                ['count' => (string) $tenant->users()->count()],
            )
            ->response();
    }

    public function restore(User $user, Tenant $tenant): Response
    {
        return $this->decide()->permission($user, $this, 'restore')->response();
    }

    public function forceDelete(User $user, Tenant $tenant): Response
    {
        return $this->decide()
            ->permission($user, $this, 'force_delete')
            ->rule($tenant->getKey() !== app(TenantContext::class)->id(), 'tenant.cannot_delete_current')
            ->response();
    }

    public function export(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'export')->response();
    }

    public function deactivate(User $user, Tenant $tenant): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->rule($tenant->is_active, 'tenant.already_inactive')
            ->ruleUsing(
                fn (): bool => Tenant::query()->active()->whereKeyNot($tenant->getKey())->exists(),
                'tenant.last_active',
            )
            ->response();
    }

    public function activate(User $user, Tenant $tenant): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->rule(! $tenant->is_active, 'tenant.already_active')
            ->response();
    }
}
