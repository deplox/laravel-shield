<?php

declare(strict_types=1);

namespace Deplox\Shield\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @property-read bool $expired
 * @property-read \Illuminate\Contracts\Auth\Authenticatable|null $owner
 */
interface IsAuthToken
{
    public static function findByToken(string $token): ?static;

    /** @return BelongsTo<Model, $this> */
    public function owner(): BelongsTo;

    public function setPlain(string $value): void;

    public function touchLastUsedAt(): void;
}
