<?php

declare(strict_types=1);

namespace Src\Contexts\Audit\Presentation\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Gate;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Identity\Presentation\Filament\Resources\UserResource;
use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Contexts\Settings\Domain\Settings\GeneralSettings;
use Src\Contexts\Settings\Domain\Settings\MailSettings;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageAppearance;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageGeneral;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageMail;
use Src\Support\Application\Contracts\TenantContext;
use Throwable;

/**
 * «إيه اللي لسه ناقص عشان المؤسسة دي تبقى شغّالة؟»
 *
 * كل بند بيقرا حالة حقيقية من قاعدة البيانات — مش checkbox المستخدم
 * بيدوس عليه. الودجت بيختفي لوحده لما كل حاجة تخلص، عشان ما يفضلش
 * واخد نص اللوحة للأبد.
 */
final class OnboardingChecklistWidget extends Widget
{
    protected string $view = 'audit.onboarding-checklist';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        if (! Gate::allows('widget.onboarding_checklist')) {
            return false;
        }

        // خلصت؟ يبقى مالهاش لازمة.
        return collect((new self)->steps())->contains(fn (array $step): bool => ! $step['done']);
    }

    /**
     * @return list<array{key: string, done: bool, url: string|null}>
     */
    public function steps(): array
    {
        return [
            [
                'key' => 'brand_name',
                'done' => $this->settingFilled(GeneralSettings::class, 'app_name'),
                'url' => $this->urlFor(ManageGeneral::class),
            ],
            [
                'key' => 'brand_logo',
                'done' => $this->settingFilled(AppearanceSettings::class, 'logo_path'),
                'url' => $this->urlFor(ManageAppearance::class),
            ],
            [
                'key' => 'mail',
                'done' => $this->settingFilled(MailSettings::class, 'from_address'),
                'url' => $this->urlFor(ManageMail::class),
            ],
            [
                'key' => 'team',
                'done' => $this->teamInvited(),
                'url' => $this->urlFor(UserResource::class),
            ],
        ];
    }

    /**
     * ⚠️ spatie/settings بيحمّل الخصائص كسول، فالـ try لازم يلفّ
     * **قراءة الخاصية** مش إنشاء الكائن — وإلا الاستثناء بيهرب قبل
     * أول migrate. (نفس المصيدة في AdminPanelProvider)
     *
     * @param  class-string  $settings
     */
    private function settingFilled(string $settings, string $property): bool
    {
        try {
            $value = app($settings)->{$property};
        } catch (Throwable) {
            return false;
        }

        if (is_array($value)) {
            return array_filter($value) !== [];
        }

        return $value !== null && $value !== '';
    }

    private function teamInvited(): bool
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return false;
        }

        // أكتر من واحد = فيه فريق فعلاً، مش المؤسِّس لوحده.
        return User::query()->inTenant($tenantId)->count() > 1;
    }

    /** الرابط بيتخفي لو المستخدم مش مسموح له بالصفحة — مفيش لينك بيودّي لـ ٤٠٣. */
    private function urlFor(string $page): ?string
    {
        try {
            return $page::canAccess() ? $page::getUrl() : null;
        } catch (Throwable) {
            return null;
        }
    }
}
