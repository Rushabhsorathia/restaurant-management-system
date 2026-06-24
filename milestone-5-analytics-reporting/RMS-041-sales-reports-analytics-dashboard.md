# RMS-041: Sales Reports & Analytics Dashboard

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-041 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-010 (Billing), RMS-015 (Order Management) |

## User Story

As a restaurant owner or outlet manager, I want a real-time sales analytics dashboard with multiple time granularity views, revenue trend charts, item-wise ranking, and comparison analysis, so that I can monitor business performance and make data-driven decisions to grow revenue.

## Description

The Sales Reports & Analytics Dashboard is the primary business intelligence interface for the restaurant management system. It provides a comprehensive real-time view of sales performance with the ability to drill down from yearly trends to hourly transactions. The dashboard aggregates data from all order sources (dine-in, takeaway, online) and presents it through interactive charts, ranked tables, and KPI widgets.

The dashboard supports multiple time granularity views (hourly, daily, weekly, monthly, yearly) enabling both tactical (today's lunch rush performance) and strategic (year-over-year growth) analysis. Comparison modes (YoY, MoM, WoW) overlay historical data on current trends to highlight growth patterns and seasonal variations. The system includes peak hour analysis to help managers optimize staffing, and item-wise sales ranking to identify best-sellers and underperformers.

All dashboard views and reports can be exported to PDF and Excel formats, and reports can be scheduled for automatic email delivery to stakeholders on configurable frequencies. The dashboard is optimized for fast loading with Redis caching and pre-aggregated data tables.

## Acceptance Criteria

- [ ] Dashboard displays real-time sales KPIs (total revenue, order count, average order value, items sold) updating every 60 seconds
- [ ] User can switch between hourly, daily, weekly, monthly, and yearly granularity views
- [ ] Revenue trend chart displays as interactive line/area chart with tooltip on hover showing exact values
- [ ] Item-wise sales ranking table shows top/bottom items by quantity and revenue with category filter
- [ ] Category performance breakdown shows revenue distribution as pie/donut chart with percentage
- [ ] Peak hour analysis heatmap visualizes sales intensity by day-of-week x hour-of-day
- [ ] Comparison mode overlays YoY, MoM, or WoW data with percentage change indicators (green/red)
- [ ] Date range picker supports preset ranges (today, yesterday, this week, last 7 days, this month, last 30 days, this quarter, this year, custom) and custom range selection
- [ ] Export to PDF generates a formatted report with charts as static images, KPIs, and data tables
- [ ] Export to Excel generates a multi-sheet workbook with raw data, summary, and chart data
- [ ] Scheduled email reports can be configured with frequency (daily/weekly/monthly), recipient list, and attached format (PDF/Excel)
- [ ] Dashboard filters support outlet selection (for multi-outlet users), order source (dine-in/takeaway/online), and payment mode
- [ ] Dashboard loads in under 3 seconds for any selected date range
- [ ] All charts are responsive and render correctly on tablet (768px) and desktop (1280px+) viewports

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Sales Dashboard | /analytics/sales | Main real-time sales analytics dashboard with KPIs and charts |
| Sales Detail Report | /analytics/sales/detail | Detailed tabular sales report with drill-down |
| Scheduled Reports Config | /analytics/sales/schedule | Configuration screen for scheduled email reports |
| Export Modal | (modal) | Export format and options selection modal |

### Screen Details

#### Sales Dashboard (/analytics/sales)

**Layout:**
- Top bar: Page title "Sales Analytics", outlet selector dropdown, date range picker, export button, schedule button
- KPI Row: 4 KPI cards in a horizontal row
  - Total Revenue (large number, trend arrow with % change vs previous period)
  - Total Orders (count, trend arrow)
  - Average Order Value (amount, trend arrow)
  - Items Sold (count, trend arrow)
- Granularity Tabs: [Hourly] [Daily] [Weekly] [Monthly] [Yearly] - toggle button group
- Comparison Toggle: [None] [YoY] [MoM] [WoW] - segmented control
- Revenue Trend Chart: Full-width area chart (Recharts AreaChart) showing revenue over selected granularity with comparison overlay as dashed line
- Two-column section:
  - Left: Item-wise Sales Ranking table
  - Right: Category Performance donut chart (Recharts PieChart)
- Peak Hour Heatmap: Full-width grid heatmap (7 rows x 24 columns or condensed) with color intensity from blue (low) to red (high)
- Payment Mode Breakdown: Horizontal stacked bar chart (Cash, Card, UPI, Wallet, Online)

**Components:**
- `SalesAnalyticsDashboard` (main container)
- `KPICard` (reusable metric card with trend indicator)
- `GranularitySelector` (tab button group)
- `ComparisonToggle` (segmented control)
- `RevenueTrendChart` (Recharts AreaChart with comparison overlay)
- `ItemSalesRankingTable` (sortable data table)
- `CategoryPerformanceChart` (Recharts PieChart)
- `PeakHourHeatmap` (custom grid component)
- `PaymentModeChart` (Recharts BarChart)
- `DateRangePicker` (preset + custom calendar)
- `ExportModal` (format selection modal)
- `ScheduleReportModal` (scheduling configuration modal)

**Item-wise Sales Ranking Table Columns:**
| Rank | Item Name | Category | Qty Sold | Revenue | Avg Rating | % of Total |
|------|-----------|----------|----------|---------|------------|------------|

- Sortable by any column
- Category filter dropdown above table
- Toggle: Top 10 / Bottom 10 / All Items
- Search box for specific item name
- Row click opens item sales detail modal

**Export Modal:**
- Format selection: Radio buttons (PDF / Excel / CSV)
- Date range: Pre-filled from current dashboard selection, editable
- Include sections: Checkboxes ([x] KPI Summary, [x] Trend Chart, [x] Item Ranking, [x] Category Breakdown, [x] Peak Hours)
- Buttons: [Cancel] [Download]

**Schedule Report Modal:**
- Report name: Text input
- Frequency: Dropdown (Daily, Weekly, Monthly)
- Day/Time: Conditional fields (daily: time picker; weekly: day-of-week + time; monthly: day-of-month + time)
- Format: Radio (PDF, Excel)
- Recipients: Multi-select email tags input with add/remove
- Active: Toggle switch
- Buttons: [Cancel] [Save Schedule]

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/analytics/sales/summary | Get KPI summary (total revenue, orders, AOV, items) for date range |
| GET | /api/v1/analytics/sales/trend | Get revenue trend data by granularity (hourly/daily/weekly/monthly/yearly) |
| GET | /api/v1/analytics/sales/comparison | Get comparison data (YoY/MoM/WoW) for selected date range |
| GET | /api/v1/analytics/sales/items-ranking | Get item-wise sales ranking with filters (top/bottom, category) |
| GET | /api/v1/analytics/sales/category-performance | Get category-wise revenue and percentage breakdown |
| GET | /api/v1/analytics/sales/peak-hours | Get peak hour heatmap data (day x hour matrix) |
| GET | /api/v1/analytics/sales/payment-breakdown | Get payment mode-wise revenue distribution |
| GET | /api/v1/analytics/sales/export | Generate and download report (params: format=pdf/xlsx/csv, date_from, date_to, sections[]) |
| POST | /api/v1/analytics/sales/schedule | Create scheduled email report configuration |
| GET | /api/v1/analytics/sales/schedules | List all scheduled report configurations |
| PUT | /api/v1/analytics/sales/schedules/{id} | Update scheduled report configuration |
| DELETE | /api/v1/analytics/sales/schedules/{id} | Delete scheduled report configuration |

## Database Tables

### sales_aggregations (new - pre-computed aggregation table)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| aggregation_date | DATE | The date of aggregation |
| aggregation_hour | TINYINT NULL | Hour of day (0-23) for hourly aggregation, NULL for daily |
| granularity | ENUM('hourly','daily','weekly','monthly','yearly') | Aggregation level |
| total_revenue | DECIMAL(12,2) | Sum of order totals |
| total_orders | INT | Count of orders |
| total_items_sold | INT | Count of items sold |
| total_discount | DECIMAL(12,2) | Sum of discounts |
| total_tax | DECIMAL(12,2) | Sum of tax collected |
| payment_cash | DECIMAL(12,2) | Cash payment total |
| payment_card | DECIMAL(12,2) | Card payment total |
| payment_upi | DECIMAL(12,2) | UPI payment total |
| payment_other | DECIMAL(12,2) | Other payment total |
| created_at | TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | Record update |
| INDEX | (outlet_id, granularity, aggregation_date) | Composite index |

### item_sales_aggregations (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| menu_item_id | BIGINT UNSIGNED FK | Reference to menu_items table |
| category_id | BIGINT UNSIGNED FK | Reference to categories table |
| aggregation_date | DATE | Date of aggregation |
| quantity_sold | INT | Total quantity sold |
| revenue | DECIMAL(12,2) | Total revenue from item |
| discount_amount | DECIMAL(12,2) | Discount applied to item |
| order_count | INT | Number of orders containing item |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| INDEX | (outlet_id, aggregation_date, menu_item_id) | |

### scheduled_reports (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| outlet_id | BIGINT UNSIGNED FK NULL | NULL for all outlets |
| report_type | VARCHAR(50) | Type of report (sales_summary, item_ranking, etc.) |
| report_name | VARCHAR(255) | User-given name |
| frequency | ENUM('daily','weekly','monthly') | Delivery frequency |
| frequency_config | JSON | Day of week, day of month, time |
| format | ENUM('pdf','xlsx','csv') | Export format |
| recipients | JSON | Array of email addresses |
| filters | JSON | Date range type, outlet, etc. |
| is_active | BOOLEAN | Active flag |
| last_run_at | TIMESTAMP NULL | Last execution time |
| created_by | BIGINT UNSIGNED FK | User who created |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

## Technical Notes

### Backend (Laravel 11)
- **Controller**: `App\Http\Controllers\Api\V1\Analytics\SalesAnalyticsController`
- **Service**: `App\Services\Analytics\SalesAggregationService` - handles data aggregation logic
- **Job**: `App\Jobs\AggregateSalesData` - scheduled job (hourly) to populate aggregation tables
- **Job**: `App\Jobs\GenerateScheduledReport` - generates and emails scheduled reports
- **Export**: Use Maatwebsite/Laravel-Excel for Excel/CSV, Barryvdh/laravel-dompdf for PDF
- **Caching**: Cache dashboard responses in Redis with 5-minute TTL, key pattern: `analytics:sales:{outlet_id}:{granularity}:{date_range_hash}`
- **Routes**: Defined in `routes/api.php` under middleware group `auth:sanctum` + `permission:analytics.view`
- **Query Optimization**: Use aggregation tables for pre-computed data, fall back to raw queries only for custom date ranges not covered

### Frontend (React + Vite)
- **Page Component**: `src/pages/analytics/SalesAnalyticsPage.jsx`
- **Container**: `src/components/analytics/SalesAnalyticsDashboard.jsx`
- **Chart Library**: Recharts (already in project) for all chart visualizations
- **State Management**: React Query (TanStack Query) for data fetching with 60-second refetch interval for real-time feel
- **Date Handling**: date-fns for date range calculations
- **Export**: Trigger download via `window.location` to API export endpoint with auth token in query param or use axios blob download
- **Responsive**: Use Tailwind CSS grid with responsive breakpoints (grid-cols-1 md:grid-cols-2 lg:grid-cols-4)

### Performance Considerations
- Pre-aggregate hourly data via scheduled job running every hour
- Nightly job rolls up hourly to daily, daily to weekly/monthly/yearly
- Redis cache with tag-based invalidation when new orders are placed
- Lazy load chart components with React.lazy to reduce initial bundle
- Paginate item ranking table server-side for large catalogs

## Subtasks

1. [ ] Create database migrations for sales_aggregations, item_sales_aggregations, scheduled_reports tables
2. [ ] Implement SalesAggregationService with methods for each granularity
3. [ ] Build hourly aggregation job and register in Laravel Scheduler
4. [ ] Build nightly rollup job (hourly -> daily -> weekly -> monthly -> yearly)
5. [ ] Implement SalesAnalyticsController with all 7 data endpoints
6. [ ] Add Redis caching layer with TTL and invalidation
7. [ ] Create SalesAnalyticsDashboard React page with layout and KPI cards
8. [ ] Implement GranularitySelector and ComparisonToggle components
9. [ ] Build RevenueTrendChart with Recharts AreaChart and comparison overlay
10. [ ] Build ItemSalesRankingTable with sorting, filtering, and pagination
11. [ ] Build CategoryPerformanceChart donut chart component
12. [ ] Build PeakHourHeatmap grid component
13. [ ] Build PaymentModeChart stacked bar chart
14. [ ] Implement DateRangePicker with presets and custom range
15. [ ] Implement ExportModal with PDF and Excel generation
16. [ ] Implement ScheduleReportModal and scheduled_reports CRUD endpoints
17. [ ] Build GenerateScheduledReport job with email delivery
18. [ ] Add unit tests for SalesAggregationService
19. [ ] Add feature tests for all API endpoints
20. [ ] Performance test dashboard load time for 1-year date range

## Testing Criteria

- [ ] Verify KPI summary matches manual calculation from orders table for a test date range
- [ ] Verify trend data granularity switching updates chart correctly
- [ ] Verify comparison overlay shows correct historical period (YoY = same period last year)
- [ ] Verify item ranking sorts correctly by quantity and revenue
- [ ] Verify category performance percentages sum to 100%
- [ ] Verify peak hour heatmap color intensity correlates with sales volume
- [ ] Verify PDF export contains all selected sections with proper formatting
- [ ] Verify Excel export opens correctly in Microsoft Excel and Google Sheets
- [ ] Verify scheduled report email arrives at configured time with correct attachment
- [ ] Verify dashboard loads in under 3 seconds for 1-year date range
- [ ] Verify outlet filter correctly scopes data for multi-outlet users
- [ ] Verify responsive layout on tablet (768px) and mobile (375px) breakpoints
