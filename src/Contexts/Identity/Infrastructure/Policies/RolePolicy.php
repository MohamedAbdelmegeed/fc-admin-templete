<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Infrastructure\Policies;

use Illuminate\Auth\Access\Response;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Support\Domain\Authorization\Policy;

final class RolePolicy extends Policy
{
    protected function resource(): string
    {
        return 'roles';
    }

    /** الأدوار المحمية مايتلمسوش — حتى من المدير العام. */
    public function invariants(): array
    {
        return ['update', 'delete'];
    }

    public function viewAny(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'view_any')->response();
    }

    public function view(User $user, Role $role): Response
    {
        return $this->decide()->permission($user, $this, 'view')->response();
    }

    public function create(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'create')->response();
    }

    public function update(User $user, Role $role): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->rule(! $role->isProtected(), 'role.protected')
            ->response();
    }

    public function delete(User $user, Role $role): Response
    {
        return $this->decide()
            ->permission($user, $this, 'delete')
            ->rule(! $role->isProtected(), 'role.protected')
            ->ruleUsing(
                fn (): bool => $role->users()->doesntExist(),
                'role.in_use',
                ['count' => (string) $role->users()->count()],
            )
            ->response();
    }
}
