<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Src\Contexts\Identity\Domain\Models\User;

/*
|--------------------------------------------------------------------------
| قنوات البث
|--------------------------------------------------------------------------
| كل قناة private أو presence. مفيش قناة عامة فيها بيانات مستخدمين —
| لو خلّيتها عامة أي حد يقدر يسمع إشعارات أي حد. (docs/20 بند ١١)
*/

Broadcast::channel('users.{userId}', fn (User $user, int $userId): bool => $user->getKey() === $userId);

Broadcast::channel(
    'tenants.{tenantId}',
    fn (User $user, int $tenantId): bool => $user->tenants()->whereKey($tenantId)->exists(),
);
