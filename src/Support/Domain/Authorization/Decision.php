<?php

declare(strict_types=1);

namespace Src\Support\Domain\Authorization;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * سلسلة فحص تفويض. بتقف عند أول رفض وبترجّع سببه.
 * الترتيب مقصود: الصلاحية الأول، بعدين قواعد الأعمال.
 */
final class Decision
{
    private ?Response $denial = null;

    /**
     * الفحص الأول دايماً: هل معاه الصلاحية أصلاً؟
     *
     * الرفض هنا **بدون رسالة** عن قصد — Filament بيخفي الإجراء بالكامل
     * لما مفيش رسالة، وده المطلوب: اللي مالوش صلاحية مالوش دعوة يعرف
     * إن الميزة موجودة. رفض قاعدة الأعمال بيرجّع رسالة فيظهر الزرار
     * معطّل بالسبب. (docs/19 بند ٨)
     */
    public function permission(
        Authenticatable $user,
        Policy $policy,
        string $action,
    ): self {
        if ($this->denial !== null) {
            return $this;
        }

        $permission = $policy->permissionFor($action);

        if (! $user->hasPermissionTo($permission, config('authorization.guard'))) {
            $this->denial = Response::deny();
        }

        return $this;
    }

    /**
     * قاعدة أعمال. الرسالة بتوصل للمستخدم فعلاً — خليها مفيدة.
     *
     * @param  array<string, string|int>  $replace
     */
    public function rule(bool $passes, string $messageKey, array $replace = []): self
    {
        if ($this->denial !== null || $passes) {
            return $this;
        }

        $this->denial = Response::deny(__("authorization.denied.{$messageKey}", $replace));

        return $this;
    }

    /**
     * نفس rule() بس بتقييم كسول — للفحوصات اللي فيها استعلام.
     *
     * @param  array<string, string|int>  $replace
     */
    public function ruleUsing(callable $passes, string $messageKey, array $replace = []): self
    {
        if ($this->denial !== null) {
            return $this;
        }

        return $this->rule((bool) $passes(), $messageKey, $replace);
    }

    /**
     * رفض بإخفاء وجود السجل (404 بدل 403).
     * استخدمها لما مجرد معرفة إن السجل موجود يعتبر تسريب.
     */
    public function ruleOrNotFound(bool $passes, string $messageKey): self
    {
        if ($this->denial !== null || $passes) {
            return $this;
        }

        $this->denial = Response::denyAsNotFound(__("authorization.denied.{$messageKey}"));

        return $this;
    }

    public function response(): Response
    {
        return $this->denial ?? Response::allow();
    }
}
