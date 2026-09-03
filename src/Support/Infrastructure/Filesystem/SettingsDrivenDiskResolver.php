<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Filesystem;

use Src\Contexts\Settings\Domain\Settings\StorageSettings;
use Src\Support\Application\Contracts\DiskResolver;
use Throwable;

/**
 * بيترجم «مجموعة وسائط» لـ «اسم ديسك» من الإعدادات وقت التشغيل.
 * مفيش 's3' ولا 'local' مكتوبين في أي كود تاني. (docs/04 بند ٣)
 */
final class SettingsDrivenDiskResolver implements DiskResolver
{
    private ?StorageSettings $settings = null;

    public function for(string $collection): string
    {
        $settings = $this->settings();

        if ($settings === null) {
            return (string) config('filesystems.default');
        }

        // ١. تجاوز صريح لمجموعة معيّنة
        if (($override = $settings->collection_disks[$collection] ?? null) !== null) {
            return $override;
        }

        // ٢. المجموعات الخاصة
        if ($this->isPrivate($collection)) {
            return $settings->private_disk;
        }

        // ٣. الافتراضي
        return $settings->default_disk;
    }

    public function forConversions(string $collection): string
    {
        $settings = $this->settings();

        // التحويلات دايماً على ديسك عام — المصغّرات مش سرية.
        return $settings?->conversions_disk ?: $this->for($collection);
    }

    public function isPrivate(string $collection): bool
    {
        $settings = $this->settings();

        return $settings !== null
            && in_array($collection, $settings->private_collections, true);
    }

    /**
     * الإعدادات ممكن ماتكونش موجودة لسه (أول migrate) — ساعتها بنرجع
     * لكونفيج Laravel بدل ما نكسر كل أمر artisan. (docs/05 بند ٦)
     *
     * ⚠️ spatie/settings بيحمّل كسول: app(Settings::class) بينجح والاستعلام
     * بيحصل عند أول قراءة خاصية. عشان كده بنقرا خاصية جوه الـ try —
     * وإلا الاستثناء بيهرب لأول استدعاء بره.
     */
    private function settings(): ?StorageSettings
    {
        if ($this->settings instanceof StorageSettings) {
            return $this->settings;
        }

        try {
            $settings = app(StorageSettings::class);

            // القراءة دي هي اللي بتجبر التحميل — مش سطر زايد.
            if ($settings->default_disk === '') {
                return null;
            }

            return $this->settings = $settings;
        } catch (Throwable) {
            return null;
        }
    }
}
