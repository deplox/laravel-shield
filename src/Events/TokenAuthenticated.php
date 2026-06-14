<?php

declare(strict_types=1);

namespace Deplox\Shield\Events;

use Deplox\Shield\Contracts\IsAuthToken;
use Illuminate\Database\Eloquent\Model;

final class TokenAuthenticated
{
    public function __construct(
        public readonly Model&IsAuthToken $token,
    ) {}
}
