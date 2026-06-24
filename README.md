# Restaurant Management System (RMS) — Monorepo

A full-stack, multi-outlet restaurant management system (POS, menu, tables, orders, billing, KOT, payments) with a Laravel 11 API backend and a React 18 + Vite SPA frontend.

This monorepo is the implementation root for all 56 tickets across 6 milestones. See [docs/](docs/) for the planning material.

## Repository Layout

```
.
|-- backend/                # Laravel 11 API (PHP 8.3, Sanctum, Horizon, Redis)
|-- frontend/               # React 18 + Vite + TypeScript SPA (Tailwind, Zustand, RHF)
|-- docker-compose.yml      # One-command local dev (MySQL + Redis + backend + frontend)
|-- .github/workflows/      # CI: backend (Pint + PHPUnit) and frontend (ESLint + Vitest + build)
|-- docs/                   # Plans, architecture, milestone & ticket specs
```

## Quick Start (Docker)

The fastest way to get everything running:

```bash
cp backend/.env.example backend/.env
docker compose up --build
```

Once all services are healthy:

- Frontend (Vite dev): http://localhost:5173
- Backend API:           http://localhost:8000
- Health check:          http://localhost:8000/api/health
- Horizon dashboard:     http://localhost:8000/horizon (local only)

## Quick Start (Local, without Docker)

You will need PHP 8.3+ (with `pdo_mysql`, `bcmath`, `zip`), Composer, Node 20+, MySQL 8, and Redis 7 running locally.

### Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve           # http://localhost:8000
php artisan horizon         # in a second terminal
```

### Frontend

```bash
cd frontend
npm install
npm run dev                 # http://localhost:5173
```

The frontend Vite dev server proxies `/api/*` to `http://localhost:8000`.

## Testing

| App | Command | Notes |
|-----|---------|-------|
| Backend | `cd backend && ./vendor/bin/phpunit` | Feature + unit tests |
| Backend lint | `cd backend && ./vendor/bin/pint --test` | Laravel preset |
| Frontend | `cd frontend && npm test` | Vitest |
| Frontend lint | `cd frontend && npm run lint` | ESLint + Prettier check |
| Frontend typecheck | `cd frontend && npx tsc -b` | TS strict mode |
| Frontend build | `cd frontend && npm run build` | Vite production build |

## CI

Two GitHub Actions workflows under [`.github/workflows/`](.github/workflows/):

- `backend.yml` — composer install, Pint lint, PHPUnit feature tests against MySQL + Redis services.
- `frontend.yml` — npm ci, ESLint, Prettier check, tsc, Vitest, Vite build.

Both run on every push and pull request touching the corresponding `backend/**` or `frontend/**` paths.

## Documentation

- [docs/INDEX.md](docs/INDEX.md) — master ticket index across all milestones.
- [docs/plans/architecture.md](docs/plans/architecture.md) — system architecture & patterns.
- [docs/milestone-1-plan.md](docs/milestone-1-plan.md) — Milestone 1 plan & critical path.
- [docs/milestone-1-foundation-and-core-billing/](docs/milestone-1-foundation-and-core-billing/) — M1 ticket specs.

## Milestone 1 — Foundation & Core Billing

The current milestone. Tickets are tracked in [docs/INDEX.md](docs/INDEX.md) and individual files under `docs/milestone-1-foundation-and-core-billing/`. This ticket (`RMS-001`) scaffolds the monorepo.
