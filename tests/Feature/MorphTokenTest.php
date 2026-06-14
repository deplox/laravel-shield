<?php

declare(strict_types=1);

use Deplox\Shield\Enums\TokenType;
use Deplox\Shield\Events\TokenCreated;
use Deplox\Shield\Resources\TokenResource;
use Deplox\Shield\Shield;
use Deplox\Shield\Tests\Fixtures\MorphToken;
use Deplox\Shield\Tests\Fixtures\MorphUser;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;

function morphShield(): void
{
    app()->singleton(Shield::class, fn () => new Shield(
        tokenModel: MorphToken::class,
        userModel: MorphUser::class,
        prefix: 'dpl_',
    ));
}

beforeEach(fn () => morphShield());

test('createToken creates a morph token linked to the user', function (): void {
    $user = MorphUser::factory()->create();

    $token = $user->createToken();

    expect($token)
        ->toBeInstanceOf(MorphToken::class)
        ->and($token->owner_type)->toBe(MorphUser::class)
        ->and($token->owner_id)->toBe($user->getKey());

    $this->assertDatabaseHas('morph_tokens', [
        'id' => $token->getKey(),
        'owner_type' => MorphUser::class,
        'owner_id' => $user->getKey(),
    ]);
});

test('morph token plain attribute is a decorated string', function (): void {
    $user = MorphUser::factory()->create();

    $token = $user->createToken();

    expect($token->plain)
        ->toBeString()
        ->toStartWith('dpl_')
        ->toHaveLength(60); // 4 prefix + 48 random + 8 crc32b
});

test('morph token owner() returns the polymorphic user', function (): void {
    $user = MorphUser::factory()->create();

    $token = $user->createToken();
    $token->refresh();

    expect($token->owner)
        ->toBeInstanceOf(MorphUser::class)
        ->and($token->owner->getKey())->toBe($user->getKey());
});

test('tokens() returns only this user morph tokens', function (): void {
    $userA = MorphUser::factory()->create();
    $userB = MorphUser::factory()->create();

    $userA->createToken();
    $userA->createToken();
    $userB->createToken();

    expect($userA->tokens()->count())->toBe(2)
        ->and($userB->tokens()->count())->toBe(1);
});

test('token() returns the most recently created morph token', function (): void {
    $user = MorphUser::factory()->create();

    $user->createToken();
    $this->travel(1)->seconds();
    $second = $user->createToken();

    expect($user->token->getKey())->toBe($second->getKey());
});

test('morph token authenticates via DynamicGuard', function (): void {
    $user = MorphUser::factory()->create();
    $token = $user->createToken();

    $this->withToken($token->plain, 'Bearer')
        ->getJson('/auth-test')
        ->assertSuccessful()
        ->assertJsonPath('id', $user->id);
});

test('expired morph token is rejected and deleted', function (): void {
    $user = MorphUser::factory()->create();

    $token = $user->createToken(expiresAt: Date::now()->subMinute());

    $this->withToken($token->plain, 'Bearer')
        ->getJson('/auth-test')
        ->assertUnauthorized();

    $this->assertDatabaseMissing('morph_tokens', ['id' => $token->getKey()]);
});

test('createToken dispatches TokenCreated for morph token', function (): void {
    Event::fake([TokenCreated::class]);

    $user = MorphUser::factory()->create();
    $token = $user->createToken();

    Event::assertDispatched(
        TokenCreated::class,
        fn (TokenCreated $e): bool => $e->token->getKey() === $token->getKey(),
    );
});

test('morph token findByToken resolves correctly', function (): void {
    $user = MorphUser::factory()->create();
    $token = $user->createToken();

    $found = MorphToken::findByToken($token->plain);

    expect($found)->not->toBeNull()
        ->and($found->getKey())->toBe($token->getKey());
});

test('morph token findByToken returns null for tampered token', function (): void {
    $user = MorphUser::factory()->create();
    $token = $user->createToken();

    $tampered = mb_substr($token->plain, 0, -1).'X';

    expect(MorphToken::findByToken($tampered))->toBeNull();
});

test('token resource shows owner_id in user_id field for morph token', function (): void {
    $user = MorphUser::factory()->create();
    $token = $user->createToken();
    $token->refresh();

    $resource = TokenResource::make($token)->toArray(request());

    expect($resource['user_id'])->toBe($user->getKey());
});

test('createToken with explicit type and name works for morph tokens', function (): void {
    $user = MorphUser::factory()->create();

    $token = $user->createToken(
        type: TokenType::Remember,
        expiresAt: Date::now()->addYear(),
        name: 'Morph Device',
    );

    expect($token->type)->toBe(TokenType::Remember)
        ->and($token->name)->toBe('Morph Device');
});
