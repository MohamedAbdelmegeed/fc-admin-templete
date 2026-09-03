<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Presentation\Filament\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

/**
 * شاشة دخول بتقبل **اسم المستخدم أو البريد** في نفس الحقل.
 *
 * Filament الافتراضي بيقبل البريد بس (والحقل عليه ->email() فبيرفض أي
 * حاجة من غير @). هنا الحقل نصّي عادي، وبنقرر عمود التحقق حسب المدخل.
 */
final class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label(__('identity::identity.auth.login_field'))
            ->required()
            ->maxLength(180)
            ->autocomplete('username')
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = (string) $data['login'];

        // القرار على وجود @ بس — مفيش استعلام هنا عشان ما نديش المهاجم
        // طريقة يعرف بيها إن الحساب موجود ولا لأ. (docs/20 بند ١٠)
        $column = str_contains($login, '@') ? 'email' : 'username';

        return [
            $column => $login,
            'password' => $data['password'],
        ];
    }

    /**
     * الأساسي بيعلّق رسالة الفشل على data.email — وحقلنا اسمه login،
     * فالرسالة كانت هتظهر بره الحقل من غير ما المستخدم يشوفها جنبه.
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
