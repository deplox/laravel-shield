<?php

declare(strict_types=1);

namespace Deplox\Shield\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 */
interface HasMorphTokens extends OwnsTokens
{
    /** @return MorphOne<Model, $this> */
    public function token(): MorphOne;

    /** @return MorphMany<Model, $this> */
    public function tokens(): MorphMany;
}
