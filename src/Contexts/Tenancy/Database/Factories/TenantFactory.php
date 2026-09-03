<?php

declare(strict_types=1);

namespace Src\Contexts\Tenancy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Src\Contexts\Tenancy\Domain\Models\Tenant;

/**
 * @extends Factory<Tenant>
 */
final class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $slug = Str::slug($this->faker->unique()->company());

        return [
            'slug' => $slug,
            'domain' => null,
            'name' => [
                'ar' => $this->faker->company(),
                'en' => Str::headline($slug),
            ],
            'description' => [
                'ar' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'primary_color' => '#12454F',
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function onTrial(): self
    {
        return $this->state(fn (): array => ['trial_ends_at' => now()->addDays(14)]);
    }
}
