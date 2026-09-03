<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** بيمنع إعادة استخدام آخر N كلمات مرور. (docs/12 بند ٤) */
final class PasswordHistory extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['user_id', 'password'];

    /** @var list<string> */
    protected $hidden = ['password'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
