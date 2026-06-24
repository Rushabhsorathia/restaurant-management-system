# RMS-006: Menu Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-006 |
| **Type** | Story |
| **Epic** | Menu |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | P0 - Critical |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-005 |

## User Story

As a restaurant manager, I want to create and manage my menu including categories, items, variations, add-ons, combos, item images, shortcodes, and GST/tax mapping per item, so that the POS can display an accurate menu and correctly calculate taxes.

## Description

This is a large ticket implementing the entire menu management module. It covers hierarchical categories (with sub-categories), menu items with rich metadata (name, description, code, shortcode, HSN code, image, base price, cost price, veg/non-veg flag), variations (size/portion with different prices), add-ons and add-on groups (single/multi select), and item combos (bundled items at a combo price). Each item maps to a tax config for correct GST calculation. Items have availability toggles and display ordering for the POS grid.

The POS-facing menu data must be cached (Redis) for fast loading and invalidated on changes. The frontend provides category management, an item list with search and filters, a detailed item form (with tabs for basic info, variations, add-ons, combos), a combo builder, a bulk import (CSV), and a quick availability toggle screen.

## Acceptance Criteria

- [ ] `GET /api/v1/menu/categories` returns hierarchical categories (with sub-categories).
- [ ] `POST/PUT/DELETE /api/v1/menu/categories` manage categories with parent, name, display order, icon, active.
- [ ] `GET /api/v1/menu/items` returns paginated, searchable items (filter by category, veg/non-veg, availability, combo).
- [ ] `POST /api/v1/menu/items` creates an item with all metadata including tax_config_id.
- [ ] `GET /api/v1/menu/items/{id}` returns item with variations, add-ons, and combo details.
- [ ] `PUT /api/v1/menu/items/{id}` updates item; `DELETE` soft-deletes.
- [ ] `POST /api/v1/menu/items/{id}/variations` manages variations; `/addons` manages add-ons.
- [ ] `POST /api/v1/menu/items/{id}/combo` creates/edits a combo with component items.
- [ ] `POST /api/v1/menu/items/{id}/image` uploads item image.
- [ ] `PATCH /api/v1/menu/items/{id}/availability` toggles availability quickly.
- [ ] `POST /api/v1/menu/import` bulk imports items via CSV.
- [ ] Menu data cached in Redis; cache invalidated on any menu change.
- [ ] Frontend Category management, Item list, Item form (tabbed), Combo builder, Bulk import, Availability toggle screens.
- [ ] Shortcodes unique per restaurant; validated.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Menu Dashboard | /menu | Category tabs + item grid overview |
| Category List | /menu/categories | Manage categories |
| Category Form | /menu/categories/new, /:id/edit | Create/edit category |
| Item List | /menu/items | Searchable item table |
| Item Form | /menu/items/new, /:id/edit | Tabbed item editor |
| Variation Manager | /menu/items/:id/variations | Manage item variations |
| Add-on Manager | /menu/items/:id/addons | Manage item add-ons |
| Add-on Groups | /menu/addon-groups | Manage add-on groups |
| Combo Builder | /menu/items/:id/combo | Build item combos |
| Bulk Import | /menu/import | CSV import |
| Availability Toggle | /menu/availability | Quick toggle grid |
| Shortcode Manager | /menu/shortcodes | Assign shortcodes |

### Screen Details

**Menu Dashboard (/menu):** Top row of category tabs (Starters, Main Course, Beverages...). Below, a grid of item cards for the selected category: each card shows image thumbnail, name, price, veg/non-veg icon, availability toggle, edit button. Search bar above. "Add Category" and "Add Item" buttons. Drag-to-reorder categories and items (updates display_order).

**Item List (/menu/items):** Data table with columns: Image, Name, Category, Shortcode, Base Price, Veg/Non-Veg, Tax Config, Variations (count), Availability (toggle), Actions (Edit, Delete). Filters: Category dropdown, Veg/Non-Veg toggle, Availability toggle, Has Variations, Is Combo, Search. Pagination. "Add Item" and "Import" buttons.

**Item Form (/menu/items/new):** Tabbed form with tabs: Basic, Pricing & Tax, Variations, Add-ons, Combo, Image. **Basic tab:** Name (required), Description (textarea), Category (select), Item Code, Shortcode (validated unique), Veg toggle (radio: Veg/Non-Veg/Egg), Tags (multi-input: Bestseller, Spicy, Chef Special), Availability toggle. **Pricing & Tax tab:** Base Price (required, decimal), Cost Price (optional), HSN Code, Tax Config (select from tax_configs), Track Inventory toggle. **Image tab:** Image upload with preview and crop. **Variations tab:** Table of variations (Name [Small/Medium/Large], Price, SKU, Available). Add/remove rows inline. **Add-ons tab:** Assign add-on groups; within each, enable specific add-ons. **Combo tab:** (if is_combo) Combo Builder: list component items with quantity, optional flag, combo price override.

**Combo Builder (/menu/items/:id/combo):** Left panel: search and pick component menu items. Right panel: selected components with quantity stepper and "optional" checkbox. Shows calculated component total vs combo price (margin indicator). Save combo.

**Availability Toggle (/menu/availability):** Full-screen grid of all items with large toggle switches and color coding (green=available, red=unavailable). Quick search. Used during service to mark items 86'd. Changes reflect on POS immediately via cache invalidation.

**Bulk Import (/menu/import):** File upload for CSV. After upload, shows a mapping table (CSV column -> field) and a preview of rows with validation errors highlighted. Confirm to import. Provides a template download link.

**Shortcode Manager (/menu/shortcodes):** Grid showing items with editable shortcode fields (numeric or alphanumeric). Validates uniqueness on blur. Bulk auto-assign button.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/menu/categories | List categories (hierarchical) |
| POST | /api/v1/menu/categories | Create category |
| PUT | /api/v1/menu/categories/{id} | Update category |
| DELETE | /api/v1/menu/categories/{id} | Delete category |
| GET | /api/v1/menu/items | List items (paginated, filterable) |
| POST | /api/v1/menu/items | Create item |
| GET | /api/v1/menu/items/{id} | Get item detail |
| PUT | /api/v1/menu/items/{id} | Update item |
| DELETE | /api/v1/menu/items/{id} | Soft-delete item |
| PATCH | /api/v1/menu/items/{id}/availability | Toggle availability |
| POST | /api/v1/menu/items/{id}/variations | Add variation |
| PUT | /api/v1/menu/items/{id}/variations/{vid} | Update variation |
| DELETE | /api/v1/menu/items/{id}/variations/{vid} | Delete variation |
| POST | /api/v1/menu/items/{id}/addons | Manage add-ons |
| POST | /api/v1/menu/items/{id}/combo | Create/update combo |
| POST | /api/v1/menu/items/{id}/image | Upload image |
| POST | /api/v1/menu/import | Bulk CSV import |
| GET | /api/v1/menu/export | Export menu as CSV |
| GET | /api/v1/menu/addon-groups | List add-on groups |
| POST | /api/v1/menu/addon-groups | Create add-on group |

## Database Tables

Creates tables (migrations in this ticket): `menu_categories`, `menu_items`, `menu_item_variations`, `menu_item_addons`, `addon_groups`, `addon_group_items`, `item_combos`, `item_combo_items`. Key columns:
- `menu_items`: id, restaurant_id, category_id, name, item_code, shortcode, hsn_code, image_url, base_price, cost_price, tax_config_id, is_veg, is_combo, has_variations, has_addons, is_available, track_inventory, display_order, tags (json).
- `menu_item_variations`: id, menu_item_id, name, price, sku, is_available.
- `menu_item_addons`: id, menu_item_id, name, price, is_default, is_available.
- `item_combos` / `item_combo_items`: combo parent + component items with quantity.

## Technical Notes

- **Caching:** Cache the full menu tree per restaurant in Redis (`menu:{restaurantId}`). Invalidate via model events (saved/deleted) or explicit `Cache::forget`. POS reads from cache for speed.
- **Hierarchical categories:** Support parent_id for sub-categories; eager-load children.
- **Soft deletes** on menu_items to preserve order history integrity.
- **Shortcode uniqueness:** Validate unique within restaurant; allow alphanumeric.
- **Image upload:** Store in `storage/app/public/menu-items`; generate thumbnail via intervention/image.
- **Tax mapping:** Each item references a `tax_config_id`; the TaxService (from RMS-005) resolves CGST/SGST/IGST at billing time.
- **Frontend:** `useMenu` hook (TanStack Query) with cache. Drag-and-drop reordering via dnd-kit. Combo builder as a dedicated component. Bulk import parses CSV with PapaParse, validates, and previews.
- **Permissions:** `menu.manage` for create/edit; `menu.view` for POS read.

## Subtasks

1. [ ] Create migrations: menu_categories, menu_items, menu_item_variations, menu_item_addons, addon_groups, addon_group_items, item_combos, item_combo_items
2. [ ] Create Eloquent models with relationships
3. [ ] Create CategoryController (CRUD, hierarchical)
4. [ ] Create MenuItemController (CRUD, search, filter)
5. [ ] Create VariationController, AddonController, AddonGroupController
6. [ ] Create ComboController (create/update combo with components)
7. [ ] Implement image upload endpoint
8. [ ] Implement availability toggle endpoint
9. [ ] Implement bulk CSV import/export
10. [ ] Implement Redis caching with invalidation
11. [ ] Frontend: Menu Dashboard with category tabs + item grid
12. [ ] Frontend: Category List + Form
13. [ ] Frontend: Item List with filters
14. [ ] Frontend: Item Form (tabbed: basic, pricing/tax, variations, addons, combo, image)
15. [ ] Frontend: Combo Builder
16. [ ] Frontend: Availability Toggle grid
17. [ ] Frontend: Bulk Import with mapping preview
18. [ ] Frontend: Shortcode Manager
19. [ ] Write tests for CRUD, caching, import

## Testing Criteria

- [ ] Categories can be created with sub-categories and displayed hierarchically.
- [ ] Items created with all fields persist and appear in list/filters.
- [ ] Variations and add-ons save and load with the item.
- [ ] Combo creation bundles component items with correct quantities.
- [ ] Availability toggle updates immediately on POS (cache invalidated).
- [ ] Shortcode uniqueness enforced; duplicate rejected.
- [ ] Bulk import creates/updates items; validation errors reported per row.
- [ ] Soft-deleted items excluded from list but referenced orders unaffected.
- [ ] Menu cache rebuilds correctly after changes.
- [ ] Tax config mapping applied to items.
- [ ] Image upload stores and returns a viewable URL.
