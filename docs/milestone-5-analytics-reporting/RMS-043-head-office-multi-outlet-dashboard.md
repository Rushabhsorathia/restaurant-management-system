# RMS-043: Head Office Multi-Outlet Dashboard

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-043 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-041 (Sales Dashboard), RMS-010 (Billing), RMS-020 (Inventory) |

## User Story

As a head office executive or chain owner, I want a consolidated dashboard showing performance across all outlets with comparison metrics, consolidated P&L, cross-outlet inventory visibility, and centralized menu management, so that I can monitor my entire restaurant chain from a single screen and make informed strategic decisions.

## Description

The Head Office Multi-Outlet Dashboard is the executive command center for restaurant chains operating multiple outlets. Unlike the single-outlet sales dashboard (RMS-041), this dashboard aggregates data across all outlets in the chain, providing a bird's-eye view of chain-wide performance with the ability to drill down into individual outlet details.

The dashboard features outlet comparison tools that rank outlets by revenue, profitability, growth rate, and operational efficiency, highlighting best and worst performers. The consolidated P&L view combines revenue, cost of goods sold, labor costs, and overheads across all outlets into a unified profit and loss statement. The cross-outlet inventory view enables stock transfer planning by showing surplus and shortage across locations.

Centralized menu management allows head office to push menu changes, price updates, and new item launches across all outlets or selected outlets in bulk, ensuring brand consistency while accommodating local pricing variations. The outlet health scorecard provides a composite rating for each outlet based on revenue growth, customer satisfaction, operational metrics, and compliance adherence.

## Acceptance Criteria

- [ ] Dashboard displays consolidated chain-wide KPIs: total revenue, total orders, combined AOV, total outlets, active outlets
- [ ] Outlet comparison table ranks all outlets by selectable metric (revenue, growth, orders, AOV, profit margin) with sort and filter
- [ ] Best and worst performing outlets are highlighted with trend indicators and percentage change
- [ ] Consolidated P&L statement combines all outlets with line items: gross revenue, discounts, net revenue, COGS, gross profit, labor cost, rent, utilities, other overheads, net profit
- [ ] P&L supports drill-down from consolidated line item to outlet-level contribution
- [ ] Cross-outlet inventory view shows stock levels for key items across all outlets with surplus/shortage indicators
- [ ] Centralized menu management allows bulk price updates to selected outlets with preview before applying
- [ ] Menu item push can target all outlets, specific outlets, or outlets in a specific city/zone
- [ ] Outlet health scorecard displays composite score (0-100) per outlet with sub-scores for revenue, satisfaction, operations, compliance
- [ ] Outlet list supports filtering by city, zone, status (active/inactive), franchise/company-owned
- [ ] All consolidated views support date range selection with comparison to previous period
- [ ] Data refresh is near real-time (within 5 minutes of transaction) with last-updated timestamp displayed

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| HO Dashboard Overview | /head-office/dashboard | Consolidated chain-wide dashboard |
| Outlet Comparison | /head-office/outlets/comparison | Outlet ranking and comparison table |
| Consolidated P&L | /head-office/finance/pnl | Unified profit and loss statement |
| Cross-Outlet Inventory | /head-office/inventory/cross-outlet | Multi-outlet stock visibility |
| Centralized Menu Management | /head-office/menu/management | Bulk menu and price management |
| Outlet Health Scorecard | /head-office/outlets/scorecard | Composite outlet scoring |

### Screen Details

#### HO Dashboard Overview (/head-office/dashboard)

**Layout:**
- Top bar: Title "Head Office Dashboard", chain name, date range picker, [Export] button, last updated timestamp
- Chain KPI Row: 5 KPI cards
  - Total Chain Revenue (with YoY trend)
  - Total Orders (with WoW trend)
  - Active Outlets / Total Outlets
  - Chain Avg Order Value
  - Chain Net Profit Margin %
- Chain Revenue Trend Chart: Multi-line area chart showing each outlet's revenue over time (top 5 outlets individually, rest grouped as "Other")
- Outlet Performance Bar Chart: Horizontal bar chart ranking outlets by revenue for selected period
- Outlet Map View: Toggle button to switch from chart to map view showing outlet locations with bubble size proportional to revenue and color indicating growth (green=growing, red=declining)
- Recent Activity Feed: Chronological list of significant events (new outlet opened, menu price changed, threshold alert triggered, large order placed)

**Components:**
- `HeadOfficeDashboard` (main container)
- `ChainKPICard` (KPI card with trend)
- `ChainRevenueTrendChart` (multi-line Recharts chart)
- `OutletPerformanceBarChart` (horizontal bar chart)
- `OutletMapView` (map with markers - react-leaflet)
- `RecentActivityFeed` (activity stream)

#### Outlet Comparison (/head-office/outlets/comparison)

**Layout:**
- Filter bar: City dropdown, zone dropdown, franchise/company toggle, outlet status, date range
- Metric selector: Dropdown to choose ranking metric (Revenue, Orders, AOV, Growth %, Profit Margin %, Customer Rating)
- Best/Worst highlight cards: 2 cards showing top performer and bottom performer with key stats
- Outlet Comparison Table (main data grid):

**Outlet Comparison Table Columns:**
| Outlet Name | City | Type | Status | Revenue | Orders | AOV | Growth % | Profit Margin % | Rating | Health Score | Trend |
|-------------|------|------|--------|---------|--------|-----|----------|-----------------|--------|-------------|-------|

- Type: Franchise / Company-Owned
- Revenue column: sortable, formatted with currency
- Growth %: green/red text with arrow
- Health Score: colored badge (green 80+, yellow 60-79, red <60)
- Trend: sparkline mini-chart
- Row click: navigates to outlet detail dashboard (single-outlet view)
- Column visibility toggle
- Export to Excel button
- Pagination or virtual scroll for 50+ outlets

#### Consolidated P&L (/head-office/finance/pnl)

**Layout:**
- Header: Period selector (monthly/quarterly/yearly), date range, comparison toggle (vs previous period, vs same period last year), [Export PDF] [Export Excel] buttons
- View toggle: [Consolidated] [By Outlet] tabs
- P&L Statement Table:

**Consolidated P&L Structure:**
| Line Item | Current Period | Previous Period | Change % | % of Revenue |
|-----------|----------------|-----------------|----------|--------------|
| Gross Revenue | X | Y | +Z% | 100% |
| Less: Discounts | (X) | (Y) | | |
| Less: Comps/Voids | (X) | (Y) | | |
| Net Revenue | X | Y | | |
| Cost of Goods Sold | (X) | (Y) | | XX% |
| **Gross Profit** | **X** | **Y** | | **XX%** |
| Labor Costs | (X) | (Y) | | |
| Rent & Occupancy | (X) | (Y) | | |
| Utilities | (X) | (Y) | | |
| Marketing | (X) | (Y) | | |
| Other Overheads | (X) | (Y) | | |
| **EBITDA** | **X** | **Y** | | **XX%** |
| Depreciation | (X) | (Y) | | |
| **Net Profit** | **X** | **Y** | | **XX%** |

- "By Outlet" tab: Same P&L structure but with columns per outlet showing each outlet's contribution
- Each line item row is expandable to show outlet-level breakdown
- Net Profit row highlighted in bold with green/red background based on positive/negative

#### Cross-Outlet Inventory (/head-office/inventory/cross-outlet)

**Layout:**
- Filter bar: Category dropdown, storage type, stock status (all/surplus/shortage/optimal), search by item name
- Summary strip: Total SKUs tracked, Outlets with shortages, Items needing transfer, Total inventory value
- Cross-Outlet Inventory Matrix Table:

**Cross-Outlet Inventory Matrix Columns:**
| Item Name | Category | Unit | Outlet A Qty | Outlet B Qty | Outlet C Qty | Total Qty | Reorder Level | Status | Suggested Transfer |
|-----------|----------|------|--------------|--------------|--------------|-----------|---------------|--------|-------------------|

- Outlet columns are dynamic based on selected outlets
- Status: color-coded per cell (green=optimal, yellow=low, red=shortage, blue=surplus)
- Suggested Transfer: auto-calculated recommendation (e.g., "Move 5kg from Outlet B to Outlet A")
- Row expand: show last 7-day consumption trend per outlet
- [Create Transfer Order] button on rows with suggested transfer

#### Centralized Menu Management (/head-office/menu/management)

**Layout:**
- Header: [New Item Push] [Bulk Price Update] [Sync Menu] buttons, outlet selector (all/specific/city/zone)
- Menu Items Table:

**Centralized Menu Table Columns:**
| Item Name | Category | Base Price | Outlet A Price | Outlet B Price | Price Variance | Availability | Last Updated | Actions |
|-----------|----------|------------|----------------|----------------|----------------|--------------|--------------|---------|

- Base Price: head office reference price
- Outlet prices: editable inline, shows variance from base price in red/green
- Availability: toggle (Available/Unavailable across all outlets)
- Actions: [Edit] [Price Update] [Push to Outlets] [View Outlet Variance]

**Bulk Price Update Modal:**
- Target outlets: Multi-select (all, or specific outlets, or by city/zone)
- Update type: Radio ([ ] Fixed Price, [ ] Percentage Increase, [ ] Percentage Decrease, [ ] Absolute Amount Change)
- Update value: Number input (with preview of new prices)
- Category filter: Optional - apply only to specific categories
- Preview table: Shows item name, current price, new price, change amount for first 20 items
- Confirmation checkbox: "I understand this will update prices for X items across Y outlets"
- Buttons: [Cancel] [Apply Update]

**New Item Push Modal:**
- Item details: Name, description, category, base price, preparation time, tax rate, image upload
- Target outlets: Multi-select or by group
- Outlet-specific pricing: Optional table to set custom price per outlet
- Availability schedule: Optional time-based availability
- Preview: Shows how item will appear in POS
- Buttons: [Cancel] [Push to Selected Outlets]

#### Outlet Health Scorecard (/head-office/outlets/scorecard)

**Layout:**
- Outlet selector: Dropdown or search to select outlet
- Overall Health Score: Large circular gauge (0-100) with color zones
- Score Breakdown: 4 category cards with sub-scores:
  - Revenue Performance (40% weight): Revenue growth, target achievement, AOV trend
  - Customer Satisfaction (25% weight): Avg rating, feedback volume, complaint rate
  - Operational Efficiency (20% weight): Order prep time, table turnover, waste %
  - Compliance (15% weight): Inventory accuracy, cash variance, staff attendance
- Each category expandable to show individual metrics with actual vs target
- Period comparison: Score this period vs last period with trend
- Peer Comparison: This outlet's score vs chain average and vs top performer
- Action recommendations: Auto-generated suggestions based on lowest scoring areas

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/head-office/dashboard/summary | Get consolidated chain KPI summary |
| GET | /api/v1/head-office/dashboard/revenue-trend | Get chain revenue trend with per-outlet breakdown |
| GET | /api/v1/head-office/outlets/comparison | Get outlet comparison data with metric ranking |
| GET | /api/v1/head-office/outlets/best-worst | Get best and worst performing outlets |
| GET | /api/v1/head-office/finance/pnl | Get consolidated P&L statement |
| GET | /api/v1/head-office/finance/pnl/by-outlet | Get P&L broken down by outlet |
| GET | /api/v1/head-office/inventory/cross-outlet | Get cross-outlet inventory matrix |
| GET | /api/v1/head-office/inventory/transfer-suggestions | Get auto-suggested stock transfers |
| POST | /api/v1/head-office/menu/bulk-price-update | Apply bulk price update to selected outlets |
| POST | /api/v1/head-office/menu/push-item | Push new menu item to selected outlets |
| POST | /api/v1/head-office/menu/sync | Sync menu from head office to outlets |
| GET | /api/v1/head-office/menu/items | Get centralized menu items with outlet price variance |
| GET | /api/v1/head-office/outlets/{id}/scorecard | Get health scorecard for specific outlet |
| GET | /api/v1/head-office/activity-feed | Get recent chain activity feed |
| GET | /api/v1/head-office/export | Export consolidated report (params: type, format, date_range) |

## Database Tables

### outlets (extends existing table)
| Additional Columns | Type | Description |
|--------------------|------|-------------|
| outlet_code | VARCHAR(50) UNIQUE | Short code for outlet (e.g., MUM-001) |
| type | ENUM('company_owned','franchise') | Ownership type |
| city | VARCHAR(100) | City name |
| zone_id | BIGINT UNSIGNED FK NULL | Reference to zones table |
| latitude | DECIMAL(10,8) NULL | GPS latitude |
| longitude | DECIMAL(11,8) NULL | GPS longitude |
| opened_date | DATE | Outlet opening date |
| status | ENUM('active','inactive','suspended') | Operational status |
| manager_id | BIGINT UNSIGNED FK NULL | Outlet manager user reference |

### consolidated_pnl_entries (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| period_type | ENUM('monthly','quarterly','yearly') | Period granularity |
| period_date | DATE | Period start date |
| gross_revenue | DECIMAL(14,2) | Gross sales |
| discounts | DECIMAL(14,2) | Total discounts |
| comps_voids | DECIMAL(14,2) | Comps and voids |
| net_revenue | DECIMAL(14,2) | Net sales |
| cogs | DECIMAL(14,2) | Cost of goods sold |
| labor_cost | DECIMAL(14,2) | Staff costs |
| rent | DECIMAL(14,2) | Rent/occupancy |
| utilities | DECIMAL(14,2) | Utilities |
| marketing | DECIMAL(14,2) | Marketing spend |
| other_overheads | DECIMAL(14,2) | Other expenses |
| depreciation | DECIMAL(14,2) | Depreciation |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| INDEX | (outlet_id, period_type, period_date) | |

### outlet_health_scores (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| score_date | DATE | Date of score calculation |
| overall_score | DECIMAL(5,2) | Composite score 0-100 |
| revenue_score | DECIMAL(5,2) | Revenue performance sub-score |
| satisfaction_score | DECIMAL(5,2) | Customer satisfaction sub-score |
| operations_score | DECIMAL(5,2) | Operational efficiency sub-score |
| compliance_score | DECIMAL(5,2) | Compliance sub-score |
| revenue_growth_pct | DECIMAL(8,2) | Revenue growth percentage |
| target_achievement_pct | DECIMAL(8,2) | Target vs actual |
| avg_rating | DECIMAL(3,2) | Average customer rating |
| avg_prep_time | INT NULL | Average preparation time (seconds) |
| table_turnover | DECIMAL(4,2) NULL | Table turnovers per day |
| waste_percentage | DECIMAL(5,2) NULL | Food waste percentage |
| cash_variance | DECIMAL(10,2) NULL | Cash drawer variance |
| staff_attendance_pct | DECIMAL(5,2) NULL | Staff attendance rate |
| recommendations | JSON | Auto-generated recommendations |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### centralized_menu_pushes (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| push_type | ENUM('new_item','price_update','availability','sync') | Type of push |
| menu_item_id | BIGINT UNSIGNED FK NULL | Reference to menu_items for existing items |
| item_data | JSON NULL | New item configuration data |
| target_type | ENUM('all','selected','city','zone') | Target selection type |
| target_outlets | JSON | Array of outlet IDs |
| price_config | JSON NULL | Price configuration per outlet |
| status | ENUM('pending','processing','completed','failed') | Push status |
| affected_outlets_count | INT | Number of outlets affected |
| applied_by | BIGINT UNSIGNED FK | User who initiated |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### ho_activity_log (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK NULL | Related outlet (NULL for chain-wide) |
| activity_type | VARCHAR(100) | Type of activity |
| description | TEXT | Activity description |
| metadata | JSON NULL | Additional context data |
| performed_by | BIGINT UNSIGNED FK | User who performed action |
| created_at | TIMESTAMP | |

## Technical Notes

### Backend (Laravel 11)
- **Controller**: `App\Http\Controllers\Api\V1\HeadOffice\DashboardController`
- **Controller**: `App\Http\Controllers\Api\V1\HeadOffice\OutletComparisonController`
- **Controller**: `App\Http\Controllers\Api\V1\HeadOffice\FinanceController`
- **Controller**: `App\Http\Controllers\Api\V1\HeadOffice\InventoryController`
- **Controller**: `App\Http\Controllers\Api\V1\HeadOffice\MenuManagementController`
- **Service**: `App\Services\HeadOffice\ConsolidationService` - aggregates data across outlets
- **Service**: `App\Services\HeadOffice\HealthScoreCalculator` - calculates composite scores
- **Job**: `App\Jobs\CalculateHealthScores` - daily job to compute outlet health scores
- **Job**: `App\Jobs\PushMenuChange` - async menu push to outlets
- **Job**: `App\Jobs\GenerateConsolidatedPnl` - scheduled P&L generation
- **Middleware**: `OutletScopeMiddleware` - restricts data access based on user role (head office sees all, outlet manager sees own)
- **Routes**: Under `auth:sanctum` + `permission:head_office.view` or `role:head_office`

### Multi-Tenant Data Access
- All queries use `OutletScope` global scope that filters by user's accessible outlets
- Head office role bypasses scope to access all outlets
- Use database read replica for heavy aggregation queries to avoid POS impact
- Cache consolidated data in Redis with 5-minute TTL

### Frontend (React + Vite)
- **Page Components**: `src/pages/head-office/HODashboardPage.jsx`, `OutletComparisonPage.jsx`, `ConsolidatedPnlPage.jsx`, `CrossOutletInventoryPage.jsx`, `CentralizedMenuManagementPage.jsx`, `OutletScorecardPage.jsx`
- **Layout**: Dedicated `HeadOfficeLayout.jsx` with sidebar navigation for HO modules
- **Map Component**: `react-leaflet` for outlet map view with OpenStreetMap tiles
- **Gauge Component**: Custom SVG circular gauge for health score, or use `react-circular-progressbar`
- **Data Grid**: `@tanstack/react-table` for complex comparison tables with sorting, filtering, column toggles
- **Permissions**: Route guard checking for `head_office` role before rendering HO pages

## Subtasks

1. [ ] Extend outlets table migration with new columns (type, city, zone, coordinates, etc.)
2. [ ] Create migrations for consolidated_pnl_entries, outlet_health_scores, centralized_menu_pushes, ho_activity_log
3. [ ] Implement ConsolidationService for chain-wide data aggregation
4. [ ] Build DashboardController with summary, revenue trend, and activity feed endpoints
5. [ ] Implement OutletComparisonController with ranking and best/worst identification
6. [ ] Build FinanceController with consolidated and by-outlet P&L endpoints
7. [ ] Implement HealthScoreCalculator service with weighted scoring algorithm
8. [ ] Build CalculateHealthScores daily scheduled job
9. [ ] Implement InventoryController with cross-outlet matrix and transfer suggestions
10. [ ] Build MenuManagementController with bulk price update and item push endpoints
11. [ ] Implement PushMenuChange job for async outlet menu updates
12. [ ] Add OutletScopeMiddleware for role-based data access control
13. [ ] Build HeadOffice layout with sidebar navigation in React
14. [ ] Create HODashboardPage with KPI cards, revenue chart, map view, activity feed
15. [ ] Build OutletComparisonPage with sortable comparison table
16. [ ] Build ConsolidatedPnlPage with P&L statement and drill-down
17. [ ] Build CrossOutletInventoryPage with matrix table and transfer suggestions
18. [ ] Build CentralizedMenuManagementPage with bulk update and item push modals
19. [ ] Build OutletScorecardPage with gauge and sub-score breakdown
20. [ ] Add feature tests for multi-outlet data aggregation accuracy
21. [ ] Add permission tests for head office vs outlet manager access scoping
22. [ ] Performance test consolidated queries with 50+ outlets

## Testing Criteria

- [ ] Verify chain KPI summary aggregates correctly across all outlets
- [ ] Verify outlet comparison ranking sorts correctly by each metric
- [ ] Verify best/worst outlet identification matches manual calculation
- [ ] Verify consolidated P&L totals match sum of individual outlet P&Ls
- [ ] Verify P&L drill-down shows correct outlet-level contribution
- [ ] Verify cross-outlet inventory matrix shows correct stock levels per outlet
- [ ] Verify surplus/shortage indicators trigger at correct thresholds
- [ ] Verify suggested transfer calculation is logical (from surplus to shortage)
- [ ] Verify bulk price update applies to all selected outlets correctly
- [ ] Verify price update preview shows correct old/new prices before applying
- [ ] Verify new item push creates item in all targeted outlets
- [ ] Verify health score calculation produces scores in 0-100 range
- [ ] Verify health score sub-categories use correct weights
- [ ] Verify outlet manager role cannot access head office endpoints
- [ ] Verify dashboard loads in under 5 seconds with 20+ outlets
- [ ] Verify map view renders outlet locations correctly
