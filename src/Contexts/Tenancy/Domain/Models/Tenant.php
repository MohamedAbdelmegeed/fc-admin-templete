<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Domain\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\HasTranslations;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Database\Factories\TenantFactory;

/**
 * المؤسسة (المستأجر). الموديل ده **مش** تابع لمستأجر — هو المستأجر نفسه،
 * فمفيش BelongsToTenant عليه. الحماية بتيجي من TenantPolicy.
 */
final class Tenant extends Model
{
    use HasFactory;
    use HasTranslations;
    use LogsActivity;
    use SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'domain',
        'name',
        'description',
        'logo_path',
        'primary_color',
        'is_active',
        'trial_ends_at',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('joined_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'domain', 'is_active', 'primary_color', 'trial_ends_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('tenancy');
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }
}
