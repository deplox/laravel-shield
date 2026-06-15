# Comparative Analysis: laravel-shield vs [laravel/sanctum](https://github.com/laravel/sanctum)

> For deeper rationale behind each design decision, see the [Architecture Decision Records](decisions/).

## Context

[laravel/sanctum](https://github.com/laravel/sanctum) is the official Laravel SPA/token authentication package. laravel-shield shares its core purpose — stateful cookie auth for SPAs and Bearer token auth for APIs — but makes different design choices in several areas.

Notable departures from upstream Sanctum:

- **Removed the abilities/scopes system** (`$abilities` param, `tokenCan()`) — see [Omitted: Abilities/Scopes](#1-no-abilitiesscopes-system) below
- **Removed `TransientToken`** — replaced with `null` on `$user->token` for session-authenticated users (see [#8](#8-null-token-for-session-authenticated-users))
- **Stricter type hints** throughout

---

## laravel-shield capabilities

### 1. IsAuthToken contract + trait vs concrete PersonalAccessToken

> ADR: [002 — Contract + Trait Pattern](decisions/002-contract-trait-pattern.md)

Sanctum provides a concrete `PersonalAccessToken` model and requires `Sanctum::usePersonalAccessTokenModel()` to swap it.
laravel-shield defines a `Contracts\IsAuthToken` interface and a `Concerns\IsAuthToken` trait — the consuming app owns its model and mixes in behavior. No escape hatch needed; model ownership is the default.

### 2. Auto-hashing token attribute mutator

> ADR: [004 — Token Hashing](decisions/004-token-hashing.md)

Sanctum hashes inside `createToken()`, making correct hashing a caller responsibility.
laravel-shield uses an Eloquent `Attribute` mutator that hashes on write regardless of call site — a plaintext token cannot be accidentally persisted.

### 3. Debounced touchLastUsedAt() (configurable, default 300s)

> ADR: [011 — Debounced last_used_at](decisions/011-debounced-last-used.md)

Sanctum writes `last_used_at` on every authenticated request or disables it entirely via a boolean toggle.
laravel-shield debounces writes to a configurable window (default 300s), cutting write volume dramatically with negligible accuracy loss.

### 4. MassPrunable vs artisan command — with idle token pruning

Sanctum ships a `sanctum:prune-expired` artisan command that only prunes tokens when a global `expiration` config is set. No scheduler integration, no idle pruning.
laravel-shield uses Laravel's built-in `model:prune` infrastructure via `MassPrunable`, integrating with existing scheduling with less code.

laravel-shield also prunes **non-expiring idle tokens** — tokens without an `expires_at` that haven't been used
(or were never used) within `pruneDays` days. Non-expiring Sanctum tokens accumulate indefinitely.
The two pruning paths are:

1. Tokens with expiry: removed `pruneDays` after their `expires_at` (grace window for audit logs).
2. Tokens without expiry: removed when `last_used_at <= cutoff` or (`last_used_at IS NULL` AND `created_at <= cutoff`).

### 5. AuthenticateToken as standalone action class

> ADR: [003 — Dynamic Guard](decisions/003-dynamic-guard.md)

Sanctum validates tokens directly inside the Guard class, coupling validation and orchestration.
laravel-shield extracts token validation into an invokable `AuthenticateToken` action — independently testable, reusable, and the Guard stays focused on orchestration.

### 6. Shield as central entry point

> ADR: [001 — Shield over Config](decisions/001-shield-over-config.md)

Sanctum uses scattered `config()` calls and static properties on the `Sanctum` facade.
laravel-shield provides a type-safe `Shield` singleton that holds all configuration, boot logic, and token-prefix methods. It validates at construction time (catches misconfiguration early) and is easier to mock in tests.

### 7. TokenType enum (Bearer vs Remember)

> ADR: [012 — Token Types](decisions/012-token-types.md)

Sanctum has no token type concept — all tokens are equivalent.
laravel-shield adds a semantic `TokenType` enum with type-specific generation logic and a centralized `generate()` method, enabling different token behaviors per type.

### 8. Null token for session-authenticated users

> ADR: [013 — Null Token for Session Auth](decisions/013-null-token-session.md)

Sanctum assigns a `TransientToken` marker instance to `$user->token` for session-authenticated requests.
laravel-shield assigns `null` — bearer-authenticated users get the actual token model instance, session-authenticated users get `null`. The discriminator is `null` vs a model instance, without a marker class.

### 9. ULIDs over auto-incrementing IDs

> ADR: [005 — ULIDs over Auto-increment](decisions/005-ulids-over-autoincrement.md)

Sanctum uses auto-incrementing integer IDs on token records.
laravel-shield uses ULIDs — non-sequential, no information leakage about total count or creation order.

### 10. TokenCreated event for audit logging

Sanctum dispatches no event on token creation.
laravel-shield dispatches `TokenCreated` on every token issuance. Together with `TokenAuthenticated`, `TokenRevoked` (with typed `TokenRevocationReason`), and `FailedLogin`, it provides a complete audit trail: creation → authentication → revocation, all with enough context to log safely without exposing credentials.

### 11. Concurrent-safe token limit enforcement

Sanctum has no token limit concept.
laravel-shield's `enforceTokenLimit()` wraps `createToken()` in a `DB::transaction` and uses `lockForUpdate()` on the token count query when `maxTokensPerUser` is configured, preventing TOCTOU races where two simultaneous login requests could both pass the count check before either commits.

### 12. `revokeOtherTokens()` — logout other devices

Sanctum has no equivalent; callers must manually query and delete.
laravel-shield provides `Logout::others($user, $currentToken)`, which revokes all tokens except the one currently in use and dispatches a `TokenRevoked(LogoutOther)` event per revoked token.

### 13. Login event dispatching

laravel-shield dispatches Laravel's standard `Login` event for both session and bearer auth, integrating tightly with the auth event system. Sanctum only fires `TokenAuthenticated`.
Whether this fits a given app depends on whether listeners expect `Login` for API tokens.

### 14. Naming

`StatefulFrontend` vs `EnsureFrontendRequestsAreStateful`, `DynamicGuard` vs `Guard`. Shorter, equally descriptive.

### 15. Hash-based token lookup (no pipe-delimited format)

> ADR: [006 — Hash Lookup](decisions/006-hash-lookup.md)

Sanctum uses an `id|token` pipe-delimited format — the ID prefix was a workaround for expensive full-table scans with auto-incrementing integer PKs.
laravel-shield uses a hash-based lookup with a DB index on the token column. No information leakage from an ID prefix, simpler token format, equivalent performance, and a natural fit for ULID primary keys.

### 16. Proactive expired token cleanup on auth

> ADR: [011 — Debounced last_used_at](decisions/011-debounced-last-used.md) *(covers proactive cleanup in context)*

laravel-shield deletes expired tokens during auth attempts, complementing the MassPrunable scheduled pruning. Sanctum does not do this — expired tokens remain in the table until the prune command runs.

### 17. Container-bound config (no static state)

> ADR: [001 — Shield over Config](decisions/001-shield-over-config.md)

`Shield::configure()` accepts the `Application` instance and binds `Shield` as a singleton directly into the container.
No static mutable state, no `$pendingConfig` bridging — standard Laravel DI from the start.

### 18. Configurable middleware stack

> ADR: [014 — Configurable Middleware](decisions/014-configurable-middleware.md)

Sanctum's stateful middleware list is hardcoded with no override mechanism.
`Shield::$middlewares` accepts overrides for `encrypt_cookies`, `validate_csrf_token`, and `authenticate_session`. Set a key to `null` to remove it.

### 19. Extension callbacks via Shield closures

> ADR: [010 — Extension Callbacks](decisions/010-extension-callbacks.md)

Both Sanctum and laravel-shield provide extension points, implemented differently. laravel-shield uses typed closures on the `Shield` singleton rather than Sanctum's static method approach:

- `$extractToken` — custom token extraction (e.g., from query param, custom header). Non-nullable with a default that returns `bearerToken()`. Override to fully own extraction — compose the fallback yourself if needed.
- `$validateToken` — custom token validation (e.g., IP allowlisting). Non-nullable with a default that returns `true` (no-op). Receives the token model and request, runs after standard checks but before `$validateUser`.
- `ActingAsToken` trait — testing convenience that creates a real token and sets the `Authorization` header.

Closures are type-safe, non-nullable with sensible defaults, and scoped to the config instance.

### 20. Environment-driven stateful domains

> ADR: [015 — Stateful Domains Config](decisions/015-stateful-domains-config.md)

Both Sanctum and laravel-shield support env vars (`SANCTUM_STATEFUL_DOMAINS` / `SHIELD_STATEFUL_DOMAINS`) and auto-derive from `APP_URL`.
laravel-shield additionally accepts explicit domain overrides via the constructor (useful in tests) and an optional `stateful_subdomains` flag that adds `*.domain/*` patterns for multi-tenant SPAs.

---

## Features not in laravel-shield

### 1. No abilities/scopes system

Sanctum ships a full abilities/scopes system: `createToken($name, $abilities)`, `tokenCan($ability)`, and middleware that gates on specific abilities (`abilities:read-user`, `ability:read-user`). laravel-shield does not include this — no `$abilities` param on `createToken()`, no `tokenCan()`, no ability-gating middleware.

This is a deliberate omission: in most apps, role/permission logic belongs in a dedicated authorization layer (Gates, Policies, Spatie Permission) rather than on tokens. Tokens in laravel-shield are binary — valid or not. If per-token scoping is needed, an `abilities` column and a custom `$validateToken` closure via the extension callback can replicate the pattern.

### ~~2. HasMany instead of MorphMany (polymorphic)~~ — Resolved

> ADR: [008 — Polymorphic Tokens](decisions/008-polymorphic-tokens.md)

**Resolved.** laravel-shield now supports both modes via alternative traits:

- **Default (direct FK):** `HasTokens` trait with `HasMany`/`HasOne` — simpler schema, database-level referential integrity, better performance. Zero changes from before.
- **Opt-in polymorphic:** `HasMorphTokens` trait with `MorphMany`/`MorphOne` — swap one trait on the owner model, override `owner()` on the token model to return `MorphTo`, and publish the polymorphic migration via `laravel-shield-morph-migrations`.

This follows the Laravel pattern of alternative traits (like `HasUuids` vs `HasUlids`). The token model's `owner()` method returns `BelongsTo` by default; since `MorphTo extends BelongsTo`, consumers can covariantly override it without a separate trait. On the owner side, `MorphMany` does not extend `HasMany`, so a separate `HasMorphTokens` trait is provided.

Both traits share a common `OwnsTokens` contract and `CreatesTokens` trait, so `createToken()` works identically in both modes.

---

## Summary

| Item                                  | Category               |
| ------------------------------------- | ---------------------- |
| IsAuthToken contract + trait          | Different design       |
| Auto-hash mutator                     | Different design       |
| Debounced writes                      | laravel-shield only    |
| MassPrunable + idle token pruning     | Different design       |
| Action class                          | Different design       |
| Shield entry point                    | Different design       |
| TokenType enum                        | laravel-shield only    |
| Null token (no TransientToken)        | Different design       |
| ULIDs                                 | Different design       |
| Hash-based lookup                     | Different design       |
| Proactive expired token cleanup       | laravel-shield only    |
| Container-bound config                | Different design       |
| Configurable middleware stack         | laravel-shield only    |
| Extension callbacks                   | Different design       |
| TokenCreated/Revoked/Auth audit trail | laravel-shield only    |
| Concurrent-safe token limit           | laravel-shield only    |
| `revokeOtherTokens()` (logout others) | laravel-shield only    |
| `expires_in` on TokenResource         | laravel-shield only    |
| Naming                                | Different design       |
| Login event integration               | Different design       |
| Polymorphic tokens (opt-in)           | Different design       |
| Env-driven stateful domains           | Different design       |
| Abilities/scopes system               | Not included           |

**Bottom line:** laravel-shield and laravel/sanctum solve the same problem with different architectural trade-offs.
laravel-shield leans toward DI over statics, contracts over concrete models, action classes, ULIDs, debounced writes, auto-hashing, idle+expired pruning, concurrent-safe token limits, a full audit event trail, and logout-other-devices.
The main capability gap relative to Sanctum is the abilities/scopes system — an intentional omission in favour of dedicated authorization layers.

**Open gaps vs. ecosystem (Fortify/WebAuthn):** Two-Factor Authentication (TOTP/passkeys), token creation metadata
(IP/device at issuance), and a wired-up "remember me" login flow. These require new migrations or external modules
and are tracked as the next iteration.
