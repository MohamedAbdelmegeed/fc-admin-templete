<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Resources;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Contexts\Identity\Domain\Models\Role;
use Src\Contexts\Identity\Presentation\Filament\Resources\RoleResource\Pages;
use Src\Support\Infrastructure\Authorization\PermissionBuilder;
use Src\Support\Presentation\Filament\Navigation\NavigationGroup;

/**
 * كتبناها بإيدنا بدل Shield: الصلاحيات عندنا جاية من
 * config/authorization.php مش من مسح الموارد، وقيمة Shield الأساسية
 * (التوليد التلقائي) كنا هنعطّلها أصلاً. (docs/02 بند ٨)
 *
 * الشاشة بتعرض الصلاحيات **مجمّعة** حسب group من الكونفيج، بأسماء
 * مترجمة، مع «تحديد الكل» لكل مجموعة.
 */
final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * الأدوار **عامة** مش تابعة لمؤسسة (tenant_id = null)، والربط
     * بالمؤسسة بيحصل في model_has_roles. من غير السطر ده Filament
     * بيحاول يقصر الاستعلام على علاقة tenant اللي مش موجودة على Role
     * وبيرمي LogicException في كل صفحة بتلمس الأدوار. (docs/02 بند ١)
     */
    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Identity->getLabel();
    }

    public static function getModelLabel(): string
    {
        return __('identity::identity.role.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('identity::identity.role.plural');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['users', 'permissions']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('identity::identity.role.sections.details'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('identity::identity.role.fields.name'))
                        ->required()
                        ->maxLength(60)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('identity::identity.role.help.name'))
                        // الدور المحمي مايتغيّرش اسمه — والـ Policy بتمنع
                        // الحفظ كمان، مش الإخفاء بس.
                        ->disabled(fn (?Role $record): bool => $record?->isProtected() === true)
                        ->saved(fn (?Role $record): bool => $record?->isProtected() !== true),

                    TextInput::make('guard_name')
                        ->label(__('identity::identity.role.fields.guard'))
                        ->default(config('authorization.guard'))
                        ->disabled()
                        ->dehydrated(),
                ]),

            ...self::permissionSections(),
        ]);
    }

    /**
     * قسم لكل مجموعة صلاحيات — الترتيب والأسماء كلها من الكونفيج
     * والترجمة، مفيش نص مكتوب هنا.
     *
     * @return list<Section>
     */
    private static function permissionSections(): array
    {
        $builder = app(PermissionBuilder::class);
        $sections = [];

        foreach ($builder->groups() as $group => $subjects) {
            $fields = [];

            foreach ($subjects as $subject => $permissions) {
                $fields[] = CheckboxList::make("permissions_{$group}_{$subject}")
                    ->label(self::subjectLabel($subject))
                    ->options(collect($permissions)
                        ->mapWithKeys(fn (string $permission): array => [$permission => permission_label($permission)])
                        ->all())
                    ->bulkToggleable()
                    ->columns(2)
                    ->columnSpanFull()
                    ->dehydrated(false);
            }

            $sections[] = Section::make(__("authorization.groups.{$group}"))
                ->columnSpanFull()
                ->collapsible()
                ->schema($fields);
        }

        return $sections;
    }

    private static function subjectLabel(string $subject): string
    {
        return match ($subject) {
            '_pages' => __('authorization.sections.pages'),
            '_widgets' => __('authorization.sections.widgets'),
            default => __("authorization.resources.{$subject}"),
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('identity::identity.role.fields.name'))
                    ->formatStateUsing(fn (string $state): string => __("authorization.roles.{$state}"))
                    ->description(fn (Role $record): string => $record->name)
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                TextColumn::make('permissions_count')
                    ->label(__('identity::identity.role.fields.permissions'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('users_count')
                    ->label(__('identity::identity.role.fields.users_count'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->label(__('common.actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->emptyStateHeading(__('identity::identity.role.empty.heading'))
            ->emptyStateDescription(__('identity::identity.role.empty.description'))
            ->emptyStateIcon('heroicon-o-shield-check');
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
