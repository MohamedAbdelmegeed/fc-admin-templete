<?php

declare(strict_types=1);

namespace Src\Contexts\Audit\Presentation\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Src\Support\Application\Contracts\DiskResolver;
use Throwable;

/**
 * حالة البنية التحتية في سطر واحد: قاعدة البيانات، الكاش، الطابور،
 * التخزين. الغرض إن المدير يعرف إن حاجة وقعت من غير ما يفتح Horizon.
 *
 * ⚠️ كل فحص متلفوف في try/catch وبيرجّع حالة — لوحة التحكم مينفعش
 * تقع لأن Redis نايم. ده بالظبط الوقت اللي محتاج تشوف فيه اللوحة.
 */
final class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 30;

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return Gate::allows('widget.system_health');
    }

    /** @return list<Stat> */
    protected function getStats(): array
    {
        // الفحوصات نفسها مكاشة لنص دقيقة — مش عايزين كل تحديث للصفحة
        // يفتح اتصال جديد على كل خدمة.
        $checks = Cache::remember('widget:system-health', now()->addSeconds(30), fn (): array => [
            'database' => $this->check(fn () => DB::connection()->getPdo()),
            'cache' => $this->check(function (): bool {
                Cache::store()->put('fc:health', 1, 5);

                return Cache::store()->get('fc:health') === 1;
            }),
            'queue' => $this->check(fn (): int => Queue::size()),
            // فحص حقيقي: كتابة وقراءة ومسح. exists() لوحده بينجح على
            // ديسك مقروء-فقط وبيدّي إحساس زائف بالأمان.
            'storage' => $this->check(function (): bool {
                $disk = Storage::disk(app(DiskResolver::class)->for('documents'));
                $probe = '.fc-health-'.bin2hex(random_bytes(4));

                try {
                    $disk->put($probe, '1');

                    return $disk->get($probe) === '1';
                } finally {
                    $disk->delete($probe);
                }
            }),
        ]);

        return [
            $this->stat('database', $checks['database'], 'heroicon-m-circle-stack'),
            $this->stat('cache', $checks['cache'], 'heroicon-m-bolt'),
            $this->stat('queue', $checks['queue'], 'heroicon-m-queue-list'),
            $this->stat('storage', $checks['storage'], 'heroicon-m-cloud'),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function check(callable $probe): array
    {
        try {
            $result = $probe();

            return [
                'ok' => true,
                'detail' => is_int($result) ? fc_number($result) : '',
            ];
        } catch (Throwable $exception) {
            // الرسالة بتتقص: نص استثناء كامل في ودجت بيبوظ التخطيط،
            // وممكن يسرّب مسارات أو بيانات اتصال.
            return [
                'ok' => false,
                'detail' => str($exception->getMessage())->limit(40)->toString(),
            ];
        }
    }

    /**
     * @param  array{ok: bool, detail: string}  $check
     */
    private function stat(string $key, array $check, string $icon): Stat
    {
        $label = __("audit::audit.health.{$key}");
        $value = $check['ok']
            ? __('audit::audit.health.up')
            : __('audit::audit.health.down');

        return Stat::make($label, $value)
            ->description($check['detail'] !== '' ? $check['detail'] : $label)
            ->descriptionIcon($icon)
            ->color($check['ok'] ? 'success' : 'danger');
    }
}
