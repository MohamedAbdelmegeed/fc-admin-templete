<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Support\Application\Contracts\TenantContext;

/**
 * الأب لكل Job في التطبيق.
 *
 * الـ Job مش شايف سياق المستأجر ولا سياق اللوج — الاتنين بيتلقطوا وقت
 * الإنشاء وبيترجّعوا وقت التنفيذ. ورّث من هنا عشان محدش ينسى.
 * (docs/03 بند ٤ · docs/11 بند ٢)
 */
abstract class ContextAwareJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?string $requestId = null;

    public ?int $tenantId = null;

    public ?int $userId = null;

    public function __construct()
    {
        $this->requestId = Log::sharedContext()['request_id'] ?? (string) Str::uuid();
        $this->tenantId = app(TenantContext::class)->id();
        $this->userId = auth()->id();
    }

    /** @return list<object> */
    public function middleware(): array
    {
        return [new RestoresLogContext];
    }
}
