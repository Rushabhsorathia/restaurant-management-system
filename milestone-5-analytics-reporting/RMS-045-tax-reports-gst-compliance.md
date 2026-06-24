# RMS-045: Tax Reports & GST Compliance

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-045 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-010 (Billing & Payment), RMS-014 (Order Management), RMS-041 (Sales Dashboard) |

## User Story
As a restaurant owner, accountant, or tax compliance officer, I want automated Indian GST reports — GSTR-1, GSTR-3B, HSN-wise summary, e-invoice IRN generation, and B2B/B2C split — so that I can file taxes accurately every month without manual spreadsheet work, and stay compliant with GSTN portal requirements.

## Description
This story delivers the complete tax compliance and reporting module for the restaurant management system, purpose-built for Indian GST (CGST/SGST/IGST) regulations. The Indian restaurant industry is subject to GST with specific rules: 5% GST on AC restaurants (no ITC), 12% on non-AC (with ITC), 18% on banquets/catering, and 28% on alcohol (separate state excise). Multi-outlet chains operating across state lines must split IGST (inter-state) from CGST/SGST (intra-state) for every invoice.

The module produces the three reports every Indian F&B business must file: **GSTR-1** (outward supplies — B2B invoice-wise, B2C state-wise summary, HSN-wise summary) in the exact JSON schema the GSTN portal accepts; **GSTR-3B** (summary of outward supplies, ITC claimed, tax payable) for monthly self-assessment; and **HSN-wise summary** showing quantity, value, and tax rate per HSN/SAC code. An **e-invoice IRN generation** workflow integrates with the GSP/ASP (ClearTax, Masters India, Cygnet) to generate IRN + QR code for B2B invoices above the mandatory threshold (currently ₹5 crore turnover, expanding to ₹1.5 crore), storing the signed IRN and Ack number on each invoice.

The module supports **period comparison** (month-on-month, quarter-on-quarter) for tax liability trends, a **tax payment register** tracking challan numbers, CIN, deposit dates, and amounts paid, and accountant-friendly **exports to Tally XML, Excel, and PDF** formats. B2B vs B2C split auto-classifies invoices by customer GSTIN presence. Reverse charge (RCM) transactions (rent, professional fees) are tracked separately. The module respects the multi-outlet architecture from RMS-043, allowing per-outlet or consolidated reports. All reports respect fiscal period boundaries and lock periods once returns are filed (no retroactive editing of invoices that have flowed into a filed return).

## Acceptance Criteria
- [ ] System generates GSTR-1 JSON in the exact GSTN portal schema with B2B, B2C, HSN summary, and document-issue sections for any selected month
- [ ] System generates GSTR-3B summary with Table 3.1 (outward supplies), Table 4 (ITC claimed), and Table 6.1 (tax payable) auto-computed
- [ ] HSN-wise summary report shows HSN code, description, UQC, total quantity, taxable value, CGST, SGST, IGST, and cess per item category
- [ ] E-invoice IRN generation integrates with at least one GSP (ClearTax/Masters India) and stores IRN, Ack no., signed QR, and signed JSON on each B2B invoice
- [ ] B2B vs B2C split auto-classifies: invoice with customer GSTIN → B2B; without → B2C; B2C further split by state and place-of-supply
- [ ] Intra-state invoices split tax into CGST + SGST; inter-state invoices tax becomes IGST based on restaurant state vs customer state
- [ ] Tax payment register tracks challan number, CIN, deposit date, amount, and tax head (CGST/SGST/IGST/Cess)
- [ ] Period comparison view shows current vs previous month/quarter tax liability with variance percentage
- [ ] Export to Tally XML format follows Tally ERP 9 voucher schema for one-click import into accountant's Tally
- [ ] Export to Excel produces multi-sheet workbook: GSTR-1, GSTR-3B, HSN summary, payment register, raw invoices
- [ ] Export to PDF generates signed, paginated report with company letterhead, GSTIN, and period header
- [ ] Filed-return lock: once a period is marked "filed", invoice edits in that period require a credit/debit note workflow
- [ ] Multi-outlet consolidated view aggregates tax across all outlets; per-outlet view available for individual filing
- [ ] Reverse charge transactions (rent, legal fees, import of services) tracked separately with RCM flag
- [ ] All tax report downloads and e-invoice IRN requests are logged with user, timestamp, and IP for audit

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Tax Dashboard | /analytics/tax | Overview of tax liability, ITC, and upcoming filing deadlines |
| GSTR-1 Report | /analytics/tax/gstr1 | GSTR-1 generation with B2B/B2C/HSN sections and JSON download |
| GSTR-3B Report | /analytics/tax/gstr3b | Monthly summary with auto-computed tax payable |
| E-Invoice Manager | /analytics/tax/e-invoice | IRN generation queue, status tracker, and bulk retry |
| Tax Payment Register | /analytics/tax/payments | Challan/CIN tracker and reconciliation view |

### Screen Details

**Tax Dashboard (/analytics/tax)**
- Layout: Top KPI strip + period selector + filing-deadline banner + 4 panels (GSTR-1, GSTR-3B, E-Invoice, Payments).
- Top KPI strip: 4 cards — Current Month Tax Liability, ITC Available, Last Filed Period, Days to Next Deadline.
- Period selector: Month picker with "As of" date; outlet selector (multi-select for consolidated view).
- Filing-deadline banner: Red alert for periods due in <7 days, yellow for <15 days.
- GSTR-1 panel: Mini summary (B2B count, B2C count, total taxable value, total tax) with "View Report" and "Download JSON" buttons.
- GSTR-3B panel: Tax payable, ITC claimed, payable after ITC; "Generate" and "Download" buttons.
- E-Invoice panel: Pending IRN count, Failed count, Last 24hr generated count; "View Queue" button.
- Payments panel: Last 5 challans with CIN, date, amount; "View Register" button.
- Right sidebar: Period comparison widget (current vs previous month tax payable with % delta).

**GSTR-1 Report (/analytics/tax/gstr1)**
- Layout: Tabbed sections (B2B Invoices | B2C Large | B2C Small | HSN Summary | Document Issue) + summary header + actions.
- Summary header: Period, Total Invoices, Total Taxable Value, Total Tax, Status (Draft/Filed).
- B2B Invoices tab: Table — GSTIN | Trade Name | Invoice No | Date | Taxable Value | CGST | SGST | IGST | Total Tax. Filter by GSTIN, search by invoice no.
- B2C Large tab: Table — State | Invoice No | Date | Place of Supply | Taxable Value | Tax. Aggregated by state.
- B2C Small tab: Aggregated state-wise summary for invoices < ₹2.5 lakh.
- HSN Summary tab: Table — HSN Code | Description | UQC | Qty | Taxable Value | CGST | SGST | IGST | Cess.
- Document Issue tab: Summary of issued vs cancelled invoices per series (invoice series ranges).
- Actions: "Download JSON" (GSTN portal format), "Download Excel", "Download PDF", "Mark as Filed" (locks period), "Preview JSON" (modal).
- Status pill: "Draft" (orange) or "Filed" (green, locked icon). "Mark as Filed" requires ARN/acknowledgment number input.

**GSTR-3B Report (/analytics/tax/gstr3b)**
- Layout: Auto-computed summary tables in 3.1, 4, 5, 6, 8 format + filing actions.
- Table 3.1: Outward supplies — Nature (Taxable/Exempt/Nil/RCM) | Taxable Value | IGST | CGST | SGST | Cess.
- Table 4: Eligible ITC — Type (Imports, Inputs, Capital Goods, ISD, Others) | IGST | CGST | SGST | Cess.
- Table 5: Exempt, nil and non-GST inward supplies.
- Table 6.1: Tax payable summary (computed: 3.1 tax - 4 ITC, broken by tax head).
- Table 8: TDS/TCS credit (if any).
- Right panel: Period selector, "Download PDF" / "Download Excel" / "File via GSTN" (if API integrated).
- "Mark as Filed" with acknowledgement number; locks period; shows locked-by and lock timestamp.

**E-Invoice Manager (/analytics/tax/e-invoice)**
- Layout: Top filter bar + queue table + side panel for selected invoice detail.
- Filter bar: Date range, status (Pending/Generated/Failed/Cancelled), invoice number search, GSTIN search, outlet.
- Queue table: Invoice No | Date | Customer GSTIN | Customer Name | Taxable Value | Total | IRN | Ack No | Status | Action.
- Status: Pending (yellow spinner), Generated (green with IRN), Failed (red with error reason), Cancelled (grey).
- Action per row: "Generate IRN" (calls GSP API), "Cancel IRN" (within 24hr window), "Retry" (if failed), "Download IRN JSON" / "Download QR".
- Side panel (drawer): Full invoice detail + IRN metadata (IRN, Ack No, Ack Date, Signed QR, Signed JSON) + raw GSP response.
- Bulk actions: Select multiple pending invoices → "Generate IRN for Selected" (queue batch job).
- E-Invoice Settings tab: GSP provider selection (ClearTax/Masters India/Cygnet), API credentials, sandbox toggle, IRN cancellation window warning.

**Tax Payment Register (/analytics/tax/payments)**
- Layout: Top filter bar + challan register table + reconciliation panel.
- Filter bar: Period range, tax head (CGST/SGST/IGST/Cess), status (Paid/Unpaid/Reconciled), challan number.
- Challan table: Date | Challan No | CIN | Tax Head | Period | Amount | Status | Action.
- "Add Challan" button: Modal form (challan no, CIN, deposit date, tax head, amount, period, bank reference, screenshot upload).
- Reconciliation panel: Auto-match payments to GSTR-3B tax liability per head; show matched/unmatched with delta.
- "Mark as Reconciled" per row (manager approval); "Export Register" (Excel/PDF).

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/analytics/tax/dashboard | Get tax dashboard summary, ITC, deadlines |
| GET | /api/v1/analytics/tax/gstr1 | Get GSTR-1 data for period (B2B, B2C, HSN, doc-issue) |
| GET | /api/v1/analytics/tax/gstr1/export | Export GSTR-1 as JSON/Excel/PDF (query: format, period, outlet_ids[]) |
| GET | /api/v1/analytics/tax/gstr3b | Get GSTR-3B computed summary (3.1, 4, 5, 6, 8) |
| GET | /api/v1/analytics/tax/gstr3b/export | Export GSTR-3B (Excel/PDF) |
| GET | /api/v1/analytics/tax/hsn-summary | Get HSN-wise summary for period |
| GET | /api/v1/analytics/tax/period-comparison | Compare current vs previous period tax liability |
| GET | /api/v1/analytics/tax/payments | List tax payment register (challans) |
| POST | /api/v1/analytics/tax/payments | Add new challan entry |
| PUT | /api/v1/analytics/tax/payments/{id} | Update challan / mark reconciled |
| GET | /api/v1/analytics/tax/e-invoices | List e-invoice queue with status filter |
| POST | /api/v1/analytics/tax/e-invoices/{invoiceId}/generate-irn | Trigger IRN generation via GSP |
| POST | /api/v1/analytics/tax/e-invoices/{invoiceId}/cancel-irn | Cancel IRN within 24hr window |
| POST | /api/v1/analytics/tax/e-invoices/bulk-generate | Queue bulk IRN generation for selected invoices |
| POST | /api/v1/analytics/tax/periods/{id}/mark-filed | Lock a period as filed (requires ARN) |
| GET | /api/v1/analytics/tax/export-tally | Generate Tally XML export for period |

## Database Tables

**tax_invoice_metadata**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- invoice_id (foreignId, orders) — the source bill
- invoice_number (string, indexed)
- invoice_date (date, indexed)
- customer_id (foreignId, customers, nullable)
- customer_gstin (string(15), nullable, indexed) — null for B2C
- customer_name (string)
- customer_state_code (string(2), nullable) — for place-of-supply
- place_of_supply (string(2))
- supply_type (enum: intra_state, inter_state, export, deemed_export)
- reverse_charge (boolean, default false)
- taxable_value (decimal(12,2))
- cgst_amount (decimal(12,2), default 0)
- sgst_amount (decimal(12,2), default 0)
- igst_amount (decimal(12,2), default 0)
- cess_amount (decimal(12,2), default 0)
- total_tax (decimal(12,2), computed)
- invoice_total (decimal(12,2))
- hsn_code (string(8), nullable, indexed)
- hsn_description (string, nullable)
- uqc (string, nullable) — unit of measurement
- quantity (decimal(12,3), nullable)
- gst_rate (decimal(5,2))
- is_b2b (boolean, generated) — computed from customer_gstin not null
- is_cancelled (boolean, default false)
- irn (string(64), nullable, indexed) — e-invoice IRN
- irn_ack_no (string(20), nullable)
- irn_ack_date (datetime, nullable)
- irn_signed_qr (text, nullable)
- irn_signed_json (json, nullable)
- irn_status (enum: pending, generated, failed, cancelled, default pending)
- irn_error_reason (text, nullable)
- filed_period (string(7), nullable) — YYYY-MM
- is_locked (boolean, default false) — true after return filed
- timestamps
- indexes: [restaurant_id, invoice_date], [customer_gstin], [filed_period], [irn_status]

**tax_payment_challans**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets, nullable) — null = consolidated
- challan_number (string(20), nullable) — GSTN challan ID
- cin (string(32), nullable, indexed) — Challan Identification Number
- tax_head (enum: cgst, sgst, igst, cess, interest, penalty, late_fee)
- period (string(7)) — YYYY-MM for which tax is paid
- amount (decimal(12,2))
- deposit_date (date, indexed)
- bank_reference (string, nullable)
- mode (enum: neft, rtgs, internet_banking, over_counter)
- screenshot_path (string, nullable) — uploaded receipt
- is_reconciled (boolean, default false)
- reconciled_at (datetime, nullable)
- reconciled_by (foreignId, users, nullable)
- notes (text, nullable)
- timestamps
- unique([cin, tax_head])

**tax_period_locks**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets, nullable) — null = all outlets
- period (string(7)) — YYYY-MM
- return_type (enum: gstr1, gstr3b)
- arn (string(20), nullable) — Acknowledgment Reference Number from GSTN
- filed_at (datetime)
- filed_by (foreignId, users)
- locked_at (datetime)
- timestamps
- unique([restaurant_id, outlet_id, period, return_type])

**e_invoice_settings**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants, unique)
- gsp_provider (enum: cleartax, masters_india, cygnet, eigen)
- api_base_url (string)
- client_id (string, encrypted)
- client_secret (string, encrypted)
- gstin (string(15))
- sandbox_mode (boolean, default true)
- auto_generate_threshold (decimal(12,2), default 500000) — auto-IRN above this value
- irn_cancel_window_hours (unsignedInteger, default 24)
- created_at, updated_at

## Technical Notes

**Laravel Backend:**
- Controllers: `TaxController` (dashboard, gstr1, gstr3b, hsnSummary, periodComparison, markFiled), `TaxPaymentController` (index, store, update, reconcile), `EInvoiceController` (index, generateIrn, cancelIrn, bulkGenerate, settings, retry), `TallyExportController` (generate).
- Service: `GstService::computeGstr1($restaurantId, $period, $outletIds)` aggregates `tax_invoice_metadata` into the GSTN JSON schema (b2b, b2cLarge, b2cSmall, hsn, docIssue). Validates HSN codes, validates state codes.
- Service: `GstService::computeGstr3b($period)` builds Tables 3.1, 4, 5, 6, 8 from `tax_invoice_metadata` + `purchase_invoices` (for ITC).
- E-Invoice: `EInvoiceService::generateIrn($invoiceId)` constructs signed invoice payload, calls GSP API (ClearTax/Masters India sandbox first), stores IRN, ack, signed QR, signed JSON. Retry with exponential backoff (3 attempts). On 24hr expiry, cancellation endpoint called.
- Period lock: `MarkReturnFiled` job — sets `tax_invoice_metadata.filed_period`, `is_locked=true`, `tax_period_locks` record; observer on Order/Settlement then blocks mutations for locked periods (requires credit/debit note).
- RCM: Listener for `ExpenseRecorded` event creates RCM-flagged `tax_invoice_metadata` with reverse charge logic.
- Exports: `maatwebsite/excel` for multi-sheet Excel; `barryvdh/dompdf` for PDF; custom Tally XML builder following Tally ERP 9 voucher schema (`<TALLYMESSAGE><VOUCHER>...`).
- Queue: `GenerateGstrReport`, `BulkIrnGeneration`, `TallyExportJob` on `reports` queue with 60-min timeout for large multi-outlet consolidations.
- Cache: GSTR-1, GSTR-3B computed reports cached per period for 24hr; invalidated on invoice edit.
- Audit: All report downloads, IRN generations, and period locks logged via `LogTaxActivity` middleware.

**React Frontend:**
- Components: `TaxDashboardPage`, `Gstr1ReportPage`, `Gstr3bReportPage`, `EInvoiceManagerPage`, `IrnGenerationDrawer`, `PaymentRegisterPage`, `ChallanFormModal`, `PeriodComparisonWidget`, `TallyExportButton`, `MarkFiledModal`, `FilingDeadlineBanner`.
- State: Zustand `useTaxStore` for selected period, outlet filter, filing context.
- Charts: Recharts for period comparison (bar/line), tax-head breakdown (pie).
- E-Invoice queue: Polling every 30s for in-flight IRN generations; optimistic UI updates.
- JSON preview: Monaco editor (read-only) for GSTR-1 JSON with syntax highlighting and validation against GSTN schema.
- Bulk actions: Confirmation modal with invoice count, expected time, and "Generate for Selected" CTA.

## Subtasks
1. [ ] Create `tax_invoice_metadata`, `tax_payment_challans`, `tax_period_locks`, `e_invoice_settings` migrations and models
2. [ ] Implement `TaxObserver` that writes `tax_invoice_metadata` on every finalized order with proper HSN, GST rate, CGST/SGST/IGST split
3. [ ] Build `GstService::computeGstr1` producing GSTN-compliant JSON (b2b, b2cLarge, b2cSmall, hsn, docIssue)
4. [ ] Build `GstService::computeGstr3b` for Tables 3.1, 4, 5, 6, 8 with auto-ITC from purchase invoices
5. [ ] Integrate GSP API (ClearTax or Masters India) for IRN generation with sandbox first, then production
6. [ ] Build `EInvoiceService::generateIrn` and `cancelIrn` with retry logic and signed-QR storage
7. [ ] Build bulk IRN generation job with batching and per-invoice status tracking
8. [ ] Implement period lock mechanism via `MarkReturnFiled` job; enforce locked-period edit restrictions
9. [ ] Build `TaxPaymentController` for challan CRUD, reconciliation logic, and screenshot upload
10. [ ] Build Tally XML export following Tally ERP 9 voucher schema
11. [ ] Build Excel (multi-sheet) and PDF export for GSTR-1, GSTR-3B, HSN, payments
12. [ ] Implement period comparison service (current vs previous month, QoQ, YoY)
13. [ ] Build React TaxDashboardPage with KPIs, filing-deadline banner, and quick actions
14. [ ] Build React Gstr1ReportPage with tabs, JSON preview, and "Mark as Filed" flow
15. [ ] Build React Gstr3bReportPage with auto-computed tables
16. [ ] Build React EInvoiceManagerPage with queue, side drawer, and bulk action
17. [ ] Build React PaymentRegisterPage with challan form, reconciliation panel
18. [ ] Write tests for GST computation, IRN flow, period lock, exports, Tally XML

## Testing Criteria
- [ ] GSTR-1 JSON validates against the official GSTN schema (test fixture comparison)
- [ ] Intra-state invoice splits correctly into CGST + SGST at half the GST rate each
- [ ] Inter-state invoice produces IGST (no CGST/SGST) when customer state differs from restaurant state
- [ ] HSN summary totals reconcile to GSTR-1 b2b + b2c totals (taxable value and tax)
- [ ] IRN generation against sandbox GSP returns signed IRN and QR; stored correctly on invoice
- [ ] IRN cancellation succeeds within 24-hour window and fails gracefully after expiry
- [ ] Period lock prevents direct invoice edit; requires credit/debit note flow
- [ ] Tally XML import test: exported XML opens in Tally ERP and creates vouchers correctly
- [ ] Multi-outlet consolidated GSTR-1 matches sum of per-outlet GSTR-1 totals
- [ ] Tax payment challan reconciles correctly to GSTR-3B tax payable (per head) when amount equals liability
- [ ] Reverse charge transaction flagged correctly and excluded from outward supply totals
- [ ] All report downloads, IRN actions, and period locks appear in audit trail with user, IP, timestamp
