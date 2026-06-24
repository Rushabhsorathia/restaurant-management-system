# RMS — Backend (Laravel 11 API)

Laravel 11 REST API for the Restaurant Management System. PHP 8.3, MySQL 8, Redis 7, Sanctum, Horizon, predis.

## Requirements

- PHP 8.3+ (with `pdo_mysql`, `bcmath`, `zip`, `mbstring`)
- Composer 2
- MySQL 8.0
- Redis 7

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

## Running

```bash
# API server
php artisan serve              # http://localhost:8000

# Queue workers (Horizon dashboard)
php artisan horizon            # http://localhost:8000/horizon
```

The health check endpoint:

```bash
curl http://localhost:8000/api/health
# => { "status": "ok", "service": "rms-api", ... }
```

## Useful artisan commands

```bash
php artisan rms:dispatch-sample-job "Hello"
php artisan queue:work --queue=default
php artisan horizon:list
```

## Testing

```bash
./vendor/bin/pint --test          # PSR-12 + Laravel preset
./vendor/bin/phpunit              # Feature + unit tests
```

The test suite uses an in-memory queue + array cache (see `phpunit.xml`). DB connectivity for feature tests requires a real MySQL or SQLite — set `DB_CONNECTION` and `DB_DATABASE` accordingly.

## Project layout

See [docs/plans/architecture.md](../docs/plans/architecture.md). The current scaffold matches the spec and exposes the directory tree the architecture document calls out. Feature directories (Repositories, Services, Policies, etc.) are added by their respective tickets (RMS-002, RMS-003, RMS-006, …).

## Folder map (current scaffold)

```
backend/
|-- app/
|   |-- Console/Commands/        # Artisan commands (e.g. DispatchSampleJob)
|   |-- Http/Controllers/Api/    # API controllers (HealthController)
|   |-- Jobs/                    # Async jobs (SampleQueueJob)
|   |-- Models/                  # Eloquent models
|   |-- Providers/               # App, Horizon, Sanctum service providers
|-- bootstrap/
|-- config/                      # Framework + package config
|-- database/migrations/         # Default migrations (incl. Sanctum tokens)
|-- routes/
|   |-- api.php                  # /api/* routes
|   |-- web.php                  # default web routes
|-- tests/                       # Feature + unit tests
|-- pint.json                    # Pint rules (PSR-12 + Laravel preset)
```

## API conventions

- Versioned under `/api/v1/...` once feature tickets are in (currently `/api/health` is unversioned per RMS-001 spec).
- Sanctum token auth via `Authorization: Bearer <token>`.
- Errors follow the standard envelope: `{ "success": false, "message": "...", "errors": { ... } }`.

## License

MIT
