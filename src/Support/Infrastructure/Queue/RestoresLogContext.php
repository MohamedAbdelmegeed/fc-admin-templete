<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Queue;

use Closure;
use Illuminate\Support\Facades\Log;
use Src\Support\Application\Contracts\TenantContext;

/**
 * بيرجّع سياق الطلب جوه الـ Job.
 *
 * من غير ده، سلسلة اللوج بتتقطع عند حدود الطابور — وتتبّع مشكلة في
 * الإنتاج بيبقى ساعات بدل دقايق. (docs/11 بند ٢)
 */
final class RestoresLogContext
{
    public function handle(object $job, Closure $next): mixed
    {
        if ($job instanceof ContextAwareJob) {
            app(TenantContext::class)->set($job->tenantId);

            Log::shareContext(array_filter([
                'request_id' => $job->requestId,
                'tenant_id' => $job->tenantId,
                'user_id' => $job->userId,
                'job' => $job::class,
            ], static fn (mixed $value): bool => $value !== null));
        }

        return $next($job);
    }
}
