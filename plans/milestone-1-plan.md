# Milestone 1 Plan: Foundation & Core Billing

**Milestone:** M1
**Duration:** 6 weeks (Weeks 1-6)
**Tickets:** 11 (RMS-001 to RMS-011)
**Total Story Points:** 86
**Objective:** Deliver a working POS that can take orders and bill customers.

---

## Table of Contents

1. [Objective & Success Criteria](#objective--success-criteria)
2. [Scope](#scope)
3. [Deliverables](#deliverables)
4. [Timeline & Week-by-Week Breakdown](#timeline--week-by-week-breakdown)
5. [Dependency Graph](#dependency-graph)
6. [Critical Path](#critical-path)
7. [Ticket List](#ticket-list)
8. [Team Allocation](#team-allocation)
9. [Risks & Mitigations](#risks--mitigations)
10. [Definition of Done](#definition-of-done)

---

## Objective & Success Criteria

**Primary Objective:** By the end of Milestone 1, a restaurant staff member can log into the system, set up a menu and floor, take a dine-in or takeaway order, send a KOT to the kitchen, generate a GST-compliant bill, accept payment via one or more tenders, and settle the bill. This is the minimum viable POS.

**Success Criteria:**
- An admin can create a restaurant, outlets, users, and assign roles.
- Menu items with variations and add-ons can be created and shown on the POS grid.
- Tables and areas can be configured with QR codes.
- An order can be created from the POS, modified, held, transferred, and cancelled.
- A GST bill (CGST/SGST/IGST) can be generated with discounts and split billing.
- A KOT prints/routs to the kitchen on order submit.
- Payment can be accepted in cash, UPI, card, or multi-tender split.

---

## Scope

**In Scope:**
- Project scaffolding (Laravel 11 + React Vite + Tailwind).
- Core database schema and migrations (users, restaurants, outlets, menu, orders, billing).
- Sanctum authentication (login, register, forgot/reset password, token management).
- User and role management with permission system.
- Restaurant and outlet (multi-tenant) setup, branding, tax config.
- Menu management: categories, items, variations, add-ons, combos, shortcodes, GST mapping.
- Table and floor management: areas, tables, capacity, QR code generation, floor plan view.
- Order management: create, modify, hold, cancel, transfer; dine-in/takeaway/delivery types; status workflow.
- Billing and invoice: GST billing, discounts (flat/percentage/combo), split bill, complimentary, hold/settle, refund/return, print bill.
- KOT printing: auto-generate on submit, category routing, format, reprint, void with reason.
- Payment processing: cash, UPI, card, wallet, multi-tender split, gateway integration, internal wallet, change calculation.

**Out of Scope (deferred to later milestones):**
- Inventory, recipes, stock deduction (M2).
- CRM, loyalty, online orders (M3).
- Advanced analytics (M4).
- Captain app, KDS screen, reservations (M5).
- Multi-outlet HQ consolidation (M6).

---

## Deliverables

1. Deployable Laravel 11 API with Sanctum auth.
2. Deployable React Vite SPA with POS terminal.
3. Admin dashboards for menu, tables, users, outlets.
4. Migration and seeder set.
5. API documentation (OpenAPI/Swagger or route listing).
6. Automated tests for auth, order, billing, and payment flows.
7. Deployment guide for a single Ubuntu server.

---

## Timeline & Week-by-Week Breakdown

| Week | Focus | Tickets |
|------|-------|---------|
| Week 1 | Project setup, core schema, auth | RMS-001, RMS-002, RMS-003 |
| Week 2 | Users/roles, restaurant/outlet setup | RMS-004, RMS-005 |
| Week 3 | Menu management | RMS-006 |
| Week 4 | Tables, orders | RMS-007, RMS-008 |
| Week 5 | Billing, KOT | RMS-009, RMS-010 |
| Week 6 | Payments, integration, hardening, UAT | RMS-011 + integration testing |

---

## Dependency Graph

```
RMS-001 (Setup)
   |
   v
RMS-002 (Schema) --------+----------+----------+----------+----------+
   |                      |          |          |          |          |
   v                      v          v          v          v          v
RMS-003 (Auth) ----> RMS-004 (Users) RMS-006   RMS-008   RMS-009   RMS-011
                          |           (Menu)  (Orders)  (Billing) (Payment)
                          v            |         |          ^
                       RMS-005 <-------+         |          |
                     (Outlet)                    |          |
                          v                      |          |
                       RMS-007 <-----------------+          |
                      (Tables)                              |
                          |                                 |
                          +-------> RMS-008 ----------------+
                                    (Orders)                |
                                       |                    |
                                       v                    |
                                    RMS-009 ----------------+
                                   (Billing)                |
                                       |                    |
                                       v                    |
                                    RMS-010                 |
                                    (KOT)                   |
                                       |                    |
                                       v                    |
                                    RMS-011 <---------------+
                                   (Payment)
```

### Dependency Notes

- **RMS-001** (setup) blocks everything.
- **RMS-002** (schema) blocks all feature tickets.
- **RMS-003** (auth) is required before any protected endpoint or UI.
- **RMS-004** (users/roles) depends on auth and is needed for all role-gated screens.
- **RMS-005** (restaurant/outlet) depends on users and is needed for tenant context.
- **RMS-006** (menu) and **RMS-007** (tables) can be developed in parallel after RMS-005.
- **RMS-008** (orders) depends on menu, tables, and outlet.
- **RMS-009** (billing) depends on orders.
- **RMS-010** (KOT) depends on orders.
- **RMS-011** (payment) depends on billing.

---

## Critical Path

The critical path is the longest chain of dependent tickets:

```
RMS-001 -> RMS-002 -> RMS-003 -> RMS-005 -> RMS-006 -> RMS-008 -> RMS-009 -> RMS-011
```

**Critical path length:** 8 tickets. Any slippage on these directly delays the milestone.

**Parallel work (off critical path):**
- RMS-004 (users/roles) runs alongside RMS-005.
- RMS-007 (tables) runs alongside RMS-006.
- RMS-010 (KOT) runs alongside RMS-009.

---

## Ticket List

| ID | Title | Priority | SP | Dependencies |
|----|-------|----------|----|--------------|
| RMS-001 | Project Setup & Boilerplate | P0 | 5 | - |
| RMS-002 | Database Schema Design & Core Migrations | P0 | 8 | RMS-001 |
| RMS-003 | Authentication System | P0 | 8 | RMS-001, RMS-002 |
| RMS-004 | User & Role Management | P0 | 8 | RMS-003 |
| RMS-005 | Restaurant & Outlet Setup | P1 | 6 | RMS-004 |
| RMS-006 | Menu Management | P0 | 13 | RMS-005 |
| RMS-007 | Table & Floor Management | P1 | 8 | RMS-005 |
| RMS-008 | Order Management | P0 | 13 | RMS-006, RMS-007 |
| RMS-009 | Billing & Invoice System | P0 | 13 | RMS-008 |
| RMS-010 | KOT Printing System | P1 | 8 | RMS-008 |
| RMS-011 | Payment Processing | P0 | 8 | RMS-009 |

---

## Team Allocation

| Role | Allocation | Focus |
|------|-----------|-------|
| Tech Lead | 1.0 | Architecture, RMS-001, RMS-002, code review |
| Backend Dev 1 | 1.0 | RMS-003, RMS-004, RMS-009, RMS-011 |
| Backend Dev 2 | 1.0 | RMS-006 (API), RMS-008 (API), RMS-010 (API) |
| Frontend Dev 1 | 1.0 | RMS-006 (UI), RMS-008 (UI/POS) |
| Frontend Dev 2 | 1.0 | RMS-003 (UI), RMS-004 (UI), RMS-005 (UI), RMS-009 (UI), RMS-011 (UI) |
| Full-stack Dev | 1.0 | RMS-007, RMS-010 (UI) |
| QA Engineer | 1.0 | Test plans, regression from Week 3 |
| UI/UX Designer | 1.0 | POS wireframes, screen design Weeks 1-3 |

---

## Risks & Mitigations

| Risk | Mitigation |
|------|-----------|
| GST/tax engine complexity | Build configurable tax service early (RMS-002 schema + RMS-009 service); isolate rules |
| Thermal printer / KOT format compatibility | Test with target hardware in Week 5; abstract print layer behind interface |
| POS UI performance with large menus | Implement virtualization, category tabs, debounced search in RMS-008 |
| Concurrent bill settle race conditions | Use Redis distributed locks in BillingService |
| Scope creep | Strictly defer inventory/CRM to M2/M3; log as future tickets |

---

## Definition of Done (per ticket)

- All acceptance criteria met and verified.
- Unit tests pass (backend PHPUnit, frontend Vitest).
- Code reviewed and merged to main.
- API endpoints documented.
- UI matches design and is responsive.
- No critical or high bugs open.
- Migration (if any) runs cleanly up and down.
