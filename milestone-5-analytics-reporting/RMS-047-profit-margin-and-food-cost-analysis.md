# RMS-047: Profit Margin & Food Cost Analysis

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-047 |
| **Type** | Story |
| **Epic** | Analytics & Reporting |
| **Milestone** | M5 - Analytics & Reporting |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-002 (Menu Management), RMS-005 (Inventory & Recipes), RMS-007 (Purchase & Stock), RMS-014 (Billing), RMS-041 (Sales Dashboard) |

## User Story
As a restaurant owner, head chef, or finance manager, I want detailed profit margin and food cost analysis — item-level margin, recipe cost vs sale price, category profitability, menu engineering matrix, waste cost impact, theoretical vs actual food cost, and COGS reports — so that I can price my menu correctly, eliminate money-losing dishes, and run a financially healthy kitchen.

## Description
This story delivers the profitability intelligence layer of the RMS, transforming menu and inventory data into clear insight on which dishes make money, which break even, and which quietly bleed margin. For a restaurant, profit margin is the difference between the menu price the customer pays and the *recipe cost* of producing that dish (ingredients at current purchase cost, plus labor allocation, plus overhead per portion). Without this analysis, restaurants routinely price items 5-15% below their true cost.

The module provides **item-level margin** showing each menu item with its current selling price, recipe cost, gross profit, and margin percentage. **Recipe cost vs sale price** view compares the two side-by-side with warning flags when margin falls below a configurable threshold (default 60% for food, 70% for beverages). **Category profitability** aggregates margins across categories (Starters, Mains, Desserts, Beverages, Bar) to identify which menu sections drive profit and which are loss leaders.

The **Menu Engineering Matrix** plots items on a 2x2: Stars (high popularity, high margin), Plowhorses (high popularity, low margin), Puzzles (low popularity, high margin), and Dogs (low popularity, low margin). It recommends actions: protect Stars, redesign Plowhorses (raise price or reduce cost), promote Puzzles, eliminate Dogs. The **waste cost impact** module tracks wastage from inventory and prices the lost food at recipe cost, showing the true margin erosion. **Theoretical vs Actual food cost** is the key KPI: theoretical cost = sum of recipes sold × recipe cost; actual cost = purchases + opening stock − closing stock. The variance (typically 3-8% in well-run restaurants; 15%+ in poorly-run) reveals theft, portion drift, waste, and unrecorded comps. **COGS reports** break down cost of goods sold by period, category, outlet, supplier, and ingredient. **Sub-recipe support** allows components (sauces, marinades) to have their own recipe, with cost flowing into parent dishes automatically.

## Acceptance Criteria
- [ ] Item-level margin report shows for every menu item: selling price, recipe cost, gross profit, margin %, GP rank
- [ ] Recipe cost is auto-computed from current ingredient purchase prices (FIFO or weighted average per RMS-005)
- [ ] Low-margin warning flags items below configurable threshold (default 60% food, 70% beverages) with red indicator
- [ ] Category profitability shows aggregated revenue, recipe cost, gross profit, and margin % per category with drill-down to items
- [ ] Menu Engineering Matrix plots items on Stars/Plowhorses/Puzzles/Dogs quadrant using sales-volume and margin as axes
- [ ] Waste cost impact report shows cost value of all wastage events by item, category, reason, and period
- [ ] Theoretical food cost = sum of (recipes sold × recipe cost at sale time) per period
- [ ] Actual food cost = (opening stock + purchases − closing stock) per period, with variance vs theoretical
- [ ] COGS report breaks down cost by period, category, outlet, supplier, and ingredient
- [ ] Sub-recipe support: components (sauces, marinades) have their own recipe; parent dish recipe cost auto-includes sub-recipe cost
- [ ] Recipe cost updates automatically when ingredient purchase price changes (recompute via queued job)
- [ ] Price-change simulation: manager can preview margin impact of proposed price change before saving
- [ ] Multi-outlet consolidated view; per-outlet view available; outlet comparison side-by-side
- [ ] Period comparison (MoM, QoQ, YoY) for margin, food cost %, waste value
- [ ] All reports exportable to Excel, PDF, and CSV

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Margin Dashboard | /analytics/margin | Overview of margin health, food cost %, top/low margin items |
| Item-Level Margin Report | /analytics/margin/items | Per-item margin, recipe cost, GP with low-margin warnings |
| Menu Engineering Matrix | /analytics/margin/menu-engineering | Stars/Plowhorses/Puzzles/Dogs quadrant with recommendations |
| Food Cost Analysis | /analytics/margin/food-cost | Theoretical vs actual food cost with variance |
| Waste & Variance | /analytics/margin/waste | Waste cost impact and inventory variance |
| Recipe Cost Manager | /analytics/margin/recipes | Recipe cost breakdown and price-simulation tool |

### Screen Details

**Margin Dashboard (/analytics/margin)**
- Layout: Top filter bar + 4 KPI cards + 3 panels (Margin trend, Category breakdown, Top 5 low-margin items) + quick actions.
- Filter bar: Date range, outlet (multi-select), category multi-select, low-margin threshold configurator.
- KPI cards: Avg Gross Margin %, Total COGS, Theoretical vs Actual Food Cost %, Total Waste Value.
- Margin trend chart: Line chart of gross margin % over the selected period (daily/weekly/monthly), with target line overlay.
- Category breakdown: Horizontal bar chart — Category | Revenue | COGS | Gross Profit | Margin %.
- Top 5 low-margin items: Sortable list with item name, category, current price, recipe cost, margin %, loss per 100 sold. Click opens item detail.
- Quick action: "Recompute Recipe Costs" (triggers queued job), "Run Price Simulation" (opens tool).

**Item-Level Margin Report (/analytics/margin/items)**
- Layout: Filter bar + data table + row drill-down + warning indicators.
- Filter bar: Date range, outlet, category, low-margin only toggle, search by item name, sort by margin/GP/revenue.
- Table columns: Item | Category | Sale Price | Recipe Cost | Gross Profit | Margin % | Units Sold | Total GP | Status.
- Status pill: Healthy (green, >threshold), Watch (yellow, within 5% of threshold), Critical (red, <threshold), Loss (negative).
- Row click drawer: Full recipe breakdown — ingredient list with qty per portion, current unit cost, line cost, total recipe cost; sub-recipe section if used; price-simulation input (try new price → see new margin).
- Inline action: "View Sales Trend" (sparkline), "Edit Menu Price" (link to menu editor), "View Recipe" (link to recipe manager). Bulk: "Apply Price Change %" with preview, "Export Selected".

**Menu Engineering Matrix (/analytics/margin/menu-engineering)**
- Layout: 2x2 matrix visualization (scatter plot) + side panel with item lists per quadrant + recommendation panel.
- Axes: X = Sales Volume (popularity, normalized 0-100), Y = Profit Margin (%). Quadrant boundaries configurable (default median of each axis).
- 4 Quadrants: Stars (high/high, green) | Plowhorses (high pop, low margin, orange) | Puzzles (low pop, high margin, blue) | Dogs (low/low, red).
- Each dot = one menu item; hover shows item name, sales count, margin %, GP. Click opens item detail drawer.
- Side panel: Tabs for each quadrant listing items with action recommendations:
  - Stars: "Promote, protect quality, don't discount"
  - Plowhorses: "Raise price 5-10% OR reduce recipe cost OR re-engineer recipe"
  - Puzzles: "Reposition on menu, train servers to upsell, rename for appeal"
  - Dogs: "Consider removing from menu, replace with new item"
- Period selector: 30/60/90 days; recomputes quadrant assignments.
- "Export Matrix" button: PNG image + CSV with item-quadrant mapping + recommendations.
- "View Item Detail" drawer with full margin and recipe breakdown.

**Food Cost Analysis (/analytics/margin/food-cost)**
- Layout: KPI strip + main comparison chart + period breakdown table + variance analysis.
- KPI strip: Theoretical Food Cost %, Actual Food Cost %, Variance % (green if actual < theoretical, red if >), Variance Value (₹).
- Main chart: Grouped bar chart — Period | Theoretical Cost (blue) | Actual Cost (orange). Variance line overlay.
- Period breakdown table: Date/Week/Month | Sales | Theoretical Cost | Actual Cost | Variance % | Variance Value.
- Variance analysis decomposes into: Waste (wastage events), Portion Variance (recipe cost drift), Unrecorded Comps, Theoretical Pricing (flag for review).
- "Investigate Variance" opens deep-dive with line-by-line reconciliation; "Export Variance Report" to Excel/PDF.

**Waste & Variance (/analytics/margin/waste)**
- Layout: Filter bar + KPI cards + waste trend chart + waste reason breakdown + cost impact table.
- Filter bar: Date range, outlet, category, waste reason (spoilage, prep error, expired, customer return, spillage), item.
- KPI cards: Total Waste Cost, Waste as % of Sales, Top Wasted Item, Waste Cost per Cover.
- Waste trend chart: Line chart of waste value over time, with reason breakdown stacked area.
- Waste reason breakdown: Pie/donut chart of cost by reason with percentages.
- Cost impact table: Item | Category | Reason | Quantity | Unit Cost | Total Waste Cost | Date | Recorded By.
- Row click: Drawer with full waste event detail (item, qty, reason, photo if any, staff who logged, supervisor approval).
- "Link to Variance" connects waste event to food-cost variance report; "Export Waste Register" to Excel/PDF.

**Recipe Cost Manager (/analytics/margin/recipes)**
- Layout: Recipe list (left) + recipe detail (center) + ingredient cost panel (right).
- Recipe list: Search/filter by item name, category. Each row shows item name, current recipe cost, last computed date, recompute button.
- Recipe detail: Item name, yield/portion size, total recipe cost, cost per portion. Ingredient list: Ingredient | Qty | Unit | Unit Cost | Line Cost | % of Recipe Cost.
- Sub-recipe section: If recipe contains sub-recipes, show as nested expandable rows with their own ingredient list.
- "Add Ingredient" button: Modal with ingredient selector, qty, unit.
- "Edit Qty" inline: Updates recipe cost live as qty changes.
- "Price Simulation" panel: Input new sale price → see new margin %, GP per portion, GP for projected sales volume.
- "Cost Trend" sparkline: Recipe cost over last 90 days (showing impact of ingredient price changes).
- "View Purchase History" link to ingredient purchase price history (from RMS-007).

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/analytics/margin/dashboard | Get margin dashboard summary (KPIs, trend, top items) |
| GET | /api/v1/analytics/margin/items | Get item-level margin report (filter: date, outlet, category) |
| GET | /api/v1/analytics/margin/items/{id}/breakdown | Get full recipe cost breakdown for item |
| POST | /api/v1/analytics/margin/items/{id}/simulate-price | Simulate margin impact of new price |
| GET | /api/v1/analytics/margin/categories | Get category-level profitability |
| GET | /api/v1/analytics/margin/menu-engineering | Get matrix data (item-quadrant mapping, recommendations) |
| GET | /api/v1/analytics/margin/food-cost | Get theoretical vs actual food cost analysis |
| GET | /api/v1/analytics/margin/food-cost/variance-decomposition | Decompose variance into waste/portion/comps/components |
| GET | /api/v1/analytics/margin/waste | Get waste events list (filter: date, reason, item) |
| GET | /api/v1/analytics/margin/cogs | Get COGS breakdown (by period, category, outlet, supplier, ingredient) |
| GET | /api/v1/analytics/margin/recipes/{itemId} | Get recipe cost detail for item |
| POST | /api/v1/analytics/margin/recipes/{itemId}/recompute | Trigger recipe cost recomputation |
| POST | /api/v1/analytics/margin/recipes/bulk-recompute | Trigger bulk recomputation (all or filtered items) |
| PUT | /api/v1/analytics/margin/recipes/{itemId}/ingredients/{ingId} | Update ingredient qty in recipe |
| POST | /api/v1/analytics/margin/recipes/{itemId}/ingredients | Add ingredient to recipe |
| GET | /api/v1/analytics/margin/period-comparison | Compare margin/food-cost metrics across periods |

## Database Tables

**recipe_costs** (cached/denormalized for performance)
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- item_id (foreignId, menu_items) — unique per item
- yield_portions (decimal(10,4), default 1)
- total_recipe_cost (decimal(12,4)) — sum of line costs at current prices
- cost_per_portion (decimal(12,4)) — total / yield
- ingredient_cost (decimal(12,4)) — sum of direct ingredients
- sub_recipe_cost (decimal(12,4), default 0) — sum of sub-recipe costs
- labor_cost_allocated (decimal(12,4), default 0) — optional labor allocation
- overhead_allocated (decimal(12,4), default 0) — optional overhead allocation
- cost_method (enum: fifo, weighted_average, latest, default weighted_average)
- last_computed_at (datetime)
- computed_by_job_id (string, nullable)
- timestamps
- unique([item_id])

**recipe_ingredients** (BOM lines)
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- parent_item_id (foreignId, menu_items) — the dish
- ingredient_id (foreignId, ingredients) — null if sub-recipe
- sub_recipe_item_id (foreignId, menu_items, nullable) — if this line is a sub-recipe reference
- quantity (decimal(12,4))
- unit_id (foreignId, units)
- unit_cost_at_recipe (decimal(12,4)) — cost snapshot when recipe was set
- line_cost (decimal(12,4)) — computed (qty × unit_cost)
- waste_factor_percent (decimal(5,2), default 0) — trim/loss allowance
- sort_order (unsignedInteger, default 0)
- notes (text, nullable)
- timestamps
- index: [parent_item_id]

**menu_engineering_assignments** (cached quadrant classification)
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- item_id (foreignId, menu_items, unique)
- period_start (date)
- period_end (date)
- sales_count (unsignedInteger)
- sales_volume_percentile (decimal(5,2))
- profit_margin_percent (decimal(5,2))
- gross_profit (decimal(12,2))
- quadrant (enum: star, plowhorse, puzzle, dog)
- recommendation (text) — auto-generated action text
- computed_at (datetime)
- timestamps
- index: [period_start, period_end, quadrant]

**food_cost_periods** (period-locked food cost calculations)
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets, nullable)
- period_start (date)
- period_end (date)
- opening_stock_value (decimal(14,2))
- purchases_value (decimal(14,2))
- closing_stock_value (decimal(14,2))
- theoretical_cost (decimal(14,2)) — sum of recipes sold × recipe cost
- actual_cost (decimal(14,2)) — (opening + purchases − closing)
- variance_value (decimal(14,2)) — actual − theoretical
- variance_percent (decimal(5,2))
- waste_value (decimal(12,2), default 0) — from waste events
- portion_variance_value (decimal(12,2), default 0)
- comp_value (decimal(12,2), default 0)
- unexplained_variance (decimal(14,2)) — variance − decompositions
- computed_at (datetime)
- timestamps
- unique([outlet_id, period_start, period_end])
- index: [restaurant_id, period_start]

**waste_events** (referenced via RMS-005, denormalized for analytics)
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- item_id (foreignId, menu_items, nullable) — if waste is a finished item
- ingredient_id (foreignId, ingredients, nullable) — if waste is raw ingredient
- quantity (decimal(12,4))
- unit_id (foreignId, units)
- unit_cost (decimal(12,4))
- total_waste_cost (decimal(12,4))
- waste_reason (enum: spoilage, expired, prep_error, spillage, customer_return, staff_meal, comp, other)
- waste_date (date, indexed)
- recorded_by (foreignId, users)
- approved_by (foreignId, users, nullable)
- photo_path (string, nullable)
- notes (text, nullable)
- timestamps
- index: [outlet_id, waste_date, waste_reason]

## Technical Notes

**Laravel Backend:**
- Controllers: `MarginController` (dashboard, items, categories, periodComparison, simulatePrice), `MenuEngineeringController` (matrix, recompute, export), `FoodCostController` (analysis, varianceDecomposition, export), `WasteController` (index, show, export, linkToVariance), `RecipeController` (show, updateIngredient, addIngredient, recompute, bulkRecompute).
- Services:
  - `RecipeCostService::compute($itemId, $method)` walks `recipe_ingredients` tree, resolves sub-recipes recursively, fetches current unit cost from `purchase_items` (FIFO or weighted average per RM-005), applies waste factor, returns cost_per_portion. Cached in `recipe_costs`.
  - `MarginService::perItem($filter)` joins `order_items` → `menu_items` → `recipe_costs`, computes GP, margin %, applies threshold flags.
  - `MenuEngineeringService::classify($itemId, $period)` computes sales_count and margin, normalizes to percentile, assigns quadrant (median split by default), generates recommendation text.
  - `FoodCostService::compute($outletId, $period)` calculates theoretical (from sales × recipe cost) and actual (from inventory stock + purchases) costs, variance, decomposition.
  - `WasteService::totalCost($filter)` aggregates `waste_events.total_waste_cost`.
- Background jobs: `RecomputeRecipeCostJob` (single), `BulkRecomputeRecipeCostJob` (all items, nightly), `ComputeFoodCostJob` (daily, per outlet), `RecomputeMenuEngineeringJob` (daily).
- Materialized view: For very large datasets, consider a daily-aggregated `menu_item_daily_sales` table summarizing sales count and GP per item per day.
- Excel/PDF export: `maatwebsite/excel` multi-sheet (Recipe Cost, Margin, Menu Engineering, Food Cost, Waste).
- Period lock: Once a food-cost period is locked, recipe costs for that period are frozen for historical reporting.

**React Frontend:**
- Components: `MarginDashboardPage`, `ItemMarginReportPage`, `ItemMarginDrawer`, `PriceSimulationPanel`, `MenuEngineeringMatrixPage`, `MenuEngineeringScatter`, `QuadrantListPanel`, `FoodCostAnalysisPage`, `FoodCostVarianceChart`, `WasteReportPage`, `WasteEventDrawer`, `RecipeCostManagerPage`, `RecipeIngredientEditor`, `SubRecipeTree`, `CostTrendSparkline`, `LowMarginAlertBadge`.
- State: Zustand `useMarginStore` for period, filter, threshold config.
- Charts: Recharts ScatterChart for menu engineering matrix (custom quadrant colors); ComposedChart for theoretical vs actual food cost (bar + line).
- Recipe editor: Inline editing with debounced auto-recompute; shows cost change in real-time as ingredient qty changes.
- Menu engineering: Interactive scatter with quadrant zoom; toggle median vs fixed threshold; click dot opens item drawer.

## Subtasks
1. [ ] Create `recipe_costs`, `recipe_ingredients`, `menu_engineering_assignments`, `food_cost_periods`, `waste_events` migrations and models
2. [ ] Build `RecipeCostService::compute` with sub-recipe recursion, waste factor, and FIFO/weighted-average/Latest cost methods
3. [ ] Build `MarginService::perItem` and `::perCategory` with threshold-based flagging
4. [ ] Build `MenuEngineeringService::classify` with median/fixed threshold, auto-recommendation generation
5. [ ] Build `FoodCostService::compute` for theoretical vs actual cost and variance decomposition
6. [ ] Build `PriceSimulationService` (preview-only, no menu price change)
7. [ ] Implement `RecomputeRecipeCostJob` (single + bulk) and `BulkRecomputeRecipeCostJob` (nightly scheduled)
8. [ ] Implement `ComputeFoodCostJob` (daily) and `RecomputeMenuEngineeringJob` (daily)
9. [ ] Build `WasteService` and `WasteController` with reason-based aggregation
10. [ ] Build `MenuEngineeringController::export` (PNG via headless browser or Chart.js canvas → server)
11. [ ] Build Excel/PDF multi-sheet exports for all margin reports
12. [ ] Build React MarginDashboardPage with KPIs, trend, top items
13. [ ] Build React ItemMarginReportPage with threshold flags, drill-down drawer, price simulation
14. [ ] Build React MenuEngineeringMatrixPage with interactive scatter, quadrant list, recommendations
15. [ ] Build React FoodCostAnalysisPage with comparison chart, variance decomposition
16. [ ] Build React WasteReportPage with reason breakdown, event detail drawer
17. [ ] Build React RecipeCostManagerPage with ingredient editor, sub-recipe tree, cost trend
18. [ ] Write tests for recipe cost (including sub-recipe recursion), quadrant classification, food cost variance, margin calculation edge cases (free items, voided bills, discounts)

## Testing Criteria
- [ ] Recipe cost for item with 5 ingredients at correct quantities and current unit costs matches manual calculation
- [ ] Sub-recipe recursion: parent dish cost includes sub-recipe's ingredient cost correctly; waste factor (e.g., 10% trim) is applied to ingredient line cost
- [ ] Low-margin flag correctly identifies items below 60% (food) / 70% (beverage) threshold
- [ ] Menu engineering quadrant classification places items in correct quadrant per median split
- [ ] Theoretical food cost = sum of recipes sold × recipe cost matches aggregate for period
- [ ] Actual food cost = (opening + purchases − closing) stock value matches inventory calculation
- [ ] Variance decomposes correctly: unexplained = variance − waste − portion − comps
- [ ] Recipe cost auto-updates when ingredient purchase price changes (FIFO/weighted avg); price simulation computes new margin % without modifying menu
- [ ] Multi-outlet consolidated margin report matches sum of per-outlet reports; period comparison shows correct MoM/QoQ/YoY for margin % and food cost %
- [ ] All exports (Excel, PDF, CSV) generate successfully and contain correct data
