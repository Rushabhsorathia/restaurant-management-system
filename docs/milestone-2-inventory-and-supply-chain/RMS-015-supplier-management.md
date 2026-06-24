# RMS-015: Supplier Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-015 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | High |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-002 |

## User Story
As a purchase manager, I want to maintain a supplier database with contact details, payment terms, and performance ratings, so that I can order efficiently and track outstanding payables.

## Description
Supplier management is the master data for all vendors. Each supplier has contact information, GST details, payment terms (credit days), and a performance scorecard based on delivery timeliness, quality acceptance rate, and price competitiveness. The system tracks outstanding payables per supplier (unpaid GRNs/invoices) and supplier-wise order history.

## Acceptance Criteria
- [ ] CRUD for suppliers: name, contact person, phone, email, address, GSTIN, PAN
- [ ] Payment terms: credit days (7/15/30/45/60), payment mode preference
- [ ] Supplier category: Vegetable Vendor, Meat Supplier, Dairy, Packaging, Equipment, etc.
- [ ] Outstanding payable tracking: sum of unpaid GRNs/invoices
- [ ] Supplier rating: auto-calculated from delivery performance (on-time %, quality acceptance %)
- [ ] Supplier-wise order history (PO count, total value, avg rate per material)
- [ ] Supplier status: Active / Inactive / Blacklisted
- [ ] Multiple contact persons per supplier
- [ ] Supplier bank details for payment (account no, IFSC, UPI)
- [ ] Supplier product list: which materials this supplier typically provides
- [ ] Negotiated contract rates: material-specific rates locked for a period

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Supplier List | /inventory/suppliers | All suppliers with outstanding and rating |
| Add/Edit Supplier | /inventory/suppliers/create, /:id/edit | Supplier master form |
| Supplier Detail | /inventory/suppliers/:id | Profile, history, payables, rating |
| Supplier Payables | /inventory/suppliers/:id/payables | Outstanding invoices |
| Supplier Rating | /inventory/suppliers/:id/rating | Performance breakdown |

### Screen Details

**Supplier List (/inventory/suppliers)**
- Filters: Category, Status (Active/Inactive/Blacklisted), Outstanding > 0
- Table: Name | Category | Contact | Phone | GSTIN | Outstanding Amount | Rating (stars) | Status | Actions
- Sort by: Outstanding (high to low), Rating (low to high - needs attention)
- Summary bar: Total Suppliers, Total Outstanding, Avg Rating

**Add/Edit Supplier Form**
- Business Name (required)
- Legal Name (for GST invoices)
- Type: Individual / Partnership / Company / Proprietorship
- GSTIN (15-char, validated with format check)
- PAN (10-char)
- Category (dropdown: Vegetables, Meat, Dairy, Spices, Dry Goods, Beverages, Packaging, Equipment, Services, Other)
- Payment Terms: Credit Days (dropdown: Cash on Delivery / 7 / 15 / 30 / 45 / 60)
- Preferred Payment Mode: UPI / Bank Transfer / Cheque / Cash
- Contact Persons (multiple):
  - Name, Role, Phone, Email
- Address: Line 1, Line 2, City, State, Pincode
- Bank Details: Account Name, Account Number, IFSC Code, Bank Name, UPI ID
- Products Supplied: multi-select materials this supplier provides
- Contract Rates (optional): material -> rate -> valid till date
- Status: Active / Inactive / Blacklisted
- Blacklist reason (if blacklisted)

**Supplier Detail (/inventory/suppliers/:id)**
- Header: Name, Category, Rating, Status, Outstanding (clickable to payables)
- Tabs: Overview | Orders | Payables | Products | Ratings | Documents

Overview Tab:
- Contact info, address, bank details
- Key metrics: Total Orders, Total Value, Avg Order Value, Last Order Date

Orders Tab:
- Table of all POs: PO# | Date | Amount | Status | GRN Count
- Click to view PO detail

Payables Tab:
- Outstanding invoices: GRN/Invoice# | Date | Amount | Due Date | Days Overdue | Status
- Total Outstanding summary
- "Record Payment" button per invoice
- Payment history

Products Tab:
- List of materials supplied with contract rate vs last rate
- Rate trend mini-chart per material

Ratings Tab:
- On-time delivery rate: % of POs delivered by expected date
- Quality acceptance rate: % of GRN items accepted vs rejected
- Price competitiveness: avg rate vs market avg
- Overall score: weighted average (0-100)
- Rating history (monthly trend)

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/suppliers | List suppliers |
| POST | /api/v1/suppliers | Create supplier |
| GET | /api/v1/suppliers/{id} | Get supplier detail |
| PUT | /api/v1/suppliers/{id} | Update supplier |
| DELETE | /api/v1/suppliers/{id} | Delete supplier (soft) |
| GET | /api/v1/suppliers/{id}/payables | Outstanding payables |
| POST | /api/v1/suppliers/{id}/payments | Record payment |
| GET | /api/v1/suppliers/{id}/orders | Order history |
| GET | /api/v1/suppliers/{id}/rating | Performance rating |
| GET | /api/v1/suppliers/{id}/products | Supplied products with rates |
| POST | /api/v1/suppliers/{id}/contract-rate | Set contract rate for material |

## Database Tables

```
suppliers:
  id (bigint, PK)
  outlet_id (bigint, FK) -- null for multi-outlet shared
  business_name (varchar 200)
  legal_name (varchar 200, nullable)
  type (enum: individual, partnership, company, proprietorship)
  category (varchar 100)
  gstin (varchar 15, nullable)
  pan (varchar 10, nullable)
  payment_terms_days (int, default 0) -- 0 = COD
  preferred_payment_mode (enum: upi, bank, cheque, cash)
  address_line1 (varchar 200)
  address_line2 (varchar 200, nullable)
  city (varchar 100)
  state (varchar 100)
  pincode (varchar 10)
  bank_account_name (varchar 200, nullable)
  bank_account_number (varchar 30, nullable)
  bank_ifsc (varchar 15, nullable)
  bank_name (varchar 100, nullable)
  upi_id (varchar 100, nullable)
  status (enum: active, inactive, blacklisted)
  blacklist_reason (text, nullable)
  rating_score (decimal 5,2, default 0) -- auto-calculated 0-100
  timestamps

supplier_contacts:
  id (bigint, PK)
  supplier_id (bigint, FK -> suppliers)
  name (varchar 200)
  role (varchar 100, nullable)
  phone (varchar 20)
  email (varchar 200, nullable)
  is_primary (boolean, default false)
  timestamps

supplier_products:
  id (bigint, PK)
  supplier_id (bigint, FK -> suppliers)
  material_id (bigint, FK -> raw_materials)
  contract_rate (decimal 10,2, nullable)
  contract_valid_till (date, nullable)
  last_rate (decimal 10,2)
  last_po_date (date, nullable)
  timestamps

supplier_payments:
  id (bigint, PK)
  supplier_id (bigint, FK -> suppliers)
  amount (decimal 12,2)
  payment_mode (enum: upi, bank, cheque, cash)
  reference_number (varchar 100)
  payment_date (date)
  notes (text, nullable)
  created_by (bigint, FK -> users)
  timestamps

supplier_payables:
  id (bigint, PK)
  supplier_id (bigint, FK -> suppliers)
  grn_id (bigint, FK -> goods_receipt_notes)
  invoice_number (varchar 100)
  invoice_date (date)
  amount (decimal 12,2)
  due_date (date)
  status (enum: unpaid, partial, paid)
  paid_amount (decimal 12,2, default 0)
  timestamps
```

## Technical Notes
- **Backend**: `SupplierController.php`. Rating auto-calculation: run as scheduled job (daily) -> recalculate for suppliers with recent activity. GSTIN validation regex: `^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$`.
- **Payables**: Auto-created when GRN is posted. Due date = GRN date + payment_terms_days. Overdue flag when due_date < today and status != paid.

## Subtasks
1. [ ] Create suppliers, supplier_contacts, supplier_products, supplier_payments, supplier_payables migrations
2. [ ] Build Supplier model with all relationships
3. [ ] Build SupplierController CRUD API
4. [ ] Implement GSTIN validation
5. [ ] Build payables auto-generation on GRN post
6. [ ] Build payment recording
7. [ ] Build rating calculation service (daily job)
8. [ ] Build contract rate management
9. [ ] Build React supplier list with outstanding and rating
10. [ ] Build supplier form with contacts and bank details
11. [ ] Build supplier detail tabs page
12. [ ] Build payables and payment recording UI
13. [ ] Write supplier tests

## Testing Criteria
- [ ] Create supplier with GSTIN -> validates format
- [ ] Create PO -> receive GRN -> payable auto-created with correct due date
- [ ] Record partial payment -> status partial -> remaining outstanding
- [ ] Record full payment -> status paid -> outstanding 0
- [ ] Rating: supplier with 90% on-time, 95% quality -> score ~92
- [ ] Blacklist supplier -> cannot create new PO
- [ ] Contract rate shows in PO creation auto-fill
