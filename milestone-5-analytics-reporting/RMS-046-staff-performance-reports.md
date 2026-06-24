# RMS-046: Staff Performance Reports

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-046 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-008 (POS & Order Entry), RMS-014 (Billing & Payment), RMS-016 (Waiter/Captain), RMS-041 (Sales Dashboard) |

## User Story
As a restaurant manager, floor manager, or HR/admin, I want comprehensive staff performance reports — per-waiter sales, items sold, average ticket size, table turnover, attendance, hours worked, tips, performance scorecard, and commission calculation — so that I can fairly evaluate my team, identify training needs, calculate accurate payroll, and reward top performers.

## Description
This story builds the staff performance analytics module, which transforms raw POS, KDS, and HR data into actionable insights about every employee who touches the order lifecycle. For Indian restaurants, the staff who directly influence revenue are waiters, captains, billing cashiers, and delivery coordinators. This module measures their individual contribution to sales, efficiency, customer satisfaction, and attendance reliability.

The report tracks **per-waiter sales** (orders taken, total revenue, average ticket size, items per order), **items sold** (which items each staff member up-sells or recommends most), **table turnover** (how quickly a section managed by a waiter turns tables), **attendance** (clock-in/out times, late marks, absent days, overtime hours) sourced from the HR/attendance module, **hours worked** (regular, overtime, split shifts) reconciled to sales for productivity ratios, and **tips** recorded against each bill (split by waiter, kitchen, helper) for transparent distribution.

A weighted **performance scorecard** combines multiple KPIs (sales achievement, AOV, upsell rate, customer feedback, attendance reliability) into a 0-100 score with customizable weights. **Commission calculation** engine handles tiered (e.g., 1% on first ₹50k, 2% on next, 3% beyond), category-specific (e.g., 5% on wine, 2% on food), and team-pool configurations. A **leaderboard** ranks staff by sales, score, or attendance with daily/weekly/monthly/all-time scopes. **Payroll export** produces a structured Excel/CSV compatible with Indian payroll software (Tally, Zoho Payroll, GreytHR) showing gross, commission, deductions, and net pay per employee per period.

All reports are outlet-aware (multi-outlet chain from RMS-043) and respect role-based access (HR sees all, manager sees own outlet, staff see only their own metrics via a personal dashboard). Period comparison (MoM, QoQ) shows trends per staff member.

## Acceptance Criteria
- [ ] Per-waiter report shows orders taken, total revenue, average ticket size, items per order, discount given for any period
- [ ] Items-sold-by-staff report shows top 20 items each staff member sells, with quantity and revenue, supporting up-sell/coach analysis
- [ ] Table turnover report shows average dwell time, tables served, occupancy contribution per waiter
- [ ] Attendance report shows days present, late marks, absent days, half-days, total hours worked, overtime hours per staff per period
- [ ] Hours-worked report reconciles clock-in/out with shift schedule, flags early/late punches and unaccounted gaps
- [ ] Tips module supports per-bill tip entry with split percentages (e.g., 70% waiter, 20% kitchen, 10% captain) and auto-aggregates per staff per day
- [ ] Performance scorecard combines weighted KPIs (sales achievement, AOV, upsell %, customer rating, attendance) into 0-100 score with admin-editable weights
- [ ] Commission calculation engine supports flat %, tiered, category-specific, and team-pool commission structures with formula preview
- [ ] Leaderboard ranks staff by selected metric (sales, score, attendance, AOV) for daily/weekly/monthly/all-time with gamification badges
- [ ] Payroll export generates Excel/CSV in the format expected by Indian payroll software (Tally, GreytHR, Zoho Payroll) with per-staff gross, commission, TDS, deductions, net
- [ ] Period comparison view shows MoM/QoQ trend per staff with variance indicators
- [ ] Multi-outlet consolidated view aggregates; per-outlet view available
- [ ] Personal dashboard lets each staff see their own metrics: sales, rank, score, commission earned, tip earned, attendance
- [ ] HR access control: HR sees all staff, outlet manager sees own outlet only, staff see only self
- [ ] All performance report views, commission runs, and payroll exports are logged for HR audit

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Staff Performance Dashboard | /analytics/staff | Overview of team performance with leaderboard and KPIs |
| Per-Waiter Sales Report | /analytics/staff/sales | Detailed sales-by-staff with comparison |
| Attendance & Hours | /analytics/staff/attendance | Attendance, late marks, hours worked per staff |
| Performance Scorecard | /analytics/staff/scorecard | Weighted scorecard configuration and per-staff scoring |
| Commission & Payroll | /analytics/staff/commission | Commission rules, calculations, payroll export |

### Screen Details

**Staff Performance Dashboard (/analytics/staff)**
- Layout: Top filter bar + KPI strip + 3 panels (Leaderboard, Top Performers, Score Distribution) + recent activity.
- Filter bar: Date range, outlet (multi-select), role (waiter/captain/cashier/all), department.
- KPI strip: 5 cards — Total Active Staff, Avg Sales/Staff, Avg Score, Total Commission Earned, Total Tips Earned.
- Leaderboard panel: Top 10 by sales (default), sortable; rank, name, avatar, sales, score, badge. Tabs: Sales | Score | Attendance | AOV | Items Sold.
- Top Performers carousel: 3 cards with monthly MVP, highest upsell, best attendance; click opens their profile.
- Score Distribution chart: Histogram showing count of staff in score brackets (0-20, 21-40, 41-60, 61-80, 81-100).
- Recent activity feed: Tips awarded, attendance alerts, score updates; timestamped.

**Per-Waiter Sales Report (/analytics/staff/sales)**
- Layout: Filter bar + sortable data table + drill-down drawer + comparison panel.
- Filter bar: Date range, outlet, role, sort metric (revenue, orders, AOV, items), top N.
- Table columns: Rank | Staff Name | Role | Outlet | Orders | Items Sold | Revenue | Avg Ticket Size | Avg Items/Order | Discount Given | MoM Change.
- Row click opens side drawer with: Sparkline of daily sales (last 30 days), top 10 items sold, customer rating summary, shift-wise breakdown.
- Comparison panel (collapsible): Side-by-side compare 2-4 staff members on same metrics.
- "View Items Sold" sub-report: Table of items with qty, revenue, % of staff's total sales.
- "View Shifts" sub-report: Hourly sales distribution by staff showing peak productivity hours.
- Export buttons: "Export to Excel", "Export to PDF", "Email to HR".

**Attendance & Hours (/analytics/staff/attendance)**
- Layout: Filter bar + summary cards + attendance calendar grid + hours breakdown chart.
- Filter bar: Date range (default current month), staff multi-select, outlet, status filter.
- Summary cards: Total Present Days, Avg Late Marks/Staff, Total Absent Days, Total Overtime Hours, Total Regular Hours.
- Calendar grid (rows = staff, columns = days): Each cell shows P (present, green) | A (absent, red) | H (half-day, yellow) | L (late, orange) | O (off, grey) | HD (holiday, blue). Hover shows in/out times, total hours.
- Hours breakdown chart: Stacked bar per staff — Regular hours (blue), Overtime (orange), Late-penalty hours (red).
- "Late Marks" tab: Table — Staff | Date | Scheduled In | Actual In | Late Minutes | Reason.
- "Absent Days" tab: Table with reason, leave type (CL/SL/EL/UL), approval status.
- Export: "Export Attendance Register" (Excel/CSV in payroll-software format).

**Performance Scorecard (/analytics/staff/scorecard)**
- Layout: Top section (KPI weight configurator) + per-staff score table + drill-down.
- Weight configurator: Sliders/input for each KPI weight — Sales Achievement (default 30%), AOV (15%), Upsell % (15%), Customer Rating (20%), Attendance Reliability (20%). "Save Weights" button. Recalculates all scores on save.
- Per-staff score table: Name | Role | Sales Achievement % | AOV Score | Upsell Score | Customer Rating | Attendance Score | **Total Score (0-100)** | Grade (A/B/C/D) | MoM Change.
- Grade legend: A (90+), B (75-89), C (60-74), D (<60) with color coding.
- Row click drawer: Score breakdown radar chart, individual KPI trends, evidence (e.g., customer reviews for low rating).
- "View Configuration" tab: Show formula preview, weight history (audit trail of weight changes), reset to default.
- "Run Recalculation" button: Recompute all scores (queued job for large teams).

**Commission & Payroll (/analytics/staff/commission)**
- Layout: 3 tabs — Commission Rules | Commission Runs | Payroll Export.
- Commission Rules tab: List of commission structures (Flat %, Tiered, Category-specific, Team Pool). "Add Rule" button opens form: rule name, applicable role/staff, period, base (sales or items), formula.
- Commission Runs tab: List of past runs — Period | Rule | Staff Count | Total Commission | Status (Draft/Approved/Paid) | Action. "New Run" button (select period + rule → preview → approve).
- Run preview: Per-staff table — Name | Base (Sales) | Rate/Tier | Commission | TDS | Net Commission. "Approve & Lock" or "Adjust" (manager override with reason).
- Payroll Export tab: Period selector, staff filter, deduction configurator (TDS, advance, loan), output format (Tally XML, GreytHR Excel, generic CSV, Zoho Payroll JSON).
- Preview: Per-staff gross pay summary before download; "Generate Payroll File" button creates downloadable file with all required fields (Employee ID, Name, PAN, DOB, Days Worked, Hours, Basic, HRA, Commission, Tips, Gross, Deductions, Net).
- "Email Payroll to HR" button: Sends file to configured HR email with password-protected attachment.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/analytics/staff/dashboard | Get staff dashboard summary, leaderboard, KPIs |
| GET | /api/v1/analytics/staff/sales | Get per-staff sales report (filter: date, outlet, role) |
| GET | /api/v1/analytics/staff/{id}/items-sold | Get items sold by specific staff member |
| GET | /api/v1/analytics/staff/table-turnover | Get table turnover and dwell time per staff |
| GET | /api/v1/analytics/staff/attendance | Get attendance register (date, staff filter) |
| GET | /api/v1/analytics/staff/{id}/hours | Get hours worked breakdown for staff |
| GET | /api/v1/analytics/staff/tips | Get tips earned per staff for period |
| GET | /api/v1/analytics/staff/scorecard | Get performance scorecard with weighted scores |
| PUT | /api/v1/analytics/staff/scorecard/weights | Update KPI weights (HR/Admin only) |
| POST | /api/v1/analytics/staff/scorecard/recalculate | Trigger score recalculation job |
| GET | /api/v1/analytics/staff/commission/rules | List commission rules |
| POST | /api/v1/analytics/staff/commission/rules | Create new commission rule |
| GET | /api/v1/analytics/staff/commission/runs | List commission run history |
| POST | /api/v1/analytics/staff/commission/runs | Create new commission run with preview |
| POST | /api/v1/analytics/staff/commission/runs/{id}/approve | Approve and lock commission run |
| GET | /api/v1/analytics/staff/payroll/export | Generate payroll file (Tally/GreytHR/Zoho/CSV) |
| GET | /api/v1/analytics/staff/leaderboard | Get leaderboard by metric and period scope |
| GET | /api/v1/analytics/staff/me | Get current staff member's personal dashboard |

## Database Tables

**staff_attendance**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- user_id (foreignId, users) — staff member
- attendance_date (date, indexed)
- scheduled_in (datetime, nullable)
- scheduled_out (datetime, nullable)
- actual_in (datetime, nullable)
- actual_out (datetime, nullable)
- late_minutes (unsignedInteger, default 0)
- early_out_minutes (unsignedInteger, default 0)
- regular_hours (decimal(6,2), default 0)
- overtime_hours (decimal(6,2), default 0)
- status (enum: present, absent, half_day, leave, holiday, off, default present)
- leave_type (enum: cl, sl, el, ul, lwp, null) — null if not on leave
- leave_approved_by (foreignId, users, nullable)
- in_method (enum: biometric, web, mobile, manual, default biometric)
- out_method (enum: biometric, web, mobile, manual, default biometric)
- notes (text, nullable)
- timestamps
- unique([user_id, attendance_date])
- index: [outlet_id, attendance_date]

**staff_tips**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- order_id (foreignId, orders)
- tip_amount (decimal(10,2))
- payment_mode (enum: cash, card, upi, wallet, included)
- split_recipients_json (json) — array of {user_id, percentage, amount}
- recorded_at (datetime)
- recorded_by (foreignId, users)
- timestamps
- index: [outlet_id, recorded_at]

**commission_rules**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- name (string)
- description (text, nullable)
- applicable_role (string, nullable) — null = all roles
- applicable_outlet_ids (json, nullable) — null = all outlets
- rule_type (enum: flat, tiered, category_specific, team_pool)
- base_metric (enum: total_sales, net_sales, items_sold, table_count)
- formula_json (json) — flexible formula definition (tiers, rates, categories)
- effective_from (date)
- effective_to (date, nullable)
- is_active (boolean, default true)
- created_by (foreignId, users)
- timestamps
- index: [restaurant_id, is_active, effective_from]

**commission_runs**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- rule_id (foreignId, commission_rules)
- period_start (date)
- period_end (date)
- outlet_ids (json, nullable)
- status (enum: draft, preview, approved, paid, locked, default draft)
- total_staff_count (unsignedInteger)
- total_commission (decimal(14,2))
- approved_at (datetime, nullable)
- approved_by (foreignId, users, nullable)
- paid_at (datetime, nullable)
- paid_reference (string, nullable) — bank ref, cheque no
- adjustments_json (json, nullable) — manager overrides
- timestamps
- index: [restaurant_id, period_start, period_end]

**commission_run_items**
- id (bigIncrements, PK)
- commission_run_id (foreignId, commission_runs)
- user_id (foreignId, users)
- base_amount (decimal(12,2))
- rate (decimal(5,2))
- gross_commission (decimal(12,2))
- tds_amount (decimal(12,2), default 0)
- adjustments (decimal(12,2), default 0)
- adjustment_reason (text, nullable)
- net_commission (decimal(12,2))
- timestamps

## Technical Notes

**Laravel Backend:**
- Controllers: `StaffPerformanceController` (dashboard, sales, itemsSold, tableTurnover, leaderboard, me), `StaffAttendanceController` (index, hours, lateMarks, exportRegister), `StaffTipsController` (index, store, split), `PerformanceScorecardController` (index, weights, recalculate), `CommissionRuleController` (index, store, update, destroy), `CommissionRunController` (index, create, preview, approve, paid), `StaffPayrollController` (export).
- Services: `StaffSalesService::perWaiter($filter)` aggregates `orders.staff_id` for revenue, item counts, AOV. `TableTurnoverService::perWaiter($filter)` joins `tables` to `orders` to compute dwell time and turnover.
- Attendance: `StaffAttendanceService::computeDailyHours($punches)` handles split shifts, overnight shifts, overtime (configurable threshold). Late marks computed from `scheduled_in - actual_in`.
- Tips: `StaffTipService::recordTip($orderId, $amount, $splitRecipients)` creates tip record and split entries; on payment refund, tips are reversed.
- Scorecard: `ScorecardService::compute($userId, $period)` calls sub-services for each KPI, applies admin weights, normalizes to 0-100. Cached per user per period.
- Commission: `CommissionService::preview($ruleId, $period)` and `::run($ruleId, $period)` evaluate the rule formula against the staff's data; tiered uses bracket lookup; category-specific joins `order_items.category_id`; team-pool divides equally among eligible staff. TDS computed per Indian rules (1% on commission above basic threshold, configurable).
- Payroll Export: `PayrollExportService::build($period, $format)` produces:
  - Tally XML: `<TALLYMESSAGE><VOUCHER>` with attendance, basic, commission, deductions
  - GreytHR Excel: 12-column template (Employee ID, Name, Days, Hours, Earnings, Deductions, Net, Bank)
  - Zoho Payroll JSON: API-compatible JSON
  - Generic CSV: Standard format
- Background jobs: `RecomputeScorecardJob` (daily), `GeneratePayrollJob` (large teams), `EmailPayrollJob` (with password-protected attachment via `stream_encrypt`).
- Audit: All commission approvals, payroll exports, scorecard weight changes logged with user.

**React Frontend:**
- Components: `StaffDashboardPage`, `StaffLeaderboard`, `StaffSalesReportPage`, `StaffSalesDetailDrawer`, `StaffItemsSoldReport`, `StaffTableTurnoverChart`, `AttendanceCalendar`, `AttendanceLateMarksTab`, `PerformanceScorecardPage`, `ScoreWeightConfigurator`, `ScoreRadarChart`, `CommissionRuleForm`, `CommissionRunPreview`, `PayrollExportConfig`, `PersonalStaffDashboard`, `StaffProfilePage`.
- State: Zustand `useStaffStore` for selected period, filters, current staff context.
- Charts: Recharts for leaderboard trends, score radar, attendance distribution, hours stacked bars.
- Calendar: Custom grid component for attendance matrix (rows × days) with color-coded cells.
- Real-time: WebSocket/SSE updates for leaderboard when new orders come in (optional, performance consideration).
- Personal dashboard: Limited view (own data only) for staff role; uses `/me` endpoint.

## Subtasks
1. [ ] Create `staff_attendance`, `staff_tips`, `commission_rules`, `commission_runs`, `commission_run_items` migrations and models
2. [ ] Build `StaffSalesService` for per-waiter aggregation (orders, revenue, AOV, items, discount)
3. [ ] Build `StaffItemsSoldService` joining order_items with orders to rank items by staff
4. [ ] Build `TableTurnoverService` joining tables + orders for dwell time and turnover calculation
5. [ ] Build `StaffAttendanceService` with split-shift, overtime, late-mark logic
6. [ ] Build `StaffTipService` with split-percentage validation and per-bill recording
7. [ ] Build `ScorecardService` with configurable weights and per-KPI normalization
8. [ ] Build `CommissionService` supporting flat, tiered, category-specific, team-pool formulas
9. [ ] Build `PayrollExportService` for Tally XML, GreytHR Excel, Zoho JSON, generic CSV
10. [ ] Implement `GeneratePayrollJob` (queued, password-protected zip output)
11. [ ] Build `EmailPayrollJob` with password-encrypted attachment delivery
12. [ ] Build `RecomputeScorecardJob` (daily scheduled, recalculates all active staff)
13. [ ] Build React StaffDashboardPage with leaderboard, score distribution, top performers
14. [ ] Build React StaffSalesReportPage with sortable table, drill-down drawer, comparison
15. [ ] Build React AttendancePage with calendar grid, late-marks tab, export register
16. [ ] Build React ScorecardPage with weight configurator, radar chart, recalculation trigger
17. [ ] Build React CommissionRuleForm, CommissionRunPreview, PayrollExportConfig components
18. [ ] Build React PersonalStaffDashboard with self-only metrics (own sales, rank, commission, tips)
19. [ ] Implement role-based access (HR, manager, self) via Laravel Policy and React route guards
20. [ ] Write tests for sales aggregation, attendance computation, score formula, commission tiers, payroll export formats

## Testing Criteria
- [ ] Per-waiter report correctly aggregates orders by staff_id with accurate AOV and item counts
- [ ] Attendance computation handles split shifts (two punch pairs in one day) and overnight shifts correctly
- [ ] Late marks flag a punch as "late" only when actual_in > scheduled_in by more than grace period
- [ ] Tips with 70/20/10 split distribute amount accurately to three staff with no rounding loss
- [ ] Scorecard weights sum to 100% (validation); changing weights triggers recalculation for all staff
- [ ] Tiered commission: ₹50k at 1%, next ₹30k at 2%, above at 3% computes correct bracket totals
- [ ] Category-specific commission: 5% on wine sales and 2% on food computes correctly per category
- [ ] Payroll export Tally XML imports cleanly into Tally ERP and creates correct vouchers
- [ ] Personal dashboard shows only the logged-in staff's metrics, not other staff
- [ ] Multi-outlet consolidated report matches sum of per-outlet reports
- [ ] HR role sees all staff; outlet manager sees only own outlet; staff sees only self
- [ ] Commission approval locks the run; subsequent edits to source orders don't affect approved commission
