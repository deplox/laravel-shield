# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `TokenCreated` event dispatched on every `createToken()` call for audit logging
- `Logout::others()` — revokes all tokens except the current one ("logout other devices"), dispatching `TokenRevoked(LogoutOther)` per token
- `TokenRevocationReason::LogoutOther` case
- `expires_in` field on `TokenResource` — seconds until expiry as an integer (`null` for non-expiring, `0` for already expired)
- Concurrent-safe token limit enforcement: `createToken()` wraps the limit check and insert in a `DB::transaction` with `lockForUpdate()` when `maxTokensPerUser` is set, preventing TOCTOU races
- Idle non-expiring token pruning: `prunable()` now removes tokens without an `expires_at` that have been idle (or never used) for longer than `pruneDays`
- `HasExpiration` trait inlined into the package — no external VCS dependency required

### Changed

- `composer.json`: dropped `deplox/laravel-support` dependency and its VCS `repositories` entry; `HasExpiration` is now provided directly by this package under `Deplox\Shield\Concerns`

## [0.1.0] - 2026-06-13

Initial release.

### Added

- `DynamicGuard` — session-first, bearer-fallback auth guard registered as `auth:dynamic`
- `IsAuthToken` trait and contract for token models — ULID primary keys, auto-hashing SHA256 mutator, `MassPrunable`, debounced `touchLastUsedAt()`, `findByToken()` hash lookup
- `HasTokens` / `HasMorphTokens` traits and contracts for user models
- `Shield` configuration singleton with container binding via `Shield::configure()`
- Configurable token prefix with CRC32B checksum decoration for secret scanning
- Per-user token cap (`maxTokensPerUser`) with `Reject` or `PruneOldest` behavior
- `Login` action with rate limiting, `validateUser` callback, and stateful/stateless modes
- `Logout` action handling bearer and session auth transparently
- `StatefulFrontend` middleware for SPA cookie authentication
- CSRF cookie endpoint auto-registered at configurable path
- `TokenAuthenticated`, `TokenRevoked`, `FailedLogin`, `TokenCreated` events
- `RevokeTokensOnPasswordReset` listener wired to Laravel's `PasswordReset` event
- `TokenResource` API resource with all token fields
- `TokenPolicy` for ownership-gated token CRUD
- `DenyAuthenticated` and `ResolveCurrentUser` middleware
- Password reset and email verification actions, controllers, and route helpers
- Polymorphic token support via `HasMorphTokens` and a separate migration
- Publishable config (`laravel-shield-config`) and migrations (`laravel-shield-migrations`, `laravel-shield-morph-migrations`)
- Architecture Decision Records in `docs/decisions/`
