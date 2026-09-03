<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Domain\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use Src\Contexts\Identity\Database\Factories\UserFactory;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Notifications\Domain\Models\NotificationPreference;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\DiskResolver;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Infrastructure\Filesystem\InteractsWithResolvedMedia;

/**
 * المستخدم مش تابع لمستأجر واحد — هو عضو في مؤسسة أو أكتر عبر tenant_user،
 * وأدواره متربطة بالمؤسسة عبر teams بتاعة spatie/permission.
 * عشان كده **مفيش** BelongsToTenant هنا. (docs/03 بند ١)
 */
final class User extends Authenticatable implements FilamentUser, HasAvatar, HasLocalePreference, HasMedia, HasTenants
{
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use InteractsWithResolvedMedia;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * قائمة بيضاء صريحة. مفيش $guarded = [] أبداً، و**مفيش** أي حقل
     * بيتحكم في الصلاحيات هنا — الأدوار بتتسند عبر assignRole. (docs/20 بند ٤)
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'locale',
        'timezone',
        'status',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    // ══════════════════════ العلاقات ══════════════════════

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)->withPivot('joined_at');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    // ══════════════════════ نطاقات ══════════════════════

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeInTenant(Builder $query, int $tenantId): Builder
    {
        return $query->whereHas('tenants', fn (Builder $q): Builder => $q->whereKey($tenantId));
    }

    // ══════════════════════ Filament ══════════════════════

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status !== UserStatus::Active) {
            return false;
        }

        // ⚠️ forUser($this) مش allows() المجرّدة: الدالة دي بتتنادى أثناء
        // تسجيل الدخول — قبل ما يبقى فيه مستخدم مصادَق عليه. لو سألنا
        // الـ Gate من غير ما نحدد المستخدم، هيقرا من auth() الفاضية
        // ويرجّع false للكل. والـ Gate هو اللي بيتعامل مع غياب المؤسسة
        // وقت الدخول (بيسأل في كل مؤسسات المستخدم).
        return Gate::forUser($this)->allows('access.panel.'.$panel->getId());
    }

    /** @return Collection<int, Tenant> */
    public function getTenants(Panel $panel): Collection
    {
        return $this->tenants()->where('is_active', true)->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $this->tenants()->whereKey($tenant->getKey())->exists()) {
            return false;
        }

        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($tenant->getKey());
        $registrar->forgetCachedPermissions();

        // مسح العلاقات المحملة عشان spatie/permission يحمل أدوار المستأجر الجديد
        // من غير كده بيستخدم أدوار أول مستأجر اتفحص (docs/03 بند ٥)
        $this->unsetRelation('roles');
        $this->unsetRelation('permissions');

        // الـ Gate مش hasPermissionTo() مباشرة — فحص الصلاحية المباشر
        // ممنوع برّه طبقة التفويض، وكمان كده المدير العام بيعدّي عبر
        // Gate::before بدل ما يتقفل برّه مؤسسة لسه ماخدش فيها دور.
        $allowed = Gate::forUser($this)->allows('access.panel.'.$this->getPanelId());

        $registrar->setPermissionsTeamId($previousTeamId);
        $registrar->forgetCachedPermissions();

        // نرجع نمسحهم تاني عشان لو الكود اللي نادى الدالة دي محتاج أدوار المستأجر الأصلي
        $this->unsetRelation('roles');
        $this->unsetRelation('permissions');

        return $allowed;
    }

    private function getPanelId(): string
    {
        return app('filament')->getCurrentPanel()?->getId() ?? 'admin';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: null;
    }

    // ══════════════════════ الترجمة والبث ══════════════════════

    /** الإشعار بيتبعت بلغة المستقبِل مش لغة المُرسِل. (docs/09 بند ٤) */
    public function preferredLocale(): string
    {
        return $this->locale ?? (string) config('app.locale');
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.'.$this->getKey();
    }

    // ══════════════════════ الوسائط ══════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk(app(DiskResolver::class)->for('avatar'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // إعدادات التحويل الأول، وبعدين المعالجة — العكس بيخلّي
        // performOnCollections() تتنادى على مُشغّل الصور مش على التحويل.
        // كل التحويلات في الطابور — مفيش nonQueued. (docs/04 بند ٥)
        $this->addMediaConversion('thumb')
            ->performOnCollections('avatar')
            ->queued()
            ->fit(Fit::Crop, 128, 128)
            ->format('webp')
            ->quality(82);
    }

    // ══════════════════════ سجل النشاط ══════════════════════

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'status', 'locale'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('identity');
    }

    public function beforeActivityLogged(Activity $activity): void
    {
        $activity->properties = $activity->properties->merge([
            'tenant_id' => app(TenantContext::class)->id(),
            'ip' => request()->ip(),
        ]);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
