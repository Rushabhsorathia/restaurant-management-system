# RMS-048: Dynamic Custom Reports Builder

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-048 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-041 (Sales Dashboard), RMS-042 (Online Order Reconciliation), RMS-043 (Multi-Outlet Dashboard), RMS-045 (Tax Reports), RMS-046 (Staff Performance), RMS-047 (Profit Margin) |

## User Story
As a restaurant owner, manager, or analyst, I want a no-code dynamic report builder that lets me drag-and-drop fields, apply filters, group by dimensions, aggregate measures (sum, average, count), choose chart types, save, share, and schedule reports for automatic email delivery, so that I can answer any business question without waiting for a developer to build a custom report.

## Description
This story delivers the final and most powerful analytics feature of Milestone 5: a **Dynamic Custom Report Builder** that empowers non-technical users to create their own reports against any data domain in the RMS — sales, orders, items, customers, staff, inventory, purchases, expenses, recipes. The builder provides drag-and-drop for selecting fields, applying filters, configuring group-by, choosing aggregations, and selecting visualization types (table, bar, line, pie, area, scatter, pivot).

Every business has questions that the standard reports don't answer. The chain owner wants to know "compare average bill size between Bangalore and Mumbai outlets over the last 6 months, by day of week". The floor manager wants "which 3 items contribute most to revenue on slow days, by hour". The accountant wants "monthly expense breakdown by category with budget vs actual". Today, answering these questions requires a developer writing SQL. With the Custom Report Builder, the user constructs the query visually: drag fields into "Columns/Group By", drag measures into "Values", drag fields into "Filters", pick an aggregation, and see the result immediately as a chart and table.

The system must support **SQL injection prevention via parameterization** — user-selected field/filter/aggregation tokens are mapped server-side to a whitelist of allowed fields and aggregate functions; raw user input is never concatenated into SQL. The query is built using a structured AST (abstract syntax tree) compiled to a parameterized Laravel query or read-only stored procedure call.

Users can **save reports** to a personal or shared library, **share** via signed URL with view-only or edit access, and **schedule** reports for automatic email delivery (PDF/Excel/CSV attachment) at configurable frequencies. The system supports **pivot/crosstab** views, **calculated fields** (e.g., "Profit = Revenue − COGS"), **period comparison** (overlaid current vs previous), and **drill-down** from aggregate to detail. Pre-built report templates (top sellers, slow movers, customer cohorts, staff productivity) are seeded to help non-technical users get started. The builder is REST API-driven, server-side rendered for performance on large datasets, supports multi-outlet filtering inherited from RMS-043, and respects role-based access.

## Acceptance Criteria
- [ ] User can drag fields from a data-domain catalog into "Columns/Group By", "Values", "Filters", and "Sort By" zones
- [ ] Data domains exposed: Orders, Order Items, Customers, Staff, Items, Categories, Inventory, Purchases, Expenses, Recipes
- [ ] Aggregations supported per measure: Sum, Average, Count, Count Distinct, Min, Max, Median, Std Dev
- [ ] Filter operators: equals, not equals, in, not in, greater than, less than, between, contains, starts with, is null, is not null, date range, date relative (today, last 7 days, this month)
- [ ] Chart types: Table, Bar, Stacked Bar, Line, Area, Pie/Donut, Scatter, Pivot/Crosstab
- [ ] Query executes in under 5 seconds for any saved report and shows results in chart + table view
- [ ] All user-selected fields, filters, and aggregations are mapped to a server-side whitelist; raw user input never reaches SQL string (SQL injection prevention)
- [ ] User can save a report with name, description, visibility (private/shared/public), and tags
- [ ] User can share a saved report via signed URL (read-only or edit permission) with optional expiry
- [ ] User can schedule a report for automatic email delivery (PDF/Excel/CSV) with daily/weekly/monthly/custom-cron frequency
- [ ] Scheduled report emails include a brief summary, the file attachment, and a "View Online" link
- [ ] Calculated fields can be added: simple math (+, −, ×, ÷) on existing measures, conditional (IF/THEN), and date diff
- [ ] Period comparison: overlay current period vs previous (MoM, QoQ, YoY) on the same chart with variance %
- [ ] Drill-down: click a chart segment to filter the report to that sub-group
- [ ] Pre-built report templates seeded (top 20 sellers, slow movers, hourly heatmap, staff productivity, customer cohorts, expense breakdown)
- [ ] Multi-outlet filter and consolidation; per-outlet scope respected
- [ ] All saved reports, schedules, and shares are listed in a personal/team library
- [ ] Role-based access: only the report owner, shared-with users, and admins can view/edit

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Report Builder | /analytics/builder | Main drag-and-drop report construction interface |
| Report Library | /analytics/builder/library | List of saved reports (personal + shared) |
| Report Viewer | /analytics/builder/report/{id} | Run and view a saved report (chart + table) |
| Schedule Manager | /analytics/builder/schedules | Configure and manage scheduled email reports |
| Template Gallery | /analytics/builder/templates | Browse pre-built report templates to clone |

### Screen Details

**Report Builder (/analytics/builder)**
- Layout: Top toolbar + 3-column workspace (Data Catalog | Configuration | Preview).
- Top toolbar: Data Domain dropdown (Orders, Order Items, etc.), Save, Share, Schedule, Export menu.
- Left column — Data Catalog: Searchable field list grouped by table (e.g., Orders: id, date, total, tax, discount, staff, customer, outlet). Each field shows icon (date/number/text/measure/dimension) and drag handle.
- Center column — Configuration: 4 drop zones (Filters, Group By / Rows, Values / Measures, Sort By). Drag fields into zones; chip-style placed fields with edit/remove.
  - Filters: Operator selector + value input; multi-value for "in"/"between".
  - Group By: Ordered list of dimensions.
  - Values: Aggregation selector (Sum/Avg/Count/Min/Max) + display format per measure.
  - Sort By: Field + direction (asc/desc).
- Calculated field: "+ Add Calculation" opens formula editor.
- Period comparison toggle: [Off] [Previous Period] [MoM] [QoQ] [YoY].
- Right column — Preview: Tab [Chart] [Table] [Pivot]. Chart type selector (table/bar/line/pie/scatter/pivot), refresh, result count, execution time, export buttons (Excel, PDF, CSV, PNG).
- Bottom bar: "Run Query" (auto-runs on field change with 300ms debounce), "Reset".

**Report Library (/analytics/builder/library)**
- Layout: Filter sidebar + report grid/table + top toolbar.
- Filter sidebar: Visibility (All / Mine / Shared / Public), Tags, Data Domain, Created Date, Last Run Date.
- View toggle: Grid (card) | Table.
- Grid card: Report name, thumbnail of last chart, description, owner avatar, last run time, run count, tags, visibility badge, favorite star.
- Table columns: Name | Domain | Owner | Last Run | Run Count | Visibility | Tags | Actions (Run, Edit, Duplicate, Share, Schedule, Delete).
- Top toolbar: "New Report" (opens builder), "Import Template", search input.
- Bulk actions: Select multiple → Share, Tag, Delete.
- Favorites tab: Pinned reports only.

**Report Viewer (/analytics/builder/report/{id})**
- Layout: Report header + filter bar (saved filters) + chart + table + drill-down drawer.
- Header: Report name, description, owner, last refresh timestamp, "Refresh Now", "Edit" (if owner), "Export" dropdown, "Schedule", "Share", favorite star.
- Filter bar: Pre-applied saved filters shown as chips; "Edit Filters" allows override (without saving).
- Chart: Selected chart type with full-screen toggle; hover shows tooltip with dimension + measure(s).
- Table: Sortable, paginated beneath chart; columns match selected fields. Pivot view (if selected) shows rows × columns matrix with totals.
- Drill-down drawer: Click a row/segment → opens detail records (e.g., from "Top 10 items" click to see actual orders).
- "Save as New Report" duplicates with modifications.

**Schedule Manager (/analytics/builder/schedules)**
- Layout: Schedule list table + new schedule form panel.
- Schedule list: Report Name | Frequency | Recipients | Next Run | Last Run Status | Active | Actions.
- New schedule form: Report selector | Frequency (Daily/Weekly/Monthly/Custom cron) | Time picker | Recipients (multi-email) | Format (PDF/Excel/CSV) | Subject template | Body template | Active toggle | "Save Schedule".
- "Run Now" per schedule for testing; "Pause/Resume" toggle; "View Run History" with success/failure log, errors, retry.

**Template Gallery (/analytics/builder/templates)**
- Layout: Searchable grid of pre-built report templates.
- Each template card: Thumbnail, name, description, data domain badge, popularity count, "Use Template" button.
- Categories: Sales, Customer, Staff, Inventory, Financial, Operational.
- "Use Template" loads the template into the builder, ready to customize and save.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/analytics/builder/domains | List available data domains with field metadata |
| GET | /api/v1/analytics/builder/domains/{name}/fields | Get field catalog for a domain (dimensions, measures, types) |
| POST | /api/v1/analytics/builder/preview | Execute ad-hoc report query (returns chart-ready data) |
| POST | /api/v1/analytics/builder/validate | Validate a report definition before save (catch errors) |
| GET | /api/v1/analytics/builder/reports | List saved reports (filter: visibility, owner, domain) |
| GET | /api/v1/analytics/builder/reports/{id} | Get a saved report configuration |
| POST | /api/v1/analytics/builder/reports | Save new report |
| PUT | /api/v1/analytics/builder/reports/{id} | Update report configuration |
| DELETE | /api/v1/analytics/builder/reports/{id} | Delete (soft) report |
| POST | /api/v1/analytics/builder/reports/{id}/duplicate | Duplicate report (with new name) |
| POST | /api/v1/analytics/builder/reports/{id}/share | Generate shareable signed URL |
| GET | /api/v1/analytics/builder/shared/{token} | Access shared report via signed URL |
| POST | /api/v1/analytics/builder/reports/{id}/run | Run saved report, return result data |
| POST | /api/v1/analytics/builder/reports/{id}/export | Export saved report (PDF/Excel/CSV/PNG) |
| GET | /api/v1/analytics/builder/schedules | List scheduled report configurations |
| POST | /api/v1/analytics/builder/schedules | Create schedule (frequency, recipients, format) |
| PUT | /api/v1/analytics/builder/schedules/{id} | Update schedule |
| DELETE | /api/v1/analytics/builder/schedules/{id} | Delete schedule |
| POST | /api/v1/analytics/builder/schedules/{id}/run-now | Trigger immediate run |
| GET | /api/v1/analytics/builder/schedules/{id}/history | Get run history with status and errors |
| GET | /api/v1/analytics/builder/templates | List available report templates |
| POST | /api/v1/analytics/builder/templates/{id}/use | Clone template into new report |

## Database Tables

**custom_reports**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- owner_user_id (foreignId, users)
- name (string, indexed)
- description (text, nullable)
- data_domain (string, indexed) — e.g., 'orders', 'order_items', 'customers'
- definition_json (json) — the full report definition (fields, filters, group_by, values, sort, chart_type, period_comparison, calculated_fields)
- chart_type (enum: table, bar, stacked_bar, line, area, pie, scatter, pivot, default table)
- visibility (enum: private, shared, public, default private)
- tags_json (json, nullable)
- is_template (boolean, default false)
- template_category (string, nullable) — sales, customer, staff, inventory, financial, operational
- run_count (unsignedInteger, default 0)
- last_run_at (datetime, nullable)
- last_run_duration_ms (unsignedInteger, nullable)
- last_run_status (enum: success, error, default null)
- share_token (string(64), nullable, unique) — for shared URLs
- share_expires_at (datetime, nullable)
- is_favorite (boolean, default false) — for owner's favorites
- softDeletes
- timestamps
- index: [restaurant_id, owner_user_id, visibility]

**custom_report_shares**
- id (bigIncrements, PK)
- custom_report_id (foreignId, custom_reports)
- shared_with_user_id (foreignId, users, nullable) — null = any authenticated user
- shared_with_role (string, nullable) — null = any role
- permission (enum: view, edit, default view)
- shared_by (foreignId, users)
- expires_at (datetime, nullable)
- timestamps
- index: [custom_report_id, shared_with_user_id]

**custom_report_schedules**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- custom_report_id (foreignId, custom_reports)
- name (string)
- frequency (enum: daily, weekly, monthly, custom)
- cron_expression (string, nullable) — for custom frequency
- time_of_day (time)
- day_of_week (unsignedTinyInteger, nullable) — 0-6 for weekly
- day_of_month (unsignedTinyInteger, nullable) — 1-31 for monthly
- recipients_json (json) — array of email addresses
- format (enum: pdf, xlsx, csv, default pdf)
- subject_template (string, nullable) — supports {{report_name}} {{date}} placeholders
- body_template (text, nullable) — HTML email body with placeholders
- is_active (boolean, default true)
- last_run_at (datetime, nullable)
- next_run_at (datetime, nullable, indexed)
- created_by (foreignId, users)
- timestamps
- index: [is_active, next_run_at]

**custom_report_schedule_runs**
- id (bigIncrements, PK)
- schedule_id (foreignId, custom_report_schedules)
- started_at (datetime)
- finished_at (datetime, nullable)
- status (enum: running, success, error, default running)
- duration_ms (unsignedInteger, nullable)
- result_row_count (unsignedInteger, nullable)
- file_path (string, nullable) — generated report file
- file_size_bytes (unsignedInteger, nullable)
- error_message (text, nullable)
- error_stack (text, nullable)
- recipients_sent (json, nullable) — successful email deliveries
- timestamps
- index: [schedule_id, started_at]

**custom_report_templates** (seeded)
- id (bigIncrements, PK)
- slug (string, unique) — 'top-20-sellers', 'slow-movers', etc.
- name (string)
- description (text)
- category (string) — sales, customer, staff, inventory, financial, operational
- data_domain (string)
- definition_json (json) — the template report definition
- thumbnail_path (string, nullable)
- popularity_count (unsignedInteger, default 0)
- is_active (boolean, default true)
- timestamps

## Technical Notes

**Laravel Backend:**
- Controllers: `ReportBuilderController` (domains, fields, preview, validate, run, export), `SavedReportController` (CRUD, duplicate, share, favorite), `ScheduleController` (CRUD, run-now, history), `TemplateController` (list, use).
- Security (CRITICAL): `ReportQueryBuilder` service maps user-selected fields/aggregations/operators to a **server-side whitelist** (allowed_fields, allowed_aggregations, allowed_operators per domain). Raw user input NEVER reaches SQL. Query is built as Laravel query builder with `?` placeholders; values are bound, not interpolated. The whitelist is audited for each new domain addition.
- Service: `ReportExecutor::execute($definition)` builds a parameterized query, runs it via DB read-only connection, paginates, returns chart-ready aggregations + raw rows.
- Service: `PivotBuilder` for crosstab view; `PeriodComparison` overlay service; `CalculatedFieldEvaluator` for IF/Math/DateDiff formulas (uses Symfony ExpressionLanguage with restricted functions).
- Pivot: For pivot/crosstab, use MySQL `GROUP_CONCAT` with conditional aggregation (`SUM(CASE WHEN ... THEN val END)`) for limited cardinality.
- Performance: For frequently-run reports, cache last result for 5 minutes; pre-aggregate to `report_cache` table for heavy reports.
- Export: `maatwebsite/excel` for Excel/CSV; `barryvdh/dompdf` for PDF; chart PNG via headless Chrome (spatie/browsershot) or server-side Recharts-to-image fallback.
- Schedules: Laravel Scheduler (cron) runs every minute, picks up due schedules from `custom_report_schedules` where `is_active=true` and `next_run_at <= now()`. `RunScheduledReportJob` generates file, sends via Mail with attachment; updates `next_run_at` per frequency.
- Email: `SendScheduledReportMail` with attachment (PDF/Excel/CSV), inline thumbnail (if any), and "View Online" link to share URL (regenerated per send).
- Audit: All report creations, shares, runs, and schedule configurations logged.

**React Frontend:**
- Components: `ReportBuilderPage`, `DataCatalogPanel`, `ConfigurationZones`, `FilterChip`, `ValueChip`, `GroupByChip`, `SortByChip`, `CalculatedFieldEditor`, `ChartPreview`, `TablePreview`, `PivotPreview`, `PeriodComparisonToggle`, `DrillDownDrawer`, `ReportLibraryPage`, `ReportCard`, `ReportViewerPage`, `ScheduleManagerPage`, `ScheduleForm`, `TemplateGalleryPage`, `TemplateCard`, `ShareLinkModal`, `ExportMenu`, `RunHistoryTable`.
- Drag-and-drop: `react-dnd` or `@dnd-kit/core` for field drag-to-zone.
- State: Zustand `useBuilderStore` for current report definition; `useSavedReportStore` for library.
- Charting: Recharts (BarChart, LineChart, PieChart, AreaChart, ScatterChart); pivot table via custom grid.
- Debounced auto-run: 300ms after last field change; loading state with skeleton.
- Calculated field editor: Monaco editor with restricted function list, syntax highlighting, live preview.
- Schedule form: cron expression builder UI (no need to write raw cron); timezone selector.

## Subtasks
1. [ ] Create `custom_reports`, `custom_report_shares`, `custom_report_schedules`, `custom_report_schedule_runs`, `custom_report_templates` migrations and models
2. [ ] Define `ReportFieldRegistry` with per-domain field whitelists (dimensions, measures, types, allowed aggregations, allowed operators)
3. [ ] Build `ReportQueryBuilder` with strict parameterization — no raw user input in SQL; security audit
4. [ ] Build `ReportExecutor::execute` returning chart-ready aggregations and raw rows with pagination
5. [ ] Build `CalculatedFieldEvaluator` (restricted ExpressionLanguage) for IF/Math/DateDiff formulas
6. [ ] Build `PivotBuilder` for crosstab view with conditional aggregation
7. [ ] Build `PeriodComparison` overlay service (current vs MoM/QoQ/YoY)
8. [ ] Build `SavedReportController` CRUD with versioning (keep last 10 versions for restore)
9. [ ] Build `ShareController` with signed URL generation, expiry, permission (view/edit)
10. [ ] Build `ScheduleController` with cron-based scheduling, run-now, and history
11. [ ] Build `RunScheduledReportJob` generating PDF/Excel/CSV and emailing recipients
12. [ ] Build `TemplateSeeder` with 20+ pre-built report templates (top sellers, slow movers, etc.)
13. [ ] Build `ExportService` for PDF, Excel (multi-sheet), CSV, PNG (chart) with branded header
14. [ ] Build React ReportBuilderPage with drag-drop zones, data catalog, live preview (300ms debounce)
15. [ ] Build React ReportLibraryPage with grid/table view, filter, bulk actions
16. [ ] Build React ReportViewerPage with chart, table, pivot, drill-down, filter override
17. [ ] Build React ScheduleManagerPage with cron UI, recipients, format, run-now, history
18. [ ] Build React TemplateGalleryPage with category filter, "Use Template" clone flow
19. [ ] Build React ShareLinkModal with permission selector, expiry date, copy-to-clipboard
20. [ ] Build React CalculatedFieldEditor with Monaco, restricted functions, live preview
21. [ ] Write security tests for SQL injection attempts across all field/filter/aggregation combinations
22. [ ] Write tests for report execution, pivot, period comparison, schedules, share URLs, exports

## Testing Criteria
- [ ] User-selected field name with SQL meta-characters (`'; DROP TABLE orders; --`) is safely parameterized and returns no results, no SQL error
- [ ] Invalid field/aggregation/operator is rejected by whitelist validation before query execution
- [ ] Drag-and-drop report: drag dimensions to Group By, measure to Values, filter applied, chart renders correctly within 5s
- [ ] All 8 aggregation functions (sum, avg, count, count distinct, min, max, median, std dev) produce correct results
- [ ] All filter operators (equals, in, between, contains, etc.) work as expected on test data
- [ ] Period comparison overlay shows current and previous period side-by-side with correct variance %
- [ ] Pivot/crosstab: rows × columns matrix displays correct cell values and totals
- [ ] Calculated field: `Revenue - COGS` produces profit; date diff `DATEDIFF(closed_at, opened_at)` produces duration
- [ ] Save report → reload → same definition re-runs and produces identical results
- [ ] Share URL with view permission allows anonymous access; edit permission requires login
- [ ] Share URL expires after set date; returns 410 Gone after expiry
- [ ] Schedule with daily frequency triggers at correct time and emails PDF to recipients
- [ ] Schedule run history shows success/failure with error message; failed run can be retried
- [ ] All 20+ pre-built templates load into builder and run successfully
- [ ] Role-based access: staff user cannot view reports they don't own or aren't shared with
- [ ] Multi-outlet filter: report respects outlet scope; consolidated view aggregates across
- [ ] Drill-down: click chart segment filters table to detail records matching that group
- [ ] Export to Excel produces multi-sheet workbook; PDF paginates correctly; CSV escapes commas/quotes
- [ ] Report with 100k+ rows executes within 5 seconds; result pagination returns correct pages
