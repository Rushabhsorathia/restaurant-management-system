# Restaurant Management System (RMS)

A comprehensive, enterprise-grade Restaurant Management System built as a full-featured Petpooja clone. The system covers the entire restaurant operations lifecycle from Point of Sale (POS) and billing to inventory, supply chain, CRM, online order aggregation, analytics, and multi-outlet management.

**Owner:** Rushabh Sorathiya
**Project Type:** Full-stack web application (API + SPA)
**Target Users:** Restaurant chains, QSRs, cafes, fine-dine, cloud kitchens, food courts.

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [Key Features](#key-features)
3. [Milestones Overview](#milestones-overview)
4. [Development Timeline](#development-timeline)
5. [Folder Structure](#folder-structure)
6. [Documentation Index](#documentation-index)

---

## Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Backend API | Laravel 11 (PHP 8.3) | REST API, business logic, queue jobs, auth |
| Frontend SPA | React 18 + Vite | Single-page application, dashboard, POS UI |
| Styling | Tailwind CSS 3 | Utility-first styling, responsive design |
| Database | MySQL 8.0 | Primary relational datastore |
| Cache / Queue | Redis 7 | Cache, session, queue, real-time pub/sub |
| Real-time | Laravel WebSockets / Soketi | KDS order push, live order updates |
| Auth | Laravel Sanctum | Token-based API authentication |
| State Management | Zustand | Lightweight frontend state stores |
| Data Fetching | TanStack Query (React Query) | Server state caching & mutations |
| Forms | React Hook Form + Zod | Form handling & validation |
| Charts | Recharts | Analytics & reporting dashboards |
| Print / KOT | Raw ESC/POS + browser print | Thermal printer integration |
| Search | Laravel Scout + Meilisearch (optional) | Menu & order search |
| File Storage | local / S3 | Item images, logos, documents |
| Deployment | Ubuntu 22.04 + Nginx + PHP-FPM + PM2 + systemd | Production hosting |

---

## Key Features

The system delivers **55+ functional modules** organized across the following domains:

### POS & Billing
- Touchscreen POS interface with quick-add menu grid
- Dine-in, takeaway, and delivery order types
- GST-compliant billing (CGST / SGST / IGST)
- Split bill, merge bill, hold bill, settle bill
- Complimentary items, item-level and bill-level discounts (flat / percentage / combo)
- Multi-tender payment (cash, UPI, card, wallet, Petpooja Pay)
- Change calculation, payment status tracking, refunds / returns

### Menu Management
- Categories, sub-categories, and display order
- Menu items with variations, add-ons, and item combos
- Item images, shortcodes, HSN/SAC codes
- GST / tax mapping per item
- Happy hours, time-based pricing, happy-hour menus
- Menu availability toggles and out-of-stock flags

### Inventory & Supply Chain
- Raw material master with units and conversion
- Recipe management (semi-finished & finished) with auto stock deduction
- Purchase orders with approval workflow and GRN
- Supplier master with GST, payment terms, rating, outstanding tracking
- Central kitchen module with indent, supply note, production planning
- Inter-outlet stock transfer with in-transit tracking
- Low stock alerts and auto-reorder (email + SMS + dashboard)
- Wastage tracking with approval and cost calculation
- E-way bill generation (GST API)
- FIFO / LIFO / Average cost valuation, stock aging, dead stock reports

### CRM & Loyalty
- Customer master with demographics and order history
- Loyalty points and reward wallets
- Campaigns, offers, and promotions
- Feedback collection and rating
- Birthday / anniversary reminders
- Customer segmentation

### Online Orders
- Aggregator integrations (Zomato, Swiggy, Petpooja Online)
- Online order dashboard with auto-accept
- Aggregator menu mapping
- Online order reconciliation and settlement

### Analytics & Reports
- Sales reports (day, week, month, custom)
- Item-wise, category-wise, hourly sales analytics
- Profit & loss, COGS, inventory valuation
- Tax reports (GST return data export)
- Custom report builder and scheduled reports
- Audit logs and user activity tracking

### Captain App & KDS
- Captain ordering app (tableside ordering)
- Kitchen Display System (KDS) with order routing
- Category-wise printer routing
- Order bumping, recall, and timer

### Reservations
- Table reservations and waitlist management
- Reservation calendar and slot management
- Guest preferences and special requests

### Multi-Outlet / HQ
- Multi-tenant / multi-outlet architecture
- Centralized menu and pricing push
- Outlet-level configuration and reporting
- HQ consolidated dashboard
- Inter-outlet transfers and central kitchen
- Role-based access per outlet

### Settings & Configuration
- Multi-language support with translation management
- App settings, branding, receipt customization
- Printer and hardware configuration
- Tax and GST configuration
- User and role permissions
- Backup and data export

---

## Milestones Overview

The project is divided into **6 milestones** delivered over **24 weeks**.

| Milestone | Title | Tickets | Weeks | Focus |
|-----------|-------|---------|-------|-------|
| **M1** | Foundation & Core Billing | 11 (RMS-001 to RMS-011) | 6 | Project setup, auth, menu, orders, billing, KOT, payments |
| **M2** | Inventory & Supply Chain | 10 (RMS-012 to RMS-021) | 4 | Raw materials, recipes, POs, suppliers, central kitchen, wastage, valuation |
| **M3** | CRM, Loyalty & Online Orders | 15 (RMS-022 to RMS-036) | 4 | Customers, loyalty, campaigns, aggregator integration, coupons, segmentation, feedback |
| **M4** | Captain App, KDS & Reservations | 4 (RMS-037 to RMS-040) | 5 | Captain tableside ordering, kitchen display, reservations, self-service kiosk |
| **M5** | Analytics & Reporting | 8 (RMS-041 to RMS-048) | 3 | Sales analytics, reconciliation, multi-outlet HQ, GST reports, profit margin, custom reports |
| **M6** | Platform & Integrations | 8 (RMS-049 to RMS-056) | 3 | Multi-language, RBAC, aggregator menu sync, Tally, supplier marketplace, offline PWA, hardware |

**Total:** 56 tickets across 24 weeks.

---

## Development Timeline

```
Week  1-6   | M1: Foundation & Core Billing
Week  7-10  | M2: Inventory & Supply Chain
Week 11-14  | M3: CRM, Loyalty & Online Orders
Week 15-17  | M4: Analytics, Reports & Dashboards
Week 18-21  | M5: Captain App, KDS & Reservations
Week 22-24  | M6: Multi-Outlet HQ, Settings & Polish
```

```
M1 [============]
M2         [========]
M3                 [========]
M4                          [======]
M5                                 [========]
M6                                          [======]
```

---

## Folder Structure

```
restaurant_management_system/
|
|-- INDEX.md                                     # Master ticket index
|-- README.md                                    # This file
|
|-- plans/                                       # Planning & design documents
|   |-- overall-development-plan.md
|   |-- architecture.md
|   |-- database-schema.md
|   |-- ui-screens.md
|   |-- milestone-1-plan.md
|   |-- milestone-2-plan.md
|   |-- milestone-3-plan.md
|   |-- milestone-4-plan.md
|   |-- milestone-5-plan.md
|   |-- milestone-6-plan.md
|
|-- milestone-1-foundation-and-core-billing/     # M1 Jira tickets (RMS-001 to RMS-011)
|   |-- RMS-001-project-setup-and-boilerplate.md
|   |-- RMS-002-database-schema-design-and-core-migrations.md
|   |-- RMS-003-authentication-system.md
|   |-- RMS-004-user-and-role-management.md
|   |-- RMS-005-restaurant-and-outlet-setup.md
|   |-- RMS-006-menu-management.md
|   |-- RMS-007-table-and-floor-management.md
|   |-- RMS-008-order-management.md
|   |-- RMS-009-billing-and-invoice-system.md
|   |-- RMS-010-kot-printing-system.md
|   |-- RMS-011-payment-processing.md
|
|-- milestone-2-inventory-and-supply-chain/      # M2 Jira tickets (RMS-012 to RMS-021)
|   |-- RMS-012-raw-material-and-stock-management.md
|   |-- RMS-013-recipe-management.md
|   |-- RMS-014-purchase-order-management.md
|   |-- RMS-015-supplier-management.md
|   |-- RMS-016-central-kitchen-module.md
|   |-- RMS-017-stock-transfer-between-outlets.md
|   |-- RMS-018-low-stock-alerts-and-auto-reorder.md
|   |-- RMS-019-wastage-tracking.md
|   |-- RMS-020-eway-bill-generation.md
|   |-- RMS-021-inventory-valuation-reports.md
|
|-- milestone-3-online-orders-and-crm/           # M3 Jira tickets (RMS-022 to RMS-036)
|   |-- RMS-022-aggregator-integration-swiggy-zomato.md
|   |-- RMS-023-online-order-dashboard-and-management.md
|   |-- RMS-024-customer-database-and-profile-management.md
|   |-- RMS-025-loyalty-program-and-reward-points.md
|   |-- RMS-026-sms-whatsapp-marketing-campaigns.md
|   |-- RMS-027-customer-feedback-system.md
|   |-- RMS-028-customer-labels-and-segmentation.md
|   |-- RMS-029-business-website-builder.md
|   |-- RMS-030-birthday-anniversary-campaigns.md
|   |-- RMS-031-aggregator-commission-tracking.md
|   |-- RMS-032-coupon-and-offer-management.md
|   |-- RMS-033-sms-whatsapp-campaign-engine.md
|   |-- RMS-034-customer-feedback-system.md
|   |-- RMS-035-customer-segmentation-engine.md
|   |-- RMS-036-birthday-anniversary-automation.md
|
|-- milestone-4-captain-kds-reservations/        # M4 Jira tickets (RMS-037 to RMS-040)
|   |-- RMS-037-captain-app-tableside-ordering.md
|   |-- RMS-038-kitchen-display-system-kds.md
|   |-- RMS-039-reservation-and-waitlist-management.md
|   |-- RMS-040-self-service-kiosk-mode.md
|
|-- milestone-5-analytics-reporting/             # M5 Jira tickets (RMS-041 to RMS-048)
|   |-- RMS-041-sales-reports-analytics-dashboard.md
|   |-- RMS-042-online-order-reconciliation.md
|   |-- RMS-043-head-office-multi-outlet-dashboard.md
|   |-- RMS-044-city-zone-grouping.md
|   |-- RMS-045-tax-reports-gst-compliance.md
|   |-- RMS-046-staff-performance-reports.md
|   |-- RMS-047-profit-margin-and-food-cost-analysis.md
|   |-- RMS-048-dynamic-custom-reports-builder.md
|
|-- milestone-6-platform-integrations/           # M6 Jira tickets (RMS-049 to RMS-056)
|   |-- RMS-049-multi-language-support-i18n.md
|   |-- RMS-050-role-based-access-control-enhancement.md
|   |-- RMS-051-aggregator-menu-sync.md
|   |-- RMS-052-accounting-integration-tally-export.md
|   |-- RMS-053-supplier-marketplace.md
|   |-- RMS-054-community-feature.md
|   |-- RMS-055-offline-mode-and-sync-engine.md
|   |-- RMS-056-hardware-integration-layer.md
|
|-- backend/                                     # Laravel 11 API application (to be built)
|-- frontend/                                    # React Vite SPA (to be built)
|
|-- README.md
```

---

## Documentation Index

| Document | Description |
|----------|-------------|
| [Overall Development Plan](plans/overall-development-plan.md) | Full roadmap, all 56 tickets, priority matrix, risk assessment |
| [Architecture](plans/architecture.md) | System architecture, module diagram, API patterns, deployment |
| [Database Schema](plans/database-schema.md) | Complete schema with all tables, columns, indexes, foreign keys |
| [UI Screens](plans/ui-screens.md) | Complete screen inventory (80+ screens) organized by module |
| [Milestone 1 Plan](plans/milestone-1-plan.md) | Foundation & Core Billing detailed plan |
| [Milestone 2 Plan](plans/milestone-2-plan.md) | Inventory & Supply Chain detailed plan |
