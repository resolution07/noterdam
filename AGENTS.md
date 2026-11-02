# AGENTS.md

Laravel 11 + Vue 3 SPA monolith (JSON REST API via Sanctum Bearer tokens). UI copy is Russian; code, DB columns, and routes are English. `README.md` is the Russian deploy/run guide.

## Commands (Docker-first)

This environment has no `php` or `node` on `PATH`; use Docker. Dev containers are `noterdam-app-dev`, `noterdam-db-dev`, and `noterdam-node-dev`; prod containers are `noterdam-app`, `noterdam-db`, and `noterdam-node`.

- `make up` / `make down` — start/stop dev app+db+node/Vite (app `:80`, db `:3306`, Vite `:5173`).
- `make dev` — alias for `make up`.
- `make test` — Pest in the dev image. Tests force in-memory SQLite, so no DB service is needed.
- Single test: `docker compose -f docker/compose.dev.yaml run --rm --no-deps app php artisan test --filter='...'` (or pass a test file path).
- After adding a migration: `docker compose -f docker/compose.dev.yaml exec app php artisan migrate` — `make up` does not migrate.
- `make lint` — **auto-fixes** PHP with Pint and JS/Vue with ESLint `--fix`; it is not a read-only check. Prettier is separate (`make npm c="run format"`).
- Asset build: prod `node` container builds assets automatically via `make prod-up`; dev `node` container runs `npm run dev` via `make up`.
- `make npm c="..."` / `make npm-install` — run npm commands inside the dev `node` container.
- `make artisan`, `make logs`, `make shell` target **prod**. For dev use `docker compose -f docker/compose.dev.yaml exec app php artisan ...`.
- Dev Compose overrides `APP_ENV`, `APP_URL`, `APP_DOMAIN`, and `SESSION_SECURE_COOKIE`; `.env.example` defaults to `APP_ENV=production`, so editing those in `.env` will not make the dev container behave differently.

## Routing and structure

- `routes/web.php` now has a single catch-all route that serves the built SPA (`view('app')`) named `dashboard`; there is no `/dashboard` route.
- All backend endpoints live in `routes/api.php` and require a Sanctum Bearer token (`auth:sanctum`), except `POST /api/login` and `POST /api/register`.
- `routes/auth.php` is unused Breeze leftover and is **not loaded**; password-reset/verification routes do not exist.
- Inertia middleware (`HandleInertiaRequests`, `StoreInertiaPreviousUrl`) and old web controllers have been removed; only JSON API controllers remain.
- Authorization is project-scoped: use `Project::visibleTo()` for lists and `ProjectPolicy`/`NotePolicy` for actions. Roles are `owner`, `editor`, `viewer`; admin is a separate `can:admin` gate.
- Attachments use the `attachments` disk (`storage/app/attachments`); size limits are in `config/noterdam.php`/`.env`, while extension allow/blacklists are in `StoreAttachmentRequest` and `AttachmentService`.
- Scheduled commands are registered in `routes/console.php`; prod runs them via the `scheduler` service (`schedule:work`).

## Markdown

- Server rendering uses `App\Services\MarkdownService`, a minimal regex converter that escapes HTML and writes `notes.content_html` on create/update. The Vue preview uses `markdown-it` + `highlight.js`.
- Existing notes keep their stored `content_html`; changing the server renderer requires a backfill, not just a code change.

## Frontend

- Vite is host-run by design, not part of Compose. `make down` deletes `public/hot`; a stale `public/hot` makes Laravel point at a dead dev server and pages render blank.
- `make lint` is the only JS check wired up; there is no typecheck script even though `vue-tsc` is installed.

## Deploy / infrastructure

- Prod `app` publishes no ports, joins the external `traefik` network, and is reached at `noterdam-app:80`. TLS terminates at Keenetic; do not add `ports`, TLS, or certresolver to `docker/compose.prod.yaml`.
- Traefik middleware `noterdam-proto` must set `X-Forwarded-Proto: https`; without it Laravel generates `http://` URLs and non-secure session cookies. Trusted proxies come from `TRUSTED_PROXIES` in `bootstrap/app.php`.
- In production the entrypoint caches config/routes/views/events. After env or code changes use `make prod-restart` (migrations + `optimize`) or rebuild; OPcache does not revalidate timestamps.
- `app.md` claims Octane/worker mode, Breeze, and CI that do not match this checkout. Trust `README.md`, Compose, and code over `app.md`.
- No `.github` CI workflows exist despite the CHANGELOG; run `make test` and `make lint` locally.

## Gotchas

- `REGISTRATION_MODE` is not read by the registration flow; `/register` is always open. The first user created with `php artisan user:create` becomes admin.
