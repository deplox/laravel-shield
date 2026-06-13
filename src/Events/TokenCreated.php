<?php

declare(strict_types=1);

namespace Deplox\Shield\Events;

use Deplox\Shield\Contracts\IsAuthToken;
use Illuminate\Database\Eloquent\Model;

/**
 * Dispatched when a token is issued.
 *
 * Useful for audit logging downstream. Fired on every createToken() call,
 * regardless of token type. Load $token->owner if you need the owning user.
 */
final class TokenCreated
{
    public function __construct(
        public readonly Model&IsAuthToken $token,
    ) {}
}
