# RMS-052: Accounting Integration - Tally XML Export

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-052 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-014 (Billing & Payment), RMS-013 (Inventory & Recipes), RMS-015 (GST & Tax) |

## User Story
As a restaurant owner or accountant, I want to export all sales, purchase, payment, receipt, and journal vouchers from the RMS to Tally Prime via a Tally-compatible XML file on a schedule (daily/hourly) with email delivery, so that my books reconcile without manual data entry, and GST classifications and HSN codes flow through correctly.

## Description
Most Indian restaurant groups maintain books in Tally Prime -- the de-facto accounting standard for SMBs in India. Today, every closed shift's revenue, every purchase invoice, every expense receipt, and every GST filing period requires someone to manually type entries into Tally, leading to delays, transcription errors, and reconciliation pain at month-end.

This story delivers a Tally Prime-compatible XML export pipeline. The output format follows TDL (Tally Definition Language) and the standard `ALLLEDGERENTRIES` and `ALLINVENTORYENTRIES` structures expected by Tally's Import Data flow (Gateway of Tally > Import > Vouchers). The exporter generates voucher types: Sales (revenue from bills), Purchase (vendor invoices), Payment (cash/bank outflows), Receipt (cash/bank inflows), Journal (adjustments, GST, discounts, inventory write-offs), and Contra (inter-account transfers).

The mapping module (`TallyConfig`) lets the admin configure: company name, financial year, base currency, default sales/purchase ledgers, GST ledgers (CGST, SGST, IGST) per tax rate, HSN code per menu category or per item, voucher numbering prefix per type, and the chart of accounts to use. All mappings are stored in `tally_configurations` and applied at export time.

Exports can be triggered three ways: (1) Manual "Export to Tally" button generating an XML file in browser; (2) Scheduled auto-export (hourly / end-of-shift / daily EOD) writing to a configurable SFTP/S3/local path and emailing the file to the accountant; (3) API endpoint for Tally's "On Demand" pull. Each export creates a `tally_export_runs` record with file path, voucher count, totals, and an import status (Pending, Imported, Failed) that can be manually updated after the accountant imports the file.

GST classification is critical: each voucher carries HSN code, CGST/SGST/IGST ledgers and amounts split correctly for intra-state vs inter-state transactions (determined by outlet state vs customer state). Tally Prime's tax classification is matched 1:1 with the RMS GST rates (5%, 12%, 18%, 28%).

## Acceptance Criteria
- [ ] Tally Prime-compatible XML generated for Sales, Purchase, Payment, Receipt, Journal, Contra voucher types.
- [ ] Configurable company name, financial year, currency, base ledgers, voucher numbering per outlet.
- [ ] GST ledgers (CGST/SGST/IGST) auto-selected based on supply type (intra vs inter-state) per GSTIN.
- [ ] HSN code mapped per menu category (default) with per-item override; falls back to category HSN.
- [ ] Manual "Export Now" button generates XML file downloadable from browser.
- [ ] Scheduled auto-export at configurable cadence (hourly / end-of-shift / daily EOD) writes to SFTP / S3 / local path and emails accountant.
- [ ] Each export creates a `tally_export_runs` row with voucher count, totals, file path, status (Pending/Imported/Failed).
- [ ] Date range filter for export (today, yesterday, this week, this month, custom range).
- [ ] Optional voucher grouping: one voucher per bill, or summary voucher per shift.
- [ ] Chart of accounts (ledger) sync: auto-create missing ledgers in Tally on first export via "Create Ledger" XML wrapper.
- [ ] Import status update: accountant clicks "Mark Imported" or "Mark Failed" with notes; status reflected in dashboard.
- [ ] Failed XML export surfaces error and saves partial file for debugging.
- [ ] Dry-run mode generates XML for preview without persisting file or triggering email.
- [ ] Voucher numbering follows configurable prefix and continuous counter; gap detection warning.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Tally Configuration | /integrations/tally/config | Company, ledgers, GST, HSN mapping |
| Export Now | /integrations/tally/export | Manual export with date range and voucher types |
| Export History | /integrations/tally/history | List of all export runs |
| Schedule Settings | /integrations/tally/schedule | Cron configuration for auto-export |
| Voucher Preview | /integrations/tally/preview | Read-only preview of generated XML |

### Screen Details

**Tally Configuration (/integrations/tally/config)**
- Top section: Company Name (input), Financial Year From (date), Financial Year To (date), Base Currency (default INR), GSTIN (string).
- Ledger Mappings accordion:
  - Sales Ledgers: per GST rate dropdown (e.g., "Sales @5%", "Sales @12%", "Sales @18%", "Sales @28%").
  - Purchase Ledgers: per GST rate.
  - Payment Ledgers: dropdown of cash/bank ledgers.
  - Receipt Ledgers: dropdown.
  - Default Cash Ledger, Default Bank Ledger.
  - Round Off Ledger, Discount Allowed, Discount Received.
- GST Classification: CGST/SGST/IGST ledger names (defaults: "CGST Input/SGST Input/IGST Input" for purchase, "Output" for sales).
- HSN Mapping: table with columns Menu Category | HSN Code | Default GST Rate | Unit of Measure (NOS/KG/LT). Inline edit.
- Voucher Numbering: per voucher type prefix input and starting counter.
- Buttons: "Save Config", "Test Connection" (if SFTP/S3 configured), "Reset to Default".

**Export Now (/integrations/tally/export)**
- Layout: Form with date range, voucher types, and preview button.
- Date Range: Preset chips (Today, Yesterday, This Week, This Month, Custom).
- Voucher Types: Multi-select checkboxes (Sales, Purchase, Payment, Receipt, Journal, Contra).
- Grouping toggle: "One voucher per bill" vs "Summary per shift".
- Include toggle: "Include pending uncleared entries", "Include cancelled bills".
- Output Destination: Local download / SFTP / S3 (radio).
- Buttons: "Preview XML", "Generate & Download", "Generate & Email", "Generate & Push to SFTP".
- Right panel: Live summary card (estimated voucher count, sales total, purchase total, tax totals).

**Export History (/integrations/tally/history)**
- Table columns: Export ID | Date Range | Voucher Count | Sales Total | Tax Total | Status | File Size | Created By | Created At | Actions.
- Status badge: Pending (yellow), Imported (green), Failed (red), Partial (orange).
- Row actions: "Download XML", "Mark Imported", "Mark Failed", "View Vouchers", "Resend Email".
- Filters: Status, Date Range, Voucher Types, Outlet.
- Top: "Export Selected" bulk action.

**Schedule Settings (/integrations/tally/schedule)**
- Toggle: "Enable Auto-Export" master switch.
- Cadence: Radio (Hourly, End of Shift, Daily EOD, Weekly, Monthly).
- Time picker: for daily/weekly/monthly.
- Recipients: Email list (chips).
- SFTP/S3 path (if not local).
- Test button: "Run Now" with last-configured schedule.

**Voucher Preview (/integrations/tally/preview)**
- Two-column layout: Form (left, date range + types) and XML preview (right).
- XML viewer: syntax-highlighted XML with line numbers; collapsible voucher nodes.
- Voucher count badge: "147 vouchers generated, ₹3,24,560 total".
- Buttons: "Download", "Copy XML", "Send to Tally" (saves as Pending export).

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/tally/config | Get Tally configuration for outlet |
| PUT | /api/v1/tally/config | Update configuration |
| GET | /api/v1/tally/hsn-mapping | List HSN mappings |
| PUT | /api/v1/tally/hsn-mapping | Bulk update HSN mappings |
| POST | /api/v1/tally/export/preview | Generate XML preview (no save) |
| POST | /api/v1/tally/export | Generate and persist export run |
| GET | /api/v1/tally/export/{id}/download | Download XML file |
| GET | /api/v1/tally/export-history | List export runs with filters |
| GET | /api/v1/tally/export-history/{id} | Get run detail |
| PATCH | /api/v1/tally/export-history/{id}/status | Update import status |
| POST | /api/v1/tally/export-history/{id}/resend | Resend email |
| GET | /api/v1/tally/schedule | Get schedule config |
| PUT | /api/v1/tally/schedule | Update schedule |
| POST | /api/v1/tally/test-sftp | Test SFTP connection |
| POST | /api/v1/tally/vouchers/lookup | Look up vouchers in date range |

## Database Tables

**tally_configurations**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets, unique)
- company_name (string)
- financial_year_from (date)
- financial_year_to (date)
- base_currency (string(3), default 'INR')
- gstin (string(15), nullable)
- sales_ledger_mapping_json (json) -- per GST rate ledger names
- purchase_ledger_mapping_json (json)
- payment_ledger_mapping_json (json)
- receipt_ledger_mapping_json (json)
- default_cash_ledger (string)
- default_bank_ledger (string)
- round_off_ledger (string, default 'Round Off')
- discount_allowed_ledger (string, default 'Discount Allowed')
- discount_received_ledger (string, default 'Discount Received')
- cgst_output_ledger (string, default 'CGST Output')
- sgst_output_ledger (string, default 'SGST Output')
- igst_output_ledger (string, default 'IGST Output')
- cgst_input_ledger (string, default 'CGST Input')
- sgst_input_ledger (string, default 'SGST Input')
- igst_input_ledger (string, default 'IGST Input')
- voucher_prefixes_json (json) -- `{sales: "S", purchase: "P", ...}`
- voucher_counters_json (json)
- auto_create_ledgers (boolean, default true)
- timestamps

**tally_hsn_mappings**
- id (bigIncrements, PK)
- menu_category_id (foreignId, menu_categories, nullable)
- menu_item_id (foreignId, menu_items, nullable)
- hsn_code (string)
- default_gst_rate (decimal(5,2))
- unit_of_measure (enum: NOS, KGS, LTR, MTR, OTH)
- description (string, nullable)
- timestamps
- index([menu_category_id])
- index([menu_item_id])

**tally_export_runs**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets)
- date_from (date)
- date_to (date)
- voucher_types_json (json) -- array of types
- grouping_mode (enum: per_bill, per_shift, default per_bill)
- voucher_count (unsignedInteger)
- sales_total (decimal(14,2), default 0)
- purchase_total (decimal(14,2), default 0)
- tax_total (decimal(14,2), default 0)
- file_path (string, nullable)
- file_size_bytes (unsignedInteger, nullable)
- file_checksum_sha256 (string(64), nullable)
- destination (enum: local, sftp, s3, email)
- status (enum: pending, imported, failed, partial, default pending)
- error_message (text, nullable)
- status_notes (text, nullable)
- emailed_to_json (json, nullable)
- created_by (foreignId, users)
- imported_at (timestamp, nullable)
- imported_by (foreignId, users, nullable)
- created_at, updated_at
- index([outlet_id, created_at])
- index([status, created_at])

**tally_voucher_mappings**
- id (bigIncrements, PK)
- export_run_id (foreignId, tally_export_runs)
- voucher_type (enum: sales, purchase, payment, receipt, journal, contra)
- source_type (string) -- `App\Models\Order`, `App\Models\PurchaseOrder`, etc.
- source_id (unsignedBigInteger)
- tally_voucher_number (string)
- ledger_entries_json (json) -- full ledger entry breakdown
- amount (decimal(14,2))
- tax_amount (decimal(14,2), default 0)
- hsn_code (string, nullable)
- supply_type (enum: intra_state, inter_state, null)
- created_at
- index([export_run_id])
- index([source_type, source_id])

## Technical Notes

**Laravel Backend:**
- `TallyExportService::generateXml($outlet, $dateRange, $voucherTypes, $grouping)` builds the XML using `spatie/array-to-xml` or direct `SimpleXMLElement` for Tally compatibility.
- XML structure follows Tally's expected format: `<ENVELOPE><HEADER><TALLYREQUEST>Import Data</TALLYREQUEST></HEADER><BODY><IMPORTDATA><REQUESTDESC><REPORTNAME>Vouchers</REPORTNAME></REQUESTDESC><REQUESTDATA>...</REQUESTDATA></IMPORTDATA></BODY></ENVELOPE>`.
- Voucher builder: `SalesVoucherBuilder`, `PurchaseVoucherBuilder`, `PaymentVoucherBuilder`, etc. Each produces Tally XML element with `<DATE>`, `<VOUCHERTYPENAME>`, `<VOUCHERNUMBER>`, `<PARTYLEDGERNAME>`, `<ALLLEDGERENTRIES.LIST>`, `<ALLINVENTORYENTRIES.LIST>`.
- Intra vs inter-state determined by `outlet.state` vs `customer.state` (for B2C) or `supplier.state` (for purchase); uses GSTIN's first 2 digits as state code.
- HSN lookup: per-item override → category default → null (warning if missing).
- `TallyConfigService::resolveLedgerForVoucher()` returns correct ledger per type/rate.
- `GenerateTallyExportJob` queued job writes file to disk/SFTP/S3, sends email, creates `tally_export_runs` record. For SFTP uses `league/flysystem-sftp-v3`.
- Scheduled task registered in `app/Console/Kernel.php`: `tally:auto-export` runs every hour, checks schedule config, dispatches per outlet.
- File stored in `storage/app/tally-exports/{outlet_id}/{run_id}.xml`; download via signed URL valid 7 days.
- Dry-run returns XML string in response without saving.
- Voucher number generation uses Redis INCR for atomic counter; preserves prefix.
- "Create missing ledgers" wraps voucher XML in `<CREATELEDGER.LIST>` block sent before vouchers on first export per company.

**React Frontend:**
- Configuration forms use React Hook Form + Zod for validation; HSN mapping table uses inline-editable rows.
- Export Now form submits, shows loading state, streams progress events (per voucher type completion).
- XML preview rendered with Prism.js syntax highlighting; collapsible voucher nodes.
- Export history table with row click to download; "Mark Imported" / "Mark Failed" inline buttons.
- Schedule settings with cron-like UI (presets + custom).
- Toast notifications on successful export and email delivery.
- Charts: export run history trend (voucher count, total over time).

## Subtasks
1. [ ] Create `tally_configurations`, `tally_hsn_mappings`, `tally_export_runs`, `tally_voucher_mappings` migrations and models
2. [ ] Build `TallyConfigService` for ledger/GST/HSN resolution
3. [ ] Implement voucher builder classes per type (Sales, Purchase, Payment, Receipt, Journal, Contra)
4. [ ] Build `TallyExportService::generateXml()` producing Tally-compatible XML
5. [ ] Implement HSN lookup with item override → category default fallback
6. [ ] Implement intra/inter-state GST classification based on GSTIN state codes
7. [ ] Build `GenerateTallyExportJob` with SFTP/S3/local destinations
8. [ ] Implement "Create missing ledgers" auto-create on first export
9. [ ] Build voucher numbering with Redis INCR and gap detection
10. [ ] Build Tally Configuration UI with all mapping sections
11. [ ] Build Export Now UI with preview, generate, download, email
12. [ ] Build Export History with filters, status updates, resend
13. [ ] Build Schedule Settings with cron-like UI
14. [ ] Implement email delivery with Mailgun/SES; attach XML
15. [ ] Implement dry-run mode for preview without persistence
16. [ ] Add scheduled command `tally:auto-export` registered in Kernel
17. [ ] Build TDL/Tally import sample test fixtures
18. [ ] Write tests for each voucher type, GST split, HSN fallback, intra/inter-state, file output, schedule trigger

## Testing Criteria
- [ ] Sales voucher XML parses successfully in Tally Prime import preview
- [ ] Intra-state bill splits into CGST + SGST ledgers; inter-state into IGST only
- [ ] HSN code from item override takes precedence over category default
- [ ] Date range "yesterday" exports exactly 24h of bills excluding future
- [ ] Scheduled daily EOD export runs at configured time and emails accountant
- [ ] SFTP upload succeeds to test server; failed upload marked with error
- [ ] "Mark Imported" updates status; accountant receives notification
- [ ] Dry-run returns XML without creating `tally_export_runs` record
- [ ] Voucher numbering is continuous with no duplicates within a financial year
- [ ] Round-off ledger used when bill total differs from line items + tax
- [ ] Cancelled bills excluded by default; flag toggles include
- [ ] Auto-create ledger block adds missing ledger to Tally on first export
