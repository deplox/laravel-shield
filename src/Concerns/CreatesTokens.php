<?php

declare(strict_types=1);

namespace Deplox\Shield\Concerns;

use DateTimeInterface;
use Deplox\Shield\Contracts\IsAuthToken as IsAuthTokenContract;
use Deplox\Shield\Enums\TokenLimitBehavior;
use Deplox\Shield\Enums\TokenType;
use Deplox\Shield\Events\TokenCreated;
use Deplox\Shield\Exceptions\TokenLimitExceededException;
use Deplox\Shield\Shield;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CreatesTokens
{
    public function createToken(TokenType $type = TokenType::Bearer, ?DateTimeInterface $expiresAt = null, ?string $name = null): Model&IsAuthTokenContract
    {
        $shield = app(Shield::class);

        $expiresAt ??= $shield->defaultTokenExpiration !== null
            ? now()->addSeconds($shield->defaultTokenExpiration)
            : null;

        // Generate entropy outside the transaction — random_bytes() needs no lock.
        $random = $type->generate();

        $create = function () use ($shield, $type, $expiresAt, $name, $random): Model&IsAuthTokenContract {
            $this->enforceTokenLimit($shield);

            return $this->tokens()->create([
                'name' => $name,
                'type' => $type,
                'token' => $random,
                'expires_at' => $expiresAt,
            ]);
        };

        // Wrap in a transaction only when a limit is configured so that the
        // lockForUpdate() in enforceTokenLimit() prevents TOCTOU races.
        $token = $shield->maxTokensPerUser !== null
            ? DB::transaction($create)
            : $create();

        $token->setPlain($shield->decorateToken($random));

        event(new TokenCreated($token));

        return $token;
    }

    /**
     * Enforce the per-user token cap configured on Shield.
     *
     * - When maxTokensPerUser is null, no enforcement.
     * - When at or above the limit and onTokenLimit is Reject, throw.
     * - When at or above the limit and onTokenLimit is PruneOldest,
     *   delete the oldest tokens until count + 1 <= limit.
     */
    private function enforceTokenLimit(Shield $shield): void
    {
        if ($shield->maxTokensPerUser === null) {
            return;
        }

        // Pessimistic lock prevents concurrent createToken() calls from both passing
        // the count check before either commits (requires the caller's transaction).
        $current = $this->tokens()->lockForUpdate()->count();

        if ($current < $shield->maxTokensPerUser) {
            return;
        }

        if ($shield->onTokenLimit === TokenLimitBehavior::Reject) {
            throw TokenLimitExceededException::forUser($shield->maxTokensPerUser);
        }

        $excess = $current - $shield->maxTokensPerUser + 1;
        $oldestIds = $this->tokens()
            ->orderBy('created_at')
            ->limit($excess)
            ->pluck($this->tokens()->getRelated()->getKeyName());

        if ($oldestIds->isNotEmpty()) {
            $this->tokens()->whereIn($this->tokens()->getRelated()->getKeyName(), $oldestIds)->delete();
        }
    }
}
