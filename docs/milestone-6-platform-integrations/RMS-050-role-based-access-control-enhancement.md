# RMS-050: Role-Based Access Control Enhancement

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-050 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-001 (Auth & Multi-Tenancy), RMS-002 (Outlet Management), RMS-003 (User Management) |

## User Story
As a multi-outlet restaurant group owner, I want a granular permission system where I can create custom roles per outlet (Captain, Cashier, Inventory Manager, Auditor), assign module-level read/write/delete/export actions, build a permission matrix, and audit every privilege change, so that staff only see and do what their job requires, and we stay compliant with DPDP and SOC 2 controls.

## Description
The existing user model has only coarse roles (owner, manager, staff). Real operations need separation of duties: a Captain should place orders and print KOT but cannot access financial reports; an Inventory Manager should manage purchase orders but not view customer PII; an Auditor should have read-only access across all modules with no edit rights. This story implements a production-grade RBAC layer on top of the existing user system.

The implementation uses `spatie/laravel-permission` as the underlying engine (battle-tested, cache-friendly, hierarchical role support) extended with three custom concerns: (1) **Outlet-scoped permissions** -- a user's role can be different at each outlet, so the same person can be a Manager at Outlet A and a Captain at Outlet B; (2) **Custom role builder** -- admins compose new roles by ticking boxes in a matrix UI (modules on rows × actions on columns); (3) **Permission audit log** -- every grant, revoke, role assignment, and role creation is recorded immutably with actor, target, before/after, and IP.

A permission matrix UI exposes the canonical grid: rows = modules (POS, KDS, Inventory, Menu, CRM, Reports, Settings, Hardware, Suppliers, Community, etc., ~25 modules × ~6 actions each = ~150 cells). Click a cell to toggle. Roles can inherit from other roles (e.g., `Outlet Manager` extends `Captain` and adds `Reports:read`). The frontend uses `<RequirePermission>` and `<RequireRole>` guards on routes and components; the API uses a `permission:` middleware on every protected route, and Laravel Policies on every Eloquent model.

API tokens (Sanctum) carry permission claims so the SPA can render selectively without round-tripping. The system supports a `deny` rule that overrides inherited grants (for emergency access lockdown). All admin actions emit a `PermissionChanged` event that the audit log subscribes to, and the Web UI shows a real-time audit feed for compliance officers.

## Acceptance Criteria
- [ ] `spatie/laravel-permission` integrated with `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` tables.
- [ ] Custom roles can be created, edited, and deleted via Role Builder UI; system roles (Super Admin, Owner) are protected from deletion.
- [ ] Permission matrix UI: modules × actions grid with toggle cells; bulk enable/disable; visual diff vs inherited role.
- [ ] Outlet-scoped permissions: user can have `Manager` role at Outlet A and `Captain` role at Outlet B; switching outlet updates active permissions.
- [ ] Hierarchical roles supported via `parent_role` column; child inherits all parent permissions unless explicitly denied.
- [ ] `deny` permission rule takes precedence over inherited grants; documented as `permissions.deny:{module}.{action}`.
- [ ] Permission middleware `permission:{guard}.{module}.{action}` on all protected API routes; Policies on all Eloquent models.
- [ ] Frontend `<RequirePermission module="pos" action="create_order">` wrapper hides UI; `<RequireRole role="manager">` for route-level guards.
- [ ] Sanctum API tokens include `permissions` claim; client can render selectively without extra calls.
- [ ] Every permission change (grant, revoke, role create/update/delete, assignment) logged in `permission_audit_log` with actor, target, before/after JSON, IP, user agent, timestamp.
- [ ] Audit feed UI shows recent changes with filters by actor, target, action, date range; export to CSV.
- [ ] Role assignment UI allows assigning multiple roles to one user across multiple outlets via `user_outlet_roles` pivot.
- [ ] `php artisan permission:sync` command syncs matrix from a YAML/JSON config for CI/CD.
- [ ] Permission cache invalidated on every change via model observers; no stale permissions.
- [ ] Emergency "lockdown" toggles a global `deny_all` flag that suspends all non-Super-Admin access.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Roles List | /admin/roles | All roles (system + custom) with member count |
| Role Builder | /admin/roles/{id}/edit | Permission matrix grid editor |
| Role Create | /admin/roles/create | New role wizard |
| User Role Assignment | /admin/users/{id}/roles | Assign roles across outlets |
| Permission Audit Log | /admin/permissions/audit | Filterable audit feed |
| Permission Matrix Overview | /admin/permissions | Read-only global matrix view |

### Screen Details

**Roles List (/admin/roles)**
- Table columns: Name | Type (System/Custom) | Member Count | Outlet Scope | Inherits From | Created At | Actions.
- Filters: Type, Outlet Scope, Has Members.
- Buttons per row: "Edit", "Duplicate", "Delete" (disabled for system roles).
- Top: "Create Role" button.
- Search bar: name-based fuzzy search.

**Role Builder (/admin/roles/{id}/edit)**
- Top section: Role Name (input), Description (textarea), Inherits From (dropdown of other roles), Outlet Scope (single/all/specific multiselect).
- Main grid: Rows = Modules (POS, KDS, Inventory, Menu, Billing, Reports, CRM, Settings, Hardware, Suppliers, Community, Analytics, Integrations, etc.). Columns = Actions (View, Create, Update, Delete, Export, Approve).
- Each cell: 3-state toggle (Off / On / Deny) with color coding (gray/green/red).
- Row header: Module name + icon + "select all actions" checkbox.
- Column header: Action name + "select all modules" checkbox.
- Right sidebar: Live preview of effective permissions (after inheritance + deny resolution).
- Footer: "Save", "Save as Copy", "Reset to inherited", "Preview as User".
- Validation: Cannot save if no permissions enabled; warning if denies shadow inherited grants.

**User Role Assignment (/admin/users/{id}/roles)**
- Layout: User header + per-outlet assignment table.
- Table columns: Outlet | Assigned Roles (multiselect chips) | Effective Permissions (read-only) | Actions.
- Each row: Outlet dropdown opens a checkbox list of available roles; "Apply to all outlets" bulk button.
- Right panel: "Effective permissions across all outlets" combined view.
- Buttons: "Save", "Cancel", "Impersonate User" (logs impersonation start).

**Permission Audit Log (/admin/permissions/audit)**
- Layout: Filterable table with rich detail drawer.
- Filters: Actor (user picker), Target (user/role), Action Type (created, updated, deleted, granted, revoked), Date Range, Outlet Scope.
- Table columns: Timestamp | Actor | Action | Target | Module/Action Affected | Diff Summary | IP.
- Row click: Opens side drawer with full before/after JSON diff, IP, user agent.
- Buttons: "Export CSV", "Export JSON", "Print".

**Permission Matrix Overview (/admin/permissions)**
- Read-only consolidated matrix showing all roles side-by-side; useful for compliance review.
- Modules × Actions grid; cells colored by role assignment count.
- Toggle: Show only differences from base role.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/roles | List roles with filters |
| POST | /api/v1/roles | Create custom role |
| GET | /api/v1/roles/{id} | Get role detail with permissions |
| PUT | /api/v1/roles/{id} | Update role (name, permissions, inheritance) |
| DELETE | /api/v1/roles/{id} | Delete custom role (system roles protected) |
| POST | /api/v1/roles/{id}/duplicate | Duplicate role |
| GET | /api/v1/roles/{id}/effective-permissions | Computed effective permissions after inheritance |
| GET | /api/v1/permissions/matrix | Get full permission matrix |
| POST | /api/v1/users/{id}/roles | Assign roles to user per outlet |
| DELETE | /api/v1/users/{id}/roles/{roleId} | Revoke role |
| GET | /api/v1/users/{id}/effective-permissions | Get user's effective permissions at active outlet |
| GET | /api/v1/permissions/audit | Query audit log with filters |
| GET | /api/v1/permissions/audit/export | Export audit log |
| POST | /api/v1/permissions/lockdown | Toggle emergency deny_all (Super Admin only) |
| GET | /api/v1/permissions/check | Check if current user has specific permission |

## Database Tables

**roles** (extends spatie)
- id (bigIncrements, PK)
- name (string, unique per guard)
- guard_name (string, default 'web')
- display_name (string)
- description (text, nullable)
- parent_role_id (foreignId, roles, nullable)
- is_system (boolean, default false)
- outlet_scope (enum: global, per_outlet, default global)
- created_at, updated_at

**permissions** (extends spatie)
- id (bigIncrements, PK)
- name (string) -- format: `{module}.{action}` e.g., `pos.create_order`
- module (string, indexed) -- `pos`, `kds`, `inventory`
- action (enum: view, create, update, delete, export, approve, deny)
- display_name (string)
- guard_name (string, default 'web')
- created_at, updated_at
- unique([name, guard_name])

**user_outlet_roles** (pivot)
- id (bigIncrements, PK)
- user_id (foreignId, users)
- outlet_id (foreignId, outlets)
- role_id (foreignId, roles)
- assigned_by (foreignId, users, nullable)
- assigned_at (timestamp)
- expires_at (timestamp, nullable) -- for temporary assignments
- timestamps
- unique([user_id, outlet_id, role_id])
- index([user_id, outlet_id])

**permission_audit_log**
- id (bigIncrements, PK)
- actor_id (foreignId, users)
- action (enum: role_created, role_updated, role_deleted, permission_granted, permission_revoked, permission_denied, role_assigned, role_revoked, lockdown_enabled, lockdown_disabled, impersonation_started, impersonation_ended)
- target_type (string) -- `role`, `user`, `permission`
- target_id (unsignedBigInteger, nullable)
- module (string, nullable)
- permission_name (string, nullable)
- before_json (json, nullable)
- after_json (json, nullable)
- ip_address (string(45), nullable)
- user_agent (text, nullable)
- outlet_id (foreignId, outlets, nullable)
- created_at
- index([actor_id, created_at])
- index([target_type, target_id])

**permission_denies** (overrides)
- id (bigIncrements, PK)
- role_id (foreignId, roles)
- permission_name (string) -- `{module}.{action}`
- reason (text, nullable)
- created_by (foreignId, users)
- created_at
- unique([role_id, permission_name])

## Technical Notes

**Laravel Backend:**
- `spatie/laravel-permission` v6 with custom migration extending default schema; uses Redis cache (`CACHE_STORE=redis`) for ` spatie.permission.cache` with `cache.expiration = 86400`.
- `PermissionService::resolveFor($user, $outletId)` returns effective permission set applying inheritance + denies + outlet scope; called by Sanctum's `Auth::user()->tokenCan()` and by `permission:` middleware.
- Middleware aliases: `role`, `permission` (with format `module.action`), `role_or_permission`. Applied to routes in `routes/api.php` via `->middleware('permission:pos.create_order')`.
- Policies generated for each module: `OrderPolicy`, `MenuItemPolicy`, `InventoryPolicy`, etc., delegating to `PermissionService`.
- Sanctum token abilities set at login: `abilities = $user->effectivePermissions->pluck('name')->toArray()`.
- Observer on `Role`, `User`, `UserOutletRole`, `PermissionDeny` models writes to `permission_audit_log` automatically.
- Lockdown flag stored in `settings` table; `EnsureNotLockdown` middleware checks on every authenticated request.
- Impersonation guarded by `ImpersonateUser` trait; starts/ends events logged.
- `php artisan permission:sync` reads `config/permissions.yaml` and idempotently upserts roles/permissions for CI.

**React Frontend:**
- `useAuth()` returns `{ user, effectivePermissions, hasPermission, hasRole, isImpersonating }`.
- `<RequirePermission module="pos" action="create_order">{children}</RequirePermission>` renders nothing or fallback.
- `<RequireRole role="manager">` for route guards via React Router v6 `loader`.
- Role Builder uses a virtualized grid (`react-virtual`) for 25×6 = 150 cells, plus column virtualization.
- Effective permissions computed client-side from a snapshot endpoint; cached in `usePermissionStore` (Zustand).
- Audit log uses infinite scroll; CSV export streamed.
- Impersonation banner sticky at top; "Exit Impersonation" button always visible.

## Subtasks
1. [ ] Install `spatie/laravel-permission` and publish + extend migration for custom columns
2. [ ] Seed initial system roles (Super Admin, Owner, Manager, Captain, Cashier, Inventory Manager, Auditor, Viewer) and base permissions
3. [ ] Build `user_outlet_roles`, `permission_audit_log`, `permission_denies` migrations and models
4. [ ] Implement `PermissionService` with inheritance + deny resolution + outlet scoping
5. [ ] Build `RoleController` CRUD and `PermissionController` matrix endpoints
6. [ ] Build `UserRoleController` for outlet-scoped role assignment
7. [ ] Implement `AuditLogger` observer on all RBAC models
8. [ ] Build `EnsureNotLockdown` middleware and `LockdownController`
9. [ ] Implement `php artisan permission:sync` for CI-driven config sync
10. [ ] Build Sanctum token ability injection at login and refresh
11. [ ] Create `RolePolicy`, `MenuItemPolicy`, `OrderPolicy`, etc. for all module models
12. [ ] Build React Roles List page with filters
13. [ ] Build React Role Builder with permission matrix grid and inheritance preview
14. [ ] Build React User Role Assignment page with per-outlet table
15. [ ] Build React Permission Audit Log page with diff drawer
16. [ ] Add `<RequirePermission>` and `<RequireRole>` components and React Router guards
17. [ ] Implement impersonation flow with audit logging
18. [ ] Write tests for inheritance, deny override, outlet scope, lockdown, audit, matrix, policies

## Testing Criteria
- [ ] Custom role can be created, assigned to a user at one outlet, and not affect another outlet
- [ ] Inherited role adds parent permissions automatically; deny rule overrides them
- [ ] Permission middleware blocks API call returning 403 with audit log entry
- [ ] Frontend `<RequirePermission>` hides UI for unauthorized users
- [ ] Sanctum token carries correct abilities after role assignment
- [ ] Audit log records every grant/revoke with full before/after JSON
- [ ] System roles (Super Admin, Owner) cannot be deleted or modified
- [ ] Lockdown blocks all non-Super-Admin users with explanatory 503
- [ ] Permission cache invalidates within 5s of role change
- [ ] `php artisan permission:sync` is idempotent across CI runs
- [ ] Impersonation writes start/end events with actor and target user
- [ ] CSV export of audit log opens cleanly in Excel with proper escaping
