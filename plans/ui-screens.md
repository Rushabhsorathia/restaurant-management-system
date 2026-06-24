# UI Screens Inventory

**Project:** Restaurant Management System (RMS)
**Total Screens:** 90+ organized by module

This document is the complete inventory of every screen in the system. For each screen: route path, purpose, key components, and the roles that can access it.

**Roles legend:** Admin (restaurant admin), HQ (HQ admin), Mgr (manager), Cashier, Waiter, Chef, Captain.

---

## Table of Contents

1. [Auth Screens](#1-auth-screens)
2. [Dashboard](#2-dashboard)
3. [Menu Management](#3-menu-management)
4. [Table Management](#4-table-management)
5. [Order Management](#5-order-management)
6. [Billing](#6-billing)
7. [Inventory](#7-inventory)
8. [CRM](#8-crm)
9. [Online Orders](#9-online-orders)
10. [Captain App](#10-captain-app)
11. [KDS](#11-kds)
12. [Reservations](#12-reservations)
13. [Reports](#13-reports)
14. [Settings](#14-settings)
15. [Multi-Outlet HQ](#15-multi-outlet-hq)

---

## 1. Auth Screens

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 1 | Login | /login | User login | LoginForm, OutletPicker, RememberMe | All |
| 2 | Forgot Password | /forgot-password | Request reset link | EmailForm | All |
| 3 | Reset Password | /reset-password/:token | Set new password | PasswordForm | All |
| 4 | First-Time Setup | /setup | Initial restaurant + admin creation | Wizard | Public |
| 5 | Profile | /profile | View/edit own profile, change password, 2FA toggle | ProfileForm, AvatarUpload | All |
| 6 | Lock Screen | /lock | Re-auth after idle timeout | PinForm | All |
| 7 | Logout | /logout | End session | ConfirmModal | All |

---

## 2. Dashboard

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 8 | Outlet Dashboard | /dashboard | Today's sales, orders, tables overview | SalesCard, OrderChart, TableStatusGrid, LiveOrders | Admin, Mgr, Cashier |
| 9 | Live Sales Widget | /dashboard#sales | Real-time sales counter | Counter, Sparkline | Admin, Mgr |
| 10 | Quick Actions Panel | /dashboard#actions | New order, new bill, add item shortcuts | ActionButtons | Admin, Mgr, Cashier |
| 11 | Notifications Center | /notifications | System + low stock + online order alerts | AlertList, Filter | All |
| 12 | Outlet Switcher | (modal) | Switch active outlet context | OutletSelectModal | Admin, HQ, Mgr |

---

## 3. Menu Management

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 13 | Menu Dashboard | /menu | Overview of categories and items | CategoryTabs, ItemGrid | Admin, Mgr |
| 14 | Category List | /menu/categories | Manage categories | DataTable, CRUD buttons | Admin, Mgr |
| 15 | Category Form | /menu/categories/new, /menu/categories/:id/edit | Create/edit category | CategoryForm | Admin, Mgr |
| 16 | Item List | /menu/items | Search & list menu items | SearchBar, ItemTable, Filters | Admin, Mgr |
| 17 | Item Form | /menu/items/new, /menu/items/:id/edit | Create/edit item | ItemForm, ImageUpload, TaxSelect | Admin, Mgr |
| 18 | Variation Manager | /menu/items/:id/variations | Manage item variations | VariationTable, VariationForm | Admin, Mgr |
| 19 | Add-on Manager | /menu/items/:id/addons | Manage item add-ons | AddonTable, AddonForm | Admin, Mgr |
| 20 | Add-on Groups | /menu/addon-groups | Manage add-on groups | GroupList, GroupForm | Admin, Mgr |
| 21 | Combo Builder | /menu/combos, /menu/items/:id/combo | Build item combos | ComboBuilder, ItemPicker | Admin, Mgr |
| 22 | Bulk Import | /menu/import | CSV import of items | FileUpload, MappingTable | Admin |
| 23 | Menu Availability Toggle | /menu/availability | Toggle item availability | ToggleGrid | Admin, Mgr, Chef |
| 24 | Happy Hours Config | /menu/happy-hours | Configure time-based pricing | TimeRangeForm, ItemSelect | Admin, Mgr |
| 25 | Shortcode Manager | /menu/shortcodes | Assign quick-order shortcodes | ShortcodeGrid | Admin, Mgr |

---

## 4. Table Management

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 26 | Floor Plan View | /tables/floor | Visual floor plan | FloorCanvas, TableNode, DragDrop | Admin, Mgr, Cashier, Captain |
| 27 | Areas & Zones | /tables/areas | Manage areas | AreaList, AreaForm | Admin, Mgr |
| 28 | Table List | /tables | List and manage tables | TableGrid, StatusBadge | Admin, Mgr |
| 29 | Table Form | /tables/new, /tables/:id/edit | Create/edit table | TableForm, CapacityInput | Admin, Mgr |
| 30 | QR Code Manager | /tables/qr | Generate/print table QR codes | QRPreview, PrintButton | Admin, Mgr |
| 31 | Table Status Board | /tables/status | Live occupancy status | StatusBoard, TimerBadge | Admin, Mgr, Cashier |

---

## 5. Order Management

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 32 | POS Terminal | /pos | Main ordering screen | MenuGrid, CartPanel, TablePicker | Admin, Mgr, Cashier, Waiter |
| 33 | Order List | /orders | List all orders with filters | OrderTable, StatusFilter, DateRange | Admin, Mgr, Cashier |
| 34 | Order Detail | /orders/:id | View/modify a specific order | OrderSummary, ItemList, ActionButtons | Admin, Mgr, Cashier |
| 35 | Held Orders | /orders/held | View and resume held orders | HeldOrderCards | Admin, Mgr, Cashier |
| 36 | Order Modify | /orders/:id/edit | Add/remove items, change qty | CartPanel, ModifierPicker | Admin, Mgr, Cashier |
| 37 | Transfer Table | /orders/:id/transfer | Move order to another table | TableSelectModal | Admin, Mgr, Cashier |
| 38 | Cancel Order | (modal) | Cancel order with reason | ReasonForm, ConfirmModal | Admin, Mgr |
| 39 | Order Type Selector | (modal) | Choose dine-in/takeaway/delivery | TypeButtons, CustomerPicker | Admin, Mgr, Cashier, Waiter |

---

## 6. Billing

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 40 | Bill Generate | /billing/generate/:orderId | Create bill from order | BillPreview, TaxBreakdown, DiscountPanel | Admin, Mgr, Cashier |
| 41 | Bill List | /billing | List all bills | BillTable, Filters, Export | Admin, Mgr |
| 42 | Bill Detail | /billing/:id | View settled bill | BillPrint, PaymentList | Admin, Mgr, Cashier |
| 43 | Split Bill | /billing/:id/split | Split bill across guests | SplitPanel, GuestTabs | Admin, Mgr, Cashier |
| 44 | Discount Apply | (modal) | Apply item/bill discount | DiscountPicker, ReasonField | Admin, Mgr, Cashier |
| 45 | Refund / Return | /billing/:id/refund | Process refund | RefundForm, ApprovalFlow | Admin, Mgr |
| 46 | Bill Customization | /settings/bill-format | Customize bill template | TemplateEditor, LogoUpload | Admin |
| 47 | Held Bills | /billing/held | Resume held bills | HeldBillCards | Admin, Mgr, Cashier |

---

## 7. Inventory

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 48 | Inventory Dashboard | /inventory | Stock value, low stock, alerts | StockValueCard, LowStockList | Admin, Mgr |
| 49 | Raw Materials | /inventory/materials | Material master list | MaterialTable, Filters | Admin, Mgr |
| 50 | Material Form | /inventory/materials/new, /:id/edit | Create/edit material | MaterialForm, UnitSelect, MinMax | Admin, Mgr |
| 51 | Stock Adjustment | /inventory/adjustments | Physical count / adjustment | AdjustmentForm, DiffCalc | Admin, Mgr |
| 52 | Stock Movement Log | /inventory/movements | Audit of stock changes | MovementTable, Filters | Admin, Mgr |
| 53 | Recipes List | /inventory/recipes | List recipes | RecipeTable | Admin, Mgr, Chef |
| 54 | Recipe Builder | /inventory/recipes/new, /:id/edit | Build recipe with ingredients | RecipeForm, IngredientPicker, YieldCalc | Admin, Mgr, Chef |
| 55 | Recipe Costing | /inventory/recipes/:id/costing | View recipe cost breakdown | CostBreakdownTable | Admin, Mgr, Chef |
| 56 | Purchase Orders | /inventory/purchase-orders | List POs | POTable, StatusFilter | Admin, Mgr |
| 57 | PO Form | /inventory/purchase-orders/new, /:id/edit | Create/edit PO | POForm, SupplierSelect, LineItems | Admin, Mgr |
| 58 | PO Approval | /inventory/purchase-orders/:id/approve | Approve PO | ApprovalForm | Admin |
| 59 | GRN (Goods Receipt) | /inventory/grn/:poId | Receive goods | GRNForm, QtyAccepted, BatchInput | Admin, Mgr |
| 60 | Suppliers | /inventory/suppliers | Supplier master | SupplierTable | Admin, Mgr |
| 61 | Supplier Form | /inventory/suppliers/new, /:id/edit | Create/edit supplier | SupplierForm, GSTInput, TermsField | Admin, Mgr |
| 62 | Supplier Statement | /inventory/suppliers/:id/statement | Payable + order history | StatementTable | Admin, Mgr |
| 63 | Central Kitchen Dashboard | /inventory/central-kitchen | CK overview | IndentList, ProductionBoard | Admin, Mgr |
| 64 | Indent Request | /inventory/central-kitchen/indents/new | Request stock from CK | IndentForm, ItemPicker | Admin, Mgr |
| 65 | Supply Note | /inventory/central-kitchen/indents/:id/dispatch | Dispatch from CK | DispatchForm | Admin, Mgr |
| 66 | Stock Transfer List | /inventory/transfers | List transfers | TransferTable | Admin, Mgr |
| 67 | Stock Transfer Form | /inventory/transfers/new, /:id/edit | Create transfer | TransferForm, OutletSelect | Admin, Mgr |
| 68 | Transfer Receipt | /inventory/transfers/:id/receive | Receive transfer | ReceiptForm, ConfirmButton | Admin, Mgr |
| 69 | Wastage Entry | /inventory/wastage/new | Log wastage | WastageForm, ReasonSelect | Admin, Mgr, Chef |
| 70 | Wastage Report | /inventory/wastage/report | Wastage analytics | WastageChart, TableByReason | Admin, Mgr |
| 71 | Low Stock Alerts | /inventory/alerts | View low stock + reorder suggestions | AlertList, DraftPOButton | Admin, Mgr |
| 72 | E-Way Bill List | /inventory/e-way-bills | List e-way bills | EWBTable | Admin, Mgr |
| 73 | E-Way Bill Generate | /inventory/e-way-bills/new | Generate e-way bill | EWBForm, TransportFields | Admin, Mgr |
| 74 | Inventory Valuation Report | /inventory/valuation | Stock value by method | ValuationTable, MethodSelect | Admin, Mgr |
| 75 | Stock Aging Report | /inventory/aging | Aging of stock batches | AgingTable | Admin, Mgr |

---

## 8. CRM

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 76 | CRM Dashboard | /crm | Customer count, loyalty overview | KpiCard, TopCustomers | Admin, Mgr |
| 77 | Customer List | /crm/customers | Customer master | CustomerTable, Search, SegmentFilter | Admin, Mgr, Cashier |
| 78 | Customer Profile | /crm/customers/:id | Customer detail + history | ProfileCard, OrderHistory, LoyaltyCard | Admin, Mgr, Cashier |
| 79 | Customer Form | /crm/customers/new, /:id/edit | Create/edit customer | CustomerForm, DemographicsFields | Admin, Mgr, Cashier |
| 80 | Loyalty Points Ledger | /crm/customers/:id/loyalty | Points history | LedgerTable | Admin, Mgr |
| 81 | Reward Wallet | /crm/customers/:id/wallet | Wallet balance + transactions | WalletCard, TxnTable | Admin, Mgr |
| 82 | Campaigns | /crm/campaigns | Campaign list | CampaignTable, StatusBadge | Admin, Mgr |
| 83 | Campaign Builder | /crm/campaigns/new, /:id/edit | Create campaign with rules | RuleBuilder, SegmentPicker, RewardConfig | Admin, Mgr |
| 84 | Feedback Inbox | /crm/feedback | View customer feedback | FeedbackList, RatingFilter | Admin, Mgr |
| 85 | Segments | /crm/segments | Define customer segments | SegmentTable, RuleEditor | Admin, Mgr |

---

## 9. Online Orders

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 86 | Online Order Dashboard | /online | Incoming online orders | OrderQueue, AutoAcceptToggle, TimerBadge | Admin, Mgr, Cashier |
| 87 | Aggregator Settings | /online/integrations | Configure aggregators | IntegrationCards, ApiKeyForm | Admin |
| 88 | Menu Mapping | /online/menu-mapping | Map aggregator items to menu | MappingTable, ItemPicker | Admin, Mgr |
| 89 | Online Order Detail | /online/:id | View/accept/reject order | OrderCard, AcceptRejectButtons | Admin, Mgr, Cashier |
| 90 | Reconciliation | /online/reconciliation | Settlement + commission report | SettlementTable, DateRange | Admin, Mgr |

---

## 10. Captain App

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 91 | Captain Home | /captain | Select table to serve | TableGrid, StatusBadge | Captain, Waiter |
| 92 | Captain Order | /captain/order/:tableId | Tableside ordering | MenuGrid, CartPanel, ModifierPicker | Captain, Waiter |
| 93 | Captain Cart Review | /captain/cart | Review before send to kitchen | CartList, NotesField, SendButton | Captain, Waiter |
| 94 | Captain Bill View | /captain/bill/:tableId | View running bill | BillPreview, RequestPaymentButton | Captain, Waiter |
| 95 | Captain Tableside Payment | /captain/payment/:billId | Initiate payment | PaymentMethodPicker, AmountEntry | Captain |

---

## 11. KDS

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 96 | KDS Display | /kds | Live kitchen order tickets | TicketColumn, BumpButton, TimerBadge | Chef, Mgr |
| 97 | KDS Station View | /kds/station/:stationId | Filter by station | FilteredTicketColumn | Chef |
| 98 | KDS Recall | /kds/recall | Recall bumped tickets | RecallList | Chef, Mgr |
| 99 | KDS Analytics | /kds/analytics | Prep time, load metrics | PrepTimeChart, LoadHeatmap | Admin, Mgr |

---

## 12. Reservations

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 100 | Reservation Calendar | /reservations | Calendar of reservations | CalendarGrid, DayCell | Admin, Mgr, Captain |
| 101 | Reservation Form | /reservations/new, /:id/edit | Create/edit reservation | ReservationForm, SlotPicker, TablePicker | Admin, Mgr, Captain |
| 102 | Waitlist | /reservations/waitlist | Manage waitlist | WaitlistTable, SeatButton | Admin, Mgr, Captain |
| 103 | Slot Management | /reservations/slots | Configure time slots | SlotForm, CapacityInput | Admin, Mgr |
| 104 | Guest Preferences | /reservations/guests/:id/preferences | Per-guest preferences | PreferenceForm | Admin, Mgr, Captain |

---

## 13. Reports

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 105 | Sales Report | /reports/sales | Day/week/month sales | DateRange, SalesChart, SalesTable | Admin, Mgr |
| 106 | Item-wise Sales | /reports/items | Item sales analytics | ItemTable, BarChart | Admin, Mgr |
| 107 | Category Sales | /reports/categories | Category-wise analytics | CategoryChart | Admin, Mgr |
| 108 | Hourly Sales | /reports/hourly | Sales by hour | Heatmap, LineChart | Admin, Mgr |
| 109 | Profit & Loss | /reports/pl | P&L with COGS | PLTable, TrendChart | Admin |
| 110 | GST Report | /reports/gst | GST return data | GSTRTable, ExportButton | Admin |
| 111 | Tax Collected | /reports/tax | Tax breakdown report | TaxTable | Admin, Mgr |
| 112 | Payment Report | /reports/payments | Payment method breakdown | PaymentPie, Table | Admin, Mgr |
| 113 | Custom Report Builder | /reports/custom | Build custom reports | BuilderUI, FieldPicker, SaveButton | Admin |
| 114 | Scheduled Reports | /reports/scheduled | Manage scheduled reports | ScheduleTable, EmailConfig | Admin |
| 115 | Audit Log Viewer | /reports/audit | Audit trail | AuditTable, UserFilter | Admin |
| 116 | Discount Report | /reports/discounts | Discount usage analytics | DiscountTable | Admin, Mgr |
| 117 | Refund Report | /reports/refunds | Refund history | RefundTable | Admin |

---

## 14. Settings

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 118 | Settings Home | /settings | Settings hub | SettingsNav | Admin |
| 119 | User Management | /settings/users | User list | UserTable, InviteButton | Admin |
| 120 | User Form | /settings/users/new, /:id/edit | Create/edit user | UserForm, RolePicker, OutletAccess | Admin |
| 121 | Roles & Permissions | /settings/roles | Role config + permissions | RoleList, PermissionMatrix | Admin |
| 122 | Tax Configuration | /settings/taxes | GST/tax setup | TaxTable, TaxForm | Admin |
| 123 | Discount Configuration | /settings/discounts | Discount master | DiscountTable, DiscountForm | Admin |
| 124 | Printer Configuration | /settings/printers | Printer setup | PrinterTable, PrinterForm | Admin |
| 125 | Bill Format | /settings/bill-format | Receipt template | TemplateEditor, Preview | Admin |
| 126 | Branding | /settings/branding | Logo, colors, theme | BrandingForm, LogoUpload | Admin |
| 127 | Languages | /settings/languages | Language list | LanguageTable | Admin |
| 128 | Translations | /settings/translations | Manage translations | TranslationEditor, SearchField | Admin |
| 129 | App Settings | /settings/app | Global toggles | SettingsForm | Admin |
| 130 | Backup & Export | /settings/backup | Backup/data export | BackupButtons, ExportForm | Admin |

---

## 15. Multi-Outlet HQ

| # | Screen | Route | Purpose | Key Components | Roles |
|---|--------|-------|---------|----------------|-------|
| 131 | HQ Dashboard | /hq | Consolidated multi-outlet view | OutletComparison, TotalCard | HQ, Admin |
| 132 | Outlet List | /hq/outlets | Manage outlets | OutletTable | HQ, Admin |
| 133 | Outlet Form | /hq/outlets/new, /:id/edit | Create/edit outlet | OutletForm, TaxConfig | HQ, Admin |
| 134 | Central Menu Push | /hq/menu-push | Push menu to outlets | PushPreview, OutletSelect | HQ, Admin |
| 135 | HQ Sales Report | /hq/reports/sales | Sales across outlets | OutletBreakdown, TotalChart | HQ |
| 136 | HQ Inventory Report | /hq/reports/inventory | Inventory across outlets | OutletInventoryTable | HQ |
| 137 | Inter-Outlet Transfer Report | /hq/reports/transfers | Transfer history | TransferReportTable | HQ, Admin |
| 138 | Central Kitchen Overview | /hq/central-kitchen | CK across all outlets | CKDashboard, IndentFeed | HQ, Admin |

---

## Summary

- **Total screens:** 138 (well above the 80+ target).
- **Modules covered:** Auth, Dashboard, Menu, Tables, Orders, Billing, Inventory, CRM, Online Orders, Captain App, KDS, Reservations, Reports, Settings, Multi-Outlet HQ.
- Each screen maps to a route in the React SPA and (where applicable) backing API endpoints documented in the Jira tickets.
