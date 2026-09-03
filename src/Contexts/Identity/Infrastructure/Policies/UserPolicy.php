<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Infrastructure\Policies;

use Illuminate\Auth\Access\Response;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Support\Application\Contracts\TenantContext;
use Src\Support\Domain\Authorization\Policy;

/**
 * اقرا أي دالة هنا بصوت عالي — بتقرا زي جملة عربية. ده المطلوب.
 *
 * الصلاحية وقواعد الأعمال في نفس الدالة عشان الإجابة تبقى واحدة سواء
 * الطلب جه من الواجهة أو API أو Job. (docs/19)
 */
final class UserPolicy extends Policy
{
    protected function resource(): string
    {
        return 'users';
    }

    /**
     * قواعد سلامة — المدير العام نفسه مايتخطاهاش.
     * من غير دي، المدير العام يقدر يحذف نفسه ويقفل على نفسه بره النظام.
     */
    public function invariants(): array
    {
        return ['delete', 'forceDelete', 'suspend', 'impersonate'];
    }

    // ══════════════════════ قدرات بدون سجل ══════════════════════

    public function viewAny(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'view_any')->response();
    }

    public function create(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'create')->response();
    }

    public function export(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'export')->response();
    }

    // ══════════════════════ قدرات على سجل ══════════════════════

    public function view(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'view')
            // مستخدم من مؤسسة تانية: 404 مش 403 — «ممنوع» بتأكد إنه موجود.
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->response();
    }

    public function update(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->rule(! $target->trashed(), 'record_trashed')
            ->response();
    }

    public function delete(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'delete')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->rule(! $target->trashed(), 'record_trashed')
            ->rule($user->isNot($target), 'user.cannot_delete_self')
            ->ruleUsing(fn (): bool => ! $this->isLastAdmin($target), 'user.last_admin')
            ->response();
    }

    public function restore(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'restore')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->response();
    }

    public function forceDelete(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'force_delete')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->rule($user->isNot($target), 'user.cannot_delete_self')
            ->response();
    }

    // ══════════════════════ قدرات مخصصة ══════════════════════

    public function suspend(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->rule($user->isNot($target), 'user.cannot_suspend_self')
            ->rule($target->status !== UserStatus::Suspended, 'user.already_suspended')
            ->ruleUsing(fn (): bool => ! $this->isLastAdmin($target), 'user.last_admin')
            ->response();
    }

    public function activate(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'update')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->rule($target->status !== UserStatus::Active, 'user.already_active')
            ->response();
    }

    public function impersonate(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'impersonate')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->rule($user->isNot($target), 'user.cannot_impersonate_self')
            // قاعدة سلامة: مفيش انتحال لمدير عام — وإلا أي منتحِل بيرقّي
            // نفسه لأعلى صلاحية في النظام.
            ->rule(
                ! $target->hasRole(config('authorization.super_admin_role')),
                'user.cannot_impersonate_super_admin',
            )
            ->response();
    }

    public function resetPassword(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'reset_password')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->response();
    }

    public function forceLogout(User $user, User $target): Response
    {
        return $this->decide()
            ->permission($user, $this, 'force_logout')
            ->ruleOrNotFound($this->sharesTenant($target), 'record_not_found')
            ->response();
    }

    /**
     * $target اختياري: الواجهة بتسأل عن القدرة قبل ما يكون فيه سجل
     * (شاشة الإنشاء)، فبتبعت اسم الكلاس بدل الموديل.
     */
    public function assignRoles(User $user, ?User $target = null): Response
    {
        return $this->decide()
            ->permission($user, $this, 'assign_roles')
            ->ruleOrNotFound($target === null || $this->sharesTenant($target), 'record_not_found')
            ->rule($target === null || $user->isNot($target), 'user.self_target')
            ->response();
    }

    // ══════════════════════ مساعدات ══════════════════════

    /** بره سياق مؤسسة (CLI مثلاً) مفيش عزل نطبّقه. */
    private function sharesTenant(User $target): bool
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return true;
        }

        return $target->tenants()->whereKey($tenantId)->exists();
    }

    /** آخر مدير في المؤسسة مايتشيلش — وإلا المؤسسة تبقى من غير إدارة. */
    private function isLastAdmin(User $target): bool
    {
        if (! $target->hasRole('admin') && ! $target->hasRole(config('authorization.super_admin_role'))) {
            return false;
        }

        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return false;
        }

        return User::query()
            ->whereKeyNot($target->getKey())
            ->inTenant($tenantId)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', config('authorization.super_admin_role')]))
            ->doesntExist();
    }
}
