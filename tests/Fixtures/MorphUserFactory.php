<?php

declare(strict_types=1);

namespace Deplox\Shield\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * @extends Factory<MorphUser>
 */
final class MorphUserFactory extends Factory
{
    protected $model = MorphUser::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'pa$$word',
            'remember_token' => Str::random(60),
            'email_verified_at' => Date::now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
