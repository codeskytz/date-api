# API Development Plan — date-api

## Overview
A concise development plan to implement the full API described in `api-endpoints.txt`. Covers endpoint routing, controllers, database schema, auth (JWT/Sanctum + OAuth), admin, stories, matching, chat, notifications, caching, storage, queues, testing, CI/CD, deployment, and monitoring.

## Assumptions
- Laravel 10+ project (existing workspace)
- Redis available for cache/sessions
- S3-compatible storage (S3 or R2) for media
- Stateless API using JWT or Laravel Sanctum token-based auth
- Load-balanced environment with multiple app instances

## Milestones & High-level Tasks
1. Map endpoints to routes & controllers (core routing) — 2d
   - Create route groups: `/api/v1`, `/api/admin`, OAuth paths.
   - Scaffold controller classes and method signatures.
2. DB schema & migrations — 3d
   - Define tables: users, posts, comments, likes, follows, matches, stories, messages, notifications, verification_requests, reports, blocks, saved_posts, oauth_accounts, admin_users, jobs/audit.
   - Add indices, FK constraints, soft deletes where appropriate.\n3. Authentication & OAuth — 3d
   - Implement JWT/Sanctum auth flows, token issuing/refresh.
   - OAuth: redirect, callback, mobile token exchange endpoints.
4. Core features implementation — posts/comments/likes/follows — 5d
   - Post creation, media handling, feed retrieval (paginated + cached).
5. Stories & matching — 3d
   - Story upload with expiry, view tracking, mutual-follow matches.
6. Chat & notifications — 4d
   - REST chat endpoints + WebSocket scaffolding (Pusher/Redis Echo), notification creation/storage.
7. Admin APIs & moderation — 3d
   - Admin auth, user management, reports moderation, bans, platform settings.
8. Storage, CDN, signed URLs — 2d
   - Integrate S3/R2, generate signed URLs, store private docs securely.
9. Caching, Redis & queues — 2d
   - Feed caching, session config, Redis rate-limits, queues for async tasks.
10. Background jobs & scheduled tasks — 2d
   - Expire stories, cleanup, verification processing jobs.
11. Security & validation — ongoing
   - Input validation, RBAC middleware, rate limiting, signed URL expiry, audit logs.
12. Tests & docs — 4d
   - Unit/Feature tests, API contract (OpenAPI), README and developer guide.
13. CI/CD & deployment — 3d
   - Build/test pipeline, migrations on deploy, Docker/Kubernetes manifests or compose, LB health checks.
14. Monitoring & release — 2d
   - Logs, Sentry, performance metrics, rollout/rollback strategy.

## Detailed Checklist (developer tasks)
- Routing & Controllers
  - Create route files and group middleware: `auth:api`, `admin`, `throttle`.
  - Scaffold controllers: `AuthController`, `OAuthController`, `UserController`, `PostController`, `StoryController`, `MatchController`, `MessageController`, `NotificationController`, `Admin/*`.

- Database
  - Write migrations with sensible defaults and indexes.
  - Use enum or small string fields for status types.
  - Add pivot tables: `follows`, `post_likes`, `post_comments`, `saved_posts`.

- Auth & OAuth
  - Choose primary strategy (JWT or Sanctum). Provide helper issuing tokens.
  - Implement OAuth code exchange without server sessions; accept provider token for mobile.

- Media & Storage
  - Media model (polymorphic) or `media` JSON column on posts/stories.
  - Use signed URLs for private documents (verification).

- Caching & Performance
  - Feed and stories cached in Redis with short TTL and invalidation hooks.
  - Denormalize counts (likes, comments, followers) and update via jobs.

- Background Processing
  - Queue jobs for notifications, feeds rebuild, media processing, verification review.
  - Scheduler to expire stories and stale verification requests.

- Real-time / Chat
  - WebSocket infra plan (Pusher, Redis + socket server, or Laravel WebSockets)
  - Auth tokens for socket connections; fallback to polling REST endpoints.

- Admin & Moderation
  - Admin middleware with role checks and IP allowlist option.
  - Auditable actions logged to `audit_logs` table.

- Testing & Documentation
  - Write API feature tests for each endpoint and edge cases.
  - Generate OpenAPI spec and serve via Swagger UI.

- CI/CD
  - Linting, static analysis, unit tests on PR.
  - Migration plan: `php artisan migrate --force` with pre-deploy backups.

- Observability & Security
  - Integrate Sentry or similar for errors.
  - Setup Prometheus metrics or equivalent.
  - Harden inputs, file size limits, rate-limits, and CORS.

## Deliverables
- `routes/api.php` with grouped endpoints
- Controller implementations for all major features
- Migrations for core schema
- Auth/OAuth implementation and token flows
- Redis-configured caching and queue workers
- Story expiry job and matching logic
- Admin endpoints and moderation tools
- Tests and OpenAPI documentation
- CI/CD pipeline and deployment manifests

## Suggested Iteration Plan (2-week sprints)
Sprint 0 (setup): routing scaffolding, migrations, basic auth
Sprint 1: posts, comments, likes, feed caching
Sprint 2: stories, matches, follows, saved-posts
Sprint 3: chat, notifications, WebSocket infra
Sprint 4: admin, moderation, verification flows
Sprint 5: tests, docs, CI/CD, deployment hardening

## Next Steps (I can do now)
- Generate `routes/api.php` skeleton and controller files.
- Create initial migrations for `users`, `posts`, `follows`, `likes`.
- Implement authentication scaffolding (JWT/Sanctum) and OAuth skeleton.

---
Plan created from `api-endpoints.txt`. Ask me which milestone to start implementing first and I will begin scaffolding code and tests.