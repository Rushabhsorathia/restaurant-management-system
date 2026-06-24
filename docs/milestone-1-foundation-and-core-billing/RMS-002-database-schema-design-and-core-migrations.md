# RMS-002: Database Schema Design & Core Migrations

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-002 |
| **Type** | Story |
| **Epic** | Foundation |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | P0 - Critical |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | Done |
| **Dependencies** | RMS-001 |

## User Story

As a backend developer, I want the core database schema and migrations created for users, restaurants, outlets, tax configs, and app settings, so that all authentication, multi-tenant, and configuration features have a stable data foundation.

## Description

This ticket implements the foundational database layer defined in the database schema document. It covers the Core/Auth module tables that are prerequisites for authentication (RMS-003), user management (RMS-004), and restaurant/outlet setup (RMS-005): `users`, `roles`, `permissions`, `role_user`, `permission_role`, `user_outlets`, `personal_access_tokens`, `restaurants`, `outlets`, `tax_configs`, `discount_configs`, and `app_settings`.

Each migration must define correct column types, foreign keys with appropriate delete rules, indexes on foreign keys and frequently queried columns, and consistent timestamp columns. Models must be created with fillable fields, relationships, and (where applicable) soft deletes. The migrations must be runnable in order and reversible.

Seeders for baseline roles, permissions, units, languages, and a sample restaurant must be created and idempotent. This ticket does not implement auth logic or controllers; it only establishes the schema and Eloquent models.

## Acceptance Criteria

- [x] Migrations created for: users, roles, permissions, role_user, permission_role, user_outlets, personal_access_tokens, restaurants, outlets, tax_configs, discount_configs, app_settings.
- [x] All foreign keys defined with `ON DELETE RESTRICT` (or CASCADE for pure pivot tables) and indexed.
- [x] Eloquent models created with `$fillable`, relationships (hasMany, belongsTo, belongsToMany), and soft deletes where appropriate.
- [x] `php artisan migrate:fresh` runs cleanly with no errors.
- [x] `php artisan migrate:rollback` rolls back cleanly.
- [x] Seeders created for roles, permissions (with role mappings), units, languages, and a sample restaurant + outlet + admin user.
- [x] Seeders are idempotent (use `firstOrCreate` / `updateOrCreate`).
- [x] `php artisan db:seed` populates baseline data successfully.
- [x] Database uses utf8mb4 charset and InnoDB engine on all tables.

## UI Screens

This ticket is backend-only; no UI screens are built.

## API Endpoints

No endpoints exposed in this ticket.

## Database Tables

| Table | Key Columns |
|-------|-------------|
| users | id, restaurant_id (FK), name, email (unique), phone, password, is_active, last_login_at, settings (json), timestamps, deleted_at |
| roles | id, name (unique), display_name, guard_name |
| permissions | id, name (unique), display_name, module, guard_name |
| role_user | user_id (FK), role_id (FK) - composite PK |
| permission_role | permission_id (FK), role_id (FK) - composite PK |
| user_outlets | user_id (FK), outlet_id (FK) - composite PK |
| personal_access_tokens | id, tokenable_type, tokenable_id, name, token (unique), abilities, last_used_at, expires_at |
| restaurants | id, name, legal_name, gstin, pan, logo_url, email, phone, address fields, currency_code, timezone, default_tax_rate, fssai_number |
| outlets | id, restaurant_id (FK), name, code (unique per restaurant), type, gstin, address fields, is_central_kitchen, is_active, settings (json) |
| tax_configs | id, restaurant_id (FK), name, type, cgst_rate, sgst_rate, igst_rate, cess_rate, is_active |
| discount_configs | id, restaurant_id (FK), name, type, value, applies_to, min_bill_amount, is_active |
| app_settings | id, restaurant_id (FK nullable), outlet_id (FK nullable), key, value |

## Technical Notes

- **Migration order** must follow the dependency graph: users -> roles -> permissions -> pivots -> restaurants -> outlets -> user_outlets -> tax_configs -> discount_configs -> app_settings.
- **Money columns** use `decimal(12,2)`; tax rates use `decimal(5,2)`.
- **Tenant scoping:** Models that are tenant-scoped (`outlet_id`, `restaurant_id`) should have these columns indexed.
- **UUIDs:** Use `uuid` char(36) columns where public IDs are needed (reserved for orders/bills in later tickets).
- **Models:** Place in `app/Models`. Define relationships: `User belongsTo Restaurant`, `User belongsToMany Roles`, `User belongsToMany Outlets`, `Restaurant hasMany Outlets`, `Outlet belongsTo Restaurant`.
- **Permission system:** Use spatie/laravel-permission package for robust role/permission management, mapping to the roles/permissions tables. This avoids reinventing the pivot logic.
- **Soft deletes** on `users`.
- **Seeders:** `RolesSeeder` (admin, manager, cashier, waiter, chef, captain, hq_admin), `PermissionsSeeder` (full permission set per module), `UnitsSeeder`, `LanguagesSeeder`, `TaxConfigSeeder`, `SampleRestaurantSeeder`, `AdminUserSeeder` (admin with forced password change flag).

## Subtasks

1. [x] Create migration: users (with soft deletes)
2. [x] Create migration: roles
3. [x] Create migration: permissions
4. [x] Create migration: role_user pivot
5. [x] Create migration: permission_role pivot
6. [x] Create migration: user_outlets pivot
7. [x] Create migration: personal_access_tokens (Sanctum)
8. [x] Create migration: restaurants
9. [x] Create migration: outlets
10. [x] Create migration: tax_configs
11. [x] Create migration: discount_configs
12. [x] Create migration: app_settings
13. [x] Create Eloquent models with relationships
14. [x] Install and configure spatie/laravel-permission
15. [x] Create RolesSeeder, PermissionsSeeder
16. [x] Create UnitsSeeder, LanguagesSeeder, TaxConfigSeeder
17. [x] Create SampleRestaurantSeeder (restaurant + outlet + admin)
18. [x] Verify migrate:fresh --seed runs cleanly

## Testing Criteria

- [x] `php artisan migrate:fresh` succeeds with zero errors.
- [x] `php artisan migrate:rollback` reverts all migrations cleanly.
- [x] Seeder run produces expected role, permission, unit, language, tax config, restaurant, outlet, and admin records.
- [x] Re-running seeders does not create duplicates (idempotency).
- [x] Foreign key constraints prevent deleting a restaurant that has outlets. (Outlet FK uses `restrictOnDelete`; user_outlets is `cascade`.)
- [x] Model relationships resolve correctly in tinker (e.g., `$restaurant->outlets` returns collection).
- [x] Unit tests assert critical table structures exist. (See `tests/Feature/DatabaseSchemaTest.php` + `SeederTest.php`.)
