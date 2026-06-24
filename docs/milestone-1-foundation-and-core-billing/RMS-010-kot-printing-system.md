# RMS-010: KOT (Kitchen Order Ticket) Printing System

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-010 |
| **Type** | Story |
| **Epic** | Core POS |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | High |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-008 |

## User Story
As a chef, I want to receive printed kitchen order tickets with clear item details and instructions, so that I can prepare the correct dishes for each table.

## Description
The KOT system bridges the POS and the kitchen. When an order is sent to kitchen, KOT(s) are generated and routed to the appropriate printer(s) or KDS screens based on item categories. A KOT contains: order number, table number, item names with quantities, variations, add-ons, special instructions, and a timestamp. Initial KOTs cover all items. Modifier KOTs cover items added later. Void KOTs cover cancelled items. Each KOT has a unique number for audit trail.

## Acceptance Criteria
- [ ] KOT auto-generates when order is sent to kitchen
- [ ] Items route to different printers based on category (e.g., beverages to bar printer, main course to kitchen printer)
- [ ] KOT format: Restaurant name, KOT number, Date/Time, Table/Order number, Item list with qty
- [ ] Special instructions printed prominently on KOT
- [ ] Variations and add-ons listed clearly under each item
- [ ] Modifier KOT generated when items added to existing order (shows only NEW items)
- [ ] Void KOT generated when items cancelled (shows cancelled items with reason)
- [ ] KOT can be reprinted with "REPRINT" watermark
- [ ] KOT sequential numbering per outlet per day
- [ ] Printer routing configuration per category in settings
- [ ] Offline fallback: if printer fails, KOT shows on screen for manual reading

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| KOT Preview | /pos/kot/:orderId | Preview KOT before printing |
| KOT History | /pos/kot/history | List of all KOTs with filters |
| Printer Routing Settings | /settings/printer-routing | Map categories to printers |
| KOT Format Settings | /settings/kot-format | Customize KOT print layout |

### Screen Details

**KOT Preview (/pos/kot/:orderId)**
- Shows what will be printed
- Routing info: "This KOT will print to: Kitchen Printer (192.168.1.50), Bar Printer (192.168.1.51)"
- Item list grouped by printer destination
- Print button, Cancel button

**Printer Routing Settings (/settings/printer-routing)**
- List of configured printers: Name, IP Address, Paper Size, Status (connected/offline), Edit/Delete
- Add Printer form: Name, IP Address, Port (default 9100), Paper Size (80mm/58mm), Categories (multi-select: Starters, Main Course, Beverages, Desserts...)
- Drag-to-reorder printer priority (fallback order if primary is offline)
- Test Print button per printer

**KOT Format Settings (/settings/kot-format)**
- Header text (restaurant name override)
- Show/hide: Table number, Order type, Waiter name, Pax count
- Font size for item names
- Special instruction formatting: Bold / Boxed / Underline
- Footer text
- Paper size: 80mm / 58mm

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/kots | Generate KOT(s) for order |
| GET | /api/v1/kots | List KOTs (filter: date, printer, type) |
| GET | /api/v1/kots/{id} | Get KOT details |
| POST | /api/v1/kots/{id}/reprint | Reprint KOT |
| POST | /api/v1/orders/{id}/modifier-kot | Generate modifier KOT for added items |
| POST | /api/v1/orders/{id}/void-kot | Generate void KOT for cancelled items |
| GET | /api/v1/printers | List configured printers |
| POST | /api/v1/printers | Add printer |
| PUT | /api/v1/printers/{id} | Update printer |
| DELETE | /api/v1/printers/{id} | Remove printer |
| POST | /api/v1/printers/{id}/test | Send test print |
| GET | /api/v1/kot-format | Get KOT format settings |
| PUT | /api/v1/kot-format | Update KOT format settings |

## Database Tables

```
kot_tickets:
  id (bigint, PK)
  outlet_id (bigint, FK)
  order_id (bigint, FK -> orders)
  kot_number (varchar 20, unique per outlet per day)
  kot_type (enum: initial, modifier, void, reprint)
  printer_id (bigint, FK -> printers, nullable)
  printed_at (timestamp)
  printed_by (bigint, FK -> users)
  is_reprint (boolean, default false)
  reprint_count (int, default 0)
  timestamps

kot_items:
  id (bigint, PK)
  kot_id (bigint, FK -> kot_tickets)
  order_item_id (bigint, FK -> order_items)
  item_name (varchar 200)
  quantity (decimal 8,2)
  variation_name (varchar, nullable)
  addon_names (json, nullable)
  special_instructions (text, nullable)
  category_name (varchar 100)
  timestamps

printers:
  id (bigint, PK)
  outlet_id (bigint, FK)
  name (varchar 100)
  ip_address (varchar 45)
  port (int, default 9100)
  paper_size (enum: 80mm, 58mm)
  is_online (boolean, default true)
  last_checked_at (timestamp, nullable)
  sort_order (int, default 0)
  timestamps

printer_categories:
  id (bigint, PK)
  printer_id (bigint, FK -> printers)
  category_id (bigint, FK -> categories)
  timestamps
```

## Technical Notes
- **Backend**: `KotController.php` + `KotService.php`. KOT generation triggered by `OrderSentToKitchen` event listener. KOT number: `KOT-{YYYYMMDD}-{SEQ}`. Printer communication via raw TCP socket (PHP `fsockopen` to printer IP:9100, send ESC/POS commands).
- **Printer Library**: Use `mike42/escpos-php` for ESC/POS command generation.
- **Routing**: When generating KOT, group items by category -> look up printer_categories mapping -> generate one KOT per printer destination.
- **Modifier KOT**: Compare current order items with last KOT items. New items only -> generate modifier KOT with "++ ADDITIONS ++" header.
- **Void KOT**: Items removed since last KOT -> generate void KOT with "-- CANCELLATION --" header.
- **Frontend**: Minimal UI (KOT is primarily a print artifact). KOT history for audit. Printer routing config page.

## Subtasks
1. [ ] Create kot_tickets, kot_items, printers, printer_categories migrations
2. [ ] Build Kot model with relationships
3. [ ] Build Printer model with category relationships
4. [ ] Install mike42/escpos-php package
5. [ ] Build KotService: generate, route by category, number assignment
6. [ ] Implement printer socket communication (TCP/ESC-POS)
7. [ ] Build OrderSentToKitchen event listener for auto-KOT generation
8. [ ] Implement modifier KOT (diff against last KOT)
9. [ ] Implement void KOT (cancelled items)
10. [ ] Implement reprint with watermark
11. [ ] Build printer CRUD and test print API
12. [ ] Build printer routing settings UI (category-to-printer mapping)
13. [ ] Build KOT history list page
14. [ ] Build KOT format settings page
15. [ ] Add printer offline fallback (KOT on screen)
16. [ ] Write tests for KOT generation and routing

## Testing Criteria
- [ ] Send order to kitchen -> KOT generated -> items correct
- [ ] Order with items from 3 categories -> routes to 2 different printers correctly
- [ ] Add item after KOT -> modifier KOT shows only new item with "++ ADDITIONS ++"
- [ ] Cancel item -> void KOT shows cancelled item with "-- CANCELLATION --"
- [ ] Reprint KOT -> shows "REPRINT" watermark, reprint_count incremented
- [ ] KOT number sequential per day per outlet
- [ ] Printer offline -> fallback to screen display
- [ ] Special instructions visible and formatted on KOT
- [ ] Multiple variations/addons listed clearly under each item
