<?php

declare(strict_types=1);

namespace Src\Contexts\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Notifications\Database\Factories\NotificationPreferenceFactory;
use Src\Support\Infrastructure\Persistence\Concerns\BelongsToTenant;

/**
 * تفضيل مستخدم واحد لإشعار واحد داخل مؤسسة واحدة.
 *
 * tenant_id هنا nullable عن قصد (تفضيل عام لما المستخدم مش في سياق
 * مؤسسة) — والـ trait بيملاه تلقائياً لما فيه سياق.
 */
final class NotificationPreference extends Model
{
    use BelongsToTenant;
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'notification_key',
        'channels',
        'enabled',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): NotificationPreferenceFactory
    {
        return NotificationPreferenceFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'enabled' => 'boolean',
        ];
    }
}
