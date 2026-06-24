# RMS-037: Captain App — Tableside Ordering

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-037 |
| **Type** | Story |
| **Epic** | Captain App & Tableside Service |
| **Milestone** | Milestone 4 - Captain App, KDS & Reservations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-011 (Menu Management), RMS-012 (Table Management), RMS-014 (Billing & Payment), RMS-019 (KOT/Kitchen Order Ticket), RMS-038 (KDS) |

## User Story
As a captain (head waiter) using a 10-inch tablet on the restaurant floor, I want to log in with a PIN, take orders tableside, send them to the kitchen, and split or merge bills, so that I can serve guests faster, reduce order errors, and operate the floor efficiently even when WiFi drops.

## Description
The Captain App is a React PWA optimised for 10-inch Android tablets used by captains and floor staff to take orders directly at the table. It eliminates paper KOTs, reduces walk-back trips to a stationary POS, and dramatically cuts order errors by transmitting orders to the Kitchen Display System (RMS-038) in real time. In Indian casual-dining and QSR outlets where the captain ratio is 1:8-12 tables, this app is the single most important productivity tool for the front-of-house team.

The app uses PIN-based authentication bound to a `captain` user role, with each staff member's identity stamped on every order they create for accountability, tip tracking, and shift handover. The captain selects a table from a live floor map (RMS-012), browses the menu (RMS-011) with category drill-down, and adds items to a per-table cart. Mandatory modifier groups (e.g., spice level, "with/without onion", add-ons) are enforced before an item can be sent to the kitchen. Each line item supports free-text cooking instructions ("less spicy", "no coriander").

Orders flow to KDS via Laravel WebSockets (Laravel Reverb) and produce a printed KOT if a printer is configured at the station. The captain can re-fire (reprint), add items to an existing open order, or void items with a reason code (manager PIN required over a configurable threshold). At bill time the captain can split equally, split by item, split by amount, or merge guest bills across tables.

Critical for Indian outlets: **offline-first operation**. Restaurant WiFi is unreliable, and a captain with 6 tables waiting cannot be blocked by network issues. Orders are queued in IndexedDB and synced when connectivity returns, with a conflict-resolution policy (last-write-wins for non-overlapping items; manager alert on collision).

## Acceptance Criteria
- [ ] Captain can log in with 4-6 digit PIN; failed attempts lock for 30 seconds after 5 wrong PINs
- [ ] Floor map view shows real-time table status (vacant, occupied, billed, dirty) with color coding and live timer for occupied tables
- [ ] Captain can select a table and view open order, last bill, and guest count
- [ ] Menu browse supports category drill-down, item search, image previews, veg/non-veg/Jain badges (Indian regulatory requirement)
- [ ] Items with mandatory modifier groups block "Add to Order" until a modifier is selected
- [ ] Per-line cooking instructions (text) and per-order table-level notes are captured
- [ ] "Send to Kitchen" transmits KOT to the relevant station(s) via WebSocket and produces KOT print if printer configured
- [ ] "Add to Existing Order" appends to a live open order without re-printing already-bumped items
- [ ] Bill split supports Equal (by guest count), By Item (each guest selects items), and By Amount (custom rupee allocation)
- [ ] Bill merge combines bills from 2-4 tables into a single payment
- [ ] Offline mode: orders placed without network are queued in IndexedDB and synced on reconnect with status indicator
- [ ] Conflict on sync surfaces a manager alert (same item modified both online and offline)
- [ ] All captain actions are logged with captain_id, timestamp, action type for accountability and tip split
- [ ] App supports Hindi + English UI toggle; number/currency formatting in Indian format (₹, lakh/crore)

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| PIN Login | /captain/login | Quick 4-6 digit PIN entry, restaurant branding |
| Floor Map | /captain/floor | Live table layout with status colors and timer |
| Table Detail | /captain/tables/{id} | Open order, bill, guest management for selected table |
| Menu Browse | /captain/menu | Category tabs, item grid with images and modifiers |
| Order Cart | /captain/tables/{id}/cart | Line items, modifiers, send-to-kitchen, bill preview |
| Bill & Payment | /captain/tables/{id}/bill | Split options, merge, payment collection |

### Screen Details

**PIN Login (/captain/login)**
- Layout: Centered card on restaurant logo background; full-screen on 10" tablet.
- Fields: Restaurant logo at top, 4-6 digit numeric PIN pad (large buttons, 60px+).
- Buttons: Number pad (0-9), Backspace, Enter; "Switch Staff" link for shift handover.
- Behavior: On Enter, calls `POST /api/v1/captain/login`; on success stores JWT in IndexedDB and routes to Floor Map. After 5 wrong PINs, lockout for 30 seconds with countdown.
- Display: Shift indicator (Morning/Evening/Night), current logged-in staff name below PIN pad.

**Floor Map (/captain/floor)**
- Layout: 2D top-down floor plan rendered from `restaurant_tables.layout_json`; tables drawn as colored shapes (circle/square/rectangle) at their x/y coordinates.
- Color coding: Green (vacant), Orange (occupied, with live timer since seated), Red (billed — awaiting payment), Gray (dirty — awaiting reset).
- Interactions: Tap a table to select; bottom sheet slides up showing Table Detail.
- Header: Restaurant name, current captain name, shift info, logout button, sync status indicator (cloud icon with online/offline/syncing state).
- Sidebar (right edge): Quick filters — All / My Tables / Billed / Dirty.
- Long-press on a table: Quick actions menu (Mark Clean, Transfer Table, View Order).

**Table Detail (/captain/tables/{id})**
- Header: Table number, capacity, status badge, "Seated since" timer, guest count.
- Sections: Open Order (with item list), Guest Count (+/- buttons), Table Notes, Assigned Server.
- Buttons: "Take Order" (routes to Menu), "Add Items" (if order exists), "Bill" (if items ordered), "Print KOT", "Transfer", "Clear Table".
- Behavior: Server can adjust guest count mid-meal (with reason); guest count affects KOT quantities on subsequent orders.

**Menu Browse (/captain/menu)**
- Layout: Two-pane — left vertical category strip (Starters / Mains / Breads / Rice / Drinks / Desserts); right item grid (2-3 columns on 10" tablet).
- Each item card: Image, name, price (₹), Veg/Non-veg/Jain badge (green/brown/red dot — Indian FSSAI convention).
- Filters: Veg only toggle, search input, "Available Now" toggle (hides 86'd items).
- On tap: Item Detail modal opens with description, modifier groups (radio/checkbox/quantity), mandatory vs optional flag, special instructions textarea.
- Buttons: "Add to Order" (disabled until required modifiers selected), "Cancel".
- Quick re-order: "Frequently Ordered" section shows top 10 items for the captain or table.

**Order Cart (/captain/tables/{id}/cart)**
- Layout: Header with table number and seat mode toggle (Takeaway / Dine-in), scrollable line item list, sticky bottom action bar.
- Line item row: Item name | Qty stepper | Modifiers (chips) | Cooking notes (italic) | Line total | Edit | Remove.
- Empty state: "No items added" with quick-add buttons (Veg Starters, Drinks, etc.).
- Order-level fields: Special instructions textarea, guest count, server name (auto-filled, editable).
- Totals panel (right): Subtotal, taxes (CGST/SGST breakdown — Indian GST compliance), discount, grand total.
- Buttons: "Send to Kitchen" (primary), "Save Draft" (queues locally), "Preview Bill", "Cancel Order".
- Behavior: "Send to Kitchen" groups items by station and dispatches one KOT per station via `KOTDispatchService`. Already-bumped items cannot be re-sent without "Re-fire" action.

**Bill & Payment (/captain/tables/{id}/bill)**
- Layout: Top section shows consolidated bill; middle shows split method tabs; bottom shows payment summary.
- Split Methods: Equal Split (enter number of guests), By Item (each guest selects items — checkbox grid), By Amount (custom ₹ amounts, validates total equals grand total).
- Merge Section: "Merge with Table ___" dropdown — combines bills into one consolidated bill.
- Payment: Cash / Card / UPI / Pay-at-Counter; supports partial payment per split (e.g., Guest 1 paid ₹500 cash, Guest 2 wants to pay separately).
- Buttons: "Collect Payment", "Print Bill", "Email Receipt", "Close Table".
- Behavior: Settling bill calls `POST /api/v1/bills/{id}/settle` from RMS-014; on success, table status auto-flips to "Dirty" for cleanup.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/captain/login | PIN-based authentication, returns JWT |
| POST | /api/v1/captain/logout | End session, clear stored token |
| GET | /api/v1/captain/floor | Live floor map with table statuses |
| GET | /api/v1/captain/tables/{id} | Table detail with open order, guest count |
| POST | /api/v1/captain/tables/{id}/seat | Mark table as occupied, start timer |
| PATCH | /api/v1/captain/tables/{id}/guests | Update guest count mid-meal |
| GET | /api/v1/captain/menu | Menu snapshot for captain app (lightweight payload) |
| GET | /api/v1/captain/items/{id}/modifiers | Get modifier groups for an item |
| POST | /api/v1/captain/orders | Create new order for table (drafts + sends) |
| PATCH | /api/v1/captain/orders/{id} | Append items to existing open order |
| POST | /api/v1/captain/orders/{id}/void-item | Void line item with reason + manager PIN |
| POST | /api/v1/captain/orders/{id}/refire | Re-print KOT for already-bumped item |
| POST | /api/v1/captain/orders/{id}/send-kitchen | Dispatch KOT to station(s) |
| GET | /api/v1/captain/orders/{id}/bill-preview | Compute bill preview without settling |
| POST | /api/v1/captain/bills/split-equal | Equal-split bill calculation |
| POST | /api/v1/captain/bills/split-by-item | Item-based split |
| POST | /api/v1/captain/bills/split-by-amount | Amount-based split |
| POST | /api/v1/captain/bills/merge | Merge bills across tables |
| POST | /api/v1/captain/sync/offline-queue | Bulk sync offline-queued orders on reconnect |
| WS | /ws/captain | Subscribe to order status updates, table changes |

## Database Tables

**captain_sessions**
- id (bigIncrements, PK)
- user_id (foreignId, users)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- device_id (string) — tablet hardware ID
- pin_attempts (unsignedTinyInteger, default 0)
- locked_until (timestamp, nullable)
- jwt_token_hash (string) — for revocation
- shift_started_at (timestamp)
- shift_ended_at (timestamp, nullable)
- timestamps

**captain_order_actions** (audit trail)
- id (bigIncrements, PK)
- captain_id (foreignId, users)
- order_id (foreignId, orders)
- action_type (enum: created, item_added, item_voided, item_modified, k Fired, bill_previewed, bill_settled)
- payload_json (json) — diff or snapshot
- manager_pin_user_id (foreignId, users, nullable) — for void approvals
- reason_text (text, nullable)
- offline_synced (boolean, default false) — true if action originated offline
- occurred_at (timestamp) — client-side timestamp
- timestamps
- index([captain_id, occurred_at])

**offline_order_queue**
- id (bigIncrements, PK)
- captain_id (foreignId, users)
- table_id (foreignId, restaurant_tables)
- client_uuid (string, unique) — generated on tablet to prevent duplicates
- payload_json (json) — full order draft
- client_created_at (timestamp)
- sync_status (enum: pending, synced, conflict, error, default pending)
- conflict_reason (string, nullable)
- server_order_id (foreignId, orders, nullable) — set on successful sync
- timestamps
- index([captain_id, sync_status])

**table_state_cache** (denormalized for fast floor map rendering)
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- table_id (foreignId, restaurant_tables, unique)
- status (enum: vacant, occupied, billed, dirty, reserved)
- occupied_since (timestamp, nullable)
- guest_count (unsignedSmallInteger, nullable)
- current_order_id (foreignId, orders, nullable)
- current_bill_id (foreignId, bills, nullable)
- last_updated_at (timestamp)
- indexes([restaurant_id, status])

## Technical Notes

**Laravel Backend:**
- Controllers: `CaptainAuthController` (PIN login, lockout, logout), `CaptainFloorController` (floor map snapshot), `CaptainTableController` (seat, guests, transfer), `CaptainOrderController` (create, append, void, refire), `CaptainBillController` (split-equal, split-by-item, split-by-amount, merge, preview), `CaptainSyncController` (offline queue bulk-sync).
- Services: `CaptainAuthService` (PIN verification, lockout), `OrderCreationService` (validates modifiers, computes KOT routing), `KOTDispatchService` (groups items by station, sends via WebSocket + print job), `BillSplitService` (three split algorithms), `BillMergeService` (combines 2-4 bills, preserves GST traceability), `OfflineSyncService` (de-duplicates by client_uuid, handles conflicts).
- Events: `CaptainLoggedIn`, `TableStateChanged`, `OrderCreated`, `OrderItemVoided`, `KOTDispatched`, `BillSplitRequested`, `BillMerged` — all broadcast via Laravel Reverb on private channel `outlet.{outlet_id}.floor`.
- Jobs: `ProcessOfflineSyncJob` (handles bulk queue drain with retry policy), `GenerateKOTPrintJob` (PDF/ESC-POS print), `SendBillSettlementJob` (integrates with RMS-014 payment flow).
- Middleware: `captain.auth` (validates JWT, attaches captain_id), `captain.shift` (ensures shift open before order creation).
- WebSocket: Laravel Reverb with private channel `outlet.{id}.floor` for state changes and `outlet.{id}.kds` for KOT dispatch to RMS-038.
- PIN security: Bcrypt-hashed PINs, constant-time comparison, rate limiting via Laravel throttle (5 attempts per minute, lockout 30s).

**React Frontend:**
- Components: `PinLoginScreen`, `FloorMapView` (SVG-based table rendering), `TableDetailSheet`, `MenuBrowsePane`, `CategorySidebar`, `ItemGrid`, `ItemDetailModal`, `ModifierGroupForm`, `OrderCartScreen`, `BillSplitScreen`, `OfflineQueueIndicator`, `SyncStatusBadge`.
- State: Zustand `useCaptainStore` (auth, currentTable, cart), `useMenuCache` (IndexedDB-backed menu snapshot), `useOfflineQueue` (sync queue with retry logic), `useWebSocket` (Reverb subscription hook).
- PWA: Service worker (`workbox`) caches shell + menu data + last 50 orders for offline read; background sync API triggers queue flush on reconnect.
- IndexedDB schema: `menu_items`, `modifier_groups`, `orders_draft`, `orders_offline_queue`, `floor_state_cache`.
- Real-time: `useWebSocket` hook subscribed to private channel; reducer pattern merges incoming `TableStateChanged` events into local Zustand state.
- Number formatting: `Intl.NumberFormat('en-IN')` for ₹ amounts; supports lakh/crore grouping.
- Animations: Framer Motion for bottom-sheet transitions and cart item add/remove; haptic feedback (`navigator.vibrate(20)`) on order send for tablet confirmation.
- Build target: Vite + React 18, code-split per route, PWA manifest with `display: fullscreen`, `orientation: landscape`.

## Subtasks
1. [ ] Create migrations for `captain_sessions`, `captain_order_actions`, `offline_order_queue`, `table_state_cache`
2. [ ] Implement `CaptainAuthService` with PIN hashing, lockout policy, and JWT issuance
3. [ ] Build `CaptainAuthController` (login/logout) and `captain.auth` middleware
4. [ ] Build `CaptainFloorController` with denormalized floor snapshot endpoint
5. [ ] Implement `TableStateCache` maintenance via observers on `restaurant_tables`, `orders`, `bills`
6. [ ] Build menu snapshot endpoint with lightweight payload (image URL, price, veg badge, modifier refs only)
7. [ ] Implement `OrderCreationService` enforcing mandatory modifier validation
8. [ ] Implement `KOTDispatchService` with station-based grouping and Reverb broadcast
9. [ ] Build `CaptainOrderController` (create, append, void with manager PIN, refire)
10. [ ] Implement `BillSplitService` with three algorithms (equal, by item, by amount)
11. [ ] Implement `BillMergeService` with GST trace preservation
12. [ ] Build `OfflineSyncService` with client_uuid dedup and conflict detection
13. [ ] Set up Laravel Reverb with private channels for floor and KDS
14. [ ] Configure PWA service worker with menu cache and offline shell
15. [ ] Build React `PinLoginScreen` with numeric pad and lockout countdown
16. [ ] Build `FloorMapView` SVG renderer with color-coded table states
17. [ ] Build `MenuBrowsePane` and `ItemDetailModal` with mandatory modifier enforcement
18. [ ] Build `OrderCartScreen` with line items, station dispatch, KOT preview
19. [ ] Build `BillSplitScreen` with three split method tabs
20. [ ] Implement `useOfflineQueue` IndexedDB-backed queue with auto-flush on reconnect
21. [ ] Write unit + integration tests for auth, modifier validation, split math, sync dedup

## Testing Criteria
- [ ] Valid PIN logs in captain and routes to floor map; invalid PIN increments attempt counter
- [ ] After 5 wrong PINs, login is locked for 30 seconds
- [ ] Floor map reflects real-time status changes from another captain's actions within 2 seconds
- [ ] Item with mandatory modifier group cannot be added to cart without selection
- [ ] "Send to Kitchen" produces exactly one KOT per station with correct items
- [ ] Equal split for ₹1000 among 4 guests produces 4 × ₹250 with rounding adjustment to first guest
- [ ] By-item split preserves each guest's items and taxes proportionally
- [ ] Bill merge combines 2 tables' bills while keeping original bill IDs in audit trail
- [ ] Offline order saved to IndexedDB persists across browser refresh
- [ ] Sync on reconnect creates server order with matching client_uuid and reports success
- [ ] Conflict scenario (item voided offline while modified online) raises manager alert
- [ ] All captain actions recorded in `captain_order_actions` with correct captain_id and timestamp