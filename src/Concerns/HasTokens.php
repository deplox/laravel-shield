<?php

declare(strict_types=1);

namespace Deplox\Shield\Concerns;

use Deplox\Shield\Shield;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasTokens
{
    use CreatesTokens;

    /** @return HasOne<\Illuminate\Database\Eloquent\Model, $this> */
    public function token(): HasOne
    {
        return $this->tokens()->one()->latestOfMany();
    }

    /** @return HasMany<\Illuminate\Database\Eloquent\Model, $this> */
    public function tokens(): HasMany
    {
        return $this->hasMany(app(Shield::class)->tokenModel);
    }
}
