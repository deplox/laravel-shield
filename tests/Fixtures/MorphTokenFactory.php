<?php

declare(strict_types=1);

namespace Deplox\Shield\Tests\Fixtures;

use Deplox\Shield\Enums\TokenType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<MorphToken>
 */
final class MorphTokenFactory extends Factory
{
    protected $model = MorphToken::class;

    public function definition(): array
    {
        return [
            'type' => TokenType::Bearer,
            'token' => TokenType::Bearer->generate(),
            'expires_at' => Date::now()->addYear(),
        ];
    }
}
