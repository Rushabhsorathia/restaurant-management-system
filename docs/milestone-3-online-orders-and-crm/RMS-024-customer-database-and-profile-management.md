# RMS-024: Customer Database & Profile Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-024 |
| **Type** | Story |
| **Epic** | Customer Relationship Management |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-014 (Billing & Payment), RMS-022 (Aggregator Integration) |

## User Story
As a restaurant manager, I want a central customer database that captures and consolidates customer information from dine-in, online, and kiosk orders, so that I can understand my customers, personalize their experience, and run targeted marketing campaigns.

## Description
This story builds the customer master data module that serves as the foundation for all CRM features in Milestone 3. Every customer interaction -- a dine-in bill, an online order from Swiggy/Zomato, a QR code order, or a reservation -- creates or updates a customer record. The system deduplicates by phone number (primary key for Indian market) and optionally by email.

The customer profile aggregates a 360-degree view: contact details, lifetime value, visit count, average spend, preferred items, last visit date, order history across all channels, dietary preferences (vegetarian, vegan, Jain, etc.), allergen alerts, and custom tags. Managers can view, edit, merge duplicate records, and export customer data.

The module is GDPR/DPDP-ready: customers can request data export and deletion. All data access is logged. Soft-delete preserves referential integrity for billing records while removing PII from active queries. A consent field tracks whether the customer has opted into marketing communications.

## Acceptance Criteria
- [ ] A customer record is auto-created when a phone number is entered during billing if no match exists; existing customer is updated if phone matches.
- [ ] Online orders from aggregators create/update customer records by matching phone number.
- [ ] Customer profile displays: name, phone, email, address, lifetime value, total visits, average spend, first/last visit, preferred items (top 5), dietary tags, and custom labels.
- [ ] Order history tab shows all orders (dine-in, online, QR, kiosk) in a unified timeline with channel badge, date, amount, and items.
- [ ] Manager can manually create, edit, and soft-delete customer records.
- [ ] Duplicate detection flags records with same phone or similar name+phone for manual merge review.
- [ ] Merge operation combines two customer records into one, preserving all order history and consolidating tags/preferences.
- [ ] Data export generates a CSV/Excel of all customers or a filtered segment; individual customer data export (JSON) for GDPR/DPDP requests.
- [ ] Customer can be marked as opted-out of marketing; opted-out customers are excluded from all campaign audiences (RMS-026).
- [ ] Search supports phone number, name, email, and tag-based filtering with pagination.
- [ ] All customer record views and edits are logged in an audit trail with user and timestamp.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Customer List | /crm/customers | Paginated, searchable customer directory |
| Customer Profile | /crm/customers/{id} | 360-degree customer view with tabs |
| Customer Create/Edit | /crm/customers/create | Form for manual customer entry |
| Merge Review | /crm/customers/merge | Side-by-side duplicate comparison and merge tool |
| Data Export | /crm/customers/export | Export configuration and download |

### Screen Details

**Customer List (/crm/customers)**
- Layout: Full-width data table with top filter/search bar and left sidebar for saved segments.
- Search bar: Global search input (searches name, phone, email), with autocomplete suggestions.
- Filters: Tier (Silver/Gold/Platinum/None), Tags (multiselect), Dietary Preference, Min Lifetime Value, Last Visit Range, Marketing Consent (Opted-in/Opted-out).
- Table columns: Name | Phone | Email | Tier | Total Visits | Lifetime Value | Avg Spend | Last Visit | Tags | Actions.
- Actions per row: "View" (icon), "Edit" (icon), "Merge" (if duplicates flagged).
- Buttons: "Add Customer", "Export", "Find Duplicates".
- Pagination: 25/50/100 per page.
- Bulk select with checkbox column enables bulk "Add Tag", "Export Selected", "Mark Opt-out".

**Customer Profile (/crm/customers/{id})**
- Layout: Header section + tabbed content area.
- Header: Avatar/initials, Name, Phone, Email, Tier badge, Marketing consent indicator, Edit/Delete buttons.
- Summary cards: Lifetime Value, Total Visits, Avg Spend, Last Visit (4 cards in a row).
- Tabs: Overview, Order History, Preferences, Labels & Tags, Notes, Audit Trail.
- Overview tab: Preferred items (top 5 with order count), Channel breakdown (dine-in vs online vs kiosk), Visit frequency chart (last 6 months), Loyalty points summary (from RMS-025).
- Order History tab: Data table with columns: Date | Channel (badge) | Order ID | Items | Amount | Payment Mode | Status. Sortable by date.
- Preferences tab: Dietary preferences (checkboxes: Vegetarian, Vegan, Jain, Eggetarian, Non-Vegetarian), Allergens (multiselect: Peanuts, Dairy, Gluten, Shellfish, etc.), Spice tolerance (slider 1-5), Seating preference (text), Default instructions (textarea).
- Labels & Tags tab: Current tags as chips, "Add Tag" input with autocomplete, auto-tag badges (system-generated).
- Notes tab: Free-text internal notes (for staff), timestamped with author.
- Audit Trail tab: Table of all views/edits: Timestamp | User | Action | Field Changed | Old Value | New Value.
- Buttons: "Create Order", "Add Note", "Print Profile", "Export Data (GDPR)", "Delete (GDPR)".

**Customer Create/Edit Form (/crm/customers/create)**
- Fields: First Name (required), Last Name, Phone (required, 10-digit Indian format validation), Email (email validation), Date of Birth (date picker), Anniversary Date (date picker), Address Line 1, Address Line 2, City, State, Pincode, Dietary Preference (dropdown), Allergens (multiselect), Marketing Consent (toggle), Tags (tag input).
- Buttons: "Save", "Save & Add Another", "Cancel".
- Validation: Phone unique (warning if exists, offer to open existing record), email format, pincode 6-digit.

**Merge Review (/crm/customers/merge)**
- Layout: Two-column side-by-side comparison of duplicate records, merged preview in center or below.
- Left column: Record A details (highlighted fields).
- Right column: Record B details.
- Per-field merge selector: Radio buttons to choose which value to keep for each field.
- Order history: Shows count from each and confirms both histories merge.
- Preview panel: Shows final merged record before confirmation.
- Buttons: "Merge into A", "Merge into B", "Cancel".
- Post-merge: The non-primary record is soft-deleted; its order history is re-pointed to the surviving record.

**Data Export (/crm/customers/export)**
- Fields: Export Scope (All Customers / Filtered Segment / Selected), Fields to Include (checkbox list: name, phone, email, address, LTV, visits, tags, etc.), Format (CSV/Excel/JSON), Date Range (for order data).
- Consent notice: Warning that exported data contains PII and should be handled per DPDP Act.
- Buttons: "Generate Export", "Download".
- Export runs as a queued job; notification appears when ready.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/customers | List customers with search, filter, pagination |
| POST | /api/v1/customers | Create new customer |
| GET | /api/v1/customers/{id} | Get customer profile detail |
| PUT | /api/v1/customers/{id} | Update customer |
| DELETE | /api/v1/customers/{id} | Soft-delete customer (GDPR) |
| GET | /api/v1/customers/{id}/orders | Get customer order history across all channels |
| GET | /api/v1/customers/{id}/preferences | Get dietary preferences and allergens |
| PUT | /api/v1/customers/{id}/preferences | Update preferences |
| POST | /api/v1/customers/{id}/tags | Add tag to customer |
| DELETE | /api/v1/customers/{id}/tags/{tagId} | Remove tag |
| GET | /api/v1/customers/find-duplicates | Get list of potential duplicate pairs |
| POST | /api/v1/customers/merge | Merge two customer records |
| GET | /api/v1/customers/{id}/export | Export individual customer data (GDPR JSON) |
| POST | /api/v1/customers/export | Generate bulk export job |
| GET | /api/v1/customers/export/{jobId}/download | Download generated export file |
| PUT | /api/v1/customers/{id}/marketing-consent | Update marketing opt-in/opt-out |
| GET | /api/v1/customers/{id}/audit-trail | Get audit trail |
| POST | /api/v1/customers/{id}/notes | Add internal note |

## Database Tables

**customers**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- first_name (string)
- last_name (string, nullable)
- phone (string, indexed) -- primary dedup key
- email (string, nullable, indexed)
- date_of_birth (date, nullable)
- anniversary_date (date, nullable)
- address_line1 (string, nullable)
- address_line2 (string, nullable)
- city (string, nullable)
- state (string, nullable)
- pincode (string, nullable)
- lifetime_value (decimal(12,2), default 0)
- total_visits (unsignedInteger, default 0)
- total_orders (unsignedInteger, default 0)
- avg_spend (decimal(10,2), default 0)
- first_visit_at (timestamp, nullable)
- last_visit_at (timestamp, nullable)
- preferred_items_json (json, computed/cached top items)
- dietary_preference (enum: none, vegetarian, vegan, jain, eggetarian, non_vegetarian, default none)
- allergens_json (json, array of allergen strings)
- spice_tolerance (unsignedTinyInteger, default 3)
- seating_preference (string, nullable)
- default_instructions (text, nullable)
- marketing_consent (boolean, default true)
- marketing_consent_updated_at (timestamp, nullable)
- merged_into_id (foreignId, customers, nullable) -- for merged records
- timestamps, softDeletes
- unique([restaurant_id, phone]) -- dedup enforcement

**customer_notes**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- user_id (foreignId, users)
- note (text)
- timestamps

**customer_audit_trail**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- user_id (foreignId, users)
- action (enum: viewed, created, updated, deleted, merged, exported)
- field_changed (string, nullable)
- old_value (text, nullable)
- new_value (text, nullable)
- metadata (json, nullable)
- created_at

**customer_tag_map** (pivot)
- customer_id (foreignId, customers)
- customer_tag_id (foreignId, customer_tags)
- source (enum: manual, auto)
- timestamps
- primary key ([customer_id, customer_tag_id])

## Technical Notes

**Laravel Backend:**
- Controllers: `CustomerController` (index, store, show, update, destroy), `CustomerOrderController` (history), `CustomerPreferenceController` (show, update), `CustomerTagController` (attach, detach), `CustomerMergeController` (findDuplicates, merge), `CustomerExportController` (individual, bulk, download), `CustomerNoteController` (store), `CustomerAuditController` (trail).
- Observer: `CustomerObserver` listens to order creation events; on order completion, updates customer `lifetime_value`, `total_visits`, `total_orders`, `avg_spend`, `last_visit_at`, and recomputes `preferred_items_json` via a queued job `RecomputeCustomerStatsJob`.
- Dedup: On new order, `CustomerResolutionService::resolveByPhone($phone)` returns existing or creates new customer. For aggregator orders without phone, a placeholder customer with name only is created, flagged for enrichment.
- Merge: `CustomerMergeService::merge($primaryId, $secondaryId)` transaction: updates all `orders.customer_id` from secondary to primary, merges tags (unique union), sums stats, soft-deletes secondary, sets `merged_into_id`.
- GDPR export: `ExportCustomerDataJob` serializes all customer data + orders + preferences to JSON file stored in `storage/app/exports/`, signed URL for download.
- Audit: `LogCustomerAccess` middleware logs all `GET /customers/{id}` requests; observer logs all mutations.
- Events: `CustomerCreated`, `CustomerUpdated`, `CustomerMerged`, `CustomerMarketingConsentChanged` broadcast for downstream CRM modules.
- Search: Use MySQL FULLTEXT index on name fields + exact phone match; for larger datasets, Scout/Meilisearch recommended as enhancement.
- Routes in `routes/api.php`.

**React Frontend:**
- Components: `CustomerListPage`, `CustomerSearchBar`, `CustomerFiltersSidebar`, `CustomerTable`, `CustomerProfilePage`, `ProfileSummaryCards`, `OrderHistoryTab`, `PreferencesTab`, `LabelsTab`, `NotesTab`, `AuditTrailTab`, `PreferredItemsList`, `ChannelBreakdownChart`, `CustomerForm`, `MergeReviewPage`, `SideBySideComparison`, `ExportConfigPage`.
- State: Zustand `useCustomerStore`.
- Customer resolution during billing: `useCustomerResolution` hook that calls `GET /customers?phone={phone}` on blur and auto-fills customer data.

## Subtasks
1. [ ] Create `customers`, `customer_notes`, `customer_audit_trail`, `customer_tag_map` migrations and models
2. [ ] Implement `CustomerObserver` with stats recomputation queue job
3. [ ] Build `CustomerResolutionService` for phone-based dedup at order/bill time
4. [ ] Build CustomerController CRUD with search, filter, pagination
5. [ ] Build CustomerOrderController unifying orders across all channels
6. [ ] Implement preference and allergen management
7. [ ] Build `CustomerMergeService` with order re-pointing and tag consolidation
8. [ ] Implement find-duplicates algorithm (phone exact + name fuzzy)
9. [ ] Build GDPR individual export job and bulk export with field selection
10. [ ] Implement audit trail logging via observer and middleware
11. [ ] Build marketing consent management with propagation to campaign module
12. [ ] Create React CustomerListPage with search, filters, and table
13. [ ] Create React CustomerProfilePage with all tabs
14. [ ] Build React MergeReviewPage with side-by-side comparison
15. [ ] Build React ExportConfigPage
16. [ ] Write tests for CRUD, dedup, merge, export, audit trail

## Testing Criteria
- [ ] New order with phone creates customer; second order with same phone updates existing record
- [ ] Customer profile shows correct lifetime value and visit count after multiple orders
- [ ] Duplicate detection flags same-phone records
- [ ] Merge operation re-points all orders and consolidates tags
- [ ] GDPR export produces complete JSON with all customer data
- [ ] Marketing opt-out excludes customer from campaign audiences
- [ ] Search by phone returns correct customer instantly
- [ ] Audit trail records every view and edit with user and timestamp
- [ ] Preferred items list reflects actual order history accurately
- [ ] Soft-deleted customer's orders remain intact for billing integrity
