# RMS-023: Online Order Dashboard & Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-023 |
| **Type** | Story |
| **Epic** | Online Ordering & Aggregators |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-022 (Aggregator Integration) |

## User Story
As a restaurant manager, I want a unified dashboard for all online orders across Swiggy, Zomato, and direct channels, so that I can efficiently manage order acceptance, kitchen assignment, and delivery dispatch from a single screen.

## Description
This story delivers the central operations hub for all online orders. Orders arriving from Swiggy, Zomato, and the direct website (RMS-029) converge into a single real-time queue. The dashboard presents each order as a card showing the platform, customer, items, total, prep timer, and SLA countdown. The operator can accept, reject, assign to a kitchen station, mark as ready, and track the delivery rider all from this screen.

The order queue updates in real time via WebSocket (Laravel Reverb). When a new order arrives from the aggregator integration (RMS-022), a card animates into the queue with an audible alert. The SLA timer counts down from the moment the order is placed, and the card border changes color as the deadline approaches (green > amber > red). If an order is auto-accepted (per integration config), it skips to the "Preparing" column directly.

A secondary view consolidates online orders with dine-in table orders so that kitchen staff see a unified production queue. The dashboard also provides aggregator-wise filtering, date-range reporting, and export capabilities for end-of-day reconciliation. Delivery rider tracking integrates the aggregator-provided rider location link and ETA where available.

## Acceptance Criteria
- [ ] Dashboard displays all online orders in a Kanban-style queue with columns: New, Accepted, Preparing, Ready, Dispatched, Delivered.
- [ ] New orders pushed via WebSocket appear on screen within 2 seconds of ingestion with visual and audible alert.
- [ ] Each order card shows: platform logo, order ID, customer name, item count, total amount, prep timer, and SLA countdown.
- [ ] Order card border color changes: green (>5 min remaining), amber (2-5 min), red (<2 min) based on SLA.
- [ ] Operator can accept an order with one click; accept action propagates to the aggregator and moves card to "Accepted".
- [ ] Operator can reject an order; rejection modal requires a reason from a predefined list and a free-text field.
- [ ] Accepted orders can be assigned to a kitchen station (Grill, Fry, Curry, etc.) and pushed to the KDS (RMS-033).
- [ ] "Ready" status triggers a notification and the card moves to Ready column; "Dispatched" captures rider details if available.
- [ ] Filter bar supports filtering by platform (Swiggy/Zomato/Direct/All), status, and date range.
- [ ] Consolidated kitchen view merges online order items with dine-in KOT items into a single production list.
- [ ] End-of-day summary panel shows total orders, total revenue, platform-wise breakdown, and average acceptance time.
- [ ] Dashboard supports keyboard shortcuts (A = Accept, R = Reject, Enter = next order) for fast operation during rush hours.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Online Order Dashboard | /online-orders | Main Kanban queue for all online orders |
| Order Detail Drawer | /online-orders/{orderId} | Slide-out drawer with full order details |
| Consolidated Kitchen View | /online-orders/kitchen-consolidated | Merged online + dine-in production queue |
| End of Day Summary | /online-orders/eod-summary | Daily reconciliation summary |

### Screen Details

**Online Order Dashboard (/online-orders)**
- Layout: Full-height Kanban board with 6 columns (New, Accepted, Preparing, Ready, Dispatched, Delivered). Top filter bar. Bottom status bar.
- Filter bar components: `PlatformFilter` (dropdown: All/Swiggy/Zomato/Direct), `StatusFilter` (multiselect), `DatePicker` (range), `SoundToggle`, `AutoScrollToggle`.
- Order card (`OnlineOrderCard`) fields:
  - Platform logo + order ID (e.g., "Swiggy #SW8842193")
  - Customer name and phone
  - Item summary: "3 items - Paneer Butter Masala, 2x Butter Naan, Coke"
  - Special instructions badge (if present, highlighted yellow)
  - Total amount (large font)
  - Prep timer (elapsed since acceptance)
  - SLA countdown ring (circular progress)
  - Card border color based on SLA
- Card actions (buttons): "Accept" (green), "Reject" (red), "Assign Kitchen" (dropdown), "Mark Ready", "Mark Dispatched".
- Sound alert: Chime on new order, escalation beep on SLA breach.
- Status bar: "12 New | 5 Preparing | 3 Ready | Avg Accept: 18s".
- Keyboard shortcuts overlay (press ? to show).

**Order Detail Drawer (slide-out from right, 450px wide)**
- Sections: Order Header, Customer Info, Itemized List, Charges Breakdown, Delivery Info, Timeline, Actions.
- Itemized list table columns: Item | Qty | Add-ons | Special Instructions | Price.
- Charges breakdown: Subtotal, Discount, Packaging Charge, Delivery Charge, Tax, Commission, Total.
- Timeline: Vertical timeline of status changes with timestamps.
- Delivery info: Address, rider name, rider phone (if available), live tracking link button.
- Buttons: "Print KOT", "Print Bill", "Accept", "Reject", "Call Customer" (tel: link).

**Rejection Modal**
- Triggered on Reject click.
- Fields: Reason dropdown (Out of stock items, Kitchen capacity, Closing soon, Invalid address, Other), Free-text notes (textarea, max 200 chars).
- Buttons: "Cancel", "Confirm Reject".
- Validation: Reason required; confirmation propagates to aggregator.

**Assign Kitchen Modal**
- Fields: Station dropdown (Grill, Fry, Curry, Tandoor, Salad, Beverages), Priority (Normal, Rush, VIP).
- Buttons: "Assign to KDS", "Cancel".

**Consolidated Kitchen View (/online-orders/kitchen-consolidated)**
- Layout: Single sortable table merging online and dine-in items.
- Table columns: Source (Online/Dine-in badge) | Order/Table | Item | Qty | Station | Status | Timer | Actions.
- Group by toggle: By Station / By Order.
- Buttons: "Mark Prepared" per row, "Bulk Mark Prepared".

**End of Day Summary (/online-orders/eod-summary)**
- Layout: Summary cards + breakdown table + chart.
- Summary cards: Total Orders, Total Revenue, Swiggy Orders, Zomato Orders, Direct Orders, Avg Accept Time, Avg Prep Time.
- Table: Platform | Orders | Gross Revenue | Commission | Net Revenue | Avg Prep Time.
- Chart: Stacked bar chart of orders by hour.
- Buttons: "Export PDF", "Export CSV", "Change Date".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/online-orders | List online orders with filters (platform, status, date_range) |
| GET | /api/v1/online-orders/{id} | Get single order detail with timeline |
| POST | /api/v1/online-orders/{id}/accept | Accept order |
| POST | /api/v1/online-orders/{id}/reject | Reject order with reason |
| POST | /api/v1/online-orders/{id}/assign-kitchen | Assign order to kitchen station |
| PUT | /api/v1/online-orders/{id}/status | Update order status (preparing, ready, dispatched, delivered) |
| GET | /api/v1/online-orders/kitchen-consolidated | Get merged online + dine-in production queue |
| GET | /api/v1/online-orders/eod-summary | Get end-of-day summary with date filter |
| GET | /api/v1/online-orders/stats | Real-time stats (counts by status, avg times) |
| GET | /api/v1/online-orders/{id}/timeline | Get status change timeline |

## Database Tables

**online_orders** (from RMS-022, extended)
- id, restaurant_id, aggregator_integration_id, platform_order_id, platform, customer_name, customer_phone, delivery_address, items_json, subtotal, discount, packaging_charge, delivery_charge, tax, commission_amount, total, status, rejection_reason, order_placed_at, order_accepted_at, accepted_within_sec, assigned_station, priority, rider_name, rider_phone, rider_tracking_url, delivered_at, raw_payload, timestamps

**online_order_timeline**
- id (bigIncrements, PK)
- online_order_id (foreignId, online_orders)
- status (string)
- event_type (enum: received, accepted, rejected, assigned, preparing, ready, dispatched, delivered, cancelled)
- changed_by (foreignId, users, nullable)
- notes (text, nullable)
- metadata (json, nullable)
- created_at

**online_order_items** (denormalized for kitchen display)
- id (bigIncrements, PK)
- online_order_id (foreignId, online_orders)
- menu_item_id (foreignId, menu_items, nullable)
- item_name (string)
- quantity (unsignedInteger)
- addons_json (json, addon selections)
- special_instructions (text, nullable)
- unit_price (decimal(10,2))
- total_price (decimal(10,2))
- assigned_station (string, nullable)
- preparation_status (enum: pending, preparing, prepared)
- timestamps

## Technical Notes

**Laravel Backend:**
- Controllers: `OnlineOrderController` (index, show, accept, reject, updateStatus, assignKitchen, eodSummary, stats, timeline), `ConsolidatedKitchenController` (index).
- The `accept` method: validates order is in "pending" status, calls `AggregatorApiService->acceptOrder()`, records `accepted_within_sec`, creates timeline entry, broadcasts `OrderAccepted` event, dispatches `PushOrderToKDSJob` if station assigned.
- The `reject` method: validates reason, calls service `rejectOrder()`, updates status, creates timeline entry, broadcasts event.
- EOD summary: `OnlineOrderController@eodSummary` aggregates via MySQL queries grouped by platform, cached in Redis for 5 minutes per restaurant per date.
- WebSocket events broadcast to private channel `restaurant.{restaurant_id}.online-orders` using `ShouldBroadcastNow`.
- Real-time stats endpoint reads from Redis cache `online-orders:stats:{restaurant_id}` updated on every status change.
- Routes in `routes/api.php` under `middleware(['auth:sanctum', 'restaurant.scope'])`.

**React Frontend:**
- Components: `OnlineOrderDashboardPage`, `OrderKanbanBoard`, `OnlineOrderCard`, `SLACountdownRing`, `OrderDetailDrawer`, `ItemizedOrderTable`, `RejectionModal`, `AssignKitchenModal`, `ConsolidatedKitchenView`, `EODSummaryPage`, `SummaryCards`, `PlatformBreakdownTable`, `OrderByHourChart` (Recharts).
- WebSocket: Reverb Echo listening to `restaurant.{id}.online-orders` channel for `.order-received`, `.order-accepted`, `.order-status-changed` events.
- State: Zustand `useOnlineOrderStore` managing orders array, filters, and stats. WebSocket events update store in real time.
- SLA timer: Client-side `useSLACountdown` hook computing remaining time from `order_placed_at` and platform SLA (configurable per platform, default 5 min for Swiggy, 6 min for Zomato).
- Audible alerts: `useSoundAlert` hook using Web Audio API, respects SoundToggle state.
- Kanban: Built with `@hello-pangea/dnd` for optional drag-to-move-status.

## Subtasks
1. [ ] Create `online_order_timeline` and `online_order_items` migrations and models with relationships
2. [ ] Build OnlineOrderController with index, show, accept, reject, updateStatus methods
3. [ ] Implement assignKitchen endpoint with station routing logic
4. [ ] Build ConsolidatedKitchenController merging online and dine-in items
5. [ ] Implement EOD summary aggregation with Redis caching
6. [ ] Implement real-time stats endpoint with Redis cache invalidation on status change
7. [ ] Set up WebSocket broadcasting for all order lifecycle events
8. [ ] Build React OnlineOrderDashboardPage with Kanban board layout
9. [ ] Build OnlineOrderCard component with SLA countdown ring and color coding
10. [ ] Build OrderDetailDrawer with itemized table and timeline
11. [ ] Build RejectionModal and AssignKitchenModal
12. [ ] Build ConsolidatedKitchenView with grouping and bulk actions
13. [ ] Build EODSummaryPage with cards, table, and chart
14. [ ] Implement audible alert system with toggle
15. [ ] Add keyboard shortcuts for accept/reject/navigation
16. [ ] Write feature tests for order lifecycle transitions

## Testing Criteria
- [ ] New order from WebSocket appears on dashboard within 2 seconds
- [ ] Accept action moves card to Accepted and propagates to aggregator
- [ ] Reject action opens modal, requires reason, and propagates to aggregator
- [ ] SLA countdown ring displays correct remaining time and color codes correctly
- [ ] Assign kitchen pushes order to correct station in KDS
- [ ] Consolidated kitchen view shows both online and dine-in items
- [ ] Filters by platform and status work correctly
- [ ] EOD summary aggregates correctly across platforms
- [ ] Sound alerts fire on new order and can be toggled off
- [ ] Keyboard shortcuts accept/reject the focused order
