# RMS-012: Raw Material & Stock Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-012 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-002, RMS-006 |

## User Story
As an inventory manager, I want to track all raw materials with current stock levels, units, and valuations, so that I always know what is available and what needs to be ordered.

## Description
The raw material management module is the foundation of the inventory system. It maintains a master list of all raw materials (ingredients) with their units of measurement, current stock levels, stock valuation (FIFO/Average), and stock adjustment history. Every stock movement (purchase in, consumption out, wastage, transfer) is tracked as a ledger entry. This module feeds into recipe management (auto-deduction), purchase orders, and low-stock alerts.

## Acceptance Criteria
- [ ] Manager can create raw materials with name, category, base unit, alternate units with conversion factors
- [ ] Each material has: SKU code, HSN code, GST rate, min stock level, max stock level, reorder level
- [ ] Stock tracking methods: FIFO, LIFO, Average Cost (configurable per material)
- [ ] Opening stock entry with batch number, quantity, rate, and expiry date
- [ ] Current stock = sum of all inward - sum of all outward (live calculation)
- [ ] Stock adjustment: increase/decrease with reason (physical count variance)
- [ ] Physical stock count / stock take module with variance report
- [ ] Material-wise stock ledger (all inward/outward transactions with running balance)
- [ ] Batch-wise tracking for expiry-prone items
- [ ] Material categories: Vegetables, Dairy, Meat, Spices, Dry Goods, Beverages, Packaging
- [ ] Multi-unit support: 1 Bag = 25 KG, 1 KG = 1000 GM (configurable conversion)
- [ ] Stock valuation report: total inventory value at any point in time

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Material Master List | /inventory/materials | All raw materials with stock levels |
| Add/Edit Material | /inventory/materials/create, /:id/edit | Create or edit material |
| Material Detail | /inventory/materials/:id | Stock ledger, batches, valuation |
| Stock Adjustment | /inventory/adjustments | Manual stock correction |
| Physical Stock Count | /inventory/stock-take | Stock take / cycle count |
| Stock Ledger | /inventory/materials/:id/ledger | Transaction history |

### Screen Details

**Material Master List (/inventory/materials)**
- Search bar: by name, SKU, category
- Filters: Category dropdown, Stock Status (All / Low / Out / Overstock), Location
- Table columns: Name | SKU | Category | Unit | Current Stock | Reorder Level | Status (badge: OK=green, Low=yellow, Out=red) | Rate (avg) | Total Value | Actions
- Export CSV button
- Bulk import CSV button (for first-time setup)

**Add/Edit Material Form**
- Material Name (required)
- SKU Code (auto-generated or manual, unique)
- Category (dropdown with "Add New" option)
- Sub-category (optional)
- Base Unit (dropdown: KG, GM, LTR, ML, PCS, BOX, BAG + custom)
- Alternate Units with conversion: e.g., "1 Bag = 25 KG"
- HSN Code (for GST)
- GST Rate (dropdown: 0%, 5%, 12%, 18%, 28%)
- Stock Tracking Method: FIFO / LIFO / Average
- Min Stock Level (number)
- Max Stock Level (number)
- Reorder Level (number, must be >= min stock)
- Default Supplier (dropdown, from RMS-015)
- Is Batch Tracked (checkbox - for expiry items)
- Is Perishable (checkbox)
- Shelf Life (days, if perishable)
- Image (optional)

**Stock Adjustment (/inventory/adjustments)**
- Select Material (searchable dropdown)
- Current Stock display (read-only)
- Adjustment Type: Increase / Decrease
- Quantity
- Unit (from material's units)
- Reason (dropdown: Physical Count / Breakage / Spoilage / Theft / Gift / Sample / Other)
- Notes (text)
- Manager approval (if quantity > threshold)
- Batch selection (if batch-tracked material)

**Physical Stock Count (/inventory/stock-take)**
- Create Stock Take: select location, category filter, assign counter person
- Stock Take Sheet: list of materials with system quantity (hidden during count) and actual count field
- Enter counted quantity for each line
- Submit -> System calculates variance (System Qty - Counted Qty)
- Variance report: Material | System Qty | Counted Qty | Variance | Variance Value | Action
- Approve -> auto-adjust stock with variance entries
- Print variance report

**Stock Ledger (/inventory/materials/:id/ledger)**
- Date range filter
- Table: Date | Type (Purchase/Consumption/Wastage/Transfer/Adjustment) | Reference | In Qty | Out Qty | Balance | Rate | Value
- Running balance shown
- Summary at top: Opening Stock, Total In, Total Out, Closing Stock, Current Value
- Export to Excel

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/materials | List materials (filter, search, paginate) |
| POST | /api/v1/materials | Create material |
| GET | /api/v1/materials/{id} | Get material detail |
| PUT | /api/v1/materials/{id} | Update material |
| DELETE | /api/v1/materials/{id} | Delete material (soft) |
| GET | /api/v1/materials/{id}/stock | Current stock + valuation |
| GET | /api/v1/materials/{id}/ledger | Stock ledger (date ranged) |
| POST | /api/v1/materials/{id}/adjust | Stock adjustment |
| POST | /api/v1/materials/{id}/opening-stock | Set opening stock |
| GET | /api/v1/material-categories | List categories |
| POST | /api/v1/material-categories | Create category |
| POST | /api/v1/stock-takes | Create stock take session |
| GET | /api/v1/stock-takes/{id} | Get stock take sheet |
| PUT | /api/v1/stock-takes/{id}/lines/{lineId} | Enter counted quantity |
| POST | /api/v1/stock-takes/{id}/submit | Submit for variance calculation |
| POST | /api/v1/stock-takes/{id}/approve | Approve and adjust stock |
| GET | /api/v1/stock-valuation | Total inventory valuation report |

## Database Tables

```
raw_materials:
  id (bigint, PK)
  outlet_id (bigint, FK, nullable -- null = shared/central)
  name (varchar 200)
  sku_code (varchar 50, unique)
  category_id (bigint, FK -> material_categories)
  sub_category (varchar 100, nullable)
  base_unit (varchar 20)
  hsn_code (varchar 10, nullable)
  gst_rate (decimal 5,2, default 0)
  tracking_method (enum: fifo, lifo, average)
  min_stock (decimal 12,3, default 0)
  max_stock (decimal 12,3, default 0)
  reorder_level (decimal 12,3, default 0)
  default_supplier_id (bigint, FK -> suppliers, nullable)
  is_batch_tracked (boolean, default false)
  is_perishable (boolean, default false)
  shelf_life_days (int, nullable)
  current_stock (decimal 12,3, default 0)
  avg_rate (decimal 10,2, default 0)
  current_value (decimal 12,2, default 0)
  is_active (boolean, default true)
  timestamps

material_categories:
  id (bigint, PK)
  name (varchar 100)
  parent_id (bigint, FK -> material_categories, nullable)
  sort_order (int, default 0)
  timestamps

material_units:
  id (bigint, PK)
  material_id (bigint, FK -> raw_materials)
  unit_name (varchar 20)
  conversion_factor (decimal 10,4) -- relative to base unit
  is_base (boolean, default false)
  timestamps

material_batches:
  id (bigint, PK)
  material_id (bigint, FK -> raw_materials)
  batch_number (varchar 50)
  quantity (decimal 12,3)
  rate (decimal 10,2)
  unit (varchar 20)
  purchase_date (date)
  expiry_date (date, nullable)
  supplier_id (bigint, FK -> suppliers, nullable)
  status (enum: active, consumed, expired, discarded)
  timestamps

stock_movements:
  id (bigint, PK)
  outlet_id (bigint, FK)
  material_id (bigint, FK -> raw_materials)
  batch_id (bigint, FK -> material_batches, nullable)
  movement_type (enum: purchase, consumption, wastage, transfer_in, transfer_out, adjustment, opening, stock_take)
  direction (enum: in, out)
  quantity (decimal 12,3)
  unit (varchar 20)
  rate (decimal 10,2)
  value (decimal 12,2)
  reference_type (varchar 50) -- PO, Order, WastageEntry, etc.
  reference_id (bigint, nullable)
  balance_after (decimal 12,3)
  notes (text, nullable)
  created_by (bigint, FK -> users)
  timestamps

stock_takes:
  id (bigint, PK)
  outlet_id (bigint, FK)
  category_id (bigint, FK -> material_categories, nullable)
  status (enum: draft, in_progress, submitted, approved, rejected)
  assigned_to (bigint, FK -> users)
  opened_at (timestamp)
  submitted_at (timestamp, nullable)
  approved_at (timestamp, nullable)
  approved_by (bigint, FK -> users, nullable)
  timestamps

stock_take_lines:
  id (bigint, PK)
  stock_take_id (bigint, FK -> stock_takes)
  material_id (bigint, FK -> raw_materials)
  system_quantity (decimal 12,3)
  counted_quantity (decimal 12,3, nullable)
  variance (decimal 12,3, nullable)
  variance_value (decimal 12,2, nullable)
  notes (text, nullable)
  timestamps

stock_adjustments:
  id (bigint, PK)
  outlet_id (bigint, FK)
  material_id (bigint, FK -> raw_materials)
  batch_id (bigint, FK -> material_batches, nullable)
  adjustment_type (enum: increase, decrease)
  quantity (decimal 12,3)
  unit (varchar 20)
  rate (decimal 10,2)
  reason (varchar 200)
  notes (text, nullable)
  approved_by (bigint, FK -> users)
  timestamps
```

## Technical Notes
- **Backend**: `MaterialController.php`, `StockMovementController.php`, `StockTakeController.php`. Service: `InventoryService.php` for stock operations (inward, outward, adjust). All stock changes go through StockMovement ledger for audit trail. Current stock is a denormalized field updated atomically with each movement.
- **Stock Valuation**: FIFO -> consume oldest batches first. Average -> weighted average on each inward. Store `avg_rate` on material, recalculate on purchase: `(current_value + purchase_value) / (current_qty + purchase_qty)`.
- **Stock Take**: System quantity is hidden during counting to prevent bias. Shown only after submission.
- **Frontend**: Material list with virtualized table (large datasets). Stock take entry optimized for mobile/tablet (big numeric inputs). Stock ledger with infinite scroll.

## Subtasks
1. [ ] Create all inventory-related migrations (materials, categories, units, batches, movements, stock_takes, adjustments)
2. [ ] Build RawMaterial model with relationships and accessor for stock status
3. [ ] Build MaterialCategory model (self-referencing tree)
4. [ ] Build StockMovement model with movement type scopes
5. [ ] Build InventoryService: inward(), outward(), adjust(), getBalance()
6. [ ] Implement FIFO/LIFO/Average stock valuation logic
7. [ ] Build MaterialController CRUD API
8. [ ] Build stock adjustment API with approval workflow
9. [ ] Build stock take module (create, enter, submit, approve)
10. [ ] Build stock ledger query API
11. [ ] Build stock valuation report API
12. [ ] Implement CSV import/export for materials
13. [ ] Build React material list page with filters and status badges
14. [ ] Build material create/edit form with unit conversion builder
15. [ ] Build stock adjustment page
16. [ ] Build stock take mobile-friendly entry page
17. [ ] Build stock ledger page with date filter and export
18. [ ] Write inventory feature tests
19. [ ] Test stock movement + balance consistency

## Testing Criteria
- [ ] Create material -> set opening stock 100 KG @ 50/KG -> current stock 100, value 5000
- [ ] Inward 50 KG @ 60/KG -> stock 150, avg rate recalculated
- [ ] Outward 30 KG (consumption) -> stock 120, correct batch consumed (FIFO)
- [ ] Stock adjustment: decrease 5 KG (spoilage) -> stock 115, movement logged
- [ ] Stock take: system 115, counted 112 -> variance -3 -> approve -> adjusted to 112
- [ ] Low stock alert triggered when current < reorder level
- [ ] Batch with expiry date shows warning when within 7 days
- [ ] Stock valuation report: total value matches sum of (stock * avg_rate) for all materials
