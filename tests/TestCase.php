<?php

declare(strict_types=1);

namespace Deplox\Shield\Tests;

use Deplox\Shield\Shield;
use Deplox\Shield\ShieldServiceProvider;
use Deplox\Shield\Tests\Fixtures\Token;
use Deplox\Shield\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ShieldServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('auth.guards.session', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        Shield::configure($app, new Shield(
            tokenModel: Token::class,
            userModel: User::class,
            prefix: 'dpl_',
            validateUser: fn ($user): bool => $user->email_verified_at !== null,
        ));
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
