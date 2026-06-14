<?php

declare(strict_types=1);

use Deplox\Shield\Tests\Fixtures\Token;
use Deplox\Shield\Tests\Fixtures\User;
use Illuminate\Support\Facades\Date;

test('expires() sets expires_at on an unsaved model', function (): void {
    $user = User::factory()->create();
    $token = Token::factory()->for($user, 'owner')->create();

    $target = Date::now()->addWeek();
    $returned = $token->expires($target);

    expect($returned)->toBe($token)
        ->and($token->expires_at->timestamp)->toBe($target->timestamp);
});

test('addDays() extends expiry from current expires_at', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-01-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::parse('2025-06-01 00:00:00'),
    ]);

    $token->addDays(10)->save();
    $token->refresh();

    expect($token->expires_at->toDateString())->toBe('2025-06-11');
});

test('addDays() starts from now() when no expires_at is set', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-01-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create(['expires_at' => null]);

    $token->addDays(5)->save();
    $token->refresh();

    expect($token->expires_at->toDateString())->toBe('2025-01-06');
});

test('addMonths() adds months to current expiry', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-01-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::parse('2025-03-01 00:00:00'),
    ]);

    $token->addMonths(2)->save();
    $token->refresh();

    expect($token->expires_at->toDateString())->toBe('2025-05-01');
});

test('addWeeks() adds weeks to current expiry', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-01-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::parse('2025-01-01 00:00:00'),
    ]);

    $token->addWeeks(2)->save();
    $token->refresh();

    expect($token->expires_at->toDateString())->toBe('2025-01-15');
});

test('addHours() adds hours to current expiry', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-06-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::parse('2025-06-01 12:00:00'),
    ]);

    $token->addHours(3)->save();
    $token->refresh();

    expect($token->expires_at->toDateTimeString())->toBe('2025-06-01 15:00:00');
});

test('addMinutes() adds minutes to current expiry', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-06-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::parse('2025-06-01 10:00:00'),
    ]);

    $token->addMinutes(90)->save();
    $token->refresh();

    expect($token->expires_at->toDateTimeString())->toBe('2025-06-01 11:30:00');
});

test('expires() methods are chainable', function (): void {
    $user = User::factory()->create();

    Date::setTestNow('2025-01-01 00:00:00');
    $token = Token::factory()->for($user, 'owner')->create(['expires_at' => null]);

    $token->addDays(1)->addHours(6)->addMinutes(30)->save();
    $token->refresh();

    expect($token->expires_at->toDateTimeString())->toBe('2025-01-02 06:30:00');
});

test('expired getter returns false for a future expires_at', function (): void {
    $user = User::factory()->create();
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->addDay(),
    ]);

    expect($token->expired)->toBeFalse();
});

test('expired getter returns true for a past expires_at', function (): void {
    $user = User::factory()->create();
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->subSecond(),
    ]);

    expect($token->expired)->toBeTrue();
});

test('expired getter returns false when expires_at is null', function (): void {
    $user = User::factory()->create();
    $token = Token::factory()->for($user, 'owner')->create(['expires_at' => null]);

    expect($token->expired)->toBeFalse();
});

test('expired setter true sets expires_at to now', function (): void {
    Date::setTestNow('2025-06-01 12:00:00');

    $user = User::factory()->create();
    $token = Token::factory()->for($user, 'owner')->create(['expires_at' => null]);

    $token->expired = true;
    $token->save();
    $token->refresh();

    expect($token->expires_at)->not->toBeNull()
        ->and($token->expired)->toBeTrue();
});

test('expired setter false clears expires_at', function (): void {
    $user = User::factory()->create();
    $token = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->subDay(),
    ]);

    $token->expired = false;
    $token->save();
    $token->refresh();

    expect($token->expires_at)->toBeNull()
        ->and($token->expired)->toBeFalse();
});

test('whereExpired scope returns only expired tokens', function (): void {
    $user = User::factory()->create();

    $expired = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->subMinute(),
    ]);
    $active = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->addMinute(),
    ]);
    $noExpiry = Token::factory()->for($user, 'owner')->create(['expires_at' => null]);

    $ids = Token::whereExpired()->pluck('id')->all();

    expect($ids)->toContain($expired->getKey())
        ->and($ids)->not->toContain($active->getKey())
        ->and($ids)->not->toContain($noExpiry->getKey());
});

test('whereNotExpired scope returns active and non-expiring tokens', function (): void {
    $user = User::factory()->create();

    $expired = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->subMinute(),
    ]);
    $active = Token::factory()->for($user, 'owner')->create([
        'expires_at' => Date::now()->addMinute(),
    ]);
    $noExpiry = Token::factory()->for($user, 'owner')->create(['expires_at' => null]);

    $ids = Token::whereNotExpired()->pluck('id')->all();

    expect($ids)->not->toContain($expired->getKey())
        ->and($ids)->toContain($active->getKey())
        ->and($ids)->toContain($noExpiry->getKey());
});
