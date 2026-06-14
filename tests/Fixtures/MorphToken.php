<?php

declare(strict_types=1);

namespace Deplox\Shield\Tests\Fixtures;

use Deplox\Shield\Concerns\IsAuthToken;
use Deplox\Shield\Contracts\IsAuthToken as IsAuthTokenContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class MorphToken extends Model implements IsAuthTokenContract
{
    use IsAuthToken;

    protected $table = 'morph_tokens';

    /**
     * Override the concern's BelongsTo owner() with a polymorphic relationship.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }
}
