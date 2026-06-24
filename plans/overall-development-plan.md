# Overall Development Plan

**Project:** Restaurant Management System (RMS) - Petpooja Clone
**Owner:** Rushabh Sorathiya
**Total Duration:** 24 weeks
**Total Tickets:** 56
**Tech Stack:** Laravel 11 + React Vite + Tailwind + MySQL + Redis

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Milestones Overview](#milestones-overview)
3. [Team Requirements](#team-requirements)
4. [Risk Assessment](#risk-assessment)
5. [Priority Matrix](#priority-matrix)
6. [Complete Ticket Register](#complete-ticket-register)

---

## Executive Summary

This plan delivers a production-ready Restaurant Management System that matches and extends the feature set of Petpooja. The system is built as a decoupled architecture: a Laravel 11 REST API backend and a React Vite SPA frontend, backed by MySQL and Redis. The delivery is organized into 6 sequential milestones, starting with a fully functional POS (Milestone 1) that can take orders and bill customers, then layering inventory, CRM, analytics, captain/KDS, and multi-outlet capabilities.

Each milestone produces a demonstrable, testable increment. Milestone 1 is the critical path: nothing else can be demoed until a POS can ring up a sale.

---

## Milestones Overview

### Milestone 1: Foundation & Core Billing (Weeks 1-6, 11 tickets)

**Objective:** Deliver a working POS that can take orders and bill customers.

**Scope:**
- Project setup (Laravel 11 + React Vite + Tailwind)
- Database schema and core migrations
- Authentication (Sanctum)
- User and role management
- Restaurant and outlet (multi-tenant) setup
- Menu management (categories, items, variations, add-ons, combos)
- Table and floor management
- Order management (dine-in, takeaway, delivery)
- Billing and invoice (GST, discounts, split bill)
- KOT (Kitchen Order Ticket) printing
- Payment processing (cash, UPI, card, wallet, multi-tender)

**Deliverables:**
- Working POS terminal that can create orders, print KOT, generate GST bills, and accept payments
- Admin dashboard for menu, tables, users, and outlet configuration
- Authentication with role-based access

**Exit Criteria:**
- A user can log in, build an order, send KOT to kitchen, generate a GST bill, accept payment, and settle the bill
- All M1 acceptance criteria pass

---

### Milestone 2: Inventory & Supply Chain (Weeks 7-10, 10 tickets)

**Objective:** Deliver full inventory control linked to menu recipes and order flow.

**Scope:**
- Raw material and stock management
- Recipe management with auto stock deduction
- Purchase order management with GRN
- Supplier management
- Central kitchen module
- Stock transfer between outlets
- Low stock alerts and auto-reorder
- Wastage tracking
- E-way bill generation
- Inventory valuation reports

**Deliverables:**
- Full inventory ledger that auto-deducts stock when orders are placed
- PO-to-GRN procurement workflow
- Central kitchen indent and transfer tracking
- Stock valuation and wastage reporting

**Exit Criteria:**
- Stock correctly deducts based on recipe on every order/KOT
- PO can be raised, approved, received (partial supported), and stock updated
- Low stock triggers alerts and a draft PO

---

### Milestone 3: CRM, Loyalty & Online Orders (Weeks 11-14, 10 tickets)

**Objective:** Deliver customer management, loyalty, marketing, and online order aggregation.

**Scope:**
- Customer master and segmentation
- Loyalty points engine
- Reward wallets
- Campaigns and promotions
- Feedback and ratings
- Aggregator integrations (Zomato, Swiggy, Petpooja Online)
- Online order dashboard and auto-accept
- Aggregator menu mapping
- Online order reconciliation
- Birthday / anniversary automation

**Deliverables:**
- Full CRM with loyalty accrual and redemption at billing
- Online orders flow into the same POS pipeline as in-store orders
- Campaign engine with targeting rules

---

### Milestone 4: Analytics, Reports & Dashboards (Weeks 15-17, 8 tickets)

**Objective:** Deliver actionable analytics and GST/financial reporting.

**Scope:**
- Sales analytics (day/week/month/custom, hourly, item/category-wise)
- Profit & loss with COGS
- GST return data export
- Inventory valuation snapshot
- Custom report builder
- Scheduled / emailed reports
- Audit log viewer
- HQ consolidated dashboard (single-outlet preview)

**Deliverables:**
- Interactive analytics dashboards with charts
- Exportable GST and financial reports
- Configurable scheduled report delivery

---

### Milestone 5: Captain App, KDS & Reservations (Weeks 18-21, 9 tickets)

**Objective:** Deliver tableside ordering, kitchen display, and reservation management.

**Scope:**
- Captain ordering app (tableside)
- Kitchen Display System (KDS) with order routing and bumping
- Category-wise printer routing
- Table reservations and calendar
- Waitlist management
- Guest preferences
- Tableside payment initiation
- Reservation slot management
- KDS analytics (prep time, load)

**Deliverables:**
- Captain app for servers to take orders at the table
- KDS displaying live orders with routing by station
- Reservation and waitlist system

---

### Milestone 6: Multi-Outlet HQ, Settings & Polish (Weeks 22-24, 8 tickets)

**Objective:** Deliver multi-outlet HQ control, configuration, and production hardening.

**Scope:**
- Multi-outlet HQ dashboard and consolidated reports
- Centralized menu and pricing push to outlets
- Outlet-level configuration
- Multi-language and translation management
- Branding and receipt customization
- Printer and hardware configuration
- Backup, data export, and audit
- Security hardening and deployment automation

**Deliverables:**
- HQ can manage multiple outlets from one console
- Full branding and localization
- Hardened, deployable production system

---

## Team Requirements

| Role | Count | Milestones Active | Responsibilities |
|------|-------|-------------------|------------------|
| Tech Lead / Architect | 1 | All | Architecture, code review, critical path |
| Backend Developer (Laravel) | 2 | All | API, migrations, services, jobs |
| Frontend Developer (React) | 2 | All | SPA pages, components, state |
| Full-stack Developer | 1 | All | Cross-cutting features, integration |
| QA Engineer | 1 | M1-M6 | Test plans, regression, UAT support |
| UI/UX Designer | 1 | M1, M5, M6 | Screen design, POS UX, branding |
| DevOps Engineer (part-time) | 0.5 | M1, M6 | CI/CD, deployment, monitoring |

**Peak team size:** ~8.5 FTE during Milestone 1 and Milestone 5.

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| GST billing rules change mid-project | Medium | High | Build tax engine as configurable; isolate GST logic in service layer |
| Real-time KDS / WebSocket scaling issues | Medium | High | Use Soketi / Laravel Reverb; load-test early in M1 |
| Aggregator API rate limits / approval delays | High | Medium | Mock aggregators in M3; integrate live API only after approval |
| Recipe-to-stock deduction accuracy | Medium | High | Add reconciliation job and alerting in M2; nightly stock audit |
| Multi-tenant data isolation bugs | Medium | Critical | Enforce tenant scoping via global scopes and middleware from M1 |
| Hardware / thermal printer compatibility | Medium | Medium | Test with target printers in M1; abstract print layer |
| Scope creep on analytics dashboards | High | Medium | Fix M4 scope to defined report set; custom builder is the extension valve |
| Team availability gaps | Medium | High | Cross-train; document services thoroughly; keep tickets self-contained |

---

## Priority Matrix

| Priority | Definition | Tickets |
|----------|-----------|---------|
| **P0 - Critical** | Blocks POS go-live or data integrity | RMS-001, RMS-002, RMS-003, RMS-004, RMS-006, RMS-008, RMS-009, RMS-011 |
| **P1 - High** | Core operations feature needed at go-live | RMS-005, RMS-007, RMS-010, RMS-012, RMS-013, RMS-014, RMS-015 |
| **P2 - Medium** | Important for full feature parity | RMS-016 through RMS-053 (see register) |
| **P3 - Low** | Enhancement / nice-to-have | RMS-054, RMS-055, RMS-056 |

---

## Complete Ticket Register

All 56 tickets with ID, title, milestone, priority, and story points.

### Milestone 1: Foundation & Core Billing (11 tickets, 86 SP)

| ID | Title | Milestone | Priority | SP |
|----|-------|-----------|----------|----|
| RMS-001 | Project Setup & Boilerplate | M1 | P0 | 5 |
| RMS-002 | Database Schema Design & Core Migrations | M1 | P0 | 8 |
| RMS-003 | Authentication System | M1 | P0 | 8 |
| RMS-004 | User & Role Management | M1 | P0 | 8 |
| RMS-005 | Restaurant & Outlet Setup | M1 | P1 | 6 |
| RMS-006 | Menu Management | M1 | P0 | 13 |
| RMS-007 | Table & Floor Management | M1 | P1 | 8 |
| RMS-008 | Order Management | M1 | P0 | 13 |
| RMS-009 | Billing & Invoice System | M1 | P0 | 13 |
| RMS-010 | KOT Printing System | M1 | P1 | 8 |
| RMS-011 | Payment Processing | M1 | P0 | 8 |

### Milestone 2: Inventory & Supply Chain (10 tickets, 89 SP)

| ID | Title | Milestone | Priority | SP |
|----|-------|-----------|----------|----|
| RMS-012 | Raw Material & Stock Management | M2 | P1 | 13 |
| RMS-013 | Recipe Management | M2 | P1 | 13 |
| RMS-014 | Purchase Order Management | M2 | P1 | 13 |
| RMS-015 | Supplier Management | M2 | P1 | 8 |
| RMS-016 | Central Kitchen Module | M2 | P2 | 13 |
| RMS-017 | Stock Transfer Between Outlets | M2 | P2 | 8 |
| RMS-018 | Low Stock Alerts & Auto-Reorder | M2 | P2 | 8 |
| RMS-019 | Wastage Tracking | M2 | P2 | 8 |
| RMS-020 | E-Way Bill Generation | M2 | P2 | 8 |
| RMS-021 | Inventory Valuation Reports | M2 | P2 | 8 |

### Milestone 3: CRM, Loyalty & Online Orders (10 tickets, 83 SP)

| ID | Title | Milestone | Priority | SP |
|----|-------|-----------|----------|----|
| RMS-022 | Customer Master & Segmentation | M3 | P2 | 8 |
| RMS-023 | Loyalty Points Engine | M3 | P2 | 8 |
| RMS-024 | Reward Wallets | M3 | P2 | 8 |
| RMS-025 | Campaigns & Promotions | M3 | P2 | 8 |
| RMS-026 | Feedback & Ratings | M3 | P2 | 5 |
| RMS-027 | Aggregator Integrations | M3 | P2 | 13 |
| RMS-028 | Online Order Dashboard & Auto-Accept | M3 | P2 | 8 |
| RMS-029 | Aggregator Menu Mapping | M3 | P2 | 8 |
| RMS-030 | Online Order Reconciliation | M3 | P2 | 8 |
| RMS-031 | Birthday / Anniversary Automation | M3 | P3 | 5 |

### Milestone 4: Analytics, Reports & Dashboards (8 tickets, 69 SP)

| ID | Title | Milestone | Priority | SP |
|----|-------|-----------|----------|----|
| RMS-032 | Sales Analytics Dashboard | M4 | P2 | 8 |
| RMS-033 | Profit & Loss with COGS | M4 | P2 | 13 |
| RMS-034 | GST Return Data Export | M4 | P2 | 8 |
| RMS-035 | Inventory Valuation Snapshot | M4 | P2 | 8 |
| RMS-036 | Custom Report Builder | M4 | P2 | 13 |
| RMS-037 | Scheduled / Emailed Reports | M4 | P3 | 5 |
| RMS-038 | Audit Log Viewer | M4 | P3 | 5 |
| RMS-039 | HQ Consolidated Dashboard (preview) | M4 | P2 | 8 |

### Milestone 5: Captain App, KDS & Reservations (9 tickets, 78 SP)

| ID | Title | Milestone | Priority | SP |
|----|-------|-----------|----------|----|
| RMS-040 | Captain Ordering App | M5 | P2 | 13 |
| RMS-041 | Kitchen Display System (KDS) | M5 | P2 | 13 |
| RMS-042 | Category-wise Printer Routing | M5 | P2 | 8 |
| RMS-043 | Table Reservations & Calendar | M5 | P2 | 8 |
| RMS-044 | Waitlist Management | M5 | P3 | 5 |
| RMS-045 | Guest Preferences & Special Requests | M5 | P3 | 5 |
| RMS-046 | Tableside Payment Initiation | M5 | P2 | 8 |
| RMS-047 | Reservation Slot Management | M5 | P2 | 8 |
| RMS-048 | KDS Analytics (prep time, load) | M5 | P3 | 5 |

### Milestone 6: Multi-Outlet HQ, Settings & Polish (8 tickets, 66 SP)

| ID | Title | Milestone | Priority | SP |
|----|-------|-----------|----------|----|
| RMS-049 | Multi-Outlet HQ Dashboard & Reports | M6 | P2 | 13 |
| RMS-050 | Centralized Menu & Pricing Push | M6 | P2 | 8 |
| RMS-051 | Outlet-Level Configuration | M6 | P2 | 8 |
| RMS-052 | Multi-Language & Translation Management | M6 | P2 | 8 |
| RMS-053 | Branding & Receipt Customization | M6 | P2 | 8 |
| RMS-054 | Printer & Hardware Configuration | M6 | P3 | 5 |
| RMS-055 | Backup, Data Export & Audit | M6 | P3 | 8 |
| RMS-056 | Security Hardening & Deployment Automation | M6 | P2 | 8 |

---

### Summary Totals

| Milestone | Tickets | Story Points | Weeks |
|-----------|---------|--------------|-------|
| M1 | 11 | 86 | 6 |
| M2 | 10 | 89 | 4 |
| M3 | 10 | 83 | 4 |
| M4 | 8 | 69 | 3 |
| M5 | 9 | 78 | 4 |
| M6 | 8 | 66 | 3 |
| **Total** | **56** | **471** | **24** |
