# RMS-017: Stock Transfer Between Outlets

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-017 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012 |

## User Story
As a multi-outlet operator, I want to transfer stock between outlets directly, so that surplus inventory at one location can cover shortages at another without going through central kitchen.

## Description
Direct outlet-to-outlet stock transfers bypass the central kitchen for efficiency. Outlet A (surplus) transfers materials to Outlet B (shortage). The transfer goes through request -> approval -> dispatch -> transit -> receive workflow. Stock is deducted from sender on dispatch and added to receiver on confirmation. Transfer rates can be at cost or negotiated.

## Acceptance Criteria
- [ ] Outlet can create a transfer request to another outlet
- [ ] Receiving outlet must accept/confirm the transfer
- [ ] Sender dispatches -> stock deducted -> status: dispatched
- [ ] Receiver confirms -> stock added -> status: received
- [ ] In-transit tracking with expected arrival date
- [ ] Partial receipt: receive less than dispatched (shortage/damage)
- [ ] Transfer rate: at cost (avg_rate) or custom rate
- [ ] Transfer report: outlet-wise, material-wise, date range
- [ ] Rejection: receiver can reject transfer with reason

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Transfer List | /inventory/transfers | All inter-outlet transfers |
| Create Transfer | /inventory/transfers/create | New transfer request |
| Transfer Detail | /inventory/transfers/:id | Transfer with dispatch/receive tracking |

### Screen Details

**Create Transfer (/inventory/transfers/create)**
- From Outlet (auto-filled), To Outlet (dropdown)
- Expected Delivery Date
- Line items: Material (search) | Quantity | Unit | Rate (default: cost) | Amount
- Notes
- Submit -> receiver outlet gets notification

**Transfer Detail (/inventory/transfers/:id)**
- Header: Transfer#, From -> To, Date, Status, Total Value
- Items table: Dispatched | Received | Difference | Status
- Timeline: Requested -> Accepted -> Dispatched -> In Transit -> Received
- Dispatch button (sender), Receive button (receiver)
- Reject button (receiver, with reason)

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/stock-transfers | Create transfer |
| GET | /api/v1/stock-transfers | List transfers |
| GET | /api/v1/stock-transfers/{id} | Transfer detail |
| PATCH | /api/v1/stock-transfers/{id}/accept | Receiver accepts |
| PATCH | /api/v1/stock-transfers/{id}/reject | Receiver rejects |
| PATCH | /api/v1/stock-transfers/{id}/dispatch | Sender dispatches |
| PATCH | /api/v1/stock-transfers/{id}/receive | Receiver confirms receipt |

## Database Tables

```
stock_transfers:
  id (bigint, PK)
  transfer_number (varchar 30, unique)
  from_outlet_id (bigint, FK -> outlets)
  to_outlet_id (bigint, FK -> outlets)
  status (enum: requested, accepted, rejected, dispatched, in_transit, received, partial_received, cancelled)
  expected_delivery_date (date)
  total_value (decimal 12,2)
  notes (text, nullable)
  dispatch_date (timestamp, nullable)
  received_date (timestamp, nullable)
  created_by (bigint, FK -> users)
  timestamps

stock_transfer_items:
  id (bigint, PK)
  stock_transfer_id (bigint, FK -> stock_transfers)
  material_id (bigint, FK -> raw_materials)
  material_name (varchar 200)
  quantity_dispatched (decimal 12,3)
  quantity_received (decimal 12,3, nullable)
  unit (varchar 20)
  rate (decimal 10,2)
  amount (decimal 12,2)
  batch_number (varchar 50, nullable)
  timestamps
```

## Technical Notes
- **Backend**: `StockTransferController.php`. Dispatch -> `InventoryService::outward()` at sender. Receive -> `InventoryService::inward()` at receiver. Notification system alerts receiving outlet.

## Subtasks
1. [ ] Create stock_transfers, stock_transfer_items migrations
2. [ ] Build StockTransfer model and service
3. [ ] Implement dispatch/receive workflow with stock adjustments
4. [ ] Build React transfer list, create, and detail pages
5. [ ] Add notification on transfer request and dispatch
6. [ ] Write tests

## Testing Criteria
- [ ] Create transfer -> receiver accepts -> sender dispatches -> stock out at sender -> receiver receives -> stock in at receiver
- [ ] Partial receipt -> partial stock adjustment
- [ ] Reject transfer -> no stock impact
