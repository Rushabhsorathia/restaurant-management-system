# RMS-053: Supplier B2B Marketplace

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-053 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | Medium |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-013 (Inventory & Recipes), RMS-018 (Purchase Orders), RMS-052 (Tally Export) |

## User Story
As a restaurant owner or inventory manager, I want a built-in B2B marketplace to discover verified suppliers (vegetables, dairy, meat, packaging, equipment), raise RFQs, compare quotes side-by-side, place orders with integrated payment, and rate suppliers, so that I can source quality ingredients faster and at better prices without leaving the RMS.

## Description
Indian restaurant operations depend on a fragmented network of suppliers -- local mandis for produce, dairy cooperatives, butchers, packaging vendors, equipment dealers. Today, sourcing is a phone-call and WhatsApp-based chaos with no price discovery, no quality comparison, no centralized payment trail, and no reliable delivery history. This story builds a curated B2B marketplace directly inside the RMS.

Suppliers self-onboard through a verification flow that includes GSTIN validation (via GST API), FSSAI license check, bank account verification (penny drop), and document upload (PAN, GST certificate, FSSAI, cancelled cheque, business proof). Verified suppliers get a `SupplierDashboard` exposing their catalog, orders, RFQ inbox, payouts, and ratings. Restaurant owners discover suppliers by category, location (delivery radius), rating, and price.

The RFQ (Request for Quote) module allows a buyer to create a request with item list, quantity, required-by date, and delivery address, then invite selected suppliers or broadcast to all matching suppliers. Each invited supplier receives the RFQ, submits a quote with line-item prices, taxes, delivery charges, payment terms, and validity. The buyer sees a comparison view: side-by-side table with totals, on-time delivery score, rating, and a "Award" button that converts the winning quote into a Purchase Order.

Payment is integrated via Razorpay (UPI, cards, netbanking) and credit terms (Net 7, Net 15, Net 30) for trusted suppliers. The supplier dashboard shows real-time order pipeline, payment status, and payout schedule. A rating and review system lets buyers rate suppliers on Quality, Delivery Timeliness, Packaging, Communication, and Value (1-5 stars each, plus text review). Ratings feed into the marketplace search ranking.

Compliance: all suppliers must hold valid FSSAI license to sell food items; license expiry is tracked and flagged. GST invoices are auto-generated on order and flow to Tally (RMS-052). The marketplace is multi-tenant: each restaurant chain sees only its own supplier relationships plus the public catalog.

## Acceptance Criteria
- [ ] Supplier self-onboarding wizard: business details, GSTIN, FSSAI, PAN, bank account, documents upload.
- [ ] GSTIN validation via GST API; FSSAI license check; penny-drop bank verification; admin approval workflow.
- [ ] Verified suppliers get a `SupplierDashboard` with catalog, orders, RFQs, payouts, ratings.
- [ ] Restaurant-side supplier directory: search by category, location/radius, rating, price, FSSAI status.
- [ ] RFQ creation: item list, quantities, required-by date, delivery address; invite specific suppliers or broadcast.
- [ ] Supplier submits quote with line items, taxes, delivery, payment terms (COD/prepaid/credit), validity.
- [ ] Quote comparison view: side-by-side table with totals, taxes, delivery, rating, on-time score.
- [ ] "Award Quote" converts winning quote into Purchase Order; losing suppliers notified automatically.
- [ ] Integrated payment via Razorpay (UPI, cards, netbanking, wallets) for prepaid orders.
- [ ] Credit terms (Net 7, Net 15, Net 30) for trusted suppliers; track outstanding receivables on supplier dashboard.
- [ ] Rating and review: 5 dimensions (Quality, Delivery, Packaging, Communication, Value) with text.
- [ ] Auto-generated GST invoice per order; flows to Tally export.
- [ ] FSSAI license expiry tracked; expired suppliers suspended from marketplace.
- [ ] Supplier analytics: order volume, revenue, payout history, rating trend.
- [ ] Multi-tenant isolation: chain A cannot see chain B's orders; public catalog visible to all.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Marketplace Home | /marketplace | Browse categories, featured suppliers, recent orders |
| Supplier Directory | /marketplace/suppliers | Searchable supplier list with filters |
| Supplier Profile | /marketplace/suppliers/{id} | Catalog, ratings, reviews, RFQ button |
| RFQ Create | /marketplace/rfq/create | New RFQ wizard |
| RFQ Inbox (Supplier) | /marketplace/rfq/inbox | Open RFQs to quote |
| Quote Comparison | /marketplace/rfq/{id}/compare | Side-by-side quote comparison |
| Purchase Order Detail | /marketplace/orders/{id} | PO with delivery, payment, status |
| Supplier Dashboard | /marketplace/dashboard | Supplier-side home with KPIs |
| Supplier Onboarding | /marketplace/onboard | Multi-step registration |

### Screen Details

**Marketplace Home (/marketplace)**: Hero search "What are you sourcing today?" with autocomplete. Category tiles (Vegetables, Fruits, Dairy, Meat & Poultry, Seafood, Grains & Pulses, Spices, Oils & Ghee, Packaging, Equipment, Cleaning). Featured verified suppliers carousel, recent orders table, quick RFQ button.

**Supplier Directory (/marketplace/suppliers)**: Left filters: Category (multi), Distance slider, Min Rating, FSSAI status, Verified-only, Price range. Sort by Rating/Distance/Price/On-time. Card grid: logo, name, stars, FSSAI badge, radius, top categories, "View"/"RFQ" buttons. 24 per page.

**Supplier Profile (/marketplace/suppliers/{id})**: Header: logo, name, rating, FSSAI/GSTIN verified badges, contact, address. Tabs: Catalog (item grid w/ images, unit, price, MOQ, "Add to RFQ"), Reviews (dimension bars, filter), RFQ ("Send RFQ" + history), About (description, certifications, photos), Compliance (GST cert, FSSAI license w/ expiry, PAN, bank verify status).

**RFQ Create (/marketplace/rfq/create)**: Wizard: Items → Delivery → Suppliers (specific or broadcast) → Review. Live total estimate. Validity 24h/48h/3d/7d. Buttons: "Save as Draft", "Send".

**Quote Comparison (/marketplace/rfq/{id}/compare)**: Rows=line items, columns=suppliers. Headers: logo, rating, on-time, total, payment terms, validity countdown. Cells show price/unit, line total, tax; lowest price highlighted green. Right panel: per-supplier summary cards. "Award" creates PO; "Negotiate" opens chat.

**Supplier Dashboard (/marketplace/dashboard)**: KPI cards: Today's Orders, Open RFQs, Pending Payouts, Rating, On-time %. Charts: order volume (line, 30d), revenue (bar, 30d), top items, rating trend. Tables: recent orders, new RFQs, payout history. Quick actions: Add product, Update stock, Respond to RFQ.

**Supplier Onboarding (/marketplace/onboard)**: 7 steps: (1) Business (legal name, GSTIN, PAN, type, years), (2) Owner (name, phone, Aadhaar), (3) Address (registered/operational), (4) Banking (penny-drop), (5) Compliance (FSSAI expiry, trade license), (6) Categories & radius, (7) Documents (GST cert, PAN, cheque, owner ID). Status tracker; "Submit for Verification".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/marketplace/categories | List marketplace categories |
| GET | /api/v1/marketplace/suppliers | Search suppliers with filters |
| GET | /api/v1/marketplace/suppliers/{id} | Get supplier detail |
| POST | /api/v1/marketplace/suppliers | Register new supplier (onboard) |
| PUT | /api/v1/marketplace/suppliers/{id} | Update supplier profile |
| POST | /api/v1/marketplace/suppliers/{id}/verify | Admin verification action |
| GET | /api/v1/marketplace/suppliers/{id}/catalog | List supplier catalog |
| POST | /api/v1/marketplace/rfq | Create RFQ |
| GET | /api/v1/marketplace/rfq | List RFQs (buyer or supplier view) |
| GET | /api/v1/marketplace/rfq/{id} | Get RFQ detail |
| POST | /api/v1/marketplace/rfq/{id}/quotes | Submit quote (supplier) |
| GET | /api/v1/marketplace/rfq/{id}/compare | Get comparison data |
| POST | /api/v1/marketplace/rfq/{id}/award | Award quote, create PO |
| GET | /api/v1/marketplace/orders | List purchase orders |
| GET | /api/v1/marketplace/orders/{id} | Get PO detail |
| POST | /api/v1/marketplace/orders/{id}/payment | Initiate payment via Razorpay |
| POST | /api/v1/marketplace/orders/{id}/delivery | Mark delivered |
| POST | /api/v1/marketplace/orders/{id}/review | Submit rating + review |
| GET | /api/v1/marketplace/gstin/verify | Verify GSTIN via API |
| GET | /api/v1/marketplace/ifsc/verify | Verify bank via IFSC + penny drop |
| GET | /api/v1/marketplace/dashboard | Supplier dashboard KPIs |

## Database Tables

**suppliers**
- id (bigIncrements, PK)
- user_id (foreignId, users, unique) -- login
- legal_name (string)
- trade_name (string)
- business_type (enum: proprietor, partnership, llp, pvt_ltd, public_ltd)
- gstin (string(15), unique, nullable)
- gstin_verified (boolean, default false)
- pan (string(10))
- fssai_license (string(14), nullable)
- fssai_expiry (date, nullable)
- fssai_verified (boolean, default false)
- years_in_business (unsignedSmallInteger)
- description (text, nullable)
- logo_path (string, nullable)
- cover_image_path (string, nullable)
- verification_status (enum: pending, in_review, verified, rejected, suspended)
- verified_at (timestamp, nullable)
- verified_by (foreignId, users, nullable)
- rejection_reason (text, nullable)
- service_radius_km (unsignedSmallInteger, default 25)
- categories_json (json) -- array of category IDs
- bank_account_encrypted (text, nullable)
- bank_ifsc (string, nullable)
- bank_verified (boolean, default false)
- bank_penny_drop_ref (string, nullable)
- average_rating (decimal(3,2), default 0)
- total_reviews (unsignedInteger, default 0)
- on_time_delivery_pct (decimal(5,2), default 0)
- total_orders_completed (unsignedInteger, default 0)
- timestamps

**supplier_addresses**
- id (bigIncrements, PK)
- supplier_id (foreignId, suppliers)
- type (enum: registered, operational, pickup)
- line1 (string)
- line2 (string, nullable)
- city (string)
- state (string)
- pincode (string(6))
- latitude (decimal(10,7), nullable)
- longitude (decimal(10,7), nullable)
- timestamps

**supplier_catalog_items**
- id (bigIncrements, PK)
- supplier_id (foreignId, suppliers)
- name (string), category (string, indexed)
- unit (enum: kg, ltr, dozen, piece, bag, box)
- price_per_unit (decimal(10,2)), min_order_qty (decimal(10,3), default 1)
- stock_qty (decimal(10,3), nullable), gst_rate (decimal(5,2))
- hsn_code (string, nullable), image_path (string, nullable)
- is_active (boolean, default true), last_price_updated_at (timestamp, nullable)
- timestamps
- index([supplier_id, category])

**rfqs**
- id (bigIncrements, PK)
- buyer_outlet_id (foreignId, outlets), title (string)
- items_json (json) -- [{name, qty, unit, notes}]
- delivery_address_json (json), required_by_date (date)
- invite_type (enum: specific, broadcast)
- invited_supplier_ids_json (json, nullable)
- estimated_value (decimal(14,2), nullable)
- validity_hours (unsignedSmallInteger, default 48), expires_at (timestamp)
- status (enum: draft, open, comparing, awarded, expired, cancelled)
- awarded_quote_id (foreignId, rfq_quotes, nullable)
- created_by (foreignId, users)
- timestamps
- index([status, expires_at])

**rfq_quotes**
- id (bigIncrements, PK)
- rfq_id (foreignId, rfqs), supplier_id (foreignId, suppliers)
- line_items_json (json), subtotal (decimal(14,2)), total_gst (decimal(14,2))
- delivery_charge (decimal(14,2), default 0), grand_total (decimal(14,2))
- payment_terms (enum: cod, prepaid, net_7, net_15, net_30)
- validity_hours (unsignedSmallInteger, default 24), expires_at (timestamp)
- status (enum: submitted, awarded, rejected, expired, withdrawn)
- submitted_at (timestamp)
- timestamps
- unique([rfq_id, supplier_id])

**marketplace_orders**
- id (bigIncrements, PK)
- po_number (string, unique)
- supplier_id (foreignId, suppliers), buyer_outlet_id (foreignId, outlets)
- rfq_id (foreignId, rfqs, nullable), items_json (json)
- subtotal, total_gst, delivery_charge, grand_total (decimal(14,2))
- payment_terms, payment_status (enums as above)
- amount_paid (decimal(14,2), default 0)
- razorpay_order_id, razorpay_payment_id (string, nullable)
- delivery_status (enum: pending, dispatched, in_transit, delivered, partial, cancelled)
- expected_delivery_at, delivered_at, invoice_number, invoice_path
- created_by (foreignId, users)
- timestamps
- index([supplier_id, created_at]), index([buyer_outlet_id, payment_status])

**supplier_reviews**
- id (bigIncrements, PK)
- order_id (foreignId, marketplace_orders, unique)
- supplier_id (foreignId, suppliers), reviewer_user_id (foreignId, users)
- quality, delivery, packaging, communication, value ratings (unsignedTinyInteger)
- average_rating (decimal(3,2))
- review_text (text, nullable), is_anonymous (boolean, default false)
- supplier_response (text, nullable), responded_at (timestamp, nullable)
- timestamps
- index([supplier_id, created_at])

## Technical Notes

**Laravel Backend:**
- `MarketplaceService` orchestrates supplier search, RFQ, quoting, award flows.
- `SupplierVerificationService::verifyGstin()` calls GST API (30d cache); `BankVerificationService::pennyDrop()` uses Razorpay Route / Cashfree (24h cooldown).
- FSSAI verification: regex + manual admin approval; expiry cron `CheckFssaiExpiry` daily suspends expired suppliers.
- `RfqService::create()` stores RFQ, dispatches notifications to invitees (email + SMS + in-app).
- `QuoteService::submit()` validates supplier invited, prevents duplicates; `AwardService::award()` creates `marketplace_orders` and notifies losers.
- `PaymentService` uses `razorpay/razorpay` PHP SDK; `RazorpayWebhookController` updates payment status.
- `InvoiceService` generates GST invoice PDF with optional IRN via ClearTax; stored in S3 with signed URL.
- `RatingService` recomputes supplier aggregate via queued job; `OutletScope` middleware enforces multi-tenant isolation.

**React Frontend:**
- React Query for cached search; infinite scroll for catalogs; horizontal bar charts for ratings.
- RFQ wizard with `react-hook-form` + Zod; quote comparison uses `react-table` with sticky first column.
- Supplier dashboard with Recharts; Razorpay checkout modal with 3DS redirect handling; real-time updates via Echo.

## Subtasks
1. [ ] Create supplier/RFQ/order/review migrations and models
2. [ ] Build `SupplierVerificationService` (GSTIN, FSSAI, penny-drop)
3. [ ] Build `MarketplaceService`, `RfqService`, `QuoteService`, `AwardService`
4. [ ] Integrate Razorpay SDK with webhook listener
5. [ ] Build `InvoiceService` for GST invoice PDF + IRN
6. [ ] Build `RatingService` with aggregate recomputation
7. [ ] Implement FSSAI expiry cron and suspension logic
8. [ ] Build supplier onboarding wizard UI (7 steps)
9. [ ] Build marketplace home, directory, supplier profile UIs
10. [ ] Build RFQ, quote comparison, and award UIs
11. [ ] Build supplier dashboard with KPIs and charts
12. [ ] Build admin verification queue UI
13. [ ] Implement supplier notifications (email, SMS, in-app)
14. [ ] Add multi-tenant scoping middleware
15. [ ] Write tests for verification, RFQ, award, payment, FSSAI, multi-tenant

## Testing Criteria
- [ ] Supplier onboard with valid GSTIN + FSSAI passes verification; invalid fails with reason
- [ ] Buyer creates RFQ, invites 3 suppliers, receives 3 quotes, awards lowest
- [ ] Awarded quote creates PO and notifies losing suppliers
- [ ] Razorpay payment webhook updates order payment status to `paid`
- [ ] Net 7 credit order tracks `amount_paid=0`; manual mark paid updates correctly
- [ ] Supplier rating recompute reflects new average within 60s
- [ ] FSSAI expiry cron suspends supplier within 24h of expiry
- [ ] Search filter "distance < 10km" excludes suppliers outside radius
- [ ] Tenant A cannot query tenant B's orders via direct API call
- [ ] GST invoice PDF contains correct supplier GSTIN, HSN, tax split
- [ ] Penny drop fails for invalid IFSC and returns actionable error
- [ ] Quote with `expires_at` in past is rejected on submit
