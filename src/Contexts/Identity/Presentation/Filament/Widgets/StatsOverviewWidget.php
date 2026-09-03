<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Support\Application\Contracts\TenantContext;

final class StatsOverviewWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    /** Gate معرّف من الكونفيج — مش نص صلاحية بيتفحص مباشرة. (docs/19 بند ٧) */
    public static function canView(): bool
    {
        return Gate::allows('widget.stats_overview');
    }

    /** @return list<Stat> */
    protected function getStats(): array
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return [];
        }

        // استعلام واحد مجمّع بدل ثلاثة — والنتيجة مكاشة لدقيقتين.
        $counts = Cache::remember(
            "widget:user-stats:{$tenantId}",
            now()->addMinutes(2),
            fn (): array => User::query()
                ->inTenant($tenantId)
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->all(),
        );

        $total = array_sum($counts);

        return [
            Stat::make(__('identity::identity.user.plural'), fc_number($total))
                ->description(__('identity::identity.user.status.active').': '
                    .fc_number((int) ($counts[UserStatus::Active->value] ?? 0)))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make(
                __('identity::identity.user.status.invited'),
                fc_number((int) ($counts[UserStatus::Invited->value] ?? 0)),
            )
                ->descriptionIcon('heroicon-m-envelope')
                ->color('warning'),

            Stat::make(
                __('identity::identity.user.status.suspended'),
                fc_number((int) ($counts[UserStatus::Suspended->value] ?? 0)),
            )
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('danger'),
        ];
    }
}
