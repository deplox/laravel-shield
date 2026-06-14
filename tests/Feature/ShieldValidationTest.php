<?php

declare(strict_types=1);

use Deplox\Shield\Shield;
use Deplox\Shield\Tests\Fixtures\Token;
use Deplox\Shield\Tests\Fixtures\User;

test('Shield accepts lastUsedAtDebounce of exactly 1', function (): void {
    $shield = new Shield(
        tokenModel: Token::class,
        userModel: User::class,
        lastUsedAtDebounce: 1,
    );

    expect($shield->lastUsedAtDebounce)->toBe(1);
});

test('Shield rejects negative maxTokensPerUser', function (): void {
    expect(fn () => new Shield(
        tokenModel: Token::class,
        userModel: User::class,
        maxTokensPerUser: -1,
    ))->toThrow(InvalidArgumentException::class, 'Max tokens per user must be a positive integer or null.');
});

test('Shield accepts null maxTokensPerUser', function (): void {
    $shield = new Shield(
        tokenModel: Token::class,
        userModel: User::class,
        maxTokensPerUser: null,
    );

    expect($shield->maxTokensPerUser)->toBeNull();
});

test('statefulDomains() returns the explicit list when set via constructor', function (): void {
    $shield = new Shield(
        tokenModel: Token::class,
        userModel: User::class,
        statefulDomains: ['example.com', 'api.example.com'],
    );

    expect($shield->statefulDomains())->toBe(['example.com', 'api.example.com']);
});

test('statefulDomains() resolves from config when not explicitly set', function (): void {
    config(['shield.stateful' => ['localhost', 'localhost:3000']]);

    $shield = new Shield(
        tokenModel: Token::class,
        userModel: User::class,
    );

    expect($shield->statefulDomains())->toContain('localhost');
});

test('statefulSubdomains() reflects the config value', function (): void {
    config(['shield.stateful_subdomains' => true]);

    $shield = new Shield(
        tokenModel: Token::class,
        userModel: User::class,
    );

    expect($shield->statefulSubdomains())->toBeTrue();
});
