<?php

declare(strict_types=1);

namespace Src\Contexts\Notifications\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Notifications\Domain\Models\NotificationPreference;

/**
 * @extends Factory<NotificationPreference>
 */
final class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $keys = array_keys((array) config('notifications.catalog', ['user_invited' => []]));

        return [
            'user_id' => User::factory(),
            'notification_key' => $this->faker->randomElement($keys),
            'channels' => ['database'],
            'enabled' => true,
        ];
    }

    public function disabled(): self
    {
        return $this->state(fn (): array => ['enabled' => false, 'channels' => []]);
    }
}
