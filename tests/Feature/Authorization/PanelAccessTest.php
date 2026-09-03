<?php

declare(strict_types=1);

use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

use Spatie\Permission\PermissionRegistrar;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Identity\Presentation\Filament\Pages\Login;
use Src\Support\Application\Contracts\TenantContext;

/*
|--------------------------------------------------------------------------
| الدخول للوحة قبل اختيار المؤسسة
|--------------------------------------------------------------------------
| مصيدة حقيقية اتكشفت أثناء البناء: الأدوار مربوطة بالمؤسسة، لكن
| Filament بينادي canAccessPanel() بعد المصادقة مباشرة — قبل ما تكون
| فيه مؤسسة مختارة. من غير الاحتياط ده محدش كان يقدر يدخل أصلاً.
*/

it('يسمح بالدخول للوحة قبل اختيار المؤسسة', function (string $role): void {
    $user = userWithRole($role);

    // ده بالظبط اللي بيحصل بعد تسجيل الدخول: مفيش مؤسسة لسه.
    app(TenantContext::class)->set(null);
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    test()->actingAs($user);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
})->with(['super_admin', 'admin', 'editor', 'viewer']);

it('يمنع المستخدم الموقوف من دخول اللوحة', function (): void {
    $user = userWithRole('admin');
    $user->update(['status' => UserStatus::Suspended]);

    test()->actingAs($user);

    expect($user->fresh()->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('يمنع مستخدماً بلا عضوية في أي مؤسسة', function (): void {
    syncAuthorization();

    $orphan = User::factory()->create();

    test()->actingAs($orphan);

    expect($orphan->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| الدخول باسم المستخدم أو بالبريد
|--------------------------------------------------------------------------
*/

it('يقبل الدخول باسم المستخدم', function (): void {
    $user = userWithRole('super_admin');
    $user->update(['username' => 'suadmin']);

    livewire(Login::class)
        ->fillForm(['login' => 'suadmin', 'password' => 'password'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->id())->toBe($user->getKey());
});

it('يقبل الدخول بالبريد الإلكتروني', function (): void {
    $user = userWithRole('super_admin');

    livewire(Login::class)
        ->fillForm(['login' => $user->email, 'password' => 'password'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->id())->toBe($user->getKey());
});

it('يرفض كلمة مرور خاطئة', function (): void {
    $user = userWithRole('super_admin');
    $user->update(['username' => 'suadmin']);

    livewire(Login::class)
        ->fillForm(['login' => 'suadmin', 'password' => 'wrong-password'])
        ->call('authenticate')
        ->assertHasFormErrors();

    expect(auth()->check())->toBeFalse();
});
