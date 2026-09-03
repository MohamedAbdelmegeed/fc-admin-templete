<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Presentation\Filament\Support;

use Src\Contexts\Settings\Presentation\Filament\Pages\ManageAppearance;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageGeneral;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageMail;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageNotifications;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageSecurity;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageStorage;

/**
 * الإعدادات مدخل واحد في القائمة، وجوّاه تبويبات — مش ٦ عناصر منفصلة
 * بتاكل نص السايدبار. (docs/07 بند ٢: أقصى ٧±٢ عنصر ظاهر)
 *
 * الصفحات بتفضل **صفحات حقيقية** بمسارات وسياسات مستقلة: التبويب
 * تجربة استخدام، والسياسة هي الأمان. حد يعرف رابط صفحة مش مسموح له
 * بيها لسه هيتقفل في وشه من canAccess(). (CLAUDE.md قاعدة ٣)
 *
 * @phpstan-type SettingsPageClass class-string<ManagedSettingsPage>
 */
final class SettingsTabs
{
    /**
     * الترتيب هنا هو ترتيب التبويبات وترتيب الأولوية للمدخل الوحيد
     * في القائمة — أول صفحة مسموح بيها هي اللي بتحمل اسم «الإعدادات».
     *
     * @var list<class-string<ManagedSettingsPage>>
     */
    private const PAGES = [
        ManageGeneral::class,
        ManageAppearance::class,
        ManageStorage::class,
        ManageMail::class,
        ManageNotifications::class,
        ManageSecurity::class,
    ];

    /** @return list<class-string<ManagedSettingsPage>> */
    public static function all(): array
    {
        return self::PAGES;
    }

    /**
     * التبويبات اللي المستخدم ده بيشوفها فعلاً — مفيش تبويب بيودّي لـ ٤٠٣.
     *
     * @return list<array{page: class-string<ManagedSettingsPage>, label: string, url: string, active: bool}>
     */
    public static function visible(string $currentPage): array
    {
        $tabs = [];

        foreach (self::PAGES as $page) {
            if (! $page::canAccess()) {
                continue;
            }

            $tabs[] = [
                'page' => $page,
                'label' => $page::tabLabel(),
                'url' => $page::getUrl(),
                'active' => $page === $currentPage,
            ];
        }

        return $tabs;
    }

    /**
     * الصفحة اللي الطلب الحالي واقف عليها — بالمقارنة على المسار، لأن
     * الـ render hook مابيوصلّهوش كائن الصفحة.
     *
     * @return class-string<ManagedSettingsPage>|null
     */
    public static function currentPage(): ?string
    {
        $path = '/'.ltrim(request()->path(), '/');

        foreach (self::PAGES as $page) {
            if (parse_url($page::getUrl(), PHP_URL_PATH) === $path) {
                return $page;
            }
        }

        return null;
    }

    /**
     * الصفحة اللي هتظهر في القائمة. لو المستخدم مش مسموح له بأول
     * صفحة، المدخل بينتقل للي بعدها بدل ما يختفي خالص.
     *
     * @return class-string<ManagedSettingsPage>|null
     */
    public static function navigationPage(): ?string
    {
        foreach (self::PAGES as $page) {
            if ($page::canAccess()) {
                return $page;
            }
        }

        return null;
    }
}
