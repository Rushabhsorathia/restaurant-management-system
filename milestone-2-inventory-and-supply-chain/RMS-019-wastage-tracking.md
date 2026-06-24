# RMS-019: Wastage Tracking

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-019 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012 |

## User Story
As a chef, I want to log all food and material wastage with reasons, so that management can identify patterns and reduce losses.

## Description
Wastage tracking records every instance of material or prepared food being discarded. Each entry captures what was wasted, how much, why (spoilage, burn, over-prep, expiry, breakage, customer return), and the cost impact. The system generates wastage reports by item, reason, date, and staff member to identify recurring patterns and take corrective action.

## Acceptance Criteria
- [ ] Log wastage: select material/menu item, quantity, unit, reason, notes
- [ ] Wastage reasons: Spoilage, Burnt/Overcooked, Expired, Breakage, Over-preparation, Customer Return, Contamination, Theft/Suspicious, Other
- [ ] Cost auto-calculated from material avg_rate or recipe cost
- [ ] Approval required for high-value wastage (> threshold)
- [ ] Photo upload for evidence (optional)
- [ ] Batch-specific wastage (link to expiry)
- [ ] Wastage reports: by material, by reason, by date, by staff, by outlet
- [ ] Daily wastage summary on dashboard
- [ ] Wastage trend analysis (is it improving or worsening?)
- [ ] Wastage as % of consumption

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Log Wastage | /inventory/wastage/create | Quick wastage entry |
| Wastage List | /inventory/wastage | All wastage entries |
| Wastage Reports | /inventory/wastage/reports | Analytics dashboard |

### Screen Details

**Log Wastage (/inventory/wastage/create)**
- Type: Raw Material / Prepared Food
- Material/Item (searchable dropdown with current stock)
- Quantity + Unit
- Reason (dropdown)
- Sub-reason (conditional, e.g., Spoilage -> "Power outage", "Refrigerator failure")
- Batch number (if batch-tracked)
- Photo upload (drag-drop, optional)
- Notes
- Submit -> deducts from stock -> logs cost impact

**Wastage Reports (/inventory/wastage/reports)**
- Date range selector
- Summary cards: Total Wastage Value, Wastage % of Consumption, Entries Count
- Chart: Wastage by Reason (pie), Wastage Trend (line, 30 days)
- Table: Material | Qty Wasted | Value | Reason | % of Total Wastage
- Filter by: reason, material, staff, outlet

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/wastage | Log wastage entry |
| GET | /api/v1/wastage | List wastage entries |
| GET | /api/v1/wastage/{id} | Wastage detail |
| DELETE | /api/v1/wastage/{id} | Delete entry (manager only) |
| GET | /api/v1/wastage/report | Wastage analytics report |
| GET | /api/v1/wastage/summary | Daily summary

## Database Tables

```
wastage_entries:
  id (bigint, PK)
  outlet_id (bigint, FK)
  type (enum: raw_material, prepared_food)
  material_id (bigint, FK -> raw_materials, nullable)
  menu_item_id (bigint, FK -> menu_items, nullable)
  item_name (varchar 200)
  quantity (decimal 12,3)
  unit (varchar 20)
  rate (decimal 10,2)
  cost_value (decimal 12,2)
  reason (enum: spoilage, burnt, expired, breakage, over_preparation, customer_return, contamination, theft, other)
  sub_reason (varchar 200, nullable)
  batch_number (varchar 50, nullable)
  photo_path (varchar, nullable)
  notes (text, nullable)
  approved_by (bigint, FK -> users, nullable)
  created_by (bigint, FK -> users)
  timestamps
```

## Technical Notes
- **Backend**: `WastageController.php`. On create: call `InventoryService::outward()` with movement_type=wastage. Cost = quantity * avg_rate for raw, recipe cost for prepared.

## Subtasks
1. [ ] Create wastage_entries migration
2. [ ] Build WastageEntry model
3. [ ] Build WastageController with stock deduction
4. [ ] Build approval workflow for high-value entries
5. [ ] Build wastage report queries
6. [ ] Build React wastage entry form
7. [ ] Build wastage report dashboard with charts
8. [ ] Write tests

## Testing Criteria
- [ ] Log raw material wastage 2 KG -> stock reduced by 2 KG -> cost logged
- [ ] Log prepared food wastage -> recipe cost calculated -> logged
- [ ] Report: 5 entries total 500 -> wastage value 500
- [ ] Trend chart shows 30-day pattern
