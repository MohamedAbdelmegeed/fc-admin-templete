<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Persistence\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Exceptions\MissingTenantContextException;
use Src\Support\Infrastructure\Persistence\TenantScope;

/**
 * أي موديل فيه عمود tenant_id لازم يستخدم الـ trait ده.
 * فيه اختبار بيمرّ على كل الموديلات تلقائياً ويفشل لو واحد نسيه.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $context = app(TenantContext::class);
            $tenantId = $context->id();

            if ($tenantId === null) {
                if ($context->isBypassed()) {
                    return;
                }

                throw new MissingTenantContextException(static::class);
            }

            $model->setAttribute('tenant_id', $tenantId);
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
