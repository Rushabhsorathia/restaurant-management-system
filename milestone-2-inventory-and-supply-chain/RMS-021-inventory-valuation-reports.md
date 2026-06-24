# RMS-021: Inventory Valuation Reports

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-021 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012 |

## User Story
As an owner, I want accurate inventory valuation reports, so that I can understand the financial health of my inventory and make informed purchasing decisions.

## Description
Inventory valuation reports provide financial visibility into the stock on hand. The system supports FIFO, LIFO, and Weighted Average costing methods. Reports include: total inventory value, stock aging (how long items have been in stock), slow-moving and dead stock identification, inventory P&L impact (how inventory changes affect profit), and category-wise value distribution.

## Acceptance Criteria
- [ ] Total inventory value report (current stock x valuation rate per material)
- [ ] Valuation method filter: FIFO / LIFO / Average / All
- [ ] Stock aging report: 0-30 days, 31-60, 61-90, 90+ days (by batch received date)
- [ ] Slow-moving items: no consumption in last 30 days but stock > 0
- [ ] Dead stock: no consumption in last 90 days
- [ ] Category-wise value distribution (pie chart)
- [ ] Inventory turnover ratio: COGS / Avg Inventory (how fast stock rotates)
- [ ] Month-end valuation snapshot (for accounting close)
- [ ] Export PDF / Excel
- [ ] Outlet-wise and consolidated views

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Inventory Valuation | /inventory/valuation | Total stock value report |
| Stock Aging | /inventory/aging | Age analysis by batch |
| Slow/Dead Stock | /inventory/slow-dead | Items not moving |
| Valuation Snapshot | /inventory/snapshot | Month-end snapshot |

### Screen Details

**Inventory Valuation (/inventory/valuation)**
- Date selector (valuation as of date)
- Valuation method tabs: FIFO / LIFO / Average
- Summary: Total Value, Total Items, Total Quantity
- Table: Material | Category | Qty | Valuation Rate | Total Value | % of Total
- Category-wise pie chart
- Export PDF/Excel buttons

**Stock Aging (/inventory/aging)**
- Buckets: 0-30 | 31-60 | 61-90 | 90+ days
- Table: Material | Batch | Qty | Rate | Value | Age (days) | Bucket
- Highlight: items in 90+ bucket (red)
- Summary by bucket: value per bucket

**Slow/Dead Stock (/inventory/slow-dead)**
- Two tabs: Slow-Moving (30 days no consumption) | Dead Stock (90 days)
- Table: Material | Qty | Value | Last Consumption Date | Days Since Last Use | Action (Mark for clearance)
- Total value of slow/dead stock

**Valuation Snapshot (/inventory/snapshot)**
- Monthly snapshots: select month -> closing valuation
- Trend chart: 12-month inventory value trend
- Compare: this month vs last month (increase/decrease)
- Download snapshot PDF for accounting

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/inventory/valuation | Total valuation report |
| GET | /api/v1/inventory/aging | Stock aging report |
| GET | /api/v1/inventory/slow-dead | Slow and dead stock |
| GET | /api/v1/inventory/turnover | Inventory turnover ratio |
| GET | /api/v1/inventory/snapshot | Monthly snapshots |
| POST | /api/v1/inventory/snapshot | Create month-end snapshot |

## Database Tables

```
inventory_valuation_snapshots:
  id (bigint, PK)
  outlet_id (bigint, FK)
  snapshot_date (date)
  total_value (decimal 14,2)
  total_items (int)
  method (enum: fifo, lifo, average)
  line_items (json) -- snapshot of each material value
  created_by (bigint, FK -> users)
  timestamps
```

## Technical Notes
- **Backend**: `InventoryReportController.php`. Valuation calculated from stock_movements + material_batches. FIFO: consume oldest batch first. Average: weighted avg stored on material. Snapshot: scheduled job on last day of month.

## Subtasks
1. [ ] Create inventory_valuation_snapshots migration
2. [ ] Build valuation report query (FIFO/LIFO/Average)
3. [ ] Build stock aging query
4. [ ] Build slow/dead stock identification
5. [ ] Build inventory turnover calculation
6. [ ] Implement monthly snapshot job
7. [ ] Build React valuation report with pie chart
8. [ ] Build aging report with bucket highlighting
9. [ ] Build slow/dead stock page
10. [ ] Build snapshot trend page
11. [ ] Write tests

## Testing Criteria
- [ ] Valuation: 5 materials with stock -> total value = sum of (qty x rate)
- [ ] FIFO: oldest batch consumed first -> remaining stock valued at newer rate
- [ ] Aging: batch from 45 days ago -> in 31-60 bucket
- [ ] Dead stock: material with 0 consumption in 90 days -> appears in dead list
- [ ] Snapshot: end of month -> total value captured -> trend chart shows 12 months
