# RMS-005: Restaurant & Outlet Setup

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-005 |
| **Type** | Story |
| **Epic** | Foundation |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | P1 - High |
| **Story Points** | 6 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-004 |

## User Story

As a restaurant admin, I want to configure my restaurant profile, create and manage multiple outlets, set branding (logo, colors), and configure tax/GST settings, so that the system is tailored to my business and ready for menu and order configuration.

## Description

This ticket implements the restaurant and outlet management module, which establishes the multi-tenant context for the entire system. An admin can view and edit the restaurant profile (legal name, GSTIN, PAN, FSSAI, address, currency, timezone), create and manage outlets (each with its own GSTIN, type, address, and active status), upload branding assets (logo), and configure tax settings (CGST/SGST/IGST rates per tax config).

The active outlet context is critical: it determines which menu, tables, orders, and bills a user interacts with. The frontend provides an outlet switcher that sets the active outlet (stored in a header `X-Outlet-Id` and/or session), and all subsequent API calls are scoped to that outlet. The backend TenantScope middleware resolves and validates the active outlet for the authenticated user.

## Acceptance Criteria

- [ ] `GET /api/v1/restaurant` returns the authenticated user's restaurant profile.
- [ ] `PUT /api/v1/restaurant` updates restaurant profile (name, legal_name, gstin, pan, fssai, address, currency, timezone).
- [ ] `POST /api/v1/restaurant/logo` uploads and stores the restaurant logo.
- [ ] `GET /api/v1/outlets` lists outlets for the restaurant.
- [ ] `POST /api/v1/outlets` creates a new outlet with name, code, type, gstin, address.
- [ ] `GET /api/v1/outlets/{id}` returns outlet detail.
- [ ] `PUT /api/v1/outlets/{id}` updates outlet details.
- [ ] `PATCH /api/v1/outlets/{id}/status` activates/deactivates an outlet.
- [ ] `GET /api/v1/tax-configs` and `POST/PUT /api/v1/tax-configs` manage GST tax configurations.
- [ ] TenantScope middleware resolves active outlet from `X-Outlet-Id` header and validates user access.
- [ ] Frontend Outlet Switcher allows switching active outlet; selection persists and is sent on all requests.
- [ ] Frontend Restaurant Profile form and Outlet management screens built.
- [ ] Frontend Tax Configuration screen to manage CGST/SGST/IGST rates.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Restaurant Profile | /settings/restaurant | Edit restaurant details and logo |
| Outlet List | /hq/outlets (or /settings/outlets) | Manage outlets |
| Outlet Form | /hq/outlets/new, /hq/outlets/:id/edit | Create/edit outlet |
| Tax Configuration | /settings/taxes | Manage GST tax configs |
| Outlet Switcher | (modal in topbar) | Switch active outlet |

### Screen Details

**Restaurant Profile (/settings/restaurant):** Form with sections: (1) Basic Info - Restaurant Name, Legal Name, Logo upload (with preview), Email, Phone. (2) Tax & Legal - GSTIN (15 char, validated), PAN (10 char), FSSAI Number (14 char). (3) Address - Address Line 1, Address Line 2, City, State (dropdown), Pincode, Country. (4) Localization - Currency Code (dropdown, default INR), Timezone (dropdown, default Asia/Kolkata). Buttons: "Save Changes". Validation on GSTIN/PAN/FSSAI format. Logo upload accepts png/jpg up to 2MB.

**Outlet List (/hq/outlets):** Table with columns: Name, Code, Type (badge), GSTIN, City, Status (Active/Inactive badge), Is Central Kitchen (badge), Actions (Edit, Activate/Deactivate). "Add Outlet" button. Search by name/code.

**Outlet Form (/hq/outlets/new):** Sections: (1) Basic - Name, Code (unique per restaurant, auto-suggested), Type (dropdown: Dine-in, QSR, Cloud Kitchen, Food Court), Is Central Kitchen checkbox. (2) Tax - GSTIN (validated). (3) Contact - Phone, Email. (4) Address - Address Line 1, City, State, Pincode. Buttons: "Create Outlet" / "Save Changes", "Cancel". Code field auto-generates from name (e.g., "Mumbai South" -> "MUM-S") with manual override.

**Tax Configuration (/settings/taxes):** Table of tax configs: Name, Type (Intra-state/Inter-state), CGST Rate, SGST Rate, IGST Rate, Cess Rate, Status, Actions (Edit, Delete). "Add Tax Config" button opens a form: Name (e.g., "GST 5%"), Type, CGST Rate (2.50), SGST Rate (2.50), IGST Rate (5.00), Cess Rate (0). Validation: rates are decimals 0-100. Common presets provided (5%, 12%, 18%, 28%).

**Outlet Switcher (modal):** Triggered from the topbar showing current outlet name. Modal lists the user's accessible outlets with radio selection. Selecting an outlet updates the active outlet in the outletStore and reloads outlet-scoped data (menu, tables, orders). Shows outlet type and status. Disabled if only one outlet.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/restaurant | Get restaurant profile |
| PUT | /api/v1/restaurant | Update restaurant profile |
| POST | /api/v1/restaurant/logo | Upload logo |
| GET | /api/v1/outlets | List outlets |
| POST | /api/v1/outlets | Create outlet |
| GET | /api/v1/outlets/{id} | Get outlet |
| PUT | /api/v1/outlets/{id} | Update outlet |
| PATCH | /api/v1/outlets/{id}/status | Toggle outlet status |
| GET | /api/v1/tax-configs | List tax configs |
| POST | /api/v1/tax-configs | Create tax config |
| PUT | /api/v1/tax-configs/{id} | Update tax config |
| DELETE | /api/v1/tax-configs/{id} | Delete tax config |
| GET | /api/v1/me/outlets | List current user's accessible outlets |

## Database Tables

Uses tables from RMS-002: `restaurants`, `outlets`, `tax_configs`, `user_outlets`. No schema changes; this ticket builds CRUD APIs and UI.

## Technical Notes

- **TenantScope middleware:** Resolves `X-Outlet-Id` header, validates the outlet belongs to the user's restaurant and that the user has access via `user_outlets`. Sets the active outlet in a request-scoped singleton (e.g., `app('active.outlet')`).
- **Global scopes:** Eloquent models with `outlet_id` auto-filter by the active outlet unless explicitly bypassed (HQ reports).
- **Logo upload:** Store in `storage/app/public/logos`; use Laravel's file storage; validate mime and size.
- **GSTIN validation:** Custom validation rule for 15-character GSTIN format with checksum.
- **Tax service:** Create a `TaxService` that resolves the applicable tax config (intra vs inter-state based on restaurant vs outlet state).
- **Frontend:** `outletStore` (Zustand) holds active outlet id and list. Axios interceptor attaches `X-Outlet-Id` header. Outlet switcher modal updates store and invalidates TanStack Query caches for outlet-scoped data.
- **Permissions:** `restaurant.manage`, `outlets.manage`, `tax.manage`.

## Subtasks

1. [ ] Create RestaurantController (show, update, uploadLogo)
2. [ ] Create OutletController (index, store, show, update, status)
3. [ ] Create TaxConfigController (CRUD)
4. [ ] Implement TenantScope middleware (resolve + validate active outlet)
5. [ ] Add GSTIN/PAN validation rules
6. [ ] Configure file storage for logo uploads
7. [ ] Create TaxService for intra/inter-state resolution
8. [ ] Frontend: Restaurant Profile form with logo upload
9. [ ] Frontend: Outlet List + Outlet Form
10. [ ] Frontend: Tax Configuration screen
11. [ ] Frontend: Outlet Switcher modal + outletStore + Axios header
12. [ ] Write tests for tenant scoping and tax config

## Testing Criteria

- [ ] Admin can update restaurant profile and logo persists.
- [ ] Creating an outlet assigns it to the current restaurant.
- [ ] A user cannot access outlets outside their assignment (403/404).
- [ ] TenantScope correctly filters data by active outlet.
- [ ] Switching outlet updates all outlet-scoped data in the UI.
- [ ] Tax configs can be created, edited, and deleted.
- [ ] GSTIN validation rejects malformed values.
- [ ] Inactive outlets are excluded from default outlet-scoped queries.
- [ ] Permissions enforced: only users with outlets.manage can create/edit outlets.
