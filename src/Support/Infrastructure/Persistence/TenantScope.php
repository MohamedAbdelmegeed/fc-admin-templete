<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Exceptions\MissingTenantContextException;

/**
 * الطبقة التانية من عزل المستأجرين — الحماية الحقيقية.
 *
 * Filament بيغطّي موارده هو بس. أي استعلام مكتوب بإيد (Action، Job،
 * Command، API) مش محمي من غير الـ scope ده. (docs/03 بند ٣)
 */
final class TenantScope implements Scope
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);
        $tenantId = $context->id();

        if ($tenantId === null) {
            // من غير سياق: نرمي بدل ما نسرّب كل الصفوف. الاستثناء الوحيد
            // هو التجاوز الصريح عبر withoutScope() في أمر إداري موثّق.
            if (! $context->isBypassed()) {
                throw new MissingTenantContextException($model::class);
            }

            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
    }
}
