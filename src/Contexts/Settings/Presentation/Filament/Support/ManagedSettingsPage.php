<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Support;

use BackedEnum;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Gate;
use ReflectionClass;
use ReflectionNamedType;
use Src\Support\Presentation\Filament\Navigation\NavigationGroup;

/**
 * الأب لكل صفحات الإعدادات.
 *
 * الكلاس ده **بره** مجلد Pages عن قصد — الاكتشاف التلقائي في
 * ContextServiceProvider بيمشي على المجلد ده، وأب مجرّد مالوش لازمة
 * في التنقّل.
 *
 * كل صفحة بتعرّف قدرة واحدة، والقدرة دي بتتفحص مرتين: مرة عشان الصفحة
 * تظهر في القائمة أصلاً، ومرة عشان الحفظ يشتغل. إخفاء الصفحة تجربة
 * استخدام — السياسة هي الأمان. (CLAUDE.md قاعدة ٣)
 */
abstract class ManagedSettingsPage extends SettingsPage
{
    /** اسم القدرة في SettingsPolicy — مثال: manageAppearance */
    abstract protected static function ability(): string;

    /** اسم التبويب بتاع الصفحة جوه شاشة الإعدادات. */
    abstract public static function tabLabel(): string;

    public static function canAccess(): bool
    {
        return Gate::allows(static::ability(), static::getSettings());
    }

    public function canEdit(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->getLabel();
    }

    /**
     * مدخل واحد في القائمة اسمه «الإعدادات» بدل ٦ مداخل — والباقي
     * بيتوصل له من التبويبات. الصفحات لسه ليها مسارات مستقلة، فالإخفاء
     * ده تنظيم قائمة مش حماية؛ canAccess() فوق هي اللي بتحمي.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::class === SettingsTabs::navigationPage();
    }

    public static function getNavigationLabel(): string
    {
        return __('settings::settings.pages.index');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    /**
     * التبويبات بتتبني من الصفحة الحالية عشان تعلّم على نفسها.
     *
     * @return list<array{page: class-string<self>, label: string, url: string, active: bool}>
     */
    public function settingsTabs(): array
    {
        return SettingsTabs::visible(static::class);
    }

    public function getSavedNotificationTitle(): ?string
    {
        return __('settings::settings.notifications.saved');
    }

    /**
     * حقل نصي فاضي في Filament بيرجع null، وكلاسات الإعدادات معرّفة
     * `string` مش `?string` — فالحفظ كان بيرمي TypeError جوه spatie
     * من غير ما يقول أنهي حقل. بنحوّل الـ null للقيمة الفاضية المناسبة
     * للنوع، والأنواع اللي مش عارفينها بتعدّي زي ما هي عشان الخطأ
     * الحقيقي يفضل ظاهر.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $settings = new ReflectionClass(static::getSettings());

        foreach ($data as $name => $value) {
            if ($value !== null || ! $settings->hasProperty($name)) {
                continue;
            }

            $type = $settings->getProperty($name)->getType();

            if (! $type instanceof ReflectionNamedType || $type->allowsNull()) {
                continue;
            }

            $data[$name] = match ($type->getName()) {
                'string' => '',
                'int' => 0,
                'float' => 0.0,
                'bool' => false,
                'array' => [],
                default => $value,
            };
        }

        return $data;
    }
}
