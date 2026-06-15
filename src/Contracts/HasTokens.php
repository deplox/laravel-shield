<?php

declare(strict_types=1);

namespace Deplox\Shield\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 */
interface HasTokens extends OwnsTokens
{
    /** @return HasOne<Model, $this> */
    public function token(): HasOne;

    /** @return HasMany<Model, $this> */
    public function tokens(): HasMany;
}
