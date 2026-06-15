<?php

declare(strict_types=1);

namespace Deplox\Shield\Concerns;

use Deplox\Shield\Shield;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasMorphTokens
{
    use CreatesTokens;

    /** @return MorphOne<\Illuminate\Database\Eloquent\Model, $this> */
    public function token(): MorphOne
    {
        return $this->tokens()->one()->latestOfMany();
    }

    /** @return MorphMany<\Illuminate\Database\Eloquent\Model, $this> */
    public function tokens(): MorphMany
    {
        return $this->morphMany(app(Shield::class)->tokenModel, 'owner');
    }
}
