<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** جلسة/جهاز مسجّل للمستخدم — أساس شاشة «أجهزتي». (docs/12 بند ٢) */
final class UserDevice extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'session_id',
        'fingerprint',
        'device_name',
        'platform',
        'browser',
        'ip_address',
        'country',
        'is_trusted',
        'last_active_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCurrent(?string $sessionId): bool
    {
        return $sessionId !== null && $this->session_id === $sessionId;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_trusted' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }
}
