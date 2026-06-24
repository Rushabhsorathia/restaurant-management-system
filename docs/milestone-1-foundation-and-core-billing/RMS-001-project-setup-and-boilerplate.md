# RMS-001: Project Setup & Boilerplate

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-001 |
| **Type** | Story |
| **Epic** | Foundation |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | P0 - Critical |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | None |

## User Story

As a development team, I want a fully configured project boilerplate (Laravel 11 API + React Vite SPA + Tailwind) with shared conventions, linting, CI, and Docker, so that all subsequent feature tickets can be built on a stable, consistent foundation.

## Description

This ticket scaffolds the entire monorepo that will host both the backend Laravel 11 API and the frontend React Vite SPA. It establishes the directory layout described in the architecture document, wires up the build tooling, configures environments for local development, staging, and production, and sets up continuous integration to enforce code quality.

The backend will be a fresh Laravel 11 application with PHP 8.3, Sanctum installed, MySQL and Redis configured, Horizon for queue management, and a PSR-12 / Laravel Pint code style enforced. The frontend will be a Vite-powered React 18 SPA with TypeScript, Tailwind CSS 3, React Router v6, TanStack Query, Zustand, React Hook Form, Zod, and ESLint/Prettier configured.

Both apps must run locally with a single command and connect to shared MySQL and Redis containers. A README in each subfolder must explain how to run, test, and build. CI pipelines must run lint and tests on every push.

## Acceptance Criteria

- [ ] Monorepo created at `/var/www/html/restaurant_management_system/` with `backend/` and `frontend/` directories.
- [ ] Laravel 11 application initialized in `backend/` running on PHP 8.3 with Sanctum installed and configured.
- [ ] MySQL 8.0 and Redis 7 configured in `backend/.env.example` with sensible defaults.
- [ ] Laravel Horizon installed and a sample queue job dispatched successfully.
- [ ] React 18 + Vite + TypeScript SPA initialized in `frontend/` with Tailwind CSS 3 configured.
- [ ] Frontend dependencies installed: React Router v6, TanStack Query, Zustand, React Hook Form, Zod, Axios, Recharts, date-fns, clsx.
- [ ] ESLint + Prettier configured for frontend; Laravel Pint configured for backend.
- [ ] Docker Compose file provided to spin up MySQL, Redis, backend, and frontend together.
- [ ] CI pipeline (GitHub Actions or GitLab CI) runs backend tests (PHPUnit) and frontend tests (Vitest) plus lint on every push.
- [ ] Both apps serve a health check endpoint (`/api/health` for backend, `/` landing for frontend).
- [ ] `.env.example` files committed for both apps; real `.env` gitignored.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Frontend Landing | / | Placeholder landing page confirming the SPA loads |
| Health Check | /api/health | JSON health endpoint |

### Screen Details

**Frontend Landing (/):** A minimal page rendered by the React app showing the project name, environment, build version, and a "System online" indicator. It confirms Vite is serving the app, Tailwind is applied (styled card), and the API health check is reachable. This screen will be replaced by the login screen in RMS-003.

**Health Check (/api/health):** Returns JSON: `{ "status": "ok", "service": "rms-api", "version": "0.1.0", "time": "<iso8601>" }`. Verifies DB and Redis connectivity and includes those in the payload.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/health | System health check |

## Database Tables

This ticket does not create business tables. It only ensures the Laravel default `migrations` table and Horizon tables exist.

## Technical Notes

- **Backend:** Use `composer create-project laravel/laravel backend "11.*"`. Install `laravel/sanctum`, `laravel/horizon`, `predis/predis` (or phpredis). Configure `config/database.php` for MySQL and Redis. Set up `app/Providers` with Sanctum and Horizon.
- **Frontend:** Use `npm create vite@latest frontend -- --template react-ts`. Install Tailwind via the official Vite plugin. Configure `tailwind.config.js` with content paths and a design token scale. Set up an Axios instance with base URL from env and interceptors for the Sanctum token and 401 handling.
- **Code style:** Backend uses Laravel Pint (PSR-12 + Laravel preset). Frontend uses ESLint with `@typescript-eslint` and Prettier.
- **Docker:** `docker-compose.yml` with services: `mysql`, `redis`, `backend` (php-fpm), `frontend` (vite dev). Volumes for persistence.
- **CI:** Separate jobs for backend (composer install, pint, phpunit) and frontend (npm ci, eslint, vitest).
- **Folder layout** must match the architecture document's backend and frontend trees.

## Subtasks

1. [ ] Initialize Laravel 11 backend with PHP 8.3
2. [ ] Install and configure Sanctum, Horizon, Redis driver
3. [ ] Configure MySQL and Redis connection in `.env.example`
4. [ ] Add `/api/health` endpoint with DB + Redis checks
5. [ ] Initialize React 18 + Vite + TypeScript frontend
6. [ ] Configure Tailwind CSS 3 with design tokens
7. [ ] Install frontend libraries (Router, TanStack Query, Zustand, RHF, Zod, Axios)
8. [ ] Configure ESLint + Prettier for frontend
9. [ ] Configure Laravel Pint for backend
10. [ ] Create Docker Compose for local dev (MySQL, Redis, backend, frontend)
11. [ ] Set up CI pipeline (lint + test) for backend and frontend
12. [ ] Write README files for backend and frontend subfolders

## Testing Criteria

- [ ] `php artisan serve` starts the API and `/api/health` returns 200 with ok status.
- [ ] `npm run dev` starts the Vite dev server and the landing page renders.
- [ ] `docker compose up` brings all services to healthy state.
- [ ] CI pipeline passes on a sample pull request.
- [ ] `php artisan horizon` starts and a dispatched test job completes.
- [ ] Pint and ESLint report zero violations on scaffolded code.
