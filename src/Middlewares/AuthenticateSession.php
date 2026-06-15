<?php

declare(strict_types=1);

namespace Deplox\Shield\Middlewares;

use Closure;
use Deplox\Shield\Shield;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateSession
{
    /**
     * @param  \Illuminate\Auth\AuthManager  $auth
     */
    public function __construct(
        private AuthFactory $auth,
        private Shield $shield,
    ) {}

    /**
     * Handle incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->user()) {
            return $next($request);
        }

        $guards = Collection::make($this->shield->guards)
            ->mapWithKeys(fn ($guard) => [$guard => $this->auth->guard($guard)])
            ->filter(fn ($guard) => $guard instanceof SessionGuard);

        $shouldLogout = $guards->filter(
            fn ($_guard, $driver) => $request->session()->has('password_hash_'.$driver)
        )->filter(
            fn ($guard, $driver) => ! $this->validatePasswordHash(
                $guard,
                $request->user()->getAuthPassword(),
                $request->session()->get('password_hash_'.$driver)
            )
        );

        if ($shouldLogout->isNotEmpty()) {
            $shouldLogout->each->logoutCurrentDevice();

            $request->session()->flush();

            throw new AuthenticationException('Unauthenticated.', [...$shouldLogout->keys()->all(), 'dynamic']);
        }

        return tap($next($request), function () use ($request, $guards) {
            if (! is_null($guard = $this->getFirstGuardWithUser($guards->keys()))) {
                $this->storePasswordHashInSession($request, $guard);
            }
        });
    }

    /**
     * Get the first authentication guard that has a user.
     *
     * @param  Collection<int, string>  $guards
     */
    private function getFirstGuardWithUser(Collection $guards): ?string
    {
        return $guards->first(fn (string $guard) => $this->auth->guard($guard)->hasUser());
    }

    /**
     * Store the user's current password hash in the session.
     */
    private function storePasswordHashInSession(Request $request, string $guard): void
    {
        $guardInstance = $this->auth->guard($guard);
        $password = $guardInstance->user()?->getAuthPassword() ?? '';

        $request->session()->put([
            "password_hash_{$guard}" => $guardInstance instanceof SessionGuard
                ? $guardInstance->hashPasswordForCookie($password)
                : $password,
        ]);
    }

    /**
     * Validate the password hash against the stored value.
     */
    private function validatePasswordHash(SessionGuard $guard, ?string $passwordHash, string $storedValue): bool
    {
        // Try HMAC format (always available on SessionGuard)...
        if (hash_equals($guard->hashPasswordForCookie($passwordHash ?? ''), $storedValue)) {
            return true;
        }

        // Fall back to raw password hash format for backward compatibility...
        return hash_equals($passwordHash ?? '', $storedValue);
    }
}
