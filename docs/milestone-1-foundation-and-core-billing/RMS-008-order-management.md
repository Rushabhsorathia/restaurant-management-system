# RMS-008: Order Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-008 |
| **Type** | Story |
| **Epic** | Core POS |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-006, RMS-007 |

## User Story
As a cashier or waiter, I want to create, modify, and manage customer orders, so that kitchen receives accurate KOTs and customers get correct bills.

## Description
The order management module is the heart of the POS system. It handles the full lifecycle of an order from creation to settlement. Orders can be of type Dine-In (linked to a table), Takeaway, or Delivery. The cashier adds items from the menu, applies variations and add-ons, sends the order to the kitchen (KOT generation), and can hold, modify, transfer, or cancel orders. Order status flows through a defined workflow with timestamps at each stage.

## Acceptance Criteria
- [ ] Cashier can create a new order (Dine-In with table, Takeaway, or Delivery)
- [ ] Order items can be searched by name, category, or barcode/shortcode
- [ ] Each order item supports: quantity, variations, add-ons, special instructions
- [ ] Items can be added, removed, or quantity changed after initial order (modifier KOT)
- [ ] Order can be put on HOLD (pause) and resumed later
- [ ] Order can be transferred from one table to another
- [ ] Order can be cancelled with mandatory reason (cancellation audit log)
- [ ] Order status workflow: Created -> Sent to Kitchen -> Preparing -> Ready -> Served -> Billed -> Settled
- [ ] Multiple KOTs can be generated for the same order (initial + modifications)
- [ ] Order shows running total with taxes in real-time
- [ ] Cashier can add special instructions per item (e.g., "less spicy", "no onion")
- [ ] Order can be split into multiple bills (shared billing)
- [ ] Order timer shows elapsed time since creation
- [ ] Rush hour indicator: orders in "Sent to Kitchen" > 10 min show red flag

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Order Taking Screen | /pos/order/new | Main POS billing screen |
| Running Orders | /pos/orders | List of all active orders |
| Order Detail | /pos/orders/:id | Full order view with edit |
| Held Orders | /pos/orders/held | List of paused orders |
| Cancelled Orders | /pos/orders/cancelled | Audit log of void orders |

### Screen Details

**Order Taking Screen (/pos/order/new) - This is the MAIN POS screen**

Layout: Split view, left 60% = menu browser, right 40% = cart/order panel

Left Panel (Menu Browser):
- Top: Search bar (auto-complete item names), Category tabs (horizontal scroll: Starters, Main Course, Beverages, Desserts...)
- Grid of item cards: Item image (thumbnail), Name, Price, Out-of-stock badge (greyed out)
- Click item card -> adds to cart on right panel
- Long press / right-click item -> shows variation/addon modal

Right Panel (Cart / Order Summary):
- Header: Order Type selector (Dine-In / Takeaway / Delivery)
  - If Dine-In: Table selector dropdown (shows available tables)
  - If Takeaway: Customer phone field (optional)
  - If Delivery: Customer name, phone, address fields
- Order Items list (scrollable):
  - Each row: Item name, variation (if any), qty [-][value][+], price, line total, special note icon, delete (X) button
  - Tap item name -> opens edit modal (variations, addons, notes)
- Subtotal row
- Tax breakdown: CGST, SGST, or IGST (based on inter/intra state)
- Discount row (if applied)
- Grand Total (large, bold)
- Bottom buttons: HOLD ORDER (yellow), SEND TO KITCHEN (orange - triggers KOT), SETTLE BILL (green)

**Variation/Add-on Modal**
- Item name at top
- Variation groups (radio buttons): e.g., "Size: Regular / Medium / Large" with price delta
- Add-on groups (checkboxes): e.g., "Extra Cheese (+30)", "Olives (+20)"
- Special Instructions textarea: "Cooking notes..."
- Quantity selector
- Total price (updates live)
- ADD TO ORDER button

**Running Orders (/pos/orders)**
- Tab bar: All / Dine-In / Takeaway / Delivery / Held
- Table/List: Order#, Table/Customer, Items count, Amount, Status badge (color-coded), Time elapsed, Action buttons
- Filter by status: Sent / Preparing / Ready / Served
- Click row -> opens Order Detail

**Order Detail (/pos/orders/:id)**
- Full order header: Order#, Type, Table/Customer, Created time, Status timeline
- Status timeline (visual): Created -> Sent -> Preparing -> Ready -> Served -> Billed
- Item list with all details
- Edit button (if order not yet billed)
- Transfer Table button (for dine-in)
- Cancel Order button (with reason modal)
- Print KOT button (reprint)
- Settle Bill button
- Audit trail section: all actions with user + timestamp

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/orders | Create new order |
| GET | /api/v1/orders | List orders (filter by status, type, date) |
| GET | /api/v1/orders/{id} | Get order details |
| PUT | /api/v1/orders/{id} | Update order (add/remove/modify items) |
| PATCH | /api/v1/orders/{id}/status | Update order status |
| POST | /api/v1/orders/{id}/hold | Hold/pause order |
| POST | /api/v1/orders/{id}/resume | Resume held order |
| POST | /api/v1/orders/{id}/transfer-table | Transfer to different table |
| POST | /api/v1/orders/{id}/cancel | Cancel order with reason |
| POST | /api/v1/orders/{id}/send-to-kitchen | Trigger KOT generation |
| POST | /api/v1/orders/{id}/split | Split order into multiple bills |
| GET | /api/v1/orders/active | Get all non-settled orders |

## Database Tables

```
orders:
  id (bigint, PK)
  outlet_id (bigint, FK)
  table_id (bigint, FK -> tables, nullable for takeaway/delivery)
  order_number (varchar 20, unique per outlet per day)
  order_type (enum: dine_in, takeaway, delivery)
  status (enum: draft, held, sent, preparing, ready, served, billed, settled, cancelled)
  customer_name (varchar, nullable)
  customer_phone (varchar, nullable)
  delivery_address (text, nullable)
  pax (int, nullable - number of guests)
  subtotal (decimal 10,2)
  tax_amount (decimal 10,2)
  discount_amount (decimal 10,2, default 0)
  rounding_amount (decimal 10,2, default 0)
  total (decimal 10,2)
  notes (text, nullable)
  held_at (timestamp, nullable)
  sent_to_kitchen_at (timestamp, nullable)
  ready_at (timestamp, nullable)
  served_at (timestamp, nullable)
  cancelled_at (timestamp, nullable)
  cancellation_reason (varchar, nullable)
  created_by (bigint, FK -> users)
  timestamps

order_items:
  id (bigint, PK)
  order_id (bigint, FK -> orders)
  menu_item_id (bigint, FK -> menu_items)
  variation_id (bigint, FK -> menu_item_variations, nullable)
  item_name (varchar 200) -- snapshot at order time
  quantity (decimal 8,2)
  base_price (decimal 10,2)
  variation_price (decimal 10,2, default 0)
  addons_price (decimal 10,2, default 0)
  line_total (decimal 10,2)
  special_instructions (text, nullable)
  gst_rate (decimal 5,2)
  gst_amount (decimal 10,2)
  status (enum: pending, sent, preparing, ready, served, cancelled)
  timestamps

order_item_addons:
  id (bigint, PK)
  order_item_id (bigint, FK -> order_items)
  addon_id (bigint, FK -> menu_item_addons)
  addon_name (varchar 200)
  price (decimal 10,2)
  timestamps

order_status_logs:
  id (bigint, PK)
  order_id (bigint, FK -> orders)
  status (varchar 50)
  changed_by (bigint, FK -> users)
  notes (text, nullable)
  timestamps
```

## Technical Notes
- **Backend**: `OrderController.php` with service layer `OrderService.php`. Order number generation: `ORD-{YYYYMMDD}-{SEQUENCE}` reset daily per outlet. Use DB transactions for order creation + items + addons. Event dispatch: `OrderCreated`, `OrderSentToKitchen`, `OrderStatusChanged` for WebSocket.
- **Frontend**: Main POS screen is the most complex component. Use Zustand for cart state management (`useCartStore`). Item search with debounce. Variation modal as separate component `VariationModal.tsx`. Cart panel component `CartPanel.tsx`.
- **Performance**: Menu items cached in Redis (invalidate on menu update). Item search uses Laravel Scout / Meilisearch for fast autocomplete.
- **KOT Integration**: When `send-to-kitchen` is called, fire `OrderSentToKitchen` event which generates KOT tickets (handled in RMS-010).
- **Order Number**: Auto-generate sequential, reset daily per outlet. Store last sequence in `outlet_daily_sequences` table.

## Subtasks
1. [ ] Create orders, order_items, order_item_addons, order_status_logs migrations
2. [ ] Build Order model with relationships (items, addons, table, outlet, user)
3. [ ] Build OrderService with create/update/hold/cancel/transfer logic
4. [ ] Implement order number generation (daily sequential per outlet)
5. [ ] Build OrderController with all API endpoints
6. [ ] Build React main POS screen (menu browser + cart panel)
7. [ ] Build variation/addon selection modal
8. [ ] Implement Zustand cart store with add/remove/modify items
9. [ ] Build running orders list page with filters and tabs
10. [ ] Build order detail page with status timeline
11. [ ] Build table transfer flow
12. [ ] Build order cancellation with reason modal
13. [ ] Add order split functionality
14. [ ] Implement order timer and rush hour flag
15. [ ] Write API feature tests (Pest)
16. [ ] Write integration tests for order lifecycle

## Testing Criteria
- [ ] Create dine-in order -> add items -> send to kitchen -> KOT generated
- [ ] Create takeaway order -> verify no table needed
- [ ] Add item after order sent -> modifier KOT generated
- [ ] Hold order -> verify in held list -> resume -> verify active
- [ ] Transfer table -> old table freed, new table occupied
- [ ] Cancel order -> cancellation reason logged -> table freed
- [ ] Split order into 2 bills -> each has correct items and totals
- [ ] Special instructions appear on KOT printout
- [ ] Tax calculation correct for intra-state (CGST+SGST) and inter-state (IGST)
