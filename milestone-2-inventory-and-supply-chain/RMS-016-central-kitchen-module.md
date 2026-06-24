# RMS-016: Central Kitchen Module

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-016 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | Medium |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012, RMS-013 |

## User Story
As a chain operator, I want a central kitchen that supplies semi-finished and finished goods to multiple outlets, so that quality is consistent and bulk preparation reduces per-unit cost.

## Description
The central kitchen module manages a commissary/central production facility that prepares base items (sauces, marinades, dough, pre-cooked items) and supplies them to outlets. Outlets raise indents (requests) for what they need. The central kitchen fulfills these through supply notes (transfers). The module handles production planning (what to produce based on outstanding indents), stock at central kitchen level, and transfer tracking.

## Acceptance Criteria
- [ ] Central kitchen setup as a special outlet type with its own stock
- [ ] Outlet can raise an indent (request) listing required items and quantities
- [ ] Central kitchen dashboard shows all pending indents by priority/date
- [ ] Central kitchen can partially fulfill indent (supply partial quantities)
- [ ] Supply note generated on fulfillment (like invoice for transfer)
- [ ] Stock deducted from central kitchen, added to receiving outlet
- [ ] Return damaged/expired stock from outlet to central kitchen
- [ ] Production planning: suggested production list based on outstanding indents + minimum stock
- [ ] Central kitchen has its own recipes (bulk recipes) for semi-finished goods
- [ ] Transfer transit tracking: dispatched -> in transit -> received
- [ ] Cost tracking: production cost per unit at central kitchen

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Central Kitchen Dashboard | /central-kitchen | Overview of indents, production, transfers |
| Indent List | /central-kitchen/indents | All indents from outlets |
| Create Indent | /central-kitchen/indents/create | Outlet raises request |
| Indent Detail | /central-kitchen/indents/:id | Indent with fulfillment status |
| Supply Note | /central-kitchen/supply-notes/:id | Transfer document |
| Production Plan | /central-kitchen/production | Suggested production list |
| Central Kitchen Stock | /central-kitchen/stock | Central stock levels |

### Screen Details

**Central Kitchen Dashboard (/central-kitchen)**
- Stats cards: Pending Indents (count + value), In Production (count), Dispatched Today (count), Low Stock Items (count)
- Pending indents table (top 5): Indent# | Outlet | Items | Requested Date | Priority | Status
- Today's dispatches: Outlet | Items | Supply Note# | Status (dispatched/transit/received)
- Production alerts: "3 items need production based on outstanding indents"

**Create Indent (/central-kitchen/indents/create)**
- Requesting Outlet (auto-filled if logged in as outlet)
- Required By Date
- Priority: Normal / Urgent / Emergency
- Line items: Material/Semi-finished Item | Quantity | Unit | Notes
- Auto-suggest: based on outlet consumption pattern + current stock
- Submit -> goes to central kitchen for fulfillment

**Indent Detail (/central-kitchen/indents/:id)**
- Header: Indent#, Outlet, Date, Priority, Status
- Items table: Requested | Supplied | Balance | Status (pending/partial/fulfilled)
- Supply notes linked to this indent
- Fulfill button -> opens supply note creation
- Timeline: Created -> Partial Supply -> Full Supply -> Closed

**Production Plan (/central-kitchen/production)**
- Auto-generated list: Item | Current Stock | Pending Indents | Min Buffer | Suggested Production Qty
- Based on: outstanding indents + minimum stock buffer - current stock
- Select items to produce -> create production batch
- Production batch: consume raw materials (recipe) -> produce semi-finished stock

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/indents | Create indent |
| GET | /api/v1/indents | List indents (filter: outlet, status) |
| GET | /api/v1/indents/{id} | Indent detail |
| PATCH | /api/v1/indents/{id}/status | Update status |
| POST | /api/v1/indents/{id}/fulfill | Create supply note (partial/full) |
| GET | /api/v1/supply-notes | List supply notes |
| GET | /api/v1/supply-notes/{id} | Supply note detail |
| PATCH | /api/v1/supply-notes/{id}/dispatch | Mark as dispatched |
| PATCH | /api/v1/supply-notes/{id}/receive | Mark as received (outlet confirms) |
| POST | /api/v1/supply-notes/{id}/return | Return damaged stock |
| GET | /api/v1/central-kitchen/production-plan | Get suggested production |
| POST | /api/v1/central-kitchen/production-batch | Create production batch |
| GET | /api/v1/central-kitchen/stock | Central kitchen stock levels |

## Database Tables

```
indents:
  id (bigint, PK)
  indent_number (varchar 30, unique)
  from_outlet_id (bigint, FK -> outlets) -- requesting outlet
  to_outlet_id (bigint, FK -> outlets) -- central kitchen
  status (enum: draft, submitted, partial, fulfilled, closed, cancelled)
  priority (enum: normal, urgent, emergency)
  required_by_date (date)
  notes (text, nullable)
  created_by (bigint, FK -> users)
  timestamps

indent_items:
  id (bigint, PK)
  indent_id (bigint, FK -> indents)
  material_id (bigint, FK -> raw_materials)
  material_name (varchar 200)
  quantity_requested (decimal 12,3)
  quantity_supplied (decimal 12,3, default 0)
  unit (varchar 20)
  notes (text, nullable)
  timestamps

supply_notes:
  id (bigint, PK)
  supply_note_number (varchar 30, unique)
  indent_id (bigint, FK -> indents, nullable)
  from_outlet_id (bigint, FK -> outlets) -- central kitchen
  to_outlet_id (bigint, FK -> outlets) -- receiving outlet
  status (enum: draft, dispatched, in_transit, received, partial_received, returned)
  dispatch_date (timestamp, nullable)
  received_date (timestamp, nullable)
  vehicle_number (varchar 20, nullable)
  driver_name (varchar 100, nullable)
  driver_phone (varchar 20, nullable)
  total_value (decimal 12,2)
  created_by (bigint, FK -> users)
  timestamps

supply_note_items:
  id (bigint, PK)
  supply_note_id (bigint, FK -> supply_notes)
  material_id (bigint, FK -> raw_materials)
  material_name (varchar 200)
  quantity_dispatched (decimal 12,3)
  quantity_received (decimal 12,3, nullable) -- confirmed at receiving end
  quantity_damaged (decimal 12,3, default 0)
  unit (varchar 20)
  rate (decimal 10,2) -- transfer rate
  amount (decimal 12,2)
  batch_number (varchar 50, nullable)
  timestamps

production_batches:
  id (bigint, PK)
  batch_number (varchar 30, unique)
  outlet_id (bigint, FK -> outlets) -- central kitchen
  product_material_id (bigint, FK -> raw_materials) -- what is being produced
  recipe_id (bigint, FK -> recipes)
  planned_quantity (decimal 12,3)
  actual_quantity (decimal 12,3, default 0)
  unit (varchar 20)
  status (enum: planned, in_progress, completed, cancelled)
  started_at (timestamp, nullable)
  completed_at (timestamp, nullable)
  wastage_percentage (decimal 5,2, default 0)
  created_by (bigint, FK -> users)
  timestamps
```

## Technical Notes
- **Backend**: `CentralKitchenController.php`, `IndentController.php`. Supply note dispatch -> stock_movement outward from central kitchen. Supply note receive -> stock_movement inward at outlet. Production batch -> consume raw materials via recipe -> produce semi-finished material stock.
- **Multi-outlet**: Central kitchen is an outlet with type=central_kitchen. Stock is tracked per outlet in raw_materials.outlet_id.
- **Transit Stock**: Between dispatch and receive, stock is "in transit" - deducted from sender but not yet in receiver.

## Subtasks
1. [ ] Create indents, indent_items, supply_notes, supply_note_items, production_batches migrations
2. [ ] Build Indent model with items and supply notes
3. [ ] Build SupplyNote model with items and status workflow
4. [ ] Build ProductionBatch model
5. [ ] Implement indent creation and fulfillment flow
6. [ ] Implement supply note dispatch/receive with stock adjustments
7. [ ] Implement return of damaged stock
8. [ ] Build production plan auto-generation
9. [ ] Build production batch with recipe consumption
10. [ ] Build React central kitchen dashboard
11. [ ] Build indent create/list/detail pages
12. [ ] Build supply note tracking page
13. [ ] Build production plan page
14. [ ] Write central kitchen tests

## Testing Criteria
- [ ] Outlet raises indent for 5 items -> central kitchen dashboard shows it
- [ ] Central kitchen fulfills 3 of 5 -> partial -> supply note -> stock transfers
- [ ] Outlet receives supply -> confirms -> stock added at outlet
- [ ] Damaged item return -> stock back at central kitchen, reduced at outlet
- [ ] Production plan: 2 items below buffer + pending indents -> suggests production
- [ ] Production batch: consume raw materials -> produce semi-finished -> stock updated
