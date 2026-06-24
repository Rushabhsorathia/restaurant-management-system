# RMS-014: Purchase Order Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-014 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012, RMS-015 |

## User Story
As a purchase manager, I want to create and track purchase orders to suppliers, so that inventory is replenished on time with proper rate tracking and goods receipt verification.

## Description
The purchase order (PO) module manages the procurement lifecycle. A PO is created listing required materials with quantities and negotiated rates, sent to a supplier, goods are received (full or partial), and the PO is closed. The system tracks PO status, handles rate comparison across suppliers, supports approval workflows for high-value orders, and generates GRN (Goods Receipt Note) which auto-updates inventory stock.

## Acceptance Criteria
- [ ] Manager can create PO with supplier, line items (material, qty, unit, rate), expected delivery date
- [ ] PO auto-suggests reorder quantities based on min/max stock levels
- [ ] PO number sequential per outlet per financial year (e.g., PO-2526-00001)
- [ ] Rate auto-fills from last purchase rate or supplier contract rate
- [ ] Rate comparison: see last 3 purchase rates for same material from different suppliers
- [ ] PO approval workflow: PO > threshold requires manager approval
- [ ] Goods Receipt (GRN): receive full or partial delivery
- [ ] Partial receipt: remaining qty stays open for next delivery
- [ ] GRN auto-updates inventory stock (inward via InventoryService)
- [ ] Rate variance alert: if received rate > PO rate by X%, flag for review
- [ ] Quality check on receipt: accept/reject/return items
- [ ] PO status: Draft -> Pending Approval -> Approved -> Sent -> Partial -> Received -> Closed -> Cancelled
- [ ] PO print/email/WhatsApp to supplier
- [ ] PO reports: pending POs, overdue POs, supplier-wise PO history

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| PO List | /inventory/purchase-orders | All POs with status filters |
| Create PO | /inventory/purchase-orders/create | PO entry form |
| PO Detail | /inventory/purchase-orders/:id | Full PO view with GRN |
| Goods Receipt | /inventory/purchase-orders/:id/receive | GRN entry |
| Rate Comparison | /inventory/rate-comparison | Compare supplier rates |

### Screen Details

**PO List (/inventory/purchase-orders)**
- Filters: Status (Draft/Pending/Sent/Partial/Received/Closed), Supplier, Date Range
- Table: PO Number | Date | Supplier | Items | Total Amount | Status (badge) | Expected Delivery | Actions
- Status badges: Draft (grey), Pending Approval (yellow), Sent (blue), Partial (orange), Received (green), Closed (dark green), Cancelled (red)
- Overdue indicator: red text if expected delivery < today and not received

**Create PO (/inventory/purchase-orders/create)**
- Supplier dropdown (searchable, shows outstanding balance)
- Expected Delivery Date (date picker)
- Auto-fill button: "Add Low Stock Items" -> populates line items from reorder list
- Line items table:
  - Material (searchable dropdown with current stock display)
  - Quantity (number)
  - Unit (dropdown from material units)
  - Rate (auto-fill last rate, editable)
  - Amount (auto-calculated)
  - GST Rate (from material)
  - GST Amount
  - Total (amount + GST)
  - "Add Line" button
- Subtotal, Total GST, Grand Total
- Notes/Instructions textarea
- Delivery address (default: outlet address, editable)
- Approval indicator: "This PO requires approval from [Manager Name] (amount > 50,000)"
- Save as Draft / Submit for Approval / Direct Send (if below threshold)

**Goods Receipt (/inventory/purchase-orders/:id/receive)**
- PO summary header (read-only)
- Line items with: Ordered Qty | Already Received | Balance | Receiving Now (input) | Rate | Batch No. | Expiry Date | Quality (Accept/Reject)
- Receive mode: Full Delivery / Partial Delivery
- Delivery challan/invoice number
- Vehicle number (for logistics)
- Supplier invoice reference + upload (photo/PDF)
- Rate variance display: "PO Rate: 50, Invoice Rate: 55, Variance: +10% (FLAG)"
- Submit -> generates GRN -> updates inventory -> creates payable entry
- Short close option: "Mark remaining as cancelled"

**PO Detail (/inventory/purchase-orders/:id)**
- PO header: Number, Date, Supplier, Status, Expected Delivery, Approved By
- Line items table (ordered vs received comparison)
- GRN history: list of all receipts against this PO with date, qty, GRN number
- Rate variance log
- Quality rejection log
- Timeline: Created -> Approved -> Sent -> GRN1 -> GRN2 -> Closed
- Action buttons: Print, Email, WhatsApp, Receive Goods, Cancel

**Rate Comparison (/inventory/rate-comparison)**
- Select material (search)
- Table: Supplier | Last 3 Rates | Average Rate | Last Purchase Date | Variance from lowest
- Best price highlighted
- "Create PO with this supplier" button

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/purchase-orders | List POs |
| POST | /api/v1/purchase-orders | Create PO |
| GET | /api/v1/purchase-orders/{id} | Get PO detail |
| PUT | /api/v1/purchase-orders/{id} | Update PO (only if Draft) |
| DELETE | /api/v1/purchase-orders/{id} | Delete PO (only if Draft) |
| PATCH | /api/v1/purchase-orders/{id}/status | Update PO status |
| POST | /api/v1/purchase-orders/{id}/submit | Submit for approval |
| POST | /api/v1/purchase-orders/{id}/approve | Approve PO |
| POST | /api/v1/purchase-orders/{id}/send | Mark as sent to supplier |
| POST | /api/v1/purchase-orders/{id}/receive | Create GRN (goods receipt) |
| POST | /api/v1/purchase-orders/{id}/short-close | Short close remaining |
| POST | /api/v1/purchase-orders/{id}/cancel | Cancel PO with reason |
| GET | /api/v1/purchase-orders/{id}/grns | List GRNs for PO |
| GET | /api/v1/rate-comparison | Rate comparison for material |
| GET | /api/v1/purchase-orders/suggested | Get suggested reorder PO |

## Database Tables

```
purchase_orders:
  id (bigint, PK)
  outlet_id (bigint, FK)
  po_number (varchar 30, unique)
  supplier_id (bigint, FK -> suppliers)
  status (enum: draft, pending_approval, approved, sent, partial, received, closed, cancelled)
  po_date (date)
  expected_delivery_date (date, nullable)
  subtotal (decimal 12,2)
  tax_amount (decimal 12,2)
  grand_total (decimal 12,2)
  notes (text, nullable)
  delivery_address (text)
  approved_by (bigint, FK -> users, nullable)
  approved_at (timestamp, nullable)
  sent_at (timestamp, nullable)
  closed_at (timestamp, nullable)
  cancellation_reason (text, nullable)
  created_by (bigint, FK -> users)
  timestamps

po_items:
  id (bigint, PK)
  purchase_order_id (bigint, FK -> purchase_orders)
  material_id (bigint, FK -> raw_materials)
  material_name (varchar 200) -- snapshot
  quantity_ordered (decimal 12,3)
  quantity_received (decimal 12,3, default 0)
  unit (varchar 20)
  rate (decimal 10,2)
  gst_rate (decimal 5,2)
  amount (decimal 12,2)
  gst_amount (decimal 12,2)
  total (decimal 12,2)
  timestamps

goods_receipt_notes:
  id (bigint, PK)
  purchase_order_id (bigint, FK -> purchase_orders)
  grn_number (varchar 30, unique)
  supplier_invoice_number (varchar 100)
  supplier_invoice_date (date, nullable)
  supplier_invoice_path (varchar, nullable) -- uploaded file
  vehicle_number (varchar 20, nullable)
  received_date (timestamp)
  received_by (bigint, FK -> users)
  status (enum: draft, posted, rejected)
  notes (text, nullable)
  timestamps

grn_items:
  id (bigint, PK)
  grn_id (bigint, FK -> goods_receipt_notes)
  po_item_id (bigint, FK -> po_items)
  material_id (bigint, FK -> raw_materials)
  quantity_received (decimal 12,3)
  quantity_rejected (decimal 12,3, default 0)
  rejection_reason (varchar 200, nullable)
  batch_number (varchar 50, nullable)
  expiry_date (date, nullable)
  rate (decimal 10,2) -- may differ from PO rate
  po_rate (decimal 10,2) -- for variance calculation
  rate_variance (decimal 10,2) -- received rate - po rate
  amount (decimal 12,2)
  quality_status (enum: accepted, partial, rejected)
  timestamps
```

## Technical Notes
- **Backend**: `PurchaseOrderController.php`, `GRNController.php`. PO number: `PO-{FY}-{SEQ}`. On GRN post: call `InventoryService::inward()` for each accepted item, create `stock_movements`, update material `avg_rate` and `current_stock`.
- **Approval Workflow**: PO amount > threshold (configurable in settings, default 50,000) -> status = pending_approval -> manager approves -> status = approved -> can send.
- **Rate Variance**: On GRN, compare received rate with PO rate. If variance > configured % (default 5%), create rate_variance_flag for review.
- **Frontend**: `PurchaseOrderList.tsx`, `CreatePO.tsx` with dynamic line items, `GoodsReceipt.tsx` with ordered vs received comparison.

## Subtasks
1. [ ] Create purchase_orders, po_items, goods_receipt_notes, grn_items migrations
2. [ ] Build PO model with supplier, items, GRNs relationships
3. [ ] Build GRN model with items
4. [ ] Build PO number generation
5. [ ] Build PO service: create, submit, approve, send, cancel
6. [ ] Build GRN service: receive goods, update inventory, rate variance check
7. [ ] Implement reorder suggestion (materials below reorder level)
8. [ ] Implement approval workflow
9. [ ] Build rate comparison query
10. [ ] Build PO print/email template
11. [ ] Build React PO list with status badges
12. [ ] Build create PO form with auto-fill reorder items
13. [ ] Build GRN entry page with ordered vs received
14. [ ] Build rate comparison page
15. [ ] Build PO detail with GRN history and timeline
16. [ ] Write PO and GRN tests

## Testing Criteria
- [ ] Create PO with 5 items -> submit -> approve (if > threshold) -> send
- [ ] Receive partial delivery -> PO status -> partial -> inventory updated for received qty
- [ ] Receive remaining -> PO status -> received -> inventory fully updated
- [ ] Rate variance: PO rate 50, GRN rate 55 -> flag +10% variance
- [ ] Quality reject: 100 ordered, 90 accepted + 10 rejected -> stock +90, rejection logged
- [ ] Cancel PO -> status cancelled, no stock impact
- [ ] Short close: 100 ordered, 80 received, close remaining 20 -> PO closed
- [ ] Rate comparison: material X from 3 suppliers -> shows last 3 rates each
- [ ] Auto-fill reorder: 5 materials below reorder level -> populated in PO
