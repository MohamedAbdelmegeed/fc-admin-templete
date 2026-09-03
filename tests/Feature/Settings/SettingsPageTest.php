<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

use Src\Contexts\Settings\Domain\Settings\AppearanceSettings;
use Src\Contexts\Settings\Domain\Settings\MailSettings;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageAppearance;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageGeneral;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageMail;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageNotifications;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageSecurity;
use Src\Contexts\Settings\Presentation\Filament\Pages\ManageStorage;

/*
|--------------------------------------------------------------------------
| صفحات الإعدادات
|--------------------------------------------------------------------------
| الإعدادات هي اللي بتتحكم في الثيم والبريد والأمان — صفحة إعدادات
| بتفتح غلط أو صلاحية ناقصة معناها إن مالك المنتج مش قادر يشغّل النظام.
*/

$pages = [
    'general' => ManageGeneral::class,
    'appearance' => ManageAppearance::class,
    'storage' => ManageStorage::class,
    'mail' => ManageMail::class,
    'notifications' => ManageNotifications::class,
    'security' => ManageSecurity::class,
];

it('يفتح كل صفحات الإعدادات للمدير', function (string $page): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    livewire($page)->assertOk();
})->with($pages);

it('يمنع المحرّر من كل صفحات الإعدادات', function (string $page): void {
    $tenant = currentTenant();
    $editor = userWithRole('editor', $tenant);

    actAsIn($editor, $tenant);

    expect($page::canAccess())->toBeFalse();
})->with($pages);

it('يحفظ لون العلامة ويطبّقه على السُّلَّم', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    livewire(ManageAppearance::class)
        ->fillForm(['primary_color' => '#0F3D46'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(AppearanceSettings::class)->primary_color)->toBe('#0F3D46');
});

it('يرفض لون علامة تباينه ضعيف', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    // أصفر فاتح — تباينه على الأبيض أقل بكتير من ٤٫٥:١، ولو عدّى
    // هيخلّي نص كل الأزرار غير مقروء.
    livewire(ManageAppearance::class)
        ->fillForm(['primary_color' => '#FFF176'])
        ->call('save')
        ->assertHasFormErrors(['primary_color']);
});

it('يرفض خط مش في القائمة البيضاء', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    livewire(ManageAppearance::class)
        ->fillForm(['font_family' => 'Comic Sans MS'])
        ->call('save')
        ->assertHasFormErrors(['font_family']);
});

it('يخزّن كلمة مرور البريد مشفّرة', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    livewire(ManageMail::class)
        ->fillForm(['password' => 'super-secret-value'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(MailSettings::class)->password)->toBe('super-secret-value');

    // ⚠️ الفحص المهم: القيمة في الجدول نفسه مش مقروءة. من غير ده
    // أي حد معاه وصول للـ DB بياخد كلمة مرور SMTP.
    $stored = DB::table('settings')
        ->where('group', 'mail')
        ->where('name', 'password')
        ->value('payload');

    expect($stored)->not->toContain('super-secret-value');
});

it('يمنع خفض أقل طول لكلمة المرور تحت الأرضية الصلبة', function (): void {
    $tenant = currentTenant();
    $admin = userWithRole('admin', $tenant);

    actAsIn($admin, $tenant);

    livewire(ManageSecurity::class)
        ->fillForm(['password_min_length' => 4])
        ->call('save')
        ->assertHasFormErrors(['password_min_length']);
});
