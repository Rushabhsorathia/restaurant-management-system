# System Architecture

**Project:** Restaurant Management System (RMS)
**Architecture Style:** Decoupled (REST API backend + SPA frontend)
**Last Updated:** 2026-06

---

## Table of Contents

1. [High-Level Architecture](#high-level-architecture)
2. [Module Diagram](#module-diagram)
3. [Backend Architecture (Laravel 11)](#backend-architecture-laravel-11)
4. [Frontend Architecture (React Vite)](#frontend-architecture-react-vite)
5. [Database Architecture](#database-architecture)
6. [Caching & Real-time](#caching--real-time)
7. [Multi-Tenant Architecture](#multi-tenant-architecture)
8. [API Design Patterns](#api-design-patterns)
9. [Security](#security)
10. [Deployment Strategy](#deployment-strategy)

---

## High-Level Architecture

```
                      +-----------------------------+
                      |        Client Devices        |
                      |  (POS terminal, tablet,      |
                      |   captain phone, manager PC) |
                      +--------------+--------------+
                                     |
                              HTTPS / WSS
                                     |
              +----------------------+----------------------+
              |                                             |
   +----------v----------+                    +-------------v-------------+
   |   React Vite SPA     |                   |  Aggregator Webhooks       |
   |  (Tailwind + Zustand)|                   |  (Zomato/Swiggy/Petpooja)  |
   |  served by Nginx     |                   +-------------+--------------+
   +----------+-----------+                                 |
              | REST (Sanctum)                              |
              |                                             |
   +----------v---------------------------------------------v----------+
   |                    Nginx (Reverse Proxy)                              |
   |           TLS termination, static assets, WS upgrade                  |
   +----------+----------------------+-----------------------+------------+
              |                      |                       |
   +----------v----------+  +--------v--------+   +----------v----------+
   |  Laravel 11 API     |  | WebSocket Server |   |  Queue Worker       |
   |  (PHP-FPM 8.3)      |  | (Reverb/Soketi)  |   |  (Laravel Horizon)  |
   |  Sanctum auth       |  | Redis pub/sub    |   |  Jobs: KOT, email,  |
   |  Controllers/Svc    |  | KDS / order push |   |  SMS, reports, stock|
   +----------+----------+  +-----------------+   +----------+----------+
              |                                              |
   +----------v----------------------+-----------------------v----------+
   |                        Redis 7                                       |
   |     cache | session | queue | pub/sub | locks                        |
   +----------+-----------------------------------------------------------+
              |
   +----------v----------+
   |    MySQL 8.0         |
   |  (Primary datastore) |
   +-----------------------+
```

### Component Responsibilities

| Component | Responsibility |
|-----------|---------------|
| React Vite SPA | All UI: POS, dashboards, admin, KDS, captain app |
| Laravel 11 API | Business logic, validation, auth, jobs, webhooks |
| Nginx | Reverse proxy, TLS, static SPA hosting, WebSocket upgrade |
| MySQL | Persistent relational data |
| Redis | Cache, sessions, queues, real-time pub/sub, distributed locks |
| WebSocket Server | Push real-time events to KDS and POS clients |
| Queue Workers (Horizon) | Async jobs: KOT print, emails, SMS, reports, stock recalculation |

---

## Module Diagram

```
                              +-------------------+
                              |     AUTH CORE     |
                              | users, roles,     |
                              | permissions,      |
                              | sanctum tokens    |
                              +---------+---------+
                                        |
         +------------------------------+------------------------------+
         |                              |                              |
+--------v--------+          +----------v----------+         +---------v---------+
|   RESTAURANT    |          |       MENU          |         |     ORDERS         |
|  multi-tenant   |--------->|  categories, items, |<-------|  orders, items,    |
|  outlets, tax   |          |  variations, combos |         |  KOT, tables       |
+--------+--------+          +----------+----------+         +---------+----------+
         |                              |                              |
         |                              |                              |
+--------v--------+          +----------v----------+         +---------v---------+
|   INVENTORY     |<---------|      RECIPES        |<-------|     BILLING        |
|  raw materials, |          |  recipe ingredients |         |  bills, payments, |
|  stock, POs     |          |  costing, yield     |         |  discounts, taxes |
+--------+--------+          +---------------------+         +---------+----------+
         |                                                              |
         |                                                              |
+--------v--------+                                          +---------v---------+
|   SUPPLY CHAIN  |                                          |      CRM           |
|  suppliers,     |                                          |  customers,        |
|  transfers,     |                                          |  loyalty, campaigns|
|  central kitchen|                                          +---------+----------+
+-----------------+                                                    |
                                                                        |
                                                              +---------v---------+
                                                              |    ANALYTICS      |
                                                              |  reports, P&L,    |
                                                              |  audit, exports   |
                                                              +-------------------+
                                                                        |
                                                              +---------v---------+
                                                              |   ONLINE ORDERS   |
                                                              |  aggregators,     |
                                                              |  reconciliation   |
                                                              +-------------------+
                                                                        |
                                                              +---------v---------+
                                                              |     HQ / MULTI    |
                                                              |  outlet dashboard |
                                                              +-------------------+
```

---

## Backend Architecture (Laravel 11)

### Folder Structure

```
backend/
|-- app/
|   |-- Console/
|   |   |-- Commands/            # Artisan commands (stock audit, report gen)
|   |-- Events/                  # OrderPlaced, BillSettled, KotGenerated
|   |-- Exceptions/
|   |-- Http/
|   |   |-- Controllers/
|   |   |   |-- Api/
|   |   |   |   |-- V1/
|   |   |   |   |   |-- Auth/
|   |   |   |   |   |-- Menu/
|   |   |   |   |   |-- Order/
|   |   |   |   |   |-- Billing/
|   |   |   |   |   |-- Inventory/
|   |   |   |   |   |-- Crm/
|   |   |   |   |   |-- Online/
|   |   |   |   |   |-- Analytics/
|   |   |   |   |   |-- Settings/
|   |   |   |-- Middleware/
|   |   |   |   |-- TenantScope.php        # Resolve active outlet
|   |   |   |   |-- RoleCheck.php
|   |   |   |   |-- AuditLogger.php
|   |   |   |-- Requests/                  # Form request validation
|   |   |   |-- Resources/                 # API transformers
|   |-- Jobs/                              # Async jobs
|   |-- Listeners/                         # Event listeners
|   |-- Models/                            # Eloquent models
|   |-- Notifications/                     # Email / SMS notifications
|   |-- Policies/                          # Authorization policies
|   |-- Providers/
|   |-- Repositories/                      # Data access layer
|   |-- Rules/                             # Custom validation rules
|   |-- Services/                          # Business logic
|   |   |-- BillingService.php
|   |   |-- KotService.php
|   |   |-- RecipeStockService.php
|   |   |-- TaxService.php
|   |   |-- PaymentService.php
|   |   |-- ReorderService.php
|   |-- Support/                           # Helpers, enums, constants
|-- config/
|-- database/
|   |-- migrations/
|   |-- seeders/
|   |-- factories/
|-- routes/
|   |-- api.php
|   |-- channels.php
|-- tests/
```

### Architectural Patterns

**Layered architecture with Repository + Service separation:**

```
Controller (HTTP) -> FormRequest (validation) -> Service (business logic)
   -> Repository (data access) -> Model (Eloquent)
```

- **Controllers** are thin: they delegate to services and return Resources.
- **Services** contain business logic and orchestrate transactions.
- **Repositories** encapsulate queries and can be swapped for testing.
- **Form Requests** validate input.
- **Resources** transform models to consistent JSON.
- **Jobs** handle async work (KOT, reports, notifications, stock recalculation).
- **Events/Listeners** decouple side effects (e.g., on `OrderPlaced`, deduct stock, push KDS).

### Key Service Layer

| Service | Responsibility |
|---------|---------------|
| `OrderService` | Create/modify/hold/cancel orders, status workflow |
| `BillingService` | Build bill, apply discounts/taxes, split, settle, refund |
| `KotService` | Generate KOT, route to printers/KDS stations |
| `PaymentService` | Record tenders, change calc, gateway calls |
| `TaxService` | CGST/SGST/IGST calculation, GST mapping |
| `RecipeStockService` | Deduct stock on order, recompute stock value |
| `ReorderService` | Evaluate reorder points, generate draft POs |
| `InventoryValuationService` | FIFO/LIFO/average cost calculation |

---

## Frontend Architecture (React Vite)

### Folder Structure

```
frontend/
|-- src/
|   |-- api/                    # API client functions (axios instances)
|   |-- assets/
|   |-- components/
|   |   |-- common/             # Button, Modal, Table, Input, Card
|   |   |-- layout/             # Sidebar, Topbar, OutletSwitcher
|   |   |-- pos/                # POS-specific widgets
|   |   |-- charts/
|   |   |-- forms/
|   |-- constants/
|   |-- hooks/
|   |   |-- useAuth.ts
|   |   |-- useOrders.ts
|   |   |-- useMenu.ts
|   |   |-- useWebSocket.ts
|   |-- pages/
|   |   |-- auth/
|   |   |-- dashboard/
|   |   |-- menu/
|   |   |-- tables/
|   |   |-- orders/
|   |   |-- billing/
|   |   |-- inventory/
|   |   |-- crm/
|   |   |-- online/
|   |   |-- kds/
|   |   |-- reports/
|   |   |-- settings/
|   |-- routes/
|   |   |-- AppRoutes.tsx       # React Router config with guards
|   |-- services/               # Business helpers on client
|   |-- stores/                 # Zustand stores
|   |   |-- authStore.ts
|   |   |-- cartStore.ts
|   |   |-- outletStore.ts
|   |   |-- uiStore.ts
|   |-- types/                  # TypeScript interfaces
|   |-- utils/
|   |-- App.tsx
|   |-- main.tsx
|-- public/
|-- package.json
|-- vite.config.ts
|-- tailwind.config.js
```

### Patterns

- **React Router v6** with role-based route guards.
- **TanStack Query** for server state (caching, mutations, optimistic updates).
- **Zustand** for client state (auth, cart, active outlet, UI toggles).
- **React Hook Form + Zod** for forms.
- **WebSocket hook** (`useWebSocket`) subscribes to KDS / order channels.
- **Feature-based pages** with shared components.

---

## Database Architecture

- **MySQL 8.0** as the primary datastore.
- All tables use `utf8mb4` and InnoDB.
- Every tenant-scoped table includes an `outlet_id` foreign key.
- UUIDs (`char(36)`) used for public-facing IDs (orders, bills) where appropriate; auto-increment integers for internal tables.
- Soft deletes (`deleted_at`) on auditable entities.
- Timestamps `created_at` / `updated_at` on all tables.
- Money stored as `decimal(12,2)`.
- Foreign keys with `ON DELETE RESTRICT` for financial integrity; `CASCADE` only for child config tables.

See [database-schema.md](database-schema.md) for the complete schema.

---

## Caching & Real-time

### Redis Usage

| Use | Mechanism |
|-----|-----------|
| Application cache | `Cache::remember()` for menu, tax config, settings |
| Sessions | Sanctum session store |
| Queues | Horizon-managed Redis queues |
| Real-time pub/sub | Channel publish for KDS/order events |
| Distributed locks | `Cache::lock()` for concurrent bill settle, stock decrement |
| Rate limiting | Throttling middleware |

### Real-time (WebSocket)

- **Server:** Laravel Reverb (or Soketi) WebSocket server.
- **Auth:** Private channels authenticated via Sanctum token.
- **Channels:**
  - `private-outlet.{outletId}.kds` - KDS order updates
  - `private-outlet.{outletId}.orders` - new/updated orders
  - `private-outlet.{outletId}.billing` - bill status
- **Flow:** Service fires event -> queued listener broadcasts -> WebSocket pushes to subscribed clients.

---

## Multi-Tenant Architecture

The system is a **multi-outlet single-instance** multi-tenant model:

- One database, shared schema.
- Tenancy is at the **restaurant** level; a restaurant owns multiple **outlets**.
- Every tenant-scoped table carries `restaurant_id` and `outlet_id`.
- `Restaurant` is the top-level tenant boundary; `Outlet` is the operational boundary.
- A user belongs to a restaurant and is assigned to one or more outlets.

### Resolution Flow

```
Request -> Middleware (TenantScope)
   -> resolve active outlet from header X-Outlet-Id or session
   -> set global query scope to filter by outlet_id
   -> attach restaurant_id from authenticated user
```

### Implementation

- **Global scopes** on models auto-filter by `outlet_id` unless the user is an HQ admin.
- **TenantScope middleware** resolves and validates the active outlet for the authenticated user.
- **Cross-outlet** operations (HQ reports, transfers) bypass the scope with explicit permission checks.
- **Data isolation tests** in QA verify no cross-tenant leakage.

---

## API Design Patterns

### Conventions

- RESTful resource-oriented URLs.
- Versioned: `/api/v1/...`.
- Sanctum token auth via `Authorization: Bearer <token>`.
- JSON responses wrapped by API Resources.
- Standard error envelope:
  ```json
  { "success": false, "message": "...", "errors": { "field": ["..."] } }
  ```
- Pagination via `?page=` and `?per_page=` (default 25).
- Filtering via query params (`?status=open`).
- Sorting via `?sort=-created_at`.
- Idempotency keys for payment / order create (`Idempotency-Key` header).

### Typical Resource Endpoints

```
GET    /api/v1/menu/categories
POST   /api/v1/menu/categories
GET    /api/v1/menu/categories/{id}
PUT    /api/v1/menu/categories/{id}
DELETE /api/v1/menu/categories/{id}

GET    /api/v1/orders
POST   /api/v1/orders
GET    /api/v1/orders/{id}
POST   /api/v1/orders/{id}/items
POST   /api/v1/orders/{id}/hold
POST   /api/v1/orders/{id}/cancel
POST   /api/v1/orders/{id}/transfer
```

### Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 204 | No content (delete) |
| 400 | Bad request |
| 401 | Unauthenticated |
| 403 | Forbidden (role/permission) |
| 404 | Not found |
| 422 | Validation error |
| 429 | Rate limited |

---

## Security

- HTTPS enforced via Nginx; HSTS enabled.
- Sanctum tokens with expiry; refresh flow.
- Role-based authorization via Policies on every write endpoint.
- Input validation on every Form Request.
- SQL injection prevented via Eloquent / parameterized queries.
- XSS prevented via React escaping; CSP headers.
- Rate limiting on auth and write endpoints.
- Audit logging on all financial mutations (bills, payments, stock adjustments).
- Secrets in `.env`, never committed; rotated.
- Daily DB backups; weekly full system snapshots.

---

## Deployment Strategy

**Target:** Ubuntu 22.04 LTS server.

### Process Managers

| Service | Manager |
|---------|---------|
| PHP-FPM | systemd |
| Nginx | systemd |
| Laravel queue workers | Laravel Horizon (supervised via systemd) |
| WebSocket server (Reverb/Soketi) | PM2 / systemd |
| Redis | systemd |
| MySQL | systemd |

### Nginx Responsibilities

- Serve the built React SPA from `/var/www/rms-frontend/dist`.
- Proxy `/api/*` to PHP-FPM.
- Upgrade `/ws/*` to the WebSocket server.
- TLS via Let's Encrypt / certbot.

### CI/CD Pipeline

```
Developer push -> Git repository
   -> CI (GitHub Actions / GitLab CI)
      -> Lint (PHP CS Fixer, ESLint)
      -> Run tests (PHPUnit, Vitest)
      -> Build SPA (vite build)
   -> Deploy to staging on green
   -> Manual promote to production
```

### Deployment Steps (production)

1. Pull latest code on server.
2. `composer install --no-dev --optimize-autoloader`.
3. `php artisan migrate --force`.
4. `php artisan config:cache && route:cache && view:cache`.
5. Build frontend: `cd frontend && npm ci && npm run build`.
6. Reload PHP-FPM.
7. Reload Nginx.
8. Restart queue workers and WebSocket server.
9. Smoke test critical endpoints.

### Monitoring

- Laravel Horizon dashboard for queue health.
- Application logs to `/storage/logs` and rotated by logrotate.
- Server monitoring via Uptime checks + system metrics (CPU, disk, memory).
- Alerting on error rate spikes and queue backlog.
