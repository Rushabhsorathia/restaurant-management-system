# Milestone 2 Plan: Inventory & Supply Chain

**Milestone:** M2
**Duration:** 4 weeks (Weeks 7-10)
**Tickets:** 10 (RMS-012 to RMS-021)
**Total Story Points:** 89
**Objective:** Deliver full inventory control linked to menu recipes and order flow.

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

**Primary Objective:** By the end of Milestone 2, the restaurant has a complete inventory ledger that automatically deducts stock when orders are placed, supports a procurement workflow from PO through GRN, manages central kitchen indents and inter-outlet transfers, tracks wastage, generates e-way bills for inter-state movement, and reports stock valuation using FIFO/LIFO/average methods.

**Success Criteria:**
- Raw materials can be created with units, min/max/reorder levels, opening stock, and valuation method.
- Recipes link menu items to raw material quantities; stock auto-deducts on order/KOT.
- Purchase orders can be raised, approved, received (partial supported), and stock updated with batch/expiry tracking.
- Suppliers are managed with GST, payment terms, outstanding tracking, and order history.
- Central kitchen indents flow from outlet request through dispatch and receipt.
- Stock transfers between outlets track in-transit and adjust both ends on receipt.
- Low stock triggers dashboard, email, and SMS alerts, and a draft PO can be auto-generated.
- Wastage entries are logged with reasons, approved, and reported with cost impact.
- E-way bills can be generated for inter-state transfers via GST API.
- Inventory valuation reports show FIFO/LIFO/average value, aging, and dead stock.

---

## Scope

**In Scope:**
- Raw material and stock management (material master, units, opening stock, valuation, adjustment, physical count).
- Recipe management (semi-finished and finished recipes, ingredients, auto stock deduction on order/KOT, costing, yield).
- Purchase order management (create, approve, GRN, partial receipt, rate comparison).
- Supplier management (master, GST, terms, rating, outstanding, history).
- Central kitchen module (setup, indent, supply note, transfer, returns, production planning, dashboard).
- Stock transfer between outlets (request, approve, in-transit, receipt, adjustment, reports).
- Low stock alerts and auto-reorder (min/max, reorder point, email/SMS/dashboard, draft PO, trends).
- Wastage tracking (entry, reason, batch/expiry, approval, reports, cost).
- E-way bill generation (GST API, inter-state auto, transport details, print, validity, extension).
- Inventory valuation reports (FIFO/LIFO/average, value, slow-moving, dead stock, aging, P&L impact).

**Out of Scope:**
- CRM, loyalty, online orders (M3).
- Advanced analytics dashboards beyond inventory (M4).
- Multi-outlet HQ consolidated views (M6).

**Prerequisites (from M1):**
- Menu items and order flow must exist (RMS-006, RMS-008) so recipes can link and stock can deduct.
- Outlet/tenant model must exist (RMS-005).

---

## Deliverables

1. Inventory module backend services and API.
2. Inventory module frontend pages (materials, recipes, POs, suppliers, transfers, wastage, reports).
3. Recipe-to-stock deduction integration with the order service.
4. PO-to-GRN procurement workflow with approval.
5. Central kitchen and inter-outlet transfer workflows.
6. Alerting jobs (low stock, reorder).
7. E-way bill integration (GST API).
8. Inventory valuation and wastage reporting.
9. Automated tests for stock deduction, PO receipt, and valuation.
10. Reconciliation job that audits stock vs movements nightly.

---

## Timeline & Week-by-Week Breakdown

| Week | Focus | Tickets |
|------|-------|---------|
| Week 7 | Raw materials, recipes, suppliers | RMS-012, RMS-013, RMS-015 |
| Week 8 | Purchase orders, GRN | RMS-014 |
| Week 9 | Central kitchen, transfers | RMS-016, RMS-017 |
| Week 10 | Alerts, wastage, e-way bill, valuation, integration, UAT | RMS-018, RMS-019, RMS-020, RMS-021 |

---

## Dependency Graph

```
RMS-012 (Raw Materials) ------+----------------+----------+----------+----------+
   |                          |                |          |          |          |
   v                          v                v          v          v          v
RMS-013 (Recipes)        RMS-015 (Suppliers) RMS-018   RMS-019   RMS-020     RMS-021
   |                          |               (Alerts) (Wastage) (E-Way)   (Valuation)
   v                          v
RMS-014 (Purchase Orders) <---+
   |
   +---> GRN -> stock update ----+
                                |
   v                            v
RMS-016 (Central Kitchen)   RMS-017 (Stock Transfer)
   |                            |
   +-------- transfer ----------+
```

### Dependency Notes

- **RMS-012** (raw materials) is the foundation for all inventory tickets.
- **RMS-013** (recipes) depends on raw materials and links to menu items (from M1).
- **RMS-014** (POs) depends on raw materials and suppliers (RMS-015).
- **RMS-015** (suppliers) depends only on the tenant model from M1; can run early.
- **RMS-016** (central kitchen) depends on raw materials and stock movements.
- **RMS-017** (transfers) depends on raw materials and stock movements.
- **RMS-018** (alerts/reorder) depends on raw materials and PO (to auto-generate drafts).
- **RMS-019** (wastage) depends on raw materials.
- **RMS-020** (e-way bill) depends on transfers (and POs for inter-state).
- **RMS-021** (valuation) depends on raw materials and stock movements/batches.

---

## Critical Path

```
RMS-012 -> RMS-013 -> RMS-014 -> RMS-016 -> RMS-017 -> RMS-020
```

**Critical path length:** 6 tickets. The recipe-to-stock deduction (RMS-013) is the highest-risk item because it touches the order service from M1.

**Parallel work (off critical path):**
- RMS-015 (suppliers) runs in Week 7 alongside RMS-012.
- RMS-018 (alerts), RMS-019 (wastage), RMS-021 (valuation) run in Week 10 in parallel.

---

## Ticket List

| ID | Title | Priority | SP | Dependencies |
|----|-------|----------|----|--------------|
| RMS-012 | Raw Material & Stock Management | P1 | 13 | M1 (menu, orders) |
| RMS-013 | Recipe Management | P1 | 13 | RMS-012 |
| RMS-014 | Purchase Order Management | P1 | 13 | RMS-012, RMS-015 |
| RMS-015 | Supplier Management | P1 | 8 | M1 (outlet) |
| RMS-016 | Central Kitchen Module | P2 | 13 | RMS-012 |
| RMS-017 | Stock Transfer Between Outlets | P2 | 8 | RMS-012 |
| RMS-018 | Low Stock Alerts & Auto-Reorder | P2 | 8 | RMS-012, RMS-014 |
| RMS-019 | Wastage Tracking | P2 | 8 | RMS-012 |
| RMS-020 | E-Way Bill Generation | P2 | 8 | RMS-017 |
| RMS-021 | Inventory Valuation Reports | P2 | 8 | RMS-012 |

---

## Team Allocation

| Role | Allocation | Focus |
|------|-----------|-------|
| Tech Lead | 1.0 | Architecture, RMS-013 (recipe-to-stock integration), review |
| Backend Dev 1 | 1.0 | RMS-012, RMS-014, RMS-018 |
| Backend Dev 2 | 1.0 | RMS-016, RMS-017, RMS-020 |
| Frontend Dev 1 | 1.0 | RMS-012 (UI), RMS-013 (UI), RMS-014 (UI) |
| Frontend Dev 2 | 1.0 | RMS-015 (UI), RMS-016 (UI), RMS-017 (UI), RMS-019 (UI) |
| Full-stack Dev | 1.0 | RMS-021, RMS-019 (API), RMS-020 (UI) |
| QA Engineer | 1.0 | Test plans from Week 8, stock reconciliation tests |

---

## Risks & Mitigations

| Risk | Mitigation |
|------|-----------|
| Recipe-to-stock deduction accuracy | Nightly reconciliation job; alerting on discrepancies; unit tests covering decimal quantities |
| GST e-way bill API approval / sandbox access | Build with mocked API first; integrate live only after access granted; abstract behind interface |
| Multi-unit conversion errors | Centralized unit conversion service with tests; never convert inline |
| FIFO batch selection bugs | Pure function with property tests; review by Tech Lead |
| Concurrent stock updates (POS + transfer) | Redis distributed locks around stock decrement; optimistic locking on raw_materials |
| Performance of valuation reports on large catalogs | Pre-aggregate nightly snapshot table; paginate; cache |

---

## Definition of Done (per ticket)

- All acceptance criteria met and verified.
- Unit tests pass (backend PHPUnit, frontend Vitest).
- Stock movements are always written for any stock change (audit trail).
- Code reviewed and merged to main.
- API endpoints documented.
- UI matches design and is responsive.
- No critical or high bugs open.
- Migration (if any) runs cleanly up and down.
- For recipe-linked changes: stock deduction verified against a sample order.
