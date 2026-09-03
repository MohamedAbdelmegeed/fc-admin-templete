<?php

declare(strict_types=1);

namespace Src\Contexts\Identity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Identity\Domain\Models\User;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => null,
            'locale' => 'ar',
            'timezone' => config('app.timezone'),
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function invited(): self
    {
        return $this->state(fn (): array => [
            'status' => UserStatus::Invited,
            'email_verified_at' => null,
        ]);
    }

    public function suspended(): self
    {
        return $this->state(fn (): array => ['status' => UserStatus::Suspended]);
    }

    public function unverified(): self
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
