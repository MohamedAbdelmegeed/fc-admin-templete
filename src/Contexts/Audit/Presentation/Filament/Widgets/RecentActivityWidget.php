<?php

declare(strict_types=1);

namespace Src\Contexts\Audit\Presentation\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Src\Contexts\Audit\Domain\Models\Activity;

/**
 * آخر ما حصل في المؤسسة — أول حاجة أي مدير بيدوّر عليها الصبح.
 *
 * العزل جاي من TenantScope على الموديل نفسه، مش من شرط مكتوب هنا —
 * عشان لو حد نسخ الودجت ده مايسرّبش. (docs/03 بند ٣)
 */
final class RecentActivityWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 20;

    public static function canView(): bool
    {
        return Gate::allows('widget.recent_activity');
    }

    public function getTableHeading(): string
    {
        return __('audit::audit.widgets.recent_activity');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Activity::query()->with(['causer'])->latest())
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
            ->emptyStateHeading(__('audit::audit.empty.activity'))
            ->emptyStateIcon('heroicon-o-clock')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->since()
                    ->tooltip(fn (Activity $record): string => $record->created_at?->toDateTimeString() ?? '')
                    ->sortable(),

                TextColumn::make('event')
                    ->label(__('audit::audit.fields.event'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === null
                        ? __('common.none')
                        : __("audit::audit.events.{$state}"))
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label(__('audit::audit.fields.description'))
                    ->wrap()
                    ->limit(60),

                TextColumn::make('causer.name')
                    ->label(__('audit::audit.fields.causer'))
                    ->placeholder(__('audit::audit.system'))
                    ->icon('heroicon-m-user'),

                TextColumn::make('log_name')
                    ->label(__('audit::audit.fields.log_name'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
