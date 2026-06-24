# Database Schema

**Project:** Restaurant Management System (RMS)
**Database:** MySQL 8.0 (InnoDB, utf8mb4)
**Convention:** snake_case table/column names; `id` BIGINT UNSIGNED auto-increment PK unless noted; `created_at`/`updated_at` on all tables; money as `decimal(12,2)`.

---

## Table of Contents

1. [Conventions](#conventions)
2. [Module: Core / Auth](#module-core--auth)
3. [Module: Menu](#module-menu)
4. [Module: Orders](#module-orders)
5. [Module: Billing](#module-billing)
6. [Module: Inventory](#module-inventory)
7. [Module: CRM](#module-crm)
8. [Module: Online Orders](#module-online-orders)
9. [Module: Analytics](#module-analytics)
10. [Module: Settings](#module-settings)
11. [Migration Order](#migration-order)
12. [Seed Data Notes](#seed-data-notes)

---

## Conventions

- All tenant-scoped tables include `restaurant_id` and (where applicable) `outlet_id`.
- Foreign keys use `ON DELETE RESTRICT` by default to protect financial data.
- Indexes created on all foreign keys and frequently filtered columns.
- Soft deletes (`deleted_at`) on auditable entities: users, menu items, customers, suppliers, orders, bills.
- Enums are stored as `varchar` with app-level validation (not MySQL ENUM) for safer migrations.

---

## Module: Core / Auth

### users

| Column | Type | Constraints / Notes |
|--------|------|---------------------|
| id | bigint unsigned PK | auto-increment |
| restaurant_id | bigint unsigned FK | -> restaurants.id, nullable for super-admin |
| name | varchar(191) | |
| email | varchar(191) | unique |
| phone | varchar(20) | |
| password | varchar(255) | bcrypt hash |
| avatar_url | varchar(255) | nullable |
| is_active | tinyint(1) | default 1 |
| last_login_at | timestamp | nullable |
| settings | json | nullable (preferences) |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Indexes: `email (unique)`, `restaurant_id`, `phone`.

### roles

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| name | varchar(50) | unique (admin, manager, cashier, waiter, chef, captain, hq_admin) |
| display_name | varchar(100) | |
| guard_name | varchar(50) | default 'sanctum' |
| created_at | timestamp | |
| updated_at | timestamp | |

### permissions

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| name | varchar(100) | unique (e.g., `menu.create`, `bill.settle`) |
| display_name | varchar(150) | |
| module | varchar(50) | (menu, billing, inventory, ...) |
| guard_name | varchar(50) | default 'sanctum' |
| created_at | timestamp | |
| updated_at | timestamp | |

### role_user (pivot)

| Column | Type | Notes |
|--------|------|-------|
| user_id | bigint unsigned FK | -> users.id |
| role_id | bigint unsigned FK | -> roles.id |
| created_at | timestamp | |

PK: composite (user_id, role_id).

### permission_role (pivot)

| Column | Type | Notes |
|--------|------|-------|
| permission_id | bigint unsigned FK | -> permissions.id |
| role_id | bigint unsigned FK | -> roles.id |

PK: composite.

### user_outlets (pivot)

| Column | Type | Notes |
|--------|------|-------|
| user_id | bigint unsigned FK | -> users.id |
| outlet_id | bigint unsigned FK | -> outlets.id |
| created_at | timestamp | |

PK: composite.

### personal_access_tokens

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| tokenable_type | varchar(191) | morphs |
| tokenable_id | bigint unsigned | morphs |
| name | varchar(191) | |
| token | varchar(64) | unique hash |
| abilities | text | nullable |
| last_used_at | timestamp | nullable |
| expires_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `tokenable_type, tokenable_id`.

### restaurants

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| name | varchar(191) | |
| legal_name | varchar(191) | |
| gstin | varchar(15) | nullable |
| pan | varchar(10) | nullable |
| logo_url | varchar(255) | nullable |
| email | varchar(191) | |
| phone | varchar(20) | |
| address_line1 | varchar(191) | |
| address_line2 | varchar(191) | nullable |
| city | varchar(100) | |
| state | varchar(100) | |
| pincode | varchar(10) | |
| country | varchar(50) | default 'India' |
| currency_code | varchar(3) | default 'INR' |
| timezone | varchar(50) | default 'Asia/Kolkata' |
| default_tax_rate | decimal(5,2) | nullable |
| fssai_number | varchar(20) | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### outlets

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | -> restaurants.id |
| name | varchar(191) | |
| code | varchar(20) | unique per restaurant |
| type | varchar(30) | dine_in / qsr / cloud_kitchen / food_court |
| gstin | varchar(15) | nullable |
| phone | varchar(20) | |
| address_line1 | varchar(191) | |
| city | varchar(100) | |
| state | varchar(100) | |
| pincode | varchar(10) | |
| is_central_kitchen | tinyint(1) | default 0 |
| is_active | tinyint(1) | default 1 |
| settings | json | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `restaurant_id`, `code`.

### tax_configs

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | -> restaurants.id |
| name | varchar(100) | (CGST+SGST, IGST, etc.) |
| type | varchar(20) | intra_state / inter_state |
| cgst_rate | decimal(5,2) | |
| sgst_rate | decimal(5,2) | |
| igst_rate | decimal(5,2) | |
| cess_rate | decimal(5,2) | default 0 |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

### discount_configs

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(100) | |
| type | varchar(20) | flat / percentage |
| value | decimal(12,2) | |
| applies_to | varchar(20) | item / bill / category |
| min_bill_amount | decimal(12,2) | nullable |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Module: Menu

### menu_categories

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| parent_id | bigint unsigned FK | -> menu_categories.id, nullable (sub-category) |
| name | varchar(100) | |
| description | varchar(255) | nullable |
| display_order | int | default 0 |
| icon_url | varchar(255) | nullable |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `restaurant_id`, `parent_id`.

### menu_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| category_id | bigint unsigned FK | -> menu_categories.id |
| name | varchar(191) | |
| description | text | nullable |
| item_code | varchar(50) | |
| shortcode | varchar(20) | for quick POS entry |
| hsn_code | varchar(15) | nullable |
| image_url | varchar(255) | nullable |
| base_price | decimal(12,2) | |
| cost_price | decimal(12,2) | nullable |
| tax_config_id | bigint unsigned FK | -> tax_configs.id, nullable |
| is_veg | tinyint(1) | 1 veg, 0 non-veg |
| is_combo | tinyint(1) | default 0 |
| has_variations | tinyint(1) | default 0 |
| has_addons | tinyint(1) | default 0 |
| is_available | tinyint(1) | default 1 |
| track_inventory | tinyint(1) | default 0 |
| display_order | int | default 0 |
| tags | json | nullable (bestseller, spicy, chef special) |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Indexes: `restaurant_id`, `category_id`, `shortcode`, `item_code`.

### menu_item_variations

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| menu_item_id | bigint unsigned FK | -> menu_items.id |
| name | varchar(100) | (Small, Medium, Large, Half/Full) |
| price | decimal(12,2) | |
| sku | varchar(50) | nullable |
| is_available | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `menu_item_id`.

### menu_item_addons

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| menu_item_id | bigint unsigned FK | -> menu_items.id |
| name | varchar(100) | |
| price | decimal(12,2) | |
| is_default | tinyint(1) | default 0 |
| is_available | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `menu_item_id`.

### item_combos

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| menu_item_id | bigint unsigned FK | -> menu_items.id (combo parent) |
| name | varchar(191) | |
| combo_price | decimal(12,2) | |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

### item_combo_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| item_combo_id | bigint unsigned FK | -> item_combos.id |
| menu_item_id | bigint unsigned FK | -> menu_items.id |
| quantity | decimal(10,3) | default 1 |
| is_optional | tinyint(1) | default 0 |
| created_at | timestamp | |

Indexes: `item_combo_id`, `menu_item_id`.

### addon_groups

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(100) | (Extra cheese, Sauce) |
| selection_type | varchar(20) | single / multiple |
| min_select | int | default 0 |
| max_select | int | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

### addon_group_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| addon_group_id | bigint unsigned FK | |
| menu_item_addon_id | bigint unsigned FK | |
| created_at | timestamp | |

---

## Module: Orders

### areas

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| name | varchar(100) | (Ground Floor, Terrace) |
| display_order | int | default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `outlet_id`.

### restaurant_tables

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| area_id | bigint unsigned FK | -> areas.id |
| name | varchar(50) | (T1, T2) |
| seating_capacity | int | |
| shape | varchar(20) | square/round/rect |
| pos_x | int | nullable (floor plan) |
| pos_y | int | nullable |
| qr_code_token | varchar(64) | unique |
| status | varchar(20) | available / occupied / reserved |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `outlet_id`, `area_id`, `qr_code_token`, `status`.

### orders

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| uuid | char(36) | unique public id |
| restaurant_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| order_number | varchar(30) | outlet-sequential |
| table_id | bigint unsigned FK | -> restaurant_tables.id, nullable |
| customer_id | bigint unsigned FK | -> customers.id, nullable |
| waiter_id | bigint unsigned FK | -> users.id, nullable |
| captain_id | bigint unsigned FK | -> users.id, nullable |
| order_type | varchar(20) | dine_in / takeaway / delivery |
| status | varchar(20) | draft / open / held / preparing / ready / served / billed / cancelled |
| pax | int | nullable |
| subtotal | decimal(12,2) | |
| discount_amount | decimal(12,2) | default 0 |
| tax_amount | decimal(12,2) | default 0 |
| total_amount | decimal(12,2) | |
| notes | text | nullable |
| source | varchar(20) | pos / captain / online / kiosk |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Indexes: `outlet_id`, `status`, `order_type`, `table_id`, `customer_id`, `created_at`.

### order_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| order_id | bigint unsigned FK | -> orders.id |
| menu_item_id | bigint unsigned FK | -> menu_items.id |
| variation_id | bigint unsigned FK | -> menu_item_variations.id, nullable |
| name | varchar(191) | snapshot |
| quantity | decimal(10,3) | |
| unit_price | decimal(12,2) | snapshot |
| discount_amount | decimal(12,2) | default 0 |
| tax_amount | decimal(12,2) | default 0 |
| line_total | decimal(12,2) | |
| status | varchar(20) | pending / preparing / ready / served / cancelled |
| is_complimentary | tinyint(1) | default 0 |
| notes | varchar(255) | nullable |
| kot_ticket_id | bigint unsigned FK | -> kot_tickets.id, nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `order_id`, `menu_item_id`, `kot_ticket_id`.

### order_item_modifiers

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| order_item_id | bigint unsigned FK | -> order_items.id |
| addon_id | bigint unsigned FK | -> menu_item_addons.id, nullable |
| name | varchar(100) | snapshot |
| price | decimal(12,2) | snapshot |
| quantity | decimal(10,3) | default 1 |
| created_at | timestamp | |

Index: `order_item_id`.

### kot_tickets

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| order_id | bigint unsigned FK | -> orders.id |
| outlet_id | bigint unsigned FK | |
| kot_number | varchar(30) | |
| station | varchar(50) | (kitchen, bar, grill) routing target |
| status | varchar(20) | pending / printing / printed / voided |
| print_count | int | default 0 |
| void_reason | varchar(255) | nullable |
| voided_by | bigint unsigned FK | -> users.id, nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `order_id`, `outlet_id`, `status`.

---

## Module: Billing

### bills

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| uuid | char(36) | unique |
| invoice_number | varchar(40) | unique per outlet |
| restaurant_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| order_id | bigint unsigned FK | -> orders.id |
| table_id | bigint unsigned FK | nullable |
| customer_id | bigint unsigned FK | nullable |
| cashier_id | bigint unsigned FK | -> users.id |
| bill_type | varchar(20) | dine_in / takeaway / delivery |
| subtotal | decimal(12,2) | |
| total_discount | decimal(12,2) | default 0 |
| taxable_amount | decimal(12,2) | |
| cgst_amount | decimal(12,2) | default 0 |
| sgst_amount | decimal(12,2) | default 0 |
| igst_amount | decimal(12,2) | default 0 |
| cess_amount | decimal(12,2) | default 0 |
| total_tax | decimal(12,2) | default 0 |
| round_off | decimal(12,2) | default 0 |
| grand_total | decimal(12,2) | |
| amount_paid | decimal(12,2) | default 0 |
| change_due | decimal(12,2) | default 0 |
| status | varchar(20) | draft / held / settled / refunded / partial |
| settled_at | timestamp | nullable |
| notes | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Indexes: `outlet_id`, `invoice_number`, `status`, `order_id`, `created_at`.

### bill_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| bill_id | bigint unsigned FK | -> bills.id |
| order_item_id | bigint unsigned FK | nullable |
| name | varchar(191) | |
| hsn_code | varchar(15) | nullable |
| quantity | decimal(10,3) | |
| unit_price | decimal(12,2) | |
| discount_amount | decimal(12,2) | default 0 |
| taxable_value | decimal(12,2) | |
| cgst_rate | decimal(5,2) | |
| cgst_amount | decimal(12,2) | |
| sgst_rate | decimal(5,2) | |
| sgst_amount | decimal(12,2) | |
| igst_rate | decimal(5,2) | |
| igst_amount | decimal(12,2) | |
| line_total | decimal(12,2) | |
| created_at | timestamp | |

Index: `bill_id`.

### bill_discounts

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| bill_id | bigint unsigned FK | |
| discount_config_id | bigint unsigned FK | nullable |
| name | varchar(100) | |
| type | varchar(20) | flat / percentage |
| value | decimal(12,2) | |
| amount | decimal(12,2) | |
| scope | varchar(20) | bill / item |
| created_at | timestamp | |

Index: `bill_id`.

### payments

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| bill_id | bigint unsigned FK | -> bills.id |
| payment_method | varchar(20) | cash / upi / card / wallet / internal_wallet / split |
| tender_amount | decimal(12,2) | |
| reference_no | varchar(100) | nullable (txn id) |
| gateway | varchar(50) | nullable |
| status | varchar(20) | pending / success / failed / refunded |
| processed_at | timestamp | nullable |
| metadata | json | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `bill_id`, `status`, `payment_method`.

### payment_splits

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| payment_id | bigint unsigned FK | -> payments.id |
| method | varchar(20) | cash / upi / card / wallet |
| amount | decimal(12,2) | |
| reference_no | varchar(100) | nullable |
| created_at | timestamp | |

Index: `payment_id`.

### refunds

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| bill_id | bigint unsigned FK | |
| payment_id | bigint unsigned FK | nullable |
| amount | decimal(12,2) | |
| reason | varchar(255) | |
| status | varchar(20) | pending / approved / completed |
| approved_by | bigint unsigned FK | -> users.id, nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Module: Inventory

### units

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| name | varchar(50) | (kg, g, litre, ml, piece) |
| code | varchar(10) | unique |
| base_unit_id | bigint unsigned FK | nullable |
| conversion_factor | decimal(12,4) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

### material_categories

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(100) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### raw_materials

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| material_category_id | bigint unsigned FK | nullable |
| name | varchar(191) | |
| code | varchar(50) | |
| unit_id | bigint unsigned FK | -> units.id |
| hsn_code | varchar(15) | nullable |
| gst_rate | decimal(5,2) | default 0 |
| min_stock | decimal(12,3) | default 0 |
| max_stock | decimal(12,3) | default 0 |
| reorder_level | decimal(12,3) | default 0 |
| current_stock | decimal(12,3) | default 0 |
| avg_cost | decimal(12,2) | default 0 |
| valuation_method | varchar(10) | fifo / lifo / avg |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Indexes: `restaurant_id`, `code`, `material_category_id`.

### stock_batches

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| raw_material_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| batch_no | varchar(50) | nullable |
| quantity | decimal(12,3) | remaining |
| unit_cost | decimal(12,2) | |
| received_at | timestamp | |
| expiry_date | date | nullable |
| created_at | timestamp | |

Indexes: `raw_material_id`, `outlet_id`.

### stock_movements

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| raw_material_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| type | varchar(20) | in / out / adjustment / transfer_in / transfer_out / wastage |
| quantity | decimal(12,3) | positive in, negative out |
| balance_after | decimal(12,3) | |
| unit_cost | decimal(12,2) | |
| reference_type | varchar(50) | nullable (Order, PurchaseOrder, Transfer, Wastage) |
| reference_id | bigint unsigned | nullable |
| reason | varchar(255) | nullable |
| created_by | bigint unsigned FK | -> users.id |
| created_at | timestamp | |

Indexes: `raw_material_id`, `outlet_id`, `type`, `created_at`.

### stock_adjustments

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| raw_material_id | bigint unsigned FK | |
| counted_qty | decimal(12,3) | |
| system_qty | decimal(12,3) | |
| difference | decimal(12,3) | |
| reason | varchar(255) | |
| status | varchar(20) | draft / approved |
| approved_by | bigint unsigned FK | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### recipes

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| menu_item_id | bigint unsigned FK | -> menu_items.id |
| restaurant_id | bigint unsigned FK | |
| name | varchar(191) | |
| type | varchar(20) | finished / semi_finished |
| yield_quantity | decimal(12,3) | output qty |
| yield_unit_id | bigint unsigned FK | -> units.id |
| preparation_time | int | minutes |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

### recipe_ingredients

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| recipe_id | bigint unsigned FK | -> recipes.id |
| ingredient_type | varchar(20) | raw_material / semi_finished_recipe |
| ingredient_id | bigint unsigned | polymorphic |
| quantity | decimal(12,3) | |
| unit_id | bigint unsigned FK | -> units.id |
| wastage_percent | decimal(5,2) | default 0 |
| created_at | timestamp | |

Index: `recipe_id`.

### suppliers

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(191) | |
| contact_person | varchar(100) | nullable |
| phone | varchar(20) | |
| email | varchar(191) | nullable |
| gstin | varchar(15) | nullable |
| address | text | nullable |
| payment_terms | varchar(100) | nullable |
| credit_limit | decimal(12,2) | default 0 |
| rating | decimal(2,1) | default 0 |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Index: `restaurant_id`.

### purchase_orders

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| po_number | varchar(30) | unique |
| restaurant_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| supplier_id | bigint unsigned FK | |
| status | varchar(20) | draft / pending_approval / approved / partially_received / received / cancelled |
| order_date | date | |
| expected_date | date | nullable |
| subtotal | decimal(12,2) | |
| tax_amount | decimal(12,2) | default 0 |
| total_amount | decimal(12,2) | |
| notes | text | nullable |
| created_by | bigint unsigned FK | -> users.id |
| approved_by | bigint unsigned FK | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `outlet_id`, `supplier_id`, `status`.

### purchase_order_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| purchase_order_id | bigint unsigned FK | |
| raw_material_id | bigint unsigned FK | |
| quantity | decimal(12,3) | |
| received_quantity | decimal(12,3) | default 0 |
| unit_price | decimal(12,2) | |
| tax_rate | decimal(5,2) | default 0 |
| line_total | decimal(12,2) | |
| created_at | timestamp | |

Index: `purchase_order_id`.

### goods_receipt_notes

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| grn_number | varchar(30) | |
| purchase_order_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| received_by | bigint unsigned FK | |
| received_at | timestamp | |
| notes | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### grn_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| grn_id | bigint unsigned FK | |
| purchase_order_item_id | bigint unsigned FK | |
| raw_material_id | bigint unsigned FK | |
| received_quantity | decimal(12,3) | |
| accepted_quantity | decimal(12,3) | |
| rejected_quantity | decimal(12,3) | default 0 |
| batch_no | varchar(50) | nullable |
| expiry_date | date | nullable |
| unit_cost | decimal(12,2) | |
| created_at | timestamp | |

### central_kitchens

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | (the CK outlet) |
| name | varchar(191) | |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

### central_kitchen_indents

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| indent_number | varchar(30) | |
| central_kitchen_id | bigint unsigned FK | |
| requesting_outlet_id | bigint unsigned FK | |
| status | varchar(20) | draft / requested / approved / dispatched / received / cancelled |
| required_date | date | |
| notes | text | nullable |
| created_by | bigint unsigned FK | |
| approved_by | bigint unsigned FK | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### central_kitchen_indent_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| indent_id | bigint unsigned FK | |
| raw_material_id | bigint unsigned FK | |
| requested_quantity | decimal(12,3) | |
| approved_quantity | decimal(12,3) | default 0 |
| dispatched_quantity | decimal(12,3) | default 0 |
| received_quantity | decimal(12,3) | default 0 |
| created_at | timestamp | |

### stock_transfers

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| transfer_number | varchar(30) | |
| restaurant_id | bigint unsigned FK | |
| from_outlet_id | bigint unsigned FK | |
| to_outlet_id | bigint unsigned FK | |
| status | varchar(20) | draft / approved / in_transit / received / cancelled |
| transfer_date | date | |
| received_date | date | nullable |
| notes | text | nullable |
| created_by | bigint unsigned FK | |
| created_at | timestamp | |
| updated_at | timestamp | |

### stock_transfer_items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| stock_transfer_id | bigint unsigned FK | |
| raw_material_id | bigint unsigned FK | |
| quantity | decimal(12,3) | |
| unit_cost | decimal(12,2) | |
| created_at | timestamp | |

### wastages

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| raw_material_id | bigint unsigned FK | nullable |
| menu_item_id | bigint unsigned FK | nullable |
| quantity | decimal(12,3) | |
| unit_cost | decimal(12,2) | |
| cost_amount | decimal(12,2) | |
| reason | varchar(255) | |
| type | varchar(20) | raw / prepared / batch_expiry |
| status | varchar(20) | pending / approved / rejected |
| approved_by | bigint unsigned FK | nullable |
| created_by | bigint unsigned FK | |
| created_at | timestamp | |
| updated_at | timestamp | |

### e_way_bills

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| reference_type | varchar(50) | (StockTransfer, PurchaseOrder) |
| reference_id | bigint unsigned | |
| ewb_number | varchar(20) | unique |
| generated_date | datetime | |
| valid_upto | datetime | |
| transporter_id | varchar(20) | nullable |
| vehicle_no | varchar(20) | nullable |
| from_gstin | varchar(15) | |
| to_gstin | varchar(15) | |
| status | varchar(20) | active / cancelled / extended |
| metadata | json | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Module: CRM

### customers

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(191) | |
| phone | varchar(20) | |
| email | varchar(191) | nullable |
| birthday | date | nullable |
| anniversary | date | nullable |
| gender | varchar(10) | nullable |
| address | text | nullable |
| city | varchar(100) | nullable |
| total_visits | int | default 0 |
| total_spent | decimal(12,2) | default 0 |
| last_visit_at | timestamp | nullable |
| tags | json | nullable |
| is_blacklisted | tinyint(1) | default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | nullable |

Indexes: `restaurant_id`, `phone`.

### loyalty_points

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| customer_id | bigint unsigned FK | |
| restaurant_id | bigint unsigned FK | |
| points | int | signed (positive earn, negative redeem) |
| balance | int | running balance |
| source | varchar(30) | order / referral / campaign / redemption |
| reference_id | bigint unsigned | nullable |
| expires_at | date | nullable |
| created_at | timestamp | |

Index: `customer_id`.

### reward_wallets

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| customer_id | bigint unsigned FK | |
| restaurant_id | bigint unsigned FK | |
| balance | decimal(12,2) | default 0 |
| currency_code | varchar(3) | default 'INR' |
| created_at | timestamp | |
| updated_at | timestamp | |

### wallet_transactions

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| reward_wallet_id | bigint unsigned FK | |
| type | varchar(10) | credit / debit |
| amount | decimal(12,2) | |
| balance_after | decimal(12,2) | |
| reason | varchar(255) | |
| reference_type | varchar(50) | nullable |
| reference_id | bigint unsigned | nullable |
| created_at | timestamp | |

### campaigns

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(191) | |
| type | varchar(30) | discount / cashback / points |
| start_date | date | |
| end_date | date | |
| rules | json | (min spend, segment, items) |
| reward_value | decimal(12,2) | |
| reward_type | varchar(20) | flat / percentage |
| status | varchar(20) | draft / active / paused / expired |
| created_at | timestamp | |
| updated_at | timestamp | |

### feedback

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| customer_id | bigint unsigned FK | nullable |
| bill_id | bigint unsigned FK | nullable |
| outlet_id | bigint unsigned FK | |
| rating | tinyint | 1-5 |
| food_rating | tinyint | nullable |
| service_rating | tinyint | nullable |
| ambience_rating | tinyint | nullable |
| comment | text | nullable |
| created_at | timestamp | |

---

## Module: Online Orders

### aggregator_integrations

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| aggregator | varchar(30) | zomato / swiggy / petpooja_online |
| restaurant_external_id | varchar(100) | |
| api_key | varchar(255) | nullable |
| webhook_secret | varchar(255) | nullable |
| is_active | tinyint(1) | default 1 |
| config | json | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### online_orders

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| aggregator_integration_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | |
| order_id | bigint unsigned FK | -> orders.id, nullable (once mapped) |
| external_order_id | varchar(100) | |
| aggregator | varchar(30) | |
| customer_name | varchar(191) | |
| customer_phone | varchar(20) | |
| delivery_address | text | nullable |
| items_json | json | raw payload |
| total_amount | decimal(12,2) | |
| commission_amount | decimal(12,2) | default 0 |
| status | varchar(20) | new / accepted / rejected / preparing / dispatched / delivered / cancelled |
| received_at | timestamp | |
| accepted_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `outlet_id`, `status`, `external_order_id`.

### aggregator_menu_mappings

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| aggregator_integration_id | bigint unsigned FK | |
| menu_item_id | bigint unsigned FK | |
| external_item_id | varchar(100) | |
| external_name | varchar(191) | |
| external_price | decimal(12,2) | |
| created_at | timestamp | |

### online_settlements

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| aggregator_integration_id | bigint unsigned FK | |
| settlement_date | date | |
| gross_amount | decimal(12,2) | |
| commission_amount | decimal(12,2) | |
| net_amount | decimal(12,2) | |
| status | varchar(20) | pending / settled |
| created_at | timestamp | |

---

## Module: Analytics

### report_configs

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| name | varchar(191) | |
| type | varchar(50) | sales / pl / gst / inventory |
| parameters | json | filters, groupings |
| schedule | varchar(20) | none / daily / weekly / monthly |
| recipients | json | emails |
| is_active | tinyint(1) | default 1 |
| created_by | bigint unsigned FK | |
| created_at | timestamp | |
| updated_at | timestamp | |

### audit_logs

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | |
| outlet_id | bigint unsigned FK | nullable |
| user_id | bigint unsigned FK | nullable |
| action | varchar(50) | create / update / delete / login / settle |
| entity_type | varchar(100) | |
| entity_id | bigint unsigned | nullable |
| old_values | json | nullable |
| new_values | json | nullable |
| ip_address | varchar(45) | |
| user_agent | varchar(255) | nullable |
| created_at | timestamp | |

Indexes: `restaurant_id`, `entity_type, entity_id`, `user_id`, `created_at`.

### reservations

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| customer_id | bigint unsigned FK | nullable |
| table_id | bigint unsigned FK | nullable |
| reservation_date | date | |
| reservation_time | time | |
| pax | int | |
| status | varchar(20) | requested / confirmed / seated / no_show / cancelled |
| source | varchar(20) | phone / online / walkin |
| notes | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `outlet_id`, `reservation_date`.

### waitlist_entries

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| customer_name | varchar(191) | |
| phone | varchar(20) | |
| pax | int | |
| status | varchar(20) | waiting / seated / left |
| quoted_wait_minutes | int | |
| checked_in_at | timestamp | |
| seated_at | timestamp | nullable |
| created_at | timestamp | |

---

## Module: Settings

### languages

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| code | varchar(5) | unique (en, hi, gu) |
| name | varchar(100) | |
| is_active | tinyint(1) | default 1 |
| is_default | tinyint(1) | default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

### translations

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| language_code | varchar(5) | FK logical -> languages.code |
| group | varchar(50) | (menu, billing, common) |
| key | varchar(191) | |
| value | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `language_code, group, key`.

### app_settings

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| restaurant_id | bigint unsigned FK | nullable (global if null) |
| outlet_id | bigint unsigned FK | nullable |
| key | varchar(100) | |
| value | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `restaurant_id, outlet_id, key`.

### printers

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| outlet_id | bigint unsigned FK | |
| name | varchar(100) | |
| type | varchar(20) | kot / bill / both |
| connection_type | varchar(20) | usb / network / bluetooth |
| ip_address | varchar(45) | nullable |
| port | int | nullable |
| is_default | tinyint(1) | default 0 |
| category_routing | json | nullable (which categories route here) |
| is_active | tinyint(1) | default 1 |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Migration Order

Migrations must be created and run in dependency order. Recommended sequence:

**Group 1 - Core (M1):**
1. users, roles, permissions, role_user, permission_role, personal_access_tokens
2. restaurants, outlets, user_outlets
3. tax_configs, discount_configs
4. menu_categories, menu_items, menu_item_variations, menu_item_addons, addon_groups, addon_group_items, item_combos, item_combo_items
5. areas, restaurant_tables
6. orders, order_items, order_item_modifiers, kot_tickets
7. bills, bill_items, bill_discounts, payments, payment_splits, refunds

**Group 2 - Inventory (M2):**
8. units, material_categories, raw_materials, stock_batches, stock_movements, stock_adjustments
9. recipes, recipe_ingredients
10. suppliers, purchase_orders, purchase_order_items, goods_receipt_notes, grn_items
11. central_kitchens, central_kitchen_indents, central_kitchen_indent_items
12. stock_transfers, stock_transfer_items
13. wastages, e_way_bills

**Group 3 - CRM (M3):**
14. customers, loyalty_points, reward_wallets, wallet_transactions
15. campaigns, feedback

**Group 4 - Online (M3):**
16. aggregator_integrations, online_orders, aggregator_menu_mappings, online_settlements

**Group 5 - Analytics (M4):**
17. report_configs, audit_logs

**Group 6 - Reservations (M5):**
18. reservations, waitlist_entries

**Group 7 - Settings (M6):**
19. languages, translations, app_settings, printers

---

## Seed Data Notes

After migrations, the following seeders should populate baseline data:

- **RolesSeeder:** admin, manager, cashier, waiter, chef, captain, hq_admin.
- **PermissionsSeeder:** Full permission set per module with role mappings.
- **UnitsSeeder:** kg, g, litre, ml, piece, dozen, box.
- **LanguagesSeeder:** English (default), Hindi, Gujarati.
- **TaxConfigSeeder:** Sample CGST/SGST 2.5% each (5% total), 6% each (12%), etc.
- **SampleRestaurantSeeder:** One demo restaurant with two outlets (dine-in + cloud kitchen).
- **SampleMenuSeeder:** 5 categories, 30+ items across veg/non-veg with variations and add-ons.
- **SampleTablesSeeder:** 3 areas, 15 tables with QR tokens.
- **AdminUserSeeder:** Default admin user (email configurable, force password change on first login).
- **MaterialCategorySeeder:** Vegetables, Dairy, Grocery, Meat, Spices, Packaging.

Seeders must be idempotent (use `firstOrCreate`) so they can run repeatedly in dev and staging.
