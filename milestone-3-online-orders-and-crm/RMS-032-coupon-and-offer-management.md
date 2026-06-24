# RMS-032: Coupon & Offer Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-032 |
| **Type** | Story |
| **Epic** | CRM & Promotions |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 (Customer Database), RMS-014 (Billing & Payment), RMS-023 (Online Order Dashboard) |

## User Story
As a restaurant marketing manager, I want a flexible coupon and offer engine that supports percentage, flat-amount, BOGO and buy-X-get-Y discounts with channel, validity, usage and per-customer restrictions, so that I can run targeted promotions across dine-in, online and takeaway to drive visits, recover lapsed customers and increase average ticket size.

## Description
This story delivers the central promotions engine that powers every discount surfaced to customers -- whether applied manually at the billing counter (RMS-014), automatically at checkout in the online ordering flow (RMS-023), triggered by birthday automation (RMS-036), or distributed through SMS/WhatsApp campaigns (RMS-033). The module is intentionally separated from billing logic so that pricing and tax computation remain stable while promo rules evolve.

Coupons support four discount types: percentage (e.g. 20% off up to a cap), flat amount (e.g. INR 150 off), Buy-One-Get-One (BOGO on selected items or categories) and Buy-X-Get-Y (e.g. buy any 2 starters get 1 dessert free). Each coupon has a unique code (auto-generated or manual), a validity window (start/end datetime with timezone), total redemption cap, per-customer usage limit, and channel restrictions across dine-in, online, takeaway, kiosk and aggregator orders. Some coupons are "automatic" -- applied without a code when conditions match (e.g. cart over INR 1000) -- while others require manual entry of a code.

The redemption lifecycle is fully tracked: every coupon applied creates a `coupon_redemptions` record linking customer, order, discount amount, timestamp and the staff user (for dine-in). Expired or fully consumed coupons are gracefully rejected with descriptive error messages in English and Hindi. The engine also exposes analytics: which coupons drive revenue, redemption rates, average incremental spend per redemption, and cannibalisation of non-discounted orders.

Integration with RMS-024 customer master means opted-out customers can still use coupons at billing (a transactional right), but they are excluded from campaigns that distribute coupon codes. Multi-outlet restaurants can scope coupons globally, per outlet or per city.

## Acceptance Criteria
- [ ] Create coupon with name, unique code (auto or manual), description, discount type and value
- [ ] Support four discount types: percentage (with optional cap), flat amount, BOGO, buy-X-get-Y
- [ ] Validity window with start/end datetime; coupons outside window are rejected
- [ ] Per-coupon total redemption cap and per-customer usage limit enforced
- [ ] Channel restriction selectable across dine-in, online, takeaway, kiosk, aggregator
- [ ] Outlets scope: all outlets, specific outlets, or specific cities
- [ ] Minimum order value, minimum item count and applicable items/categories constraints
- [ ] Manual entry (code required) and automatic application (trigger when cart matches rule) modes
- [ ] Stacking rule: select whether coupon can combine with other coupons or loyalty discounts
- [ ] Redemption recorded against customer + order + user; bill shows original total, discount, final total
- [ ] Expired or fully redeemed coupons return clear error messages at billing/checkout
- [ ] Manager UI lists coupons with status (active/scheduled/expired/exhausted), redemptions, revenue impact
- [ ] Coupon analytics: redemption rate, incremental revenue, top coupons, channel-wise split
- [ ] Bulk coupon generation (e.g. unique code per VIP customer) with downloadable CSV
- [ ] Customer-side coupon list on online checkout shows applicable coupons, terms and "Apply" button

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Coupon List | /crm/coupons | All coupons with filters and status badges |
| Coupon Builder | /crm/coupons/create | Step-by-step coupon creation wizard |
| Coupon Detail | /crm/coupons/{id} | Coupon analytics and redemption log |
| Coupon Redemption Log | /crm/coupons/{id}/redemptions | Per-redemption audit list |
| Bulk Coupon Generator | /crm/coupons/bulk | Generate unique codes for a customer list |

### Screen Details

**Coupon List (/crm/coupons)**
- Layout: Top filter bar, then data table with status badges.
- Filters: Status (Active, Scheduled, Expired, Exhausted, Draft), Discount Type, Channel, Outlet, Date Range, Search by code/name.
- Table columns: Code | Name | Type | Value | Channel | Valid Till | Redemptions/Cap | Status badge | Revenue Impact | Actions.
- Row actions: "View", "Edit", "Pause/Resume", "Clone", "Delete".
- Buttons: "Create Coupon", "Bulk Generate", "Export".
- Pagination: 25/50/100 per page.

**Coupon Builder (/crm/coupons/create)**
- Multi-step wizard: Basics → Discount → Restrictions → Limits → Review.
- Basics step: Name, Code (auto-generate button with format hint like SAVE-XXXX), Description (internal), Terms & Conditions (customer-visible).
- Discount step: Type radio (Percentage, Flat, BOGO, Buy-X-Get-Y) with conditional fields. Percentage shows value + max cap. Flat shows amount. BOGO shows buy item + get item. Buy-X-Get-Y shows buy quantity + get quantity.
- Restrictions step: Applicable Channels (multiselect), Applicable Outlets (multiselect with "All"), Applicable Items/Categories (multiselect or "All"), Customer Tier eligibility (All / Silver+ / Gold+ / Platinum), Min Order Value, Min Item Count, Applicable Days of Week, Applicable Time Window.
- Limits step: Total redemption cap (numeric, blank = unlimited), Per-customer limit, Max discount per order, Start datetime, End datetime, Stacking (combine with other coupons Yes/No).
- Mode toggle: Manual code entry / Automatic application (show rule preview).
- Review step: Summary card with all fields; "Save as Draft", "Publish".

**Coupon Detail (/crm/coupons/{id})**
- Header: Code, name, status badge, edit/pause buttons.
- Summary cards: Total Redemptions, Total Discount Given, Incremental Revenue, Average Order Value with Coupon.
- Charts: Redemption trend (line), Channel split (donut), Hour-of-day heatmap.
- Tabs: Redemptions, Customer List, ROI Analysis.
- Redemptions tab: Table with Date | Customer | Order | Channel | Discount | Net Order Value | Outlet.

**Bulk Coupon Generator (/crm/coupons/bulk)**
- Inputs: Base code prefix, code format (e.g. VIP-####), quantity, audience (segment or selected customers).
- Preview: First 10 generated codes.
- Buttons: "Generate", "Download CSV", "Cancel".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/coupons | List coupons with filters and pagination |
| POST | /api/v1/coupons | Create new coupon |
| GET | /api/v1/coupons/{id} | Get coupon detail |
| PUT | /api/v1/coupons/{id} | Update coupon |
| DELETE | /api/v1/coupons/{id} | Soft-delete coupon |
| POST | /api/v1/coupons/{id}/pause | Pause active coupon |
| POST | /api/v1/coupons/{id}/resume | Resume paused coupon |
| POST | /api/v1/coupons/validate | Validate coupon code against cart and return discount preview |
| POST | /api/v1/coupons/apply | Apply coupon to a draft order/bill |
| DELETE | /api/v1/coupons/apply | Remove applied coupon |
| GET | /api/v1/coupons/{id}/redemptions | List redemption log |
| GET | /api/v1/coupons/{id}/analytics | Get redemption analytics |
| POST | /api/v1/coupons/bulk-generate | Bulk generate unique codes |
| GET | /api/v1/coupons/applicable | Get coupons applicable to a given cart/customer |
| POST | /api/v1/coupons/{id}/clone | Clone coupon for quick variant |

## Database Tables

**coupons**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- name (string)
- code (string, indexed, unique within restaurant)
- description (text, nullable)
- terms (text, nullable)
- discount_type (enum: percentage, flat, bogo, buy_x_get_y)
- discount_value (decimal(10,2), nullable) -- percentage or flat amount
- max_discount_amount (decimal(10,2), nullable) -- cap for percentage
- bogo_config (json, nullable) -- {buy_item_id, get_item_id, min_qty, get_qty}
- buy_x_get_y_config (json, nullable) -- {buy_category_id, buy_qty, get_category_id, get_qty}
- channels_json (json) -- ['dine_in','online','takeaway','kiosk','aggregator']
- outlet_ids_json (json, nullable) -- null means all
- applicable_item_ids_json (json, nullable) -- null means all
- applicable_category_ids_json (json, nullable) -- null means all
- eligible_tiers_json (json, nullable) -- ['silver','gold','platinum'] or null
- min_order_value (decimal(10,2), nullable)
- min_item_count (unsignedSmallInteger, nullable)
- applicable_days_json (json, nullable) -- [1..7]
- applicable_time_start (time, nullable)
- applicable_time_end (time, nullable)
- total_redemption_cap (unsignedInteger, nullable) -- null = unlimited
- per_customer_limit (unsignedSmallInteger, default 1)
- max_discount_per_order (decimal(10,2), nullable)
- stacking_allowed (boolean, default false)
- mode (enum: manual, automatic, default manual)
- auto_apply_rule_json (json, nullable) -- trigger condition for automatic
- starts_at (timestamp)
- ends_at (timestamp)
- status (enum: draft, scheduled, active, paused, expired, exhausted)
- total_redemptions (unsignedInteger, default 0) -- cached counter
- total_discount_given (decimal(12,2), default 0)
- created_by (foreignId, users)
- timestamps, softDeletes
- unique([restaurant_id, code])

**coupon_redemptions**
- id (bigIncrements, PK)
- coupon_id (foreignId, coupons)
- customer_id (foreignId, customers, nullable)
- order_id (foreignId, orders, nullable)
- bill_id (foreignId, bills, nullable)
- channel (enum: dine_in, online, takeaway, kiosk, aggregator)
- outlet_id (foreignId, outlets)
- discount_amount (decimal(10,2))
- order_subtotal (decimal(12,2))
- order_total (decimal(12,2))
- redeemed_by_user_id (foreignId, users, nullable) -- staff at POS
- redeemed_at (timestamp)
- metadata (json, nullable) -- IP, user agent, campaign source
- indexes: [coupon_id, redeemed_at], [customer_id, coupon_id]

**coupon_customer_limits**
- id (bigIncrements, PK)
- coupon_id (foreignId, coupons)
- customer_id (foreignId, customers)
- redemption_count (unsignedSmallInteger, default 0)
- last_redeemed_at (timestamp, nullable)
- timestamps
- unique([coupon_id, customer_id]) -- for fast per-customer check

## Technical Notes

**Laravel Backend:**
- Controllers: `CouponController` (CRUD), `CouponValidationController` (validate, apply, remove), `CouponBulkController` (generate), `CouponAnalyticsController`.
- Services: `CouponEngine` orchestrates eligibility checks, `DiscountCalculator` computes monetary impact per type, `CouponRedemptionService` records usage within DB transaction.
- Job: `CouponExpiryJob` runs hourly to flip status to expired; `CouponAnalyticsRollupJob` nightly refreshes cached counters.
- Validation pipeline: code valid → within window → channel/outlet match → min order satisfied → per-customer limit not exceeded → total cap not exceeded. All checks happen in `CouponEngine::eligible($coupon, $cart, $customer)` returning structured result.
- Stacking: `stacking_allowed=false` (default) prevents combining with other coupons; loyalty points (RMS-025) always apply unless explicitly disabled per coupon.
- Automatic mode: `CouponEngine::findAutomatic($cart, $customer)` returns the best automatic coupon based on `auto_apply_rule_json` priority list.
- Events: `CouponRedeemed`, `CouponExhausted`, `CouponExpired` broadcast for analytics and CRM modules.
- Routes in `routes/api.php` under `Route::prefix('coupons')`.

**React Frontend:**
- Components: `CouponListPage`, `CouponBuilderWizard` with steps, `CouponBasicStep`, `DiscountStep`, `RestrictionsStep`, `LimitsStep`, `ReviewStep`, `CouponDetailPage`, `RedemptionTable`, `AnalyticsCharts`, `BulkGeneratorPage`, `ApplicableCouponsList` (used in checkout and billing).
- State: Zustand `useCouponStore` for builder draft state.
- Hook: `useCouponValidation(cart)` debounces server-side validation as cart changes.
- Form: React Hook Form with Zod schema mirroring backend rules; shows live preview of eligibility when restrictions step is edited.

## Subtasks
1. [ ] Create `coupons`, `coupon_redemptions`, `coupon_customer_limits` migrations and Eloquent models
2. [ ] Implement `CouponEngine` service with eligibility check pipeline
3. [ ] Build `DiscountCalculator` for percentage, flat, BOGO, buy-X-get-Y types
4. [ ] Implement CouponController CRUD with status management
5. [ ] Build `CouponValidationController` (validate, apply, remove endpoints)
6. [ ] Implement per-customer limit tracking via `coupon_customer_limits`
7. [ ] Build automatic application rule engine
8. [ ] Integrate coupon application into billing flow (RMS-014)
9. [ ] Integrate coupon application into online checkout (RMS-023)
10. [ ] Build bulk code generation service with downloadable CSV
11. [ ] Build redemption log and analytics API
12. [ ] Build React CouponListPage with filters
13. [ ] Build React CouponBuilderWizard with multi-step form
14. [ ] Build React CouponDetailPage with charts
15. [ ] Build React BulkGeneratorPage
16. [ ] Add coupon selection UI to online checkout and POS
17. [ ] Write tests for eligibility, redemption, limits, expiry, stacking rules

## Testing Criteria
- [ ] Percentage coupon applies correctly with cap enforcement
- [ ] Flat coupon deducts exact amount from bill
- [ ] BOGO coupon adds free item only when trigger item present in cart
- [ ] Buy-X-Get-Y coupon validates quantity threshold before applying
- [ ] Expired coupon is rejected with clear error message
- [ ] Per-customer limit blocks redemption after N uses
- [ ] Total redemption cap flips coupon to "exhausted" status
- [ ] Channel-restricted coupon is hidden in unsupported channels
- [ ] Automatic coupon applies without code when cart matches rule
- [ ] Manual coupon cannot be stacked when `stacking_allowed=false`
- [ ] Redemption record links to correct customer, order and bill
- [ ] Bulk generator creates N unique codes and CSV downloads correctly