# RMS-044: City-wise & Zone-wise Grouping

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-044 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | Medium |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-043 (Head Office Dashboard) |

## User Story

As a regional manager or zone manager, I want to group outlets by city and zone with dedicated dashboards, performance comparisons, inventory pooling, and staff allocation views, so that I can manage my territory efficiently and identify regional trends and opportunities.

## Description

The City-wise and Zone-wise Grouping module adds a geographic organizational layer to the multi-outlet dashboard. While the Head Office Dashboard (RMS-043) provides a chain-wide view, this module enables intermediate management tiers (zone managers, city managers) to focus on their specific geographic territory with relevant data, comparisons, and operational tools.

The system supports a hierarchical geographic grouping: zones (e.g., North Zone, South Zone, West Zone, East Zone) contain cities (e.g., Mumbai, Pune, Ahmedabad under West Zone), and cities contain outlets. Zone managers see all outlets within their assigned zone, city managers see outlets within their city. This scoping ensures managers focus on relevant data without being overwhelmed by chain-wide noise.

The module includes regional trend analysis that identifies geographic patterns (e.g., South Indian items sell better in Bangalore outlets), zone-wise inventory pooling that enables stock sharing within a zone to reduce wastage and shortages, and staff allocation views that help zone managers balance workforce across outlets based on demand patterns.

## Acceptance Criteria

- [ ] Outlets can be assigned to zones and cities via administrative interface with drag-and-drop or dropdown assignment
- [ ] Zone manager role sees only outlets within their assigned zone; city manager sees only their city's outlets
- [ ] Zone dashboard displays zone-level KPIs: zone revenue, zone order count, zone AOV, outlet count, zone growth %
- [ ] City-wise performance comparison table ranks cities within a zone by revenue, growth, and outlet count
- [ ] Regional trend analysis chart shows sales trends by city/zone with item preference heatmap
- [ ] Zone-wise inventory pooling view shows aggregate stock and enables inter-outlet transfer requests within zone
- [ ] Territory-wise sales comparison overlays multiple cities/zones on a single trend chart
- [ ] Zone-wise staff allocation view shows headcount, labor cost, and productivity per outlet in the zone
- [ ] Geographic map view shows outlets plotted by location with zone/city boundary coloring
- [ ] Zone/city filters are available on all analytics pages for users with appropriate permissions
- [ ] Reports can be generated at zone level or city level with PDF/Excel export

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Zone Manager Dashboard | /zones/{zoneId}/dashboard | Zone-level overview dashboard |
| City Performance Comparison | /analytics/geo/city-comparison | City-wise ranking within zone |
| Regional Trends | /analytics/geo/regional-trends | Geographic trend and preference analysis |
| Zone Inventory Pooling | /zones/{zoneId}/inventory-pooling | Cross-outlet inventory within zone |
| Zone Staff Allocation | /zones/{zoneId}/staff-allocation | Staff distribution across zone outlets |
| Geographic Configuration | /admin/geo-config | Zone, city, and outlet assignment management |

### Screen Details

#### Zone Manager Dashboard (/zones/{zoneId}/dashboard)

**Layout:**
- Top bar: Zone selector dropdown, date range picker, [Export] button
- Zone KPI Row: 5 cards
  - Zone Total Revenue (with growth %)
  - Zone Total Orders
  - Zone AOV
  - Active Outlets in Zone
  - Zone Customer Satisfaction Score
- Zone Revenue Trend Chart: Area chart showing zone revenue over time with city-level breakdown as stacked areas
- City Performance Summary: Table showing each city in the zone with revenue, outlets, growth
- Zone Outlet List: Compact table of all outlets in zone with key metrics and health score
- Regional Alerts: Cards showing zone-specific alerts (outlet underperforming, inventory shortage in city, staff shortage)

**Components:**
- `ZoneManagerDashboard` (main container)
- `ZoneKPICard` (zone-level metric card)
- `ZoneRevenueTrendChart` (stacked area chart by city)
- `CityPerformanceTable` (per-city summary in zone)
- `ZoneOutletList` (compact outlet listing)
- `RegionalAlertCard` (zone-specific alert widget)

**City Performance Table Columns:**
| City | Outlets | Revenue | Orders | AOV | Growth % | Top Item | Satisfaction |
|------|---------|---------|--------|-----|----------|----------|--------------|

**Zone Outlet List Columns:**
| Outlet | City | Manager | Revenue | Growth % | Health Score | Status |
|--------|------|---------|---------|----------|-------------|--------|

#### City Performance Comparison (/analytics/geo/city-comparison)

**Layout:**
- Filter bar: Zone selector, date range, metric selector (Revenue/Orders/AOV/Growth/Satisfaction)
- City Ranking Bar Chart: Horizontal bar chart ranking cities by selected metric
- City Comparison Table:

**City Comparison Table Columns:**
| Rank | City | Zone | Outlets | Revenue | Orders | AOV | Growth % | Profit Margin | Avg Rating | Best Outlet | Worst Outlet |
|------|------|------|---------|---------|--------|-----|----------|---------------|-------------|-------------|--------------|

- Row click: navigates to city detail view with all outlets in that city
- Export button
- City growth heatmap: mini map of zone with cities colored by growth rate

#### Regional Trends (/analytics/geo/regional-trends)

**Layout:**
- Filter bar: Zone/city selector, date range, comparison toggle (vs other zones, vs chain average)
- Regional Sales Trend: Multi-line chart comparing selected cities/zones over time
- Item Preference Heatmap: Matrix of cities (rows) x top items/categories (columns) with color intensity showing sales volume
- Regional Bestsellers Table:

**Regional Bestsellers Table Columns:**
| Rank | Item Name | Mumbai | Pune | Ahmedabad | Bangalore | Chennai | Regional Avg | Top City |
|------|-----------|--------|------|-----------|-----------|---------|--------------|----------|

- Each city column shows quantity sold for that item
- Top City highlighted
- Identifies regional taste preferences

- Daypart Analysis by Region: Stacked bar chart showing sales distribution by daypart (breakfast, lunch, snacks, dinner) per city

#### Zone Inventory Pooling (/zones/{zoneId}/inventory-pooling)

**Layout:**
- Filter: Item category, stock status, search by item name
- Summary: Total inventory value in zone, items in surplus, items in shortage, potential transfers
- Zone Inventory Pooling Matrix:

**Zone Inventory Pooling Matrix Columns:**
| Item | Category | Unit | Outlet 1 | Outlet 2 | Outlet 3 | Zone Total | Reorder Point | Status | Suggested Action |
|------|----------|------|----------|----------|----------|------------|---------------|--------|-----------------|

- Each outlet column: color-coded stock level (red=shortage, yellow=low, green=optimal, blue=surplus)
- Suggested Action: auto-generated (e.g., "Transfer 10kg from Outlet 2 to Outlet 1")
- [Create Transfer Request] button per row with suggested action
- Transfer Requests Section: List of pending inter-outlet transfer requests within zone

**Transfer Request Modal:**
- Source outlet: Dropdown (outlets with surplus)
- Destination outlet: Dropdown (outlets with shortage)
- Item: Pre-filled from row, or searchable dropdown
- Quantity: Number input with unit
- Transfer date: Date picker
- Notes: Text area
- Buttons: [Cancel] [Submit Request]

**Transfer Request Table Columns:**
| Request ID | From Outlet | To Outlet | Item | Qty | Status | Requested Date | Completed Date | Actions |
|-------------|-------------|-----------|------|-----|--------|---------------|----------------|---------|

- Status: Pending, Approved, In Transit, Completed, Rejected
- Actions: [Approve] [Reject] [Mark Completed] based on status and user role

#### Zone Staff Allocation (/zones/{zoneId}/staff-allocation)

**Layout:**
- Filter: Date/week selector, role filter (all/waiter/cook/manager)
- Zone Staff Summary: Total headcount, total labor cost, avg labor cost per outlet, labor cost as % of revenue
- Staff Allocation Table by Outlet:

**Staff Allocation Table Columns:**
| Outlet | City | Total Staff | Waiters | Cooks | Managers | Others | Labor Cost | Labor Cost % | Revenue per Staff | Productivity Score |
|--------|------|-------------|---------|-------|----------|--------|------------|--------------|-------------------|-------------------|

- Labor Cost %: red if > 25%, yellow if 20-25%, green if < 20%
- Revenue per Staff: productivity indicator
- Productivity Score: composite of orders/staff, sales/staff, rating contribution
- Row expand: shows individual staff members with their metrics

- Staff Distribution Chart: Pie/donut chart showing staff distribution across outlets in zone
- Shift Coverage View: Calendar grid showing shift coverage per outlet per day with gaps highlighted

#### Geographic Configuration (/admin/geo-config)

**Layout:**
- Zone Management Section:
  - Zone list table: Zone Name, Cities Count, Outlets Count, Zone Manager, Actions [Edit] [Delete]
  - [Add Zone] button opens modal
- City Management Section:
  - City list table: City Name, Zone, Outlets Count, City Manager, Actions [Edit] [Delete]
  - [Add City] button opens modal
- Outlet Assignment Section:
  - Outlet list with current zone/city assignment
  - Dropdown or drag-drop to reassign outlet to zone/city
  - Bulk assignment: select multiple outlets and assign to zone/city

**Add Zone Modal:**
- Zone Name: Text input
- Zone Code: Text input (e.g., NORTH, SOUTH)
- Zone Manager: User selector dropdown
- Description: Text area
- Buttons: [Cancel] [Create Zone]

**Add City Modal:**
- City Name: Text input
- State: Text input or dropdown
- Zone: Dropdown (select parent zone)
- City Manager: User selector dropdown
- Buttons: [Cancel] [Create City]

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/zones | List all zones |
| POST | /api/v1/zones | Create new zone |
| GET | /api/v1/zones/{id} | Get zone detail |
| PUT | /api/v1/zones/{id} | Update zone |
| DELETE | /api/v1/zones/{id} | Delete zone |
| GET | /api/v1/zones/{id}/dashboard | Get zone dashboard summary data |
| GET | /api/v1/cities | List all cities (filterable by zone) |
| POST | /api/v1/cities | Create new city |
| PUT | /api/v1/cities/{id} | Update city |
| DELETE | /api/v1/cities/{id} | Delete city |
| GET | /api/v1/analytics/geo/city-comparison | Get city-wise performance comparison |
| GET | /api/v1/analytics/geo/regional-trends | Get regional trend data |
| GET | /api/v1/analytics/geo/item-preferences | Get item preference heatmap by city |
| GET | /api/v1/zones/{id}/inventory-pooling | Get zone inventory pooling matrix |
| POST | /api/v1/zones/{id}/transfer-requests | Create inter-outlet transfer request |
| GET | /api/v1/zones/{id}/transfer-requests | List transfer requests in zone |
| PUT | /api/v1/transfer-requests/{id} | Update transfer request status |
| GET | /api/v1/zones/{id}/staff-allocation | Get zone staff allocation data |
| PUT | /api/v1/outlets/{id}/assignment | Assign outlet to zone/city |

## Database Tables

### zones (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| zone_name | VARCHAR(100) | Zone display name |
| zone_code | VARCHAR(20) UNIQUE | Short code (NORTH, SOUTH, etc.) |
| description | TEXT NULL | Zone description |
| manager_id | BIGINT UNSIGNED FK NULL | Zone manager user reference |
| is_active | BOOLEAN DEFAULT TRUE | Active flag |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### cities (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| city_name | VARCHAR(100) | City display name |
| state | VARCHAR(100) | State name |
| zone_id | BIGINT UNSIGNED FK | Reference to zones table |
| manager_id | BIGINT UNSIGNED FK NULL | City manager user reference |
| is_active | BOOLEAN DEFAULT TRUE | Active flag |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### inter_outlet_transfer_requests (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| request_number | VARCHAR(50) UNIQUE | Human-readable request ID |
| from_outlet_id | BIGINT UNSIGNED FK | Source outlet |
| to_outlet_id | BIGINT UNSIGNED FK | Destination outlet |
| zone_id | BIGINT UNSIGNED FK | Zone context |
| inventory_item_id | BIGINT UNSIGNED FK | Item being transferred |
| quantity | DECIMAL(10,3) | Transfer quantity |
| unit | VARCHAR(20) | Unit of measure |
| status | ENUM('pending','approved','rejected','in_transit','completed','cancelled') | Request status |
| requested_by | BIGINT UNSIGNED FK | Requesting user |
| approved_by | BIGINT UNSIGNED FK NULL | Approving user |
| requested_at | TIMESTAMP | Request timestamp |
| approved_at | TIMESTAMP NULL | Approval timestamp |
| completed_at | TIMESTAMP NULL | Completion timestamp |
| notes | TEXT NULL | Transfer notes |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### geo_sales_aggregations (new - extends sales_aggregations concept)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| zone_id | BIGINT UNSIGNED FK | Reference to zones table |
| city_id | BIGINT UNSIGNED FK | Reference to cities table |
| outlet_id | BIGINT UNSIGNED FK | Reference to outlets table |
| aggregation_date | DATE | Date of aggregation |
| total_revenue | DECIMAL(12,2) | Total revenue |
| total_orders | INT | Order count |
| total_items_sold | INT | Items sold |
| labor_cost | DECIMAL(12,2) NULL | Labor cost for the day |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| INDEX | (zone_id, city_id, aggregation_date) | |

### geo_item_preferences (new)
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED PK | Auto increment |
| city_id | BIGINT UNSIGNED FK | Reference to cities table |
| menu_item_id | BIGINT UNSIGNED FK | Reference to menu_items table |
| category_id | BIGINT UNSIGNED FK | Reference to categories table |
| aggregation_date | DATE | Date |
| quantity_sold | INT | Quantity sold in that city |
| revenue | DECIMAL(12,2) | Revenue from item in city |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| INDEX | (city_id, aggregation_date) | |

## Technical Notes

### Backend (Laravel 11)
- **Controller**: `App\Http\Controllers\Api\V1\Geo\ZoneController`
- **Controller**: `App\Http\Controllers\Api\V1\Geo\CityController`
- **Controller**: `App\Http\Controllers\Api\V1\Analytics\GeoAnalyticsController`
- **Controller**: `App\Http\Controllers\Api\V1\Inventory\TransferRequestController`
- **Service**: `App\Services\Geo\GeoAggregationService` - geographic data aggregation
- **Service**: `App\Services\Inventory\TransferSuggestionService` - suggests transfers within zone
- **Policy**: `ZonePolicy` - ensures zone managers only access their zone data
- **Scope**: `ZoneScope` - global scope filtering outlets by user's zone/city assignment
- **Middleware**: `ZoneAccessMiddleware` - verifies user has access to requested zone
- **Routes**: Under `auth:sanctum` + appropriate permission checks

### Role-Based Geographic Access
- Head Office role: access all zones and cities
- Zone Manager role: access only their assigned zone (zone_id from user profile)
- City Manager role: access only their assigned city (city_id from user profile)
- Outlet Manager role: access only their assigned outlet
- Implement via `ZoneScope` global scope on Outlet model

### Frontend (React + Vite)
- **Page Components**: `src/pages/geo/ZoneDashboardPage.jsx`, `CityComparisonPage.jsx`, `RegionalTrendsPage.jsx`, `ZoneInventoryPoolingPage.jsx`, `ZoneStaffAllocationPage.jsx`, `GeoConfigPage.jsx`
- **Components**: `src/components/geo/ZoneKPICard.jsx`, `CityPerformanceTable.jsx`, `ItemPreferenceHeatmap.jsx`, `InventoryPoolingMatrix.jsx`, `TransferRequestModal.jsx`, `StaffAllocationTable.jsx`, `ZoneSelector.jsx`
- **Heatmap**: Custom CSS grid heatmap or use `react-calendar-heatmap` adapted for matrix display
- **Map**: `react-leaflet` for geographic map view with zone boundary overlays
- **Guard**: React route guard checking user's zone/city access before rendering

## Subtasks

1. [ ] Create migrations for zones, cities, inter_outlet_transfer_requests, geo_sales_aggregations, geo_item_preferences tables
2. [ ] Add zone_id and city_id foreign keys to outlets table
3. [ ] Add zone_id/city_id assignment to users table for manager roles
4. [ ] Implement ZoneController and CityController with CRUD operations
5. [ ] Build ZonePolicy and ZoneScope for geographic access control
6. [ ] Implement ZoneAccessMiddleware for route-level zone access verification
7. [ ] Build GeoAggregationService for zone/city level data aggregation
8. [ ] Implement GeoAnalyticsController with city comparison and regional trends endpoints
9. [ ] Build item preference aggregation and heatmap data endpoint
10. [ ] Implement TransferRequestController with full lifecycle management
11. [ ] Build TransferSuggestionService for auto-generating transfer recommendations
12. [ ] Add geographic data aggregation scheduled jobs
13. [ ] Create Zone Manager Dashboard React page with KPI cards and charts
14. [ ] Build City Comparison page with ranking table and bar chart
15. [ ] Build Regional Trends page with multi-line chart and item preference heatmap
16. [ ] Build Zone Inventory Pooling page with matrix and transfer request workflow
17. [ ] Build Transfer Request Modal and transfer request management table
18. [ ] Build Zone Staff Allocation page with staff metrics and shift coverage
19. [ ] Build Geographic Configuration admin page for zone/city management
20. [ ] Add geographic filters to existing analytics pages
21. [ ] Add unit tests for ZonePolicy access control
22. [ ] Add feature tests for geographic data scoping

## Testing Criteria

- [ ] Verify zone manager can only see outlets in their assigned zone
- [ ] Verify city manager can only see outlets in their assigned city
- [ ] Verify head office can see all zones and cities
- [ ] Verify zone dashboard aggregates data correctly from all outlets in zone
- [ ] Verify city comparison ranking is correct for each metric
- [ ] Verify item preference heatmap shows correct sales volume per city
- [ ] Verify regional bestsellers correctly identifies top city per item
- [ ] Verify inventory pooling matrix shows correct stock levels per outlet
- [ ] Verify transfer suggestion calculation is logical (surplus to shortage)
- [ ] Verify transfer request lifecycle: create -> approve -> in_transit -> completed
- [ ] Verify staff allocation metrics calculate correctly (labor cost %, revenue per staff)
- [ ] Verify geographic configuration CRUD operations work correctly
- [ ] Verify outlet reassignment to different zone/city updates scoping
- [ ] Verify zone/city filters on analytics pages scope data correctly
