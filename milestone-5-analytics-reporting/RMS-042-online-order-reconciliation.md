# RMS-042: Online Order Reconciliation

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-042 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-025 (Swiggy Integration), RMS-026 (Zomato Integration), RMS-027 (magicpin Integration) |

## User Story

As a restaurant owner or accountant, I want to reconcile online orders from food aggregators with settlement payouts, verify commission charges, and flag discrepancies automatically, so that I can ensure I am receiving the correct payments and not losing money to incorrect commission deductions or missing settlements.

## Description

The Online Order Reconciliation module is a financial control system that matches every online order received from food aggregators (Swiggy, Zomato, magicpin) against the settlement payouts reported by those aggregators. This module is critical for multi-channel restaurants where aggregator orders can represent 30-60% of total revenue and commission structures are complex and error-prone.

The system ingests settlement reports from each aggregator (via API download or manual CSV upload), automatically matches each payout line item to corresponding orders in the system, verifies that commission rates match contracted rates, and flags any discrepancies for manual review. Common discrepancies include incorrect commission percentage, missing orders in settlement, extra charges not agreed upon, and delayed payouts.

The reconciliation engine supports configurable auto-reconcile rules (e.g., auto-accept if discrepancy is under Rs. 10, auto-flag if commission exceeds 22%), generates detailed reconciliation reports per aggregator per period, and tracks chargebacks and disputes through resolution. This module typically saves restaurant owners 2-5% of online revenue by catching aggregation errors.

## Acceptance Criteria

- [ ] System can ingest settlement reports from Swiggy, Zomato, and magicpin via both API download and manual CSV/Excel upload
- [ ] Each settlement line item is automatically matched to the corresponding order in the system by aggregator order ID
- [ ] Commission percentage is verified against contracted rate per aggregator and flagged if it exceeds the agreed percentage
- [ ] Settlement reconciliation status is displayed as: Matched, Partially Matched, Unmatched, Discrepancy Flagged
- [ ] Discrepancy flagging highlights specific fields with mismatch (commission amount, payout amount, missing order, extra charge)
- [ ] Auto-reconcile rules are configurable per aggregator (auto-accept threshold, auto-flag threshold, commission cap)
- [ ] Payout tracking shows expected vs received payout amounts with date tracking and aging
- [ ] Chargeback and dispute tracking workflow captures reason, evidence upload, status (open/in-progress/resolved/won/lost), and resolution amount
- [ ] Reconciliation reports are generated per aggregator with period summary, order-level detail, and discrepancy listing
- [ ] Unmatched orders (in system but not in settlement) and unmatched payouts (in settlement but not in system) are listed separately
- [ ] Reconciliation dashboard shows overall reconciliation health: % matched, total discrepancy amount, pending payouts
- [ ] Reports exportable to PDF and Excel with aggregator breakup

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Reconciliation Dashboard | /analytics/reconciliation | Overview of reconciliation status across all aggregators |
| Reconciliation Detail | /analytics/reconciliation/{aggregator} | Order-level reconciliation detail per aggregator |
| Settlement Upload | /analytics/reconciliation/upload | Upload settlement report CSV/Excel or trigger API download |
| Auto-Reconcile Rules | /analytics/reconciliation/rules | Configure auto-reconcile rules per aggregator |
| Dispute Management | /analytics/reconciliation/disputes | Track and manage chargebacks and disputes |
| Reconciliation Report | /analytics/reconciliation/report | Generated reconciliation report with export options |

### Screen Details

#### Reconciliation Dashboard (/analytics/reconciliation)

**Layout:**
- Top bar: Title "Online Order Reconciliation", date range picker, export button
- Aggregator Summary Cards (3 cards in row):
  - Swiggy Card: Logo, Total Orders, Total Order Value, Total Commission, Net Payout, Matched %, Discrepancy Amount, Status indicator (green/yellow/red)
  - Zomato Card: Same layout
  - magicpin Card: Same layout
- Overall Summary Section:
  - Total Online Revenue (period)
  - Total Commission Paid
  - Total Net Payout Expected
  - Total Net Payout Received
  - Total Discrepancy Amount (red highlight if > 0)
  - Overall Match Rate %
- Unmatched Items Section (two tabs):
  - Tab 1: "Orders Not in Settlement" - table of system orders with no matching settlement line
  - Tab 2: "Settlement Lines Not in System" - table of settlement lines with no matching order
- Pending Payouts Table: Expected payouts not yet received with aging buckets (0-7 days, 8-14 days, 15-30 days, 30+ days)

**Components:**
- `ReconciliationDashboard` (main container)
- `AggregatorSummaryCard` (per-aggregator summary widget)
- `UnmatchedOrdersTable` (tabbed table for unmatched items)
- `PendingPayoutsTable` (aging analysis table)
- `DisputeStatusBadge` (status badge component)

**Unmatched Orders Table Columns:**
| Aggregator Order ID | Aggregator | Order Date | Order Amount | Status | Action |
|---------------------|------------|------------|--------------|--------|--------|

- Action button: [Create Dispute] or [Mark Resolved]

**Pending Payouts Table Columns:**
| Aggregator | Expected Payout Date | Expected Amount | Days Overdue | Aging Bucket | Status |
|------------|---------------------|-----------------|--------------|--------------|--------|

#### Reconciliation Detail (/analytics/reconciliation/{aggregator})

**Layout:**
- Breadcrumb: Reconciliation > [Aggregator Name] Detail
- Header: Aggregator logo, period selector, [Upload Settlement] button, [Run Reconciliation] button
- Summary strip: Total Orders, Matched, Partially Matched, Unmatched, Discrepancy Amount
- Filter bar: Match status dropdown, date range, search by order ID
- Reconciliation Table (main data grid):

**Reconciliation Table Columns:**
| Aggregator Order ID | Order Date | Order Amount | Commission % | Commission Amount | Expected Payout | Settlement Payout | Difference | Match Status | Action |
|--------------------|-----------|--------------|--------------|-------------------|-----------------|-------------------|------------|--------------|--------|

- Commission % column shows red if exceeds contracted rate
- Difference column shows red if non-zero
- Match Status: colored badge (green=Matched, yellow=Partial, red=Unmatched/Discrepancy)
- Action: [View Detail] opens order reconciliation detail drawer
- Pagination: 50 rows per page
- Column toggle: user can show/hide columns
- Export button: exports current filtered view

**Order Reconciliation Detail Drawer (slide-in panel):**
- Order information section: System order ID, aggregator order ID, order date, items, order total
- Settlement information section: Settlement date, payout reference, commission breakdown, payout amount
- Discrepancy section: Line-by-line comparison with highlighted mismatches
- Dispute section: [Open Dispute] button if discrepancy, existing dispute details if present
- Action buttons: [Accept as Reconciled] [Flag for Review] [Create Dispute]

#### Settlement Upload (/analytics/reconciliation/upload)

**Layout:**
- Card: "Upload Settlement Report"
  - Aggregator selector: Dropdown (Swiggy, Zomato, magicpin)
  - Period: Date range picker
  - Upload method: Radio ([ ] Manual File Upload, [ ] API Auto-Download)
  - Manual upload: Drag-and-drop file zone, accepted formats: .csv, .xlsx
  - API download: [Download from API] button (fetches from aggregator API)
- Upload History Table:
  | Date | Aggregator | Period | File Name | Records | Status | Actions |
  - Status: Processing, Completed, Failed
  - Actions: [View] [Reprocess] [Delete]

#### Auto-Reconcile Rules (/analytics/reconciliation/rules)

**Layout:**
- Per-aggregator configuration card (3 cards):
  - Aggregator name and logo
  - Contracted Commission Rate (%): Number input
  - Auto-Accept Threshold (Rs.): Number input (discrepancies below this auto-accepted)
  - Auto-Flag Threshold (Rs.): Number input (discrepancies above this auto-flagged)
  - Commission Cap (%): Number input (flag if commission exceeds)
  - Rules active: Toggle switch
  - [Save Rules] button

#### Dispute Management (/analytics/reconciliation/disputes)

**Layout:**
- Filter bar: Aggregator, Status (Open/In-Progress/Resolved/Won/Lost), Date range
- Dispute Table:

**Dispute Table Columns:**
| Dispute ID | Aggregator | Order ID | Dispute Type | Dispute Amount | Reason | Status | Opened Date | Resolution Date | Actions |
|-------------|------------|----------|--------------|----------------|--------|--------|-------------|-----------------|---------|

- Dispute Type: Commission Error, Missing Payout, Short Payment, Chargeback, Incorrect Tax, Other
- Actions: [View Detail] [Update Status]

**Dispute Detail Modal:**
- Dispute information: ID, aggregator, order ID, type, amount
- Description: Multi-line text
- Evidence upload: File upload zone (supporting documents, screenshots, emails)
- Communication log: Chronological list of messages with timestamp and author
- Status workflow: Status dropdown with transition buttons
- Resolution fields: Resolution amount, resolution notes (visible when status = Resolved/Won/Lost)
- Buttons: [Save] [Add Comment] [Upload Evidence] [Close Dispute]

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/reconciliation/dashboard | Get reconciliation dashboard summary across all aggregators |
| GET | /api/v1/reconciliation/{aggregator}/detail | Get order-level reconciliation detail for specific aggregator |
| POST | /api/v1/reconciliation/settlements/upload | Upload settlement report file (multipart form data) |
| POST | /api/v1/reconciliation/settlements/api-download | Trigger API download of settlement report from aggregator |
| POST | /api/v1/reconciliation/run | Run reconciliation engine for specified aggregator and period |
| GET | /api/v1/reconciliation/unmatched | Get unmatched orders and settlement lines |
| GET | /api/v1/reconciliation/pending-payouts | Get pending payout tracking with aging |
| GET | /api/v1/reconciliation/rules/{aggregator} | Get auto-reconcile rules for aggregator |
| PUT | /api/v1/reconciliation/rules/{aggregator} | Update auto-reconcile rules |
| GET | /api/v1/reconciliation/disputes | List all disputes with filters |
| POST | /api/v1/reconciliation/disputes | Create new dispute |
| GET | /api/v1/reconciliation/disputes/{id} | Get dispute detail |
| PUT | /api/v1/reconciliation/disputes/{id} | Update dispute (status, resolution, comments) |
| POST | /api/v1/reconciliation/disputes/{id}/evidence | Upload evidence file for dispute |
| POST | /api/v1/reconciliation/orders/{id}/accept | Accept order reconciliation (mark as reconciled) |
| POST | /api/v1/reconciliation/orders/{id}/flag | Flag order reconciliation for manual review |
| GET | /api/v1/reconciliation/report | Generate reconciliation report (params: aggregator, date_from, date_to, format) |
| GET | /api/v1/reconciliation/settlements/history | Get upload history for settlement reports |

## Database Tables

### settlement_reports (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| aggregator | ENUM('swiggy','zomato','magicpin') | Aggregator name |
| period_from | DATE | Settlement period start |
| period_to | DATE | Settlement period end |
| file_name | VARCHAR(255) NULL | Uploaded file name |
| file_path | VARCHAR(500) NULL | Storage path for uploaded file |
| source | ENUM('upload','api') | Ingestion method |
| total_records | INT | Number of line items in report |
| total_payout | DECIMAL(12,2) | Total payout amount in report |
| status | ENUM('processing','completed','failed') | Processing status |
| processed_at | TIMESTAMP NULL | When processing completed |
| error_message | TEXT NULL | Error details if failed |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### settlement_line_items (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| settlement_report_id | BIGINT UNSIGNED FK | Reference to settlement_reports |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| aggregator | ENUM('swiggy','zomato','magicpin') | Aggregator name |
| aggregator_order_id | VARCHAR(100) | Order ID from aggregator |
| order_date | DATETIME | Order date per settlement |
| order_amount | DECIMAL(12,2) | Gross order amount |
| commission_rate | DECIMAL(5,2) | Commission percentage applied |
| commission_amount | DECIMAL(12,2) | Commission deducted |
| tax_on_commission | DECIMAL(12,2) | GST on commission |
| other_charges | DECIMAL(12,2) | Other deductions |
| payout_amount | DECIMAL(12,2) | Net payout for this order |
| payout_date | DATE NULL | Date payout was made |
| payout_reference | VARCHAR(100) NULL | Payout reference number |
| matched_order_id | BIGINT UNSIGNED FK NULL | Reference to orders table if matched |
| match_status | ENUM('matched','partial','unmatched','discrepancy') | Reconciliation status |
| discrepancy_amount | DECIMAL(12,2) DEFAULT 0 | Calculated discrepancy |
| discrepancy_reason | VARCHAR(255) NULL | Reason for discrepancy |
| reconciled_at | TIMESTAMP NULL | When reconciled |
| reconciled_by | BIGINT UNSIGNED FK NULL | User who reconciled |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| INDEX | (outlet_id, aggregator, aggregator_order_id) | Match lookup index |
| INDEX | (settlement_report_id) | |

### reconciliation_rules (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| aggregator | ENUM('swiggy','zomato','magicpin') | Aggregator name |
| contracted_commission_rate | DECIMAL(5,2) | Agreed commission percentage |
| auto_accept_threshold | DECIMAL(10,2) | Discrepancy below this auto-accepted |
| auto_flag_threshold | DECIMAL(10,2) | Discrepancy above this auto-flagged |
| commission_cap | DECIMAL(5,2) | Maximum acceptable commission % |
| is_active | BOOLEAN DEFAULT TRUE | Rules active flag |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### reconciliation_disputes (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| aggregator | ENUM('swiggy','zomato','magicpin') | Aggregator name |
| aggregator_order_id | VARCHAR(100) | Related aggregator order |
| settlement_line_item_id | BIGINT UNSIGNED FK NULL | Related settlement line |
| dispute_type | ENUM('commission_error','missing_payout','short_payment','chargeback','incorrect_tax','other') | Type |
| dispute_amount | DECIMAL(12,2) | Amount in dispute |
| reason | TEXT | Detailed reason |
| status | ENUM('open','in_progress','resolved','won','lost') | Dispute status |
| resolution_amount | DECIMAL(12,2) NULL | Amount recovered/resolved |
| resolution_notes | TEXT NULL | Resolution details |
| opened_at | TIMESTAMP | When dispute opened |
| opened_by | BIGINT UNSIGNED FK | User who opened |
| resolved_at | TIMESTAMP NULL | When resolved |
| resolved_by | BIGINT UNSIGNED FK NULL | User who resolved |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### dispute_communications (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| dispute_id | BIGINT UNSIGNED FK | Reference to reconciliation_disputes |
| author_id | BIGINT UNSIGNED FK | User who wrote |
| author_type | ENUM('internal','aggregator') | Author side |
| message | TEXT | Communication content |
| attachments | JSON NULL | Array of file paths |
| created_at | TIMESTAMP | |

## Technical Notes

### Backend (Laravel 11)
- **Controller**: `App\Http\Controllers\Api\V1\Reconciliation\ReconciliationController`
- **Controller**: `App\Http\Controllers\Api\V1\Reconciliation\DisputeController`
- **Service**: `App\Services\Reconciliation\ReconciliationEngineService` - core matching logic
- **Service**: `App\Services\Reconciliation\SettlementParserService` - parses CSV/Excel/API settlement data
- **Job**: `App\Jobs\ProcessSettlementReport` - async processing of uploaded settlement files
- **Job**: `App\Jobs\RunReconciliation` - executes matching engine for a period
- **Import**: Use Maatwebsite/Laravel-Excel for parsing uploaded settlement files with custom import class `SettlementReportImport`
- **Routes**: Defined in `routes/api.php` under `auth:sanctum` + `permission:reconciliation.view` / `permission:reconciliation.manage`

### Reconciliation Engine Logic
1. Load all settlement line items for the selected aggregator and period
2. For each line item, find matching order by `aggregator_order_id` in orders table
3. If matched: compare commission rate with contracted rate, compare payout amount with expected payout
4. Apply auto-reconcile rules: auto-accept if discrepancy < threshold, auto-flag if > threshold
5. Generate discrepancy records with reason codes
6. Update `match_status` on settlement line items
7. Produce summary statistics for dashboard

### Frontend (React + Vite)
- **Page Components**: `src/pages/reconciliation/ReconciliationDashboardPage.jsx`, `ReconciliationDetailPage.jsx`, `SettlementUploadPage.jsx`, `ReconciliationRulesPage.jsx`, `DisputeManagementPage.jsx`
- **Components**: `src/components/reconciliation/AggregatorSummaryCard.jsx`, `ReconciliationTable.jsx`, `OrderReconciliationDrawer.jsx`, `DisputeDetailModal.jsx`, `SettlementUploadZone.jsx`, `AutoReconcileRulesForm.jsx`
- **State Management**: React Query for server state, React state for filters and selected items
- **File Upload**: Use react-dropzone for drag-and-drop file upload
- **Drawer**: Use headless UI or custom slide-in panel for order detail
- **Table Virtualization**: Use @tanstack/react-table for large reconciliation tables

## Subtasks

1. [ ] Create database migrations for settlement_reports, settlement_line_items, reconciliation_rules, reconciliation_disputes, dispute_communications tables
2. [ ] Implement SettlementParserService for CSV and Excel parsing per aggregator format
3. [ ] Implement API settlement download for Swiggy, Zomato, magicpin
4. [ ] Build ProcessSettlementReport job for async file processing
5. [ ] Implement ReconciliationEngineService with order matching and comparison logic
6. [ ] Build RunReconciliation job and manual trigger endpoint
7. [ ] Implement auto-reconcile rules CRUD with per-aggregator configuration
8. [ ] Implement ReconciliationController with dashboard and detail endpoints
9. [ ] Implement unmatched orders and pending payouts tracking
10. [ ] Implement DisputeController with full dispute lifecycle management
11. [ ] Add evidence file upload for disputes with storage to S3/local
12. [ ] Build Reconciliation Dashboard React page with aggregator summary cards
13. [ ] Build Reconciliation Detail page with main table and filters
14. [ ] Build Order Reconciliation Drawer with line-by-line comparison
15. [ ] Build Settlement Upload page with drag-and-drop and API download
16. [ ] Build Auto-Reconcile Rules configuration page
17. [ ] Build Dispute Management page with table and detail modal
18. [ ] Implement reconciliation report generation with PDF/Excel export
19. [ ] Add unit tests for ReconciliationEngineService matching logic
20. [ ] Add integration tests with sample settlement data for each aggregator
21. [ ] Test dispute workflow end-to-end (open, communicate, resolve)

## Testing Criteria

- [ ] Upload a sample Swiggy settlement CSV and verify all line items are parsed correctly
- [ ] Upload a sample Zomato settlement Excel and verify all line items are parsed correctly
- [ ] Run reconciliation and verify orders are matched by aggregator_order_id
- [ ] Verify commission rate comparison flags orders exceeding contracted rate
- [ ] Verify auto-accept rule accepts discrepancies below threshold automatically
- [ ] Verify auto-flag rule flags discrepancies above threshold
- [ ] Verify unmatched orders and unmatched settlement lines are listed separately
- [ ] Verify pending payout aging calculation is correct (0-7, 8-14, 15-30, 30+ days)
- [ ] Create a dispute and verify it appears in dispute management with correct status
- [ ] Upload evidence file to dispute and verify it is stored and retrievable
- [ ] Update dispute status through workflow (open -> in_progress -> resolved) and verify transitions
- [ ] Verify reconciliation report PDF contains aggregator breakup and discrepancy listing
- [ ] Verify reconciliation report Excel opens correctly with all data
- [ ] Verify dashboard summary numbers match detail-level calculations
