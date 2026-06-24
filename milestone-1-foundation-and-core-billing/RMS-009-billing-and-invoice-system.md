# RMS-009: Billing & Invoice System

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-009 |
| **Type** | Story |
| **Epic** | Core POS |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-008 |

## User Story
As a cashier, I want to generate accurate GST-compliant bills with discounts, taxes, and flexible payment options, so that customers receive proper invoices and the restaurant stays tax-compliant.

## Description
The billing system converts a settled order into a formal invoice/bill. It handles Indian GST requirements (CGST/SGST for intra-state, IGST for inter-state), multiple discount types, complimentary items, bill holds, refunds, and customizable bill print formats. The bill is the legal financial document of the transaction and must be sequentially numbered, non-editable after settlement (modifications require credit notes).

## Acceptance Criteria
- [ ] Bill auto-generates from order with correct tax calculation (CGST+SGST or IGST)
- [ ] Tax rate determined by item HSN code and restaurant state vs customer state
- [ ] Discount types: Flat amount, Percentage, Combo offer, Happy Hour, Staff discount
- [ ] Multiple discounts can stack with configurable priority and max cap
- [ ] Complimentary items (free) with reason logging
- [ ] Bill can be HELD (printed without payment) for corporate accounts
- [ ] Bill SETTLE records payment and locks the bill
- [ ] Refund/Return: partial or full refund with reason, generates credit note
- [ ] Bill format customizable: restaurant logo, address, GSTIN, FSSAI, terms, footer
- [ ] Bill number sequential and non-editable after settlement
- [ ] Round-off option configurable (nearest rupee)
- [ ] Service charge (optional) configurable as percentage
- [ ] Export bill as PDF and print via thermal printer
- [ ] Optional: digital e-bill via SMS/WhatsApp link

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Bill Settlement | /pos/billing/:orderId | Bill preview + payment entry |
| Bill List | /billing/bills | All bills with filters |
| Bill Detail | /billing/bills/:id | Full bill view with print |
| Refund / Credit Note | /billing/bills/:id/refund | Process refund |
| Bill Format Settings | /settings/bill-format | Customize bill print layout |
| Held Bills | /billing/held | Unsettled corporate/due bills |

### Screen Details

**Bill Settlement Screen (/pos/billing/:orderId)**

Layout: Left 60% bill preview, right 40% payment panel

Left (Bill Preview):
- Restaurant header: Logo, Name, Address, GSTIN, FSSAI No., Phone
- Bill meta: Bill No, Date/Time, Table No, Order Type, Cashier name
- Item table: # | Item | Qty | Rate | Amount
- Tax breakdown: Taxable value, CGST (x%), SGST (x%), or IGST (x%)
- Subtotal, Discount (if any), Service Charge (if configured), Round-off
- Grand Total (large)
- Footer: Terms, Thank you message, QR code for digital bill (optional)

Right (Payment Panel):
- Grand Total display (large)
- Discount section:
  - "Add Discount" button -> opens modal:
    - Type: Flat / Percentage
    - Value input
    - Reason (dropdown: Happy Hour / Staff / Corporate / Promo / Birthday / Custom)
    - Apply to: Entire bill / Specific items
  - Active discounts displayed with remove option
- Complimentary button -> select items to mark free + reason
- Service Charge toggle (if configured) with editable percentage
- Round-off toggle (on/off)
- Payment buttons (navigate to RMS-011 for details)
- HOLD BILL button (yellow) - save without payment
- SETTLE BILL button (green) - record payment, lock bill, free table

**Bill Format Settings (/settings/bill-format)**
- Logo upload (max 1MB, PNG/JPG)
- Restaurant name, address line 1, address line 2, city, state, pincode
- GSTIN, FSSAI License No.
- Phone, Email
- Bill header text (custom message)
- Bill footer text (terms, thank you)
- Show/hide toggles: Logo, Customer name, Table number, Service charge column, HSN code column, QR code
- Paper size: 80mm thermal / 58mm thermal / A4
- Font size: Small / Medium / Large
- Live preview panel (right side)

**Refund / Credit Note**
- Original bill details (read-only)
- Refund type: Full / Partial
- If partial: select items to refund + quantities
- Refund reason (dropdown: Wrong order / Food quality / Service issue / Customer request / Other)
- Refund amount (auto-calculated or manual for partial)
- Refund method: Cash / Original payment method / Wallet
- Credit note number (auto-generated)
- Manager approval required (if amount > configured threshold)
- Print credit note button

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/bills | Generate bill from order |
| GET | /api/v1/bills | List bills (filter: date, status, type) |
| GET | /api/v1/bills/{id} | Get bill details |
| PATCH | /api/v1/bills/{id}/settle | Settle bill (record payment) |
| PATCH | /api/v1/bills/{id}/hold | Hold bill (unpaid) |
| POST | /api/v1/bills/{id}/discount | Apply discount |
| DELETE | /api/v1/bills/{id}/discount/{discountId} | Remove discount |
| POST | /api/v1/bills/{id}/complimentary | Mark items complimentary |
| POST | /api/v1/bills/{id}/refund | Process refund / credit note |
| GET | /api/v1/bills/{id}/print | Get print-ready bill data |
| GET | /api/v1/bills/{id}/pdf | Download bill as PDF |
| POST | /api/v1/bills/{id}/send-digital | Send bill via SMS/WhatsApp |
| GET | /api/v1/bill-format | Get bill format settings |
| PUT | /api/v1/bill-format | Update bill format settings |

## Database Tables

```
bills:
  id (bigint, PK)
  outlet_id (bigint, FK)
  order_id (bigint, FK -> orders)
  bill_number (varchar 30, unique per outlet per financial year)
  bill_date (timestamp)
  customer_id (bigint, FK -> customers, nullable)
  customer_name (varchar, nullable)
  customer_phone (varchar, nullable)
  customer_gstin (varchar 15, nullable)
  subtotal (decimal 12,2)
  discount_amount (decimal 12,2, default 0)
  discount_type (enum: flat, percentage, combo, null)
  service_charge_percentage (decimal 5,2, default 0)
  service_charge_amount (decimal 12,2, default 0)
  cgst_amount (decimal 12,2, default 0)
  sgst_amount (decimal 12,2, default 0)
  igst_amount (decimal 12,2, default 0)
  rounding_amount (decimal 12,2, default 0)
  grand_total (decimal 12,2)
  status (enum: draft, held, settled, refunded, partially_refunded)
  settled_at (timestamp, nullable)
  settled_by (bigint, FK -> users)
  timestamps

bill_items:
  id (bigint, PK)
  bill_id (bigint, FK -> bills)
  menu_item_id (bigint, FK)
  hsn_code (varchar 10)
  item_name (varchar 200)
  quantity (decimal 8,2)
  rate (decimal 10,2)
  taxable_value (decimal 12,2)
  gst_rate (decimal 5,2)
  gst_amount (decimal 12,2)
  total (decimal 12,2)
  is_complimentary (boolean, default false)
  complimentary_reason (varchar, nullable)
  timestamps

bill_discounts:
  id (bigint, PK)
  bill_id (bigint, FK -> bills)
  discount_type (enum: flat, percentage, combo, happy_hour, staff)
  value (decimal 10,2)
  amount (decimal 12,2)
  reason (varchar 200)
  applied_by (bigint, FK -> users)
  timestamps

credit_notes:
  id (bigint, PK)
  bill_id (bigint, FK -> bills)
  credit_note_number (varchar 30, unique)
  refund_type (enum: full, partial)
  refund_amount (decimal 12,2)
  reason (varchar 200)
  refund_method (enum: cash, original, wallet)
  approved_by (bigint, FK -> users)
  timestamps

bill_format_settings:
  id (bigint, PK)
  outlet_id (bigint, FK -> outlets)
  logo_path (varchar, nullable)
  show_logo (boolean, default true)
  show_customer_name (boolean, default true)
  show_table_number (boolean, default true)
  show_service_charge (boolean, default false)
  show_hsn_code (boolean, default false)
  show_qr_code (boolean, default false)
  paper_size (enum: 80mm, 58mm, A4)
  font_size (enum: small, medium, large)
  header_text (text, nullable)
  footer_text (text, nullable)
  enable_rounding (boolean, default true)
  default_service_charge (decimal 5,2, default 0)
  timestamps
```

## Technical Notes
- **Backend**: `BillController.php` + `BillService.php`. Bill number: `{PREFIX}{FY}{SEQUENTIAL}` e.g. `RST2526-000001`. Financial year format: Apr-Mar (India). Tax calculation: check if customer state == outlet state -> CGST+SGST, else IGST. Use `GST` service class.
- **Frontend**: Bill preview component `BillPreview.tsx` renders the exact bill layout. Payment panel component `PaymentPanel.tsx`. Bill format settings page with live preview.
- **Immutability**: Once `settled`, bill_items and amounts are locked. Any modification creates credit_notes. Use `$bill->lock()` pattern.
- **Print**: Thermal printer format via ESC/POS commands (integration in RMS-056). PDF generation via `barryvdh/laravel-dompdf`.
- **FSSAI**: Required field on bill by Indian law. Store at outlet level.

## Subtasks
1. [ ] Create bills, bill_items, bill_discounts, credit_notes, bill_format_settings migrations
2. [ ] Build Bill model with all relationships
3. [ ] Build BillService: generate from order, tax calc, discount logic
4. [ ] Implement bill number generation (financial year sequential)
5. [ ] Build GST calculation service (CGST/SGST/IGST determination)
6. [ ] Build discount engine (flat, percentage, combo, stacking logic)
7. [ ] Build complimentary items with reason logging
8. [ ] Build bill hold/settle workflow
9. [ ] Build refund/credit note system with approval
10. [ ] Build bill format settings page with live preview
11. [ ] Install dompdf, implement PDF download
12. [ ] Build React bill settlement screen (preview + payment)
13. [ ] Build discount modal component
14. [ ] Build refund flow UI
15. [ ] Build bill list page with filters
16. [ ] Implement digital bill via SMS/WhatsApp link
17. [ ] Write feature tests for billing scenarios
18. [ ] Test GST calculation accuracy (compare with manual calc)

## Testing Criteria
- [ ] Generate bill from dine-in order -> correct CGST+SGST amounts
- [ ] Generate bill from delivery to different state -> correct IGST amount
- [ ] Apply flat discount -> grand total reduces by exact amount
- [ ] Apply percentage discount -> correct percentage off subtotal
- [ ] Mark item complimentary -> amount zeroed, reason logged
- [ ] Hold bill -> status held -> settle later -> status settled
- [ ] Full refund -> credit note generated, bill status refunded
- [ ] Partial refund -> select items, correct amount, status partially_refunded
- [ ] Bill number sequential with no gaps
- [ ] Print format matches thermal printer output
- [ ] Rounding applied correctly (nearest rupee)
