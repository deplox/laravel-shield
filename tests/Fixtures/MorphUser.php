<?php

declare(strict_types=1);

namespace Deplox\Shield\Tests\Fixtures;

use Deplox\Shield\Concerns\HasMorphTokens;
use Deplox\Shield\Contracts\HasMorphTokens as HasMorphTokensContract;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 * @property-read \Carbon\CarbonInterface $created_at
 * @property-read \Carbon\CarbonInterface $updated_at
 */
#[\Illuminate\Database\Eloquent\Attributes\UseFactory(MorphUserFactory::class)]
final class MorphUser extends Authenticatable implements HasMorphTokensContract
{
    /** @use HasFactory<MorphUserFactory> */
    use HasFactory;

    use HasMorphTokens;
    use HasUlids;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = ['name', 'email', 'password'];

    /** @var list<string> */
    protected $hidden = ['password'];
}
