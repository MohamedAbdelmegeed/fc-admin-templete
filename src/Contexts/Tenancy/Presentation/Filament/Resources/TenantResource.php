<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Presentation\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Contexts\Tenancy\Presentation\Filament\Resources\TenantResource\Pages;
use Src\Support\Infrastructure\Theming\ColorScaleGenerator;
use Src\Support\Presentation\Filament\Navigation\NavigationGroup;

/**
 * المؤسسة نفسها — مش تابعة لمستأجر، فالمورد ده **مش** داخل نطاق
 * المستأجر (isScopedToTenant = false). الحماية من TenantPolicy.
 */
final class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Tenancy->getLabel();
    }

    public static function getModelLabel(): string
    {
        return __('tenancy::tenancy.tenant.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('tenancy::tenancy.tenant.plural');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('users');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('tenancy::tenancy.tenant.sections.details'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Tabs::make('translations')
                        ->columnSpanFull()
                        ->tabs(collect(config('app.supported_locales'))
                            ->map(fn (string $locale): Tab => Tab::make($locale)
                                ->label(__("common.locales.{$locale}"))
                                ->schema([
                                    TextInput::make("name.{$locale}")
                                        ->label(__('tenancy::tenancy.tenant.fields.name'))
                                        // اللغة الاحتياطية إجبارية، الباقي اختياري.
                                        ->required($locale === config('app.fallback_locale'))
                                        ->maxLength(120),

                                    Textarea::make("description.{$locale}")
                                        ->label(__('tenancy::tenancy.tenant.fields.description'))
                                        ->rows(2)
                                        ->maxLength(500),
                                ]))
                            ->all()),

                    TextInput::make('slug')
                        ->label(__('tenancy::tenancy.tenant.fields.slug'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(60)
                        ->helperText(__('tenancy::tenancy.tenant.help.slug')),

                    TextInput::make('domain')
                        ->label(__('tenancy::tenancy.tenant.fields.domain'))
                        ->unique(ignoreRecord: true)
                        ->maxLength(180)
                        ->helperText(__('tenancy::tenancy.tenant.help.domain')),
                ]),

            Section::make(__('tenancy::tenancy.tenant.sections.branding'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    ColorPicker::make('primary_color')
                        ->label(__('tenancy::tenancy.tenant.fields.primary_color'))
                        ->hex()
                        ->required()
                        ->default('#12454F')
                        ->helperText(__('tenancy::tenancy.tenant.help.primary_color'))
                        // لون تباينه ضعيف بيخلّي كل نص أبيض فوق الأزرار
                        // غير مقروء — فبنرفضه وقت الحفظ مش بعد النشر.
                        ->rule(self::contrastRule()),
                ]),

            Section::make(__('tenancy::tenancy.tenant.sections.subscription'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('tenancy::tenancy.tenant.fields.is_active'))
                        ->default(true),

                    DateTimePicker::make('trial_ends_at')
                        ->label(__('tenancy::tenancy.tenant.fields.trial_ends_at'))
                        ->seconds(false),
                ]),
        ]);
    }

    private static function contrastRule(): \Closure
    {
        return static function (): \Closure {
            return static function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $failures = app(ColorScaleGenerator::class)->contrastFailures($value);

                if ($failures !== []) {
                    $fail(__('authorization.denied.settings.contrast_failed', [
                        'ratio' => (string) reset($failures),
                    ]));
                }
            };
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tenancy::tenancy.tenant.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (Tenant $record): string => $record->slug),

                IconColumn::make('is_active')
                    ->label(__('tenancy::tenancy.tenant.fields.is_active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label(__('tenancy::tenancy.tenant.fields.users_count'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('trial_ends_at')
                    ->label(__('tenancy::tenancy.tenant.fields.trial_ends_at'))
                    ->dateTime('d M Y')
                    ->placeholder(__('tenancy::tenancy.tenant.trial.none'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label(__('common.active')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('deactivate')
                        ->label(__('tenancy::tenancy.tenant.actions.deactivate'))
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->authorize('deactivate')
                        ->authorizationTooltip()
                        ->requiresConfirmation()
                        ->modalHeading(__('tenancy::tenancy.tenant.confirm.deactivate_heading'))
                        ->modalDescription(__('tenancy::tenancy.tenant.confirm.deactivate_body'))
                        ->action(fn (Tenant $record) => $record->update(['is_active' => false]))
                        ->successNotificationTitle(__('tenancy::tenancy.tenant.notifications.deactivated')),

                    Action::make('activate')
                        ->label(__('tenancy::tenancy.tenant.actions.activate'))
                        ->icon('heroicon-o-play-circle')
                        ->color('success')
                        ->authorize('activate')
                        ->authorizationTooltip()
                        ->action(fn (Tenant $record) => $record->update(['is_active' => true]))
                        ->successNotificationTitle(__('tenancy::tenancy.tenant.notifications.activated')),

                    DeleteAction::make(),
                    RestoreAction::make(),
                ])
                    ->label(__('common.actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords('delete'),
                ]),
            ])
            ->emptyStateHeading(__('tenancy::tenancy.tenant.empty.heading'))
            ->emptyStateDescription(__('tenancy::tenancy.tenant.empty.description'))
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateActions([
                CreateAction::make()->label(__('tenancy::tenancy.tenant.empty.cta')),
            ]);
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    /** @return list<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug', 'domain'];
    }
}
