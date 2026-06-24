# RMS-013: Recipe Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-013 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-006, RMS-012 |

## User Story
As a chef, I want to define recipes for each menu item with exact ingredient quantities, so that raw materials are auto-deducted from inventory when items are sold and I can calculate accurate food costs.

## Description
Recipe management connects the menu to the inventory. Each menu item has a recipe listing the raw materials and quantities required to prepare one serving. When an order item is sent to kitchen (KOT), the recipe automatically deducts those raw materials from stock. Recipes can be multi-stage (a base sauce is a semi-finished recipe used in multiple dishes). The system calculates the food cost per item based on current raw material rates, enabling margin analysis.

## Acceptance Criteria
- [ ] Each menu item can have a recipe with multiple raw material ingredients
- [ ] Each ingredient specifies: material, quantity per serving, unit
- [ ] Semi-finished/intermediate recipes: a recipe can use another recipe as an ingredient (e.g., "Paneer Tikka" uses "Marinade" recipe)
- [ ] Auto stock deduction when order item is sent to kitchen (linked to KOT)
- [ ] Recipe costing: total cost = sum of (ingredient qty x current avg rate)
- [ ] Yield calculation: input quantity vs output quantity (e.g., 1 KG raw chicken -> 700 GM cooked)
- [ ] Recipe variants: different sizes (Regular/Medium/Large) with different ingredient quantities
- [ ] Recipe status: Draft / Active / Disabled
- [ ] Recipe update triggers cost recalculation for linked menu item
- [ ] Recipe without auto-deduct option (for items where stock tracking isn't feasible)
- [ ] Bulk recipe import from Excel (for initial setup)
- [ ] Cost vs Selling Price margin display on recipe page

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Recipe List | /inventory/recipes | All recipes with cost and margin |
| Recipe Builder | /inventory/recipes/create, /:id/edit | Build recipe with ingredients |
| Recipe Detail | /inventory/recipes/:id | Cost breakdown, linked menu items |
| Semi-Finished Goods | /inventory/recipes/semi-finished | List of intermediate recipes |

### Screen Details

**Recipe List (/inventory/recipes)**
- Filters: Category, Status (Active/Draft), Margin (Low <30% / Medium / High >60%)
- Table: Menu Item | Category | Portion Size | Total Cost | Selling Price | Margin % | Status | Actions
- Color coding: Margin < 30% = red, 30-50% = yellow, > 50% = green
- Export button

**Recipe Builder (/inventory/recipes/create)**
- Link to Menu Item (dropdown, searchable)
- Portion Size: Regular / Medium / Large / Custom
- Yield section: Input qty+unit, Output qty+unit, Yield % (auto-calculated)
- Ingredients table:
  - Add row: Material search (dropdown with current stock + rate display)
  - Columns: Material | Qty | Unit | Rate (auto from avg) | Cost (qty x rate)
  - Drag to reorder
  - Delete row
- Semi-finished ingredient: instead of raw material, select another recipe -> shows its sub-ingredients
- Sub-recipe nesting indicator: "This recipe uses: Marinade (sub-recipe)"
- Auto-deduct toggle: "Deduct from stock on KOT generation" (default ON)
- Cost Summary: Total Ingredient Cost | Per Serving Cost | Waste/Factor | Final Cost
- Selling Price (from linked menu item, read-only)
- Margin: (Selling Price - Final Cost) / Selling Price * 100
- Save as Draft / Publish

**Recipe Detail (/inventory/recipes/:id)**
- Recipe header: Menu Item name, portion size, status
- Cost breakdown table: Material | Qty | Unit | Rate | Cost | % of Total
- Visual: Pie chart of cost distribution by ingredient
- Yield calculation display
- Final cost per serving (large)
- Margin analysis: Cost | Selling Price | Margin % | Margin Amount
- Linked menu items (for semi-finished recipes: shows which dishes use this)
- Cost trend: last 30 days (has cost changed due to rate fluctuation?)
- Edit / Duplicate / Disable buttons

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/recipes | List recipes |
| POST | /api/v1/recipes | Create recipe |
| GET | /api/v1/recipes/{id} | Get recipe detail |
| PUT | /api/v1/recipes/{id} | Update recipe |
| DELETE | /api/v1/recipes/{id} | Delete recipe |
| POST | /api/v1/recipes/{id}/publish | Publish recipe (status -> Active) |
| GET | /api/v1/recipes/{id}/cost | Get current cost breakdown |
| GET | /api/v1/recipes/menu-item/{menuItemId} | Get recipe by menu item |
| POST | /api/v1/recipes/bulk-import | Import recipes from Excel |

## Database Tables

```
recipes:
  id (bigint, PK)
  outlet_id (bigint, FK)
  menu_item_id (bigint, FK -> menu_items)
  portion_size (enum: regular, medium, large, custom)
  yield_input_qty (decimal 10,3, nullable)
  yield_input_unit (varchar 20, nullable)
  yield_output_qty (decimal 10,3, nullable)
  yield_output_unit (varchar 20, nullable)
  yield_percentage (decimal 5,2, nullable)
  total_cost (decimal 10,2, default 0) -- auto-calculated
  cost_per_serving (decimal 10,2, default 0) -- with yield factor
  auto_deduct (boolean, default true)
  status (enum: draft, active, disabled)
  timestamps

recipe_ingredients:
  id (bigint, PK)
  recipe_id (bigint, FK -> recipes)
  material_id (bigint, FK -> raw_materials, nullable) -- if raw material
  sub_recipe_id (bigint, FK -> recipes, nullable) -- if semi-finished
  ingredient_type (enum: raw_material, sub_recipe)
  quantity (decimal 10,3)
  unit (varchar 20)
  rate (decimal 10,2) -- snapshot at recipe creation
  cost (decimal 10,2) -- quantity x rate
  sort_order (int, default 0)
  timestamps
```

## Technical Notes
- **Backend**: `RecipeController.php` + `RecipeService.php`. Cost recalculation triggered on: recipe save, material rate change (avg_rate update). Sub-recipe cost aggregation: recursively sum sub-recipe costs.
- **Auto-deduction**: Listener on `OrderSentToKitchen` event -> for each order item, find recipe -> deduct raw materials via `InventoryService::outward()`. Batch FIFO consumption. If sub-recipes exist, deduct their raw materials recursively.
- **Yield Factor**: cost_per_serving = total_cost / yield_percentage. E.g., 1000 GM inputs at 100 cost, yield 80% -> cost per serving = 100/0.80 = 125.
- **Rate Snapshot**: recipe_ingredients stores rate at save time but cost is recalculated using CURRENT avg_rate for real-time analysis.

## Subtasks
1. [ ] Create recipes and recipe_ingredients migrations
2. [ ] Build Recipe model with self-referencing for sub-recipes
3. [ ] Build RecipeIngredient model
4. [ ] Build RecipeService: calculate cost, handle sub-recipe nesting
5. [ ] Implement yield factor calculation
6. [ ] Build auto-deduction event listener on OrderSentToKitchen
7. [ ] Implement recursive sub-recipe material deduction
8. [ ] Build RecipeController API
9. [ ] Build cost recalculation on material rate change
10. [ ] Build Excel bulk import (recipe template download + upload)
11. [ ] Build React recipe list with margin color coding
12. [ ] Build recipe builder with dynamic ingredient rows and live cost
13. [ ] Build recipe detail with cost breakdown pie chart
14. [ ] Write recipe deduction tests
15. [ ] Test multi-level sub-recipe costing

## Testing Criteria
- [ ] Create recipe for "Paneer Butter Masala" with 5 ingredients -> total cost calculated
- [ ] Sub-recipe: "Marinade" used in "Paneer Tikka" -> cost includes marinade ingredients
- [ ] Order 2x Paneer Butter Masala -> correct qty of each material deducted
- [ ] Material rate changes -> recipe cost auto-updates
- [ ] Yield: 1KG chicken input, 700GM output -> cost per serving = total / 0.70
- [ ] Margin display: cost 80, selling 250 -> margin 68%
- [ ] Auto-deduct disabled -> order does not deduct stock
- [ ] Sub-recipe disabled -> parent recipe shows warning
