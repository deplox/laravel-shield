# Graph Report - .  (2026-06-12)

## Corpus Check
- Corpus is ~29,963 words - fits in a single context window. You may not need a graph.

## Summary
- 445 nodes · 681 edges · 39 communities (30 shown, 9 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 49 edges (avg confidence: 0.83)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Session Auth & Config|Session Auth & Config]]
- [[_COMMUNITY_Token Revocation & Audit|Token Revocation & Audit]]
- [[_COMMUNITY_Package Configuration|Package Configuration]]
- [[_COMMUNITY_Design Patterns & ADRs|Design Patterns & ADRs]]
- [[_COMMUNITY_Password Reset & Revocation|Password Reset & Revocation]]
- [[_COMMUNITY_Token Factory & Password Send|Token Factory & Password Send]]
- [[_COMMUNITY_Token Authentication Flow|Token Authentication Flow]]
- [[_COMMUNITY_Token Creation & Polymorphism|Token Creation & Polymorphism]]
- [[_COMMUNITY_Session Middleware|Session Middleware]]
- [[_COMMUNITY_Shield Core Service|Shield Core Service]]
- [[_COMMUNITY_Password Reset Routes|Password Reset Routes]]
- [[_COMMUNITY_Token Limits & Middleware|Token Limits & Middleware]]
- [[_COMMUNITY_Email Verification Routes|Email Verification Routes]]
- [[_COMMUNITY_Token Policy & Resources|Token Policy & Resources]]
- [[_COMMUNITY_Login Action|Login Action]]
- [[_COMMUNITY_Email Verification Actions|Email Verification Actions]]
- [[_COMMUNITY_Test Auth Helpers|Test Auth Helpers]]
- [[_COMMUNITY_Auth Token Contract|Auth Token Contract]]
- [[_COMMUNITY_Graphify Tooling|Graphify Tooling]]
- [[_COMMUNITY_Token Ownership Contract|Token Ownership Contract]]
- [[_COMMUNITY_CSRF Cookie Handler|CSRF Cookie Handler]]
- [[_COMMUNITY_Polymorphic Token Contract|Polymorphic Token Contract]]
- [[_COMMUNITY_Direct Token Contract|Direct Token Contract]]
- [[_COMMUNITY_Graphify Skill Docs|Graphify Skill Docs]]
- [[_COMMUNITY_Failed Login Events|Failed Login Events]]
- [[_COMMUNITY_Claude Project Config|Claude Project Config]]
- [[_COMMUNITY_Claude MD (Root)|Claude MD (Root)]]
- [[_COMMUNITY_Claude Settings|Claude Settings]]
- [[_COMMUNITY_Claude Local Settings|Claude Local Settings]]
- [[_COMMUNITY_Composer Package|Composer Package]]
- [[_COMMUNITY_Token Limit Shield Helper|Token Limit Shield Helper]]
- [[_COMMUNITY_Graphify Transcribe Ref|Graphify Transcribe Ref]]
- [[_COMMUNITY_CI Workflow|CI Workflow]]

## God Nodes (most connected - your core abstractions)
1. `Shield` - 30 edges
2. `User` - 30 edges
3. `User` - 26 edges
4. `Architecture Decision Records Index` - 15 edges
5. `Shield` - 14 edges
6. `laravel-shield vs Sanctum Fork Comparison` - 13 edges
7. `TokenRevocationReason` - 9 edges
8. `RevokeTokensOnPasswordReset` - 8 edges
9. `TestCase` - 8 edges
10. `Login` - 7 edges

## Surprising Connections (you probably didn't know these)
- `Shield Config File` --references--> `Session-First Bearer-Fallback Auth Strategy`  [INFERRED]
  config/shield.php → README.md
- `Dynamic Guard (session-priority then bearer fallback)` --conceptually_related_to--> `Null Token for Session Auth Discriminator`  [EXTRACTED]
  src/Shield.php → docs/decisions/013-null-token-session.md
- `Dynamic Guard (session-priority then bearer fallback)` --conceptually_related_to--> `Extension Callbacks (Non-nullable Closures)`  [EXTRACTED]
  src/Shield.php → docs/decisions/010-extension-callbacks.md
- `ADR 003: Dynamic Guard` --rationale_for--> `Dynamic Guard (session-priority then bearer fallback)`  [EXTRACTED]
  docs/decisions/003-dynamic-guard.md → src/Shield.php
- `User` --mixes_in--> `MustVerifyEmail`  [EXTRACTED]
  tests/Fixtures/User.php → src/Actions/SendEmailVerification.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Bearer Token Authentication Flow** — actions_authenticatetoken, concerns_isauthtoken, concept_token_hashing [INFERRED 0.90]
- **Token Ownership Trait Variants (Direct FK vs Polymorphic)** — concerns_hastokens, concerns_hasomorphtokens, concerns_createstokens, concept_polymorphic_tokens [INFERRED 0.85]
- **Rate-Limited Auth Actions (Login, SendEmailVerification)** — actions_login, actions_sendemailverification [INFERRED 0.80]
- **Token Lifecycle Audit Event System** — events_tokencreated_tokencreated, events_tokenauthenticated_tokenauthenticated, events_tokenrevoked_tokenrevoked, contracts_isauthtoken_isauthtoken [INFERRED 0.85]
- **Password Reset Token Revocation Flow** — listeners_revoketokensonpasswordreset_revoketokensonpasswordreset, enums_revokeonpasswordchange_revokeonpasswordchange, enums_tokentype_tokentype, events_tokenrevoked_tokenrevoked [EXTRACTED 1.00]
- **Token Ownership Interface Hierarchy** — contracts_ownstokens_ownstokens, contracts_hastokens_hastokens, contracts_hasmorphtokens_hasmorphtokens [EXTRACTED 1.00]
- **Shield Boot Registration Flow: ShieldServiceProvider boots Shield which registers DynamicGuard and StatefulFrontend** — src_shieldserviceprovider, src_shield, middlewares_statefulfrontend, concept_dynamic_guard [EXTRACTED 1.00]
- **Extension Callback Triad: Shield accepts extractToken, validateToken, validateUser closures for extensible auth** — src_shield, feature_extensioncallbacktest, feature_middlewareintegrationtest [EXTRACTED 1.00]
- **Token Format Security Layers: prefix + random entropy + CRC32B checksum + SHA256 DB hash** — src_shield, concept_crc32b_token_checksum, feature_createtokentest [EXTRACTED 1.00]
- **Token Security Test Suite (expiration, limits, revocation on password reset)** — feature_tokenexpirationtest_tokenexpirationtest, feature_tokenlimitest_tokenlimittest, feature_revoketokensonpasswordresettest_revoketokensonpasswordresettest, feature_passwordresettest_passwordresettest [INFERRED 0.85]
- **Test User Infrastructure (User, UserFactory, AlternateUser, migration)** — fixtures_user_user, fixtures_userfactory_userfactory, fixtures_alternateuser_alternateuser, migrations_0000_00_00_000000_create_users_table_createuserstable [INFERRED 0.95]
- **Graphify Skill Reference Document Suite** — graphify_skill_md_graphify_skill, references_extraction_spec_graphify_extraction_spec, references_add_watch_graphify_add_watch, references_exports_graphify_exports, references_github_and_merge_graphify_github_merge [EXTRACTED 1.00]
- **Shield DI Pattern: Singleton + Constructor Injection + No Static State** — concept_shield_singleton, concept_extension_callbacks, concept_configurable_middleware [INFERRED 0.95]
- **Token Security Chain: ULID + Hash Mutator + Hash Lookup + Prefix Checksum** — concept_ulid_primary_keys, concept_auto_hash_mutator, concept_hash_based_lookup, concept_token_prefix_checksum [INFERRED 0.95]
- **Dual Auth Discrimination: Dynamic Guard + Null Token + Extension Callbacks** — concept_dynamic_guard, concept_null_token_session, concept_extension_callbacks [INFERRED 0.95]

## Communities (39 total, 9 thin omitted)

### Community 0 - "Session Auth & Config"
Cohesion: 0.08
Nodes (14): Authenticatable, AuthenticateSession, BaseTestCase, CRC32B Token Format Validation (non-cryptographic checksum), authSessionMiddleware(), shieldWithLimit(), DynamicGuard, CreateUsersTable Migration (+6 more)

### Community 1 - "Token Revocation & Audit"
Cohesion: 0.09
Nodes (22): Logout, Token Audit Logging via Events, Token Ownership Interface Hierarchy, HasMorphTokens Contract, HasTokens Contract, IsAuthToken Contract, OwnsTokens Contract, RevokeOnPasswordChange Enum (+14 more)

### Community 2 - "Package Configuration"
Cohesion: 0.06
Nodes (35): pestphp/pest-plugin, authors, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+27 more)

### Community 3 - "Design Patterns & ADRs"
Cohesion: 0.10
Nodes (33): ActingAsToken Test Helper Trait, Auto-Hashing Token Attribute Mutator, Configurable Middleware Stack (Null-to-Remove), Contract + Trait Composability Pattern, Debounced last_used_at Write, Dynamic Guard (session-priority then bearer fallback), Extension Callbacks (Non-nullable Closures), Hash-Based Token Lookup (No Pipe Format) (+25 more)

### Community 4 - "Password Reset & Revocation"
Cohesion: 0.09
Nodes (26): ResetPassword, CanResetPassword, CanResetPasswordContract, Password Reset Token Revocation Pattern, Stateful Domain Matching Logic, Token Lifecycle (create/expire/prune/revoke), Token Limit Enforcement (Reject vs PruneOldest), PasswordResetTest (+18 more)

### Community 5 - "Token Factory & Password Send"
Cohesion: 0.09
Nodes (15): SendPasswordReset, static, TokenFactory, Factory, UserFactory, DenyAuthenticated, ServiceProvider, Closure (+7 more)

### Community 6 - "Token Authentication Flow"
Cohesion: 0.11
Nodes (17): AuthenticateToken, Attribute, Builder, Session-First Bearer-Fallback Auth Strategy, Token Prefix and CRC32B Decoration, Automatic SHA256 Token Hashing, bootIsAuthToken(), findByToken() (+9 more)

### Community 7 - "Token Creation & Polymorphism"
Cohesion: 0.13
Nodes (16): Polymorphic Token Ownership, Per-User Token Limit Enforcement, createToken(), enforceTokenLimit(), token(), tokens(), token(), tokens() (+8 more)

### Community 8 - "Session Middleware"
Cohesion: 0.18
Nodes (10): Collection, AuthenticateSession, StatefulFrontend, Closure, Request, Response, SessionGuard, Closure (+2 more)

### Community 9 - "Shield Core Service"
Cohesion: 0.14
Nodes (6): Application, Auth, Closure, Kernel, self, Shield

### Community 10 - "Password Reset Routes"
Cohesion: 0.19
Nodes (10): ResetPasswordController, SendPasswordResetController, shieldWithReset(), ResetPassword, SendPasswordReset, JsonResponse, Request, JsonResponse (+2 more)

### Community 11 - "Token Limits & Middleware"
Cohesion: 0.21
Nodes (8): AuthenticationException, TokenLimitBehavior Enum, TokenLimitExceededException, ResolveCurrentUser, self, Closure, Request, Response

### Community 12 - "Email Verification Routes"
Cohesion: 0.24
Nodes (8): SendEmailVerificationController, VerifyEmailController, SendEmailVerification, JsonResponse, Request, JsonResponse, Request, VerifyEmail

### Community 13 - "Token Policy & Resources"
Cohesion: 0.28
Nodes (6): JsonResource, TokenPolicy, TokenResource, Authenticatable, Model, Request

### Community 14 - "Login Action"
Cohesion: 0.36
Nodes (3): Login, Authenticatable, SessionGuard

### Community 15 - "Email Verification Actions"
Cohesion: 0.29
Nodes (3): SendEmailVerification, VerifyEmail, MustVerifyEmail

### Community 16 - "Test Auth Helpers"
Cohesion: 0.48
Nodes (6): actingAsToken(), OwnsTokens, Authenticatable, DateTimeInterface, static, TokenType

### Community 17 - "Auth Token Contract"
Cohesion: 0.33
Nodes (4): findByToken(), owner(), BelongsTo, static

### Community 18 - "Graphify Tooling"
Cohesion: 0.33
Nodes (6): Graphify Incremental Update (AST-only Code Path), Graphify Post-Commit Hook, Graphify Query Vocab Expansion, Graphify Hooks Reference, Graphify Query/Path/Explain Reference, Graphify Incremental Update Reference

### Community 19 - "Token Ownership Contract"
Cohesion: 0.53
Nodes (5): createToken(), DateTimeInterface, IsAuthToken, Model, TokenType

### Community 20 - "CSRF Cookie Handler"
Cohesion: 0.53
Nodes (4): CsrfCookieController, JsonResponse, Request, Response

### Community 21 - "Polymorphic Token Contract"
Cohesion: 0.60
Nodes (4): token(), tokens(), MorphMany, MorphOne

### Community 22 - "Direct Token Contract"
Cohesion: 0.60
Nodes (4): token(), tokens(), HasMany, HasOne

### Community 23 - "Graphify Skill Docs"
Cohesion: 0.40
Nodes (5): Graphify Skill Definition, Graphify Add and Watch Reference, Graphify Exports Reference, Graphify Extraction Spec, Graphify GitHub and Merge Reference

## Knowledge Gaps
- **60 isolated node(s):** `$schema`, `name`, `type`, `description`, `license` (+55 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **9 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Shield` connect `Session Auth & Config` to `Token Revocation & Audit`, `Token Factory & Password Send`, `Token Authentication Flow`, `Token Creation & Polymorphism`, `Session Middleware`, `Password Reset Routes`, `Token Limits & Middleware`, `Login Action`, `Email Verification Actions`?**
  _High betweenness centrality (0.193) - this node is a cross-community bridge._
- **Why does `Dynamic Guard (session-priority then bearer fallback)` connect `Design Patterns & ADRs` to `Session Auth & Config`?**
  _High betweenness centrality (0.098) - this node is a cross-community bridge._
- **Why does `User` connect `Password Reset & Revocation` to `Session Auth & Config`, `Token Factory & Password Send`, `Email Verification Actions`?**
  _High betweenness centrality (0.077) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _63 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Session Auth & Config` be split into smaller, more focused modules?**
  _Cohesion score 0.0753045404208195 - nodes in this community are weakly interconnected._
- **Should `Token Revocation & Audit` be split into smaller, more focused modules?**
  _Cohesion score 0.08636977058029689 - nodes in this community are weakly interconnected._
- **Should `Package Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.05555555555555555 - nodes in this community are weakly interconnected._