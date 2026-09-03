<?php

declare(strict_types=1);

namespace Src\Contexts\Audit\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Infrastructure\Persistence\TenantScope;

/**
 * سجل النشاط مربوط بالمؤسسة.
 *
 * ⚠️ استثناء موثّق من BelongsToTenant — زي Role بالظبط:
 *
 * الـ trait بيعمل حاجتين، وإحنا عايزين واحدة بس منهم.
 *   · النطاق العام على **القراءة** → عايزينه. من غيره أي مؤسسة بتشوف
 *     نشاط الباقيين، وده أخطر تسريب في المشروع (CLAUDE.md خطر رقم ١).
 *   · الرمي على **الكتابة** لما مفيش سياق → مش عايزينه. النشاط بيتسجّل
 *     من أوامر الكونسول والسيدرز والطوابير، ودي مالهاش سياق مؤسسة.
 *     لو رمينا، `migrate --seed` نفسه بيقع وقت إنشاء أول مؤسسة.
 *
 * فبنركّب النطاق يدوي، وبنسيب العمود nullable للكتابة.
 * الاستثناء ده مسجّل في اختبار العمارة في tests/Feature/Tenancy.
 */
final class Activity extends SpatieActivity
{
    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new TenantScope);

        self::creating(function (self $activity): void {
            if ($activity->getAttribute('tenant_id') !== null) {
                return;
            }

            // null مقبول: نشاط بره أي مؤسسة. المهم إن القراءة متعزولة.
            $activity->setAttribute('tenant_id', app(TenantContext::class)->id());
        });
    }
}
