<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Resources;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Src\Contexts\Identity\Application\Actions\SyncUserRolesAction;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Identity\Presentation\Filament\Resources\UserResource\Pages;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Presentation\Filament\Navigation\NavigationGroup;

/**
 * المورد المرجعي للقالب — كل الأنماط المطلوبة موجودة هنا:
 * تفويض بالـ Policy، عزل مستأجر، منع حفظ فعلي للحقول الحساسة،
 * بادچ مكاش، بحث شامل، وحالة فارغة تعليمية.
 */
final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * المستخدم عضو في المؤسسة عبر many-to-many مش بعمود tenant_id،
     * فلازم نقول لـ Filament يقصر بـ whereHas('tenants') بدل ما يدوّر
     * على علاقة tenant() المفردة (اللي مش موجودة على User).
     */
    protected static ?string $tenantOwnershipRelationshipName = 'tenants';

    /** العلاقة المقابلة على موديل المؤسسة — Tenant::users(). */
    protected static ?string $tenantRelationshipName = 'users';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Identity->getLabel();
    }

    public static function getModelLabel(): string
    {
        return __('identity::identity.user.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('identity::identity.user.plural');
    }

    /** بيمرّ على الـ Policy — مش على نص صلاحية. */
    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    /**
     * البادچ بيتحسب في كل طلب لكل مورد ظاهر — الكاش إلزامي مش اختياري.
     * (docs/07 بند ٤)
     */
    public static function getNavigationBadge(): ?string
    {
        $tenantId = app(TenantContext::class)->id() ?? 'global';

        return Cache::remember(
            "nav:invited-users:{$tenantId}",
            now()->addMinutes(2),
            function (): ?string {
                $count = static::getEloquentQuery()->where('status', UserStatus::Invited)->count();

                return $count > 0 ? (string) $count : null;
            },
        );
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('identity::identity.user.badges.invited_users');
    }

    /** eager loading صريح — من غيره الجدول بيعمل N+1 على الأدوار. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles:id,name']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('identity::identity.user.sections.account'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('identity::identity.user.fields.name'))
                        ->required()
                        ->maxLength(120),

                    TextInput::make('email')
                        ->label(__('identity::identity.user.fields.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(180),

                    TextInput::make('username')
                        ->label(__('identity::identity.user.fields.username'))
                        ->helperText(__('identity::identity.user.help.username'))
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(60),

                    TextInput::make('phone')
                        ->label(__('identity::identity.user.fields.phone'))
                        ->tel()
                        ->maxLength(32),

                    TextInput::make('password')
                        ->label(__('identity::identity.user.fields.password'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create'),
                ]),

            Section::make(__('identity::identity.user.sections.access'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('status')
                        ->label(__('identity::identity.user.fields.status'))
                        ->options(UserStatus::class)
                        ->default(UserStatus::Invited)
                        ->required(),

                    // حقل حساس: الإخفاء مش أمان — لازم منع حفظ فعلي كمان.
                    // (docs/20 بند ٢)
                    //
                    // ⚠️ الحفظ بيعدّي من Action مش من sync() بتاع العلاقة:
                    // model_has_roles.tenant_id عمود NOT NULL، وحفظ العلاقة
                    // المجرّد بيكتب الصف من غيره فيقع بـ 23502. spatie
                    // بيحطّ المؤسسة كـ pivot value جوه assignRole() بس.
                    Select::make('roles')
                        ->label(__('identity::identity.user.fields.roles'))
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText(__('identity::identity.user.help.roles'))
                        ->visible(fn (?User $record): bool => self::canAssignRoles($record))
                        ->saved(fn (?User $record): bool => self::canAssignRoles($record))
                        ->saveRelationshipsUsing(function (?User $record, mixed $state): void {
                            // التفويض بيتفحص هنا كمان — saved() تجربة استخدام،
                            // ودي النقطة اللي بتكتب فعلاً.
                            if ($record === null || ! self::canAssignRoles($record)) {
                                return;
                            }

                            app(SyncUserRolesAction::class)->handle(
                                $record,
                                array_values(array_filter((array) $state)),
                                Filament::getTenant()?->getKey(),
                            );
                        }),
                ]),

            Section::make(__('identity::identity.user.sections.preferences'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('locale')
                        ->label(__('identity::identity.user.fields.locale'))
                        ->options(fn (): array => collect(config('app.supported_locales'))
                            ->mapWithKeys(fn (string $locale): array => [$locale => __("common.locales.{$locale}")])
                            ->all())
                        ->default(config('app.locale'))
                        ->helperText(__('identity::identity.user.help.locale'))
                        ->required(),

                    Select::make('timezone')
                        ->label(__('identity::identity.user.fields.timezone'))
                        ->options(fn (): array => collect(timezone_identifiers_list())
                            ->mapWithKeys(fn (string $timezone): array => [$timezone => $timezone])
                            ->all())
                        ->searchable()
                        ->default(config('app.timezone')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('identity::identity.user.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (User $record): string => $record->email)
                    ->wrap(),

                TextColumn::make('roles.name')
                    ->label(__('identity::identity.user.fields.roles'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("authorization.roles.{$state}"))
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('common.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label(__('identity::identity.user.fields.last_login'))
                    ->dateTime('d M Y')
                    ->since()
                    ->placeholder(__('common.never'))
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
                SelectFilter::make('status')
                    ->label(__('common.status'))
                    ->options(UserStatus::class)
                    ->multiple(),

                SelectFilter::make('roles')
                    ->label(__('identity::identity.user.fields.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
                    ->label(__('common.actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // فحص كل سجل بالـ Policy — مش فحص شامل واحد.
                    DeleteBulkAction::make()->authorizeIndividualRecords('delete'),
                ]),
            ])
            ->emptyStateHeading(__('identity::identity.user.empty.heading'))
            ->emptyStateDescription(__('identity::identity.user.empty.description'))
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                CreateAction::make()->label(__('identity::identity.user.empty.cta')),
            ]);
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /** @return list<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    /** @return array<string, string> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('identity::identity.user.fields.email') => $record->email,
            __('identity::identity.user.fields.roles') => $record->roles->pluck('name')->join(', '),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('roles:id,name');
    }

    private static function canAssignRoles(?User $record): bool
    {
        return auth()->user()?->can('assignRoles', $record ?? User::class) === true;
    }
}
