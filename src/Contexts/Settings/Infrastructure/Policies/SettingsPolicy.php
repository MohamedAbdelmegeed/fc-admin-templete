<?php

declare(strict_types=1);

namespace Src\Contexts\Settings\Infrastructure\Policies;

use Illuminate\Auth\Access\Response;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Support\Domain\Authorization\Policy;

/**
 * سياسة واحدة لكل مجموعات الإعدادات — كل مجموعة ليها قدرة منفصلة، عشان
 * اللي بيظبط البريد ما يبقاش تلقائياً بيظبط الأمان. (docs/05 بند ٧)
 *
 * الإعدادات مش موديلات Eloquent، فالسياسة بتتسجّل صراحةً على كلاس كل
 * مجموعة في SettingsServiceProvider — الاكتشاف بالاصطلاح مش هيلاقيها
 * لأنها في Domain\Settings مش Domain\Models.
 */
final class SettingsPolicy extends Policy
{
    protected function resource(): string
    {
        return 'settings';
    }

    public function viewAny(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'view_any')->response();
    }

    public function update(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'update')->response();
    }

    public function manageGeneral(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'manage_general')->response();
    }

    public function manageAppearance(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'manage_appearance')->response();
    }

    public function manageStorage(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'manage_storage')->response();
    }

    public function manageMail(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'manage_mail')->response();
    }

    public function manageNotifications(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'manage_notifications')->response();
    }

    public function manageSecurity(User $user): Response
    {
        return $this->decide()->permission($user, $this, 'manage_security')->response();
    }
}
