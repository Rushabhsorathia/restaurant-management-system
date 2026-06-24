# RMS-004: User & Role Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-004 |
| **Type** | Story |
| **Epic** | Foundation |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | P0 - Critical |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | Done |
| **Dependencies** | RMS-003 |

## User Story

As a restaurant admin, I want to create and manage staff users, assign them roles (Admin, Manager, Cashier, Waiter, Chef, Captain), and control which outlets they can access, so that each staff member has the right permissions for their job.

## Description

This ticket delivers the user and role management module. It builds on the authentication system and the spatie/laravel-permission foundation from RMS-002/003 to provide full CRUD for users and role assignment, plus a permission matrix view. An admin can invite new staff, assign one or more roles, assign one or more outlets (multi-outlet access), activate or deactivate accounts, and reset a user's password.

Roles are predefined (admin, manager, cashier, waiter, chef, captain, hq_admin) with mapped permissions per module (menu.create, bill.settle, etc.). The admin can view the permission matrix showing which permissions each role has. The frontend provides a user list with search/filter, a user create/edit form, and a roles & permissions management screen.

## Acceptance Criteria

- [x] `GET /api/v1/users` returns a paginated, searchable list of users (filter by role, outlet, active status).
- [x] `POST /api/v1/users` creates a user with name, email, phone, password, roles, and outlet assignments.
- [x] `GET /api/v1/users/{id}` returns a single user with roles, permissions, and outlets.
- [x] `PUT /api/v1/users/{id}` updates user details, roles, and outlets.
- [x] `PATCH /api/v1/users/{id}/status` activates/deactivates a user.
- [x] `POST /api/v1/users/{id}/reset-password` triggers a password reset for the user.
- [x] `DELETE /api/v1/users/{id}` soft-deletes a user.
- [x] `GET /api/v1/roles` lists all roles; `GET /api/v1/roles/{id}/permissions` lists permissions for a role.
- [x] `PUT /api/v1/roles/{id}/permissions` updates the permission set for a role.
- [x] `GET /api/v1/permissions` lists all permissions grouped by module.
- [x] Frontend User List screen with search, role filter, outlet filter, and active filter.
- [x] Frontend User Form to create/edit with role multi-select and outlet multi-select.
- [x] Frontend Roles & Permissions screen with a permission matrix (roles x permissions checkboxes).
- [x] Only users with `users.manage` permission can access these screens (admin role by default).

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| User List | /settings/users | Paginated table of staff users |
| User Form | /settings/users/new, /settings/users/:id/edit | Create or edit a user |
| Roles & Permissions | /settings/roles | Permission matrix for roles |

### Screen Details

**User List (/settings/users):** Page header "Staff Users" with a "Add User" button. Filters row: Search input (name/email/phone), Role dropdown (All/Admin/Manager/Cashier/...), Outlet dropdown, Active status toggle. Data table columns: Avatar+Name, Email, Phone, Roles (badges), Outlets (badges), Status (Active/Inactive badge), Last Login, Actions (Edit, Deactivate/Activate, Reset Password, Delete). Pagination at bottom. Empty state with "No users found". Delete shows a confirm modal with typing the user's name to confirm.

**User Form (/settings/users/new):** Form sections: (1) Personal Info - Name, Email, Phone, Avatar upload. (2) Security - Password (with generate button), Send invite email checkbox. (3) Roles - multi-select checkboxes (Admin, Manager, Cashier, Waiter, Chef, Captain). (4) Outlet Access - multi-select of outlets (checkbox list with "Select all"). Buttons: "Save User" (primary), "Cancel". Validation: name required, email required + unique, phone required, at least one role, at least one outlet. Edit form pre-fills and shows "Last login" and "Account created" read-only.

**Roles & Permissions (/settings/roles):** Left column lists roles (Admin, Manager, Cashier, Waiter, Chef, Captain, HQ Admin). Selecting a role shows a permission matrix on the right: rows grouped by module (Auth, Menu, Orders, Billing, Inventory, CRM, Reports, Settings), columns are actions (view, create, edit, delete, special). Checkboxes toggle permissions. "Save Changes" button persists. System roles (admin) may have locked permissions shown as disabled. Note: "HQ Admin" role applies across all restaurants.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/users | List users (paginated, filterable) |
| POST | /api/v1/users | Create user |
| GET | /api/v1/users/{id} | Get user detail |
| PUT | /api/v1/users/{id} | Update user |
| PATCH | /api/v1/users/{id}/status | Activate/deactivate |
| POST | /api/v1/users/{id}/reset-password | Reset user password |
| DELETE | /api/v1/users/{id} | Soft-delete user |
| GET | /api/v1/roles | List roles |
| GET | /api/v1/roles/{id}/permissions | Get role permissions |
| PUT | /api/v1/roles/{id}/permissions | Update role permissions |
| GET | /api/v1/permissions | List all permissions grouped by module |

## Database Tables

Uses tables from RMS-002: `users`, `roles`, `permissions`, `role_user`, `permission_role`, `user_outlets`. No schema changes required; this ticket builds the API and UI on top.

## Technical Notes

- **Backend:** `UserController` (resource controller) with Form Requests (`StoreUserRequest`, `UpdateUserRequest`). Use spatie/laravel-permission's `assignRole`, `syncRoles`, `givePermissionTo`, `syncPermissions`. Apply Policies (`UserPolicy`) for authorization.
- **Outlet assignment:** Sync via `user_outlets` pivot; validate the admin can only assign outlets within their restaurant.
- **Soft delete:** Use Eloquent soft deletes; ensure deleted users cannot log in.
- **Password reset:** Use the password broker to send reset to the user's email.
- **Resources:** `UserResource` transforms user with roles, permissions (flattened), and outlets.
- **Frontend:** `useUsers` hook (TanStack Query) for list with filters. React Hook Form + Zod for the user form. Outlet and role selects are multi-select checkbox components.
- **Permission guard:** `<RequirePermission permission="users.manage">` wrapper on the settings nav and routes.

## Subtasks

1. [x] Create UserController (index, store, show, update, destroy)
2. [x] Create status toggle and reset-password endpoints
3. [x] Create Form Requests (StoreUserRequest, UpdateUserRequest)
4. [x] Create UserPolicy for authorization
5. [x] Create UserResource (with roles, permissions, outlets)
6. [x] Create RoleController (index, permissions, updatePermissions)
7. [x] Create PermissionController (index grouped by module)
8. [x] Frontend: User List page with filters and table
9. [x] Frontend: User Form (create/edit) with role + outlet multi-select
10. [x] Frontend: Roles & Permissions matrix screen
11. [x] Frontend: RequirePermission guard component
12. [x] Write backend tests for CRUD and authorization
13. [x] Seed default role-permission mappings

## Testing Criteria

- [x] Admin can create a user with roles and outlets; user appears in list.
- [x] Non-admin (e.g., cashier) receives 403 on user management endpoints.
- [x] Updating a user syncs roles and outlets correctly.
- [x] Deactivating a user prevents login.
- [x] Reset password sends a reset email.
- [x] Soft-deleted users excluded from list but restorable.
- [x] Permission matrix changes persist and affect authorization immediately.
- [x] Search and filters return correct subsets.
- [x] Validation rejects duplicate email and missing required fields.
