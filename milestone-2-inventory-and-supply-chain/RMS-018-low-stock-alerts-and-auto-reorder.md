# RMS-018: Low Stock Alerts & Auto-Reorder

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-018 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | High |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012 |

## User Story
As an inventory manager, I want automatic alerts when materials fall below reorder levels, so that I never run out of critical ingredients during operations.

## Description
This module proactively monitors stock levels and triggers alerts when materials hit their reorder thresholds. Alerts go out via dashboard, email, and SMS. The system can auto-generate draft purchase orders for low-stock items based on suggested reorder quantities (calculated from consumption rate and lead time). Stock movement trends help predict when a material will run out.

## Acceptance Criteria
- [ ] Real-time check: when stock_movement reduces stock below reorder_level -> alert triggered
- [ ] Alert channels: dashboard notification, email, SMS (configurable per material)
- [ ] Auto-generate draft PO: collects all low-stock items, groups by default supplier, creates draft PO
- [ ] Suggested reorder quantity: based on avg daily consumption x lead time + buffer
- [ ] Stock-out prediction: "At current consumption, XYZ will run out in ~3 days"
- [ ] Alert snooze: dismiss alert for X hours/days (avoid spam)
- [ ] Alert history log: all triggered alerts with action taken
- [ ] Configurable alert levels: warning (approaching reorder), critical (at min stock), emergency (stock out)

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Low Stock Dashboard | /inventory/alerts | All active alerts |
| Alert Settings | /inventory/alerts/settings | Configure per-material alert prefs |
| Auto-Reorder Review | /inventory/auto-reorder | Review and approve auto-generated POs |

### Screen Details

**Low Stock Dashboard (/inventory/alerts)**
- Summary: X Warning, Y Critical, Z Out of Stock
- Table: Material | Current Stock | Reorder Level | Status (badge) | Days Until Stockout | Default Supplier | Action (Create PO / Snooze)
- Filter by severity level
- "Auto-Generate POs" button -> creates draft POs grouped by supplier

**Alert Settings (/inventory/alerts/settings)**
- Per material: enable/disable alerts
- Alert level thresholds: Warning (% of reorder), Critical (at reorder), Emergency (at min)
- Notification channels: Dashboard (always), Email, SMS
- Snooze duration options: 4h, 8h, 24h, until next stock-in

**Auto-Reorder Review (/inventory/auto-reorder)**
- List of draft POs auto-generated: Supplier | Items | Total Amount | Suggested Delivery Date
- Review line items, adjust quantities
- Approve -> submit PO / Delete draft

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/inventory/alerts | Active low stock alerts |
| GET | /api/v1/inventory/alerts/history | Alert history log |
| POST | /api/v1/inventory/alerts/{id}/snooze | Snooze alert |
| POST | /api/v1/inventory/alerts/{id}/dismiss | Dismiss alert |
| GET | /api/v1/inventory/alerts/settings | Alert configuration |
| PUT | /api/v1/inventory/alerts/settings | Update settings |
| POST | /api/v1/inventory/auto-reorder/generate | Generate draft POs for low stock |
| GET | /api/v1/inventory/stockout-prediction | Predict stockout dates |

## Database Tables

```
stock_alerts:
  id (bigint, PK)
  outlet_id (bigint, FK)
  material_id (bigint, FK -> raw_materials)
  alert_level (enum: warning, critical, emergency)
  stock_at_trigger (decimal 12,3)
  triggered_at (timestamp)
  status (enum: active, snoozed, dismissed, resolved)
  snoozed_until (timestamp, nullable)
  resolved_at (timestamp, nullable)
  action_taken (varchar 200, nullable)
  timestamps

stock_alert_settings:
  id (bigint, PK)
  outlet_id (bigint, FK)
  material_id (bigint, FK -> raw_materials)
  is_enabled (boolean, default true)
  warning_threshold_pct (decimal 5,2, default 120) -- 120% of reorder
  alert_channels (json) -- ["dashboard","email","sms"]
  snooze_default_hours (int, default 8)
  timestamps

stock_consumption_trends:
  id (bigint, PK)
  outlet_id (bigint, FK)
  material_id (bigint, FK -> raw_materials)
  period (enum: daily, weekly, monthly)
  avg_consumption (decimal 12,3)
  calculated_at (timestamp)
  timestamps
```

## Technical Notes
- **Backend**: `StockAlertService.php`. Event listener on `StockMovementCreated` -> check if material stock < reorder_level -> create alert. Scheduled job (hourly): recalculate consumption trends, predict stockout dates, trigger warning-level alerts.
- **Consumption Trend**: Query stock_movements (type=consumption) for last 30 days -> average per day. Stockout prediction: current_stock / avg_daily_consumption = days remaining.
- **Auto-Reorder**: For each material below reorder, calculate suggested qty: (avg_daily_consumption * lead_time_days * 1.5) - current_stock + min_stock. Group by default_supplier_id -> create draft PO per supplier.

## Subtasks
1. [ ] Create stock_alerts, stock_alert_settings, stock_consumption_trends migrations
2. [ ] Build StockAlert model and service
3. [ ] Implement alert trigger on stock movement
4. [ ] Build consumption trend calculator (scheduled job)
5. [ ] Implement stockout prediction
6. [ ] Build auto-reorder PO generation
7. [ ] Build snooze/dismiss functionality
8. [ ] Build email/SMS notification integration
9. [ ] Build React alerts dashboard
10. [ ] Build alert settings page
11. [ ] Build auto-reorder review page
12. [ ] Write tests

## Testing Criteria
- [ ] Stock drops below reorder_level -> alert created with correct level
- [ ] Consumption trend: 30 KG consumed in 30 days -> avg 1 KG/day -> 10 KG stock = 10 days
- [ ] Auto-reorder: 5 low items from 2 suppliers -> 2 draft POs created
- [ ] Snooze alert -> disappears for X hours -> reappears if still low
- [ ] Stock replenished -> alert auto-resolved
