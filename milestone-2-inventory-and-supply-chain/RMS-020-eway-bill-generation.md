# RMS-020: E-Way Bill Generation

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-020 |
| **Type** | Story |
| **Epic** | Inventory |
| **Milestone** | M2 - Inventory & Supply Chain |
| **Priority** | Low |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-014, RMS-017 |

## User Story
As an operations manager, I want to generate GST e-way bills automatically for inter-state transfers and high-value purchases, so that goods movement is legally compliant.

## Description
E-way bill is mandatory under Indian GST for movement of goods exceeding 50,000 in value. This module integrates with the NIC e-way bill API to generate, manage, and track e-way bills for stock transfers and purchase returns. It auto-populates GSTIN, HSN codes, transport details, and generates the e-way bill number and QR code.

## Acceptance Criteria
- [ ] Auto-detect when a transfer/transaction requires e-way bill (value > 50,000, inter-state)
- [ ] Generate e-way bill via NIC API with: supplier GSTIN, recipient GSTIN, HSN, value, transport details
- [ ] E-way bill number and QR code received from NIC
- [ ] Print e-way bill with QR code for transporter
- [ ] Track validity (1 day per 200 KM, configurable)
- [ ] Extend validity (Form EWB-02) before expiry
- [ ] Cancel e-way bill within 24 hours of generation
- [ ] Update transporter details (vehicle number) after generation
- [ ] E-way bill report: all generated bills with status

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| E-Way Bill List | /inventory/eway-bills | All e-way bills |
| Generate E-Way Bill | /inventory/eway-bills/generate | Form with auto-filled data |
| E-Way Bill Detail | /inventory/eway-bills/:id | View + print + manage |

### Screen Details

**Generate E-Way Bill**
- Source: linked to transfer/PO (auto-fills line items, values, GSTINs)
- Supply Type: Inward / Outward
- Sub-type: Regular / Bill of Entry / For Own Use
- From GSTIN (auto), To GSTIN (auto)
- Dispatch Address, Destination Address
- Transport: By Road / Rail / Air / Ship
- Vehicle Number, Transporter Name, Transporter ID
- Distance (KM) -> auto-calculate validity
- Line items: HSN, Item, Qty, Taxable Value, Tax Rate
- Generate -> NIC API call -> receive EWB number + QR

**E-Way Bill Detail**
- EWB Number, Date, Validity (expiry date/time)
- QR Code display
- Status: Active / Expired / Cancelled
- Actions: Print, Extend Validity, Cancel, Update Vehicle

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/eway-bills | Generate e-way bill |
| GET | /api/v1/eway-bills | List e-way bills |
| GET | /api/v1/eway-bills/{id} | E-way bill detail |
| POST | /api/v1/eway-bills/{id}/extend | Extend validity |
| POST | /api/v1/eway-bills/{id}/cancel | Cancel (within 24h) |
| PUT | /api/v1/eway-bills/{id}/transporter | Update transporter details |
| GET | /api/v1/eway-bills/{id}/print | Print-ready data with QR |

## Database Tables

```
eway_bills:
  id (bigint, PK)
  outlet_id (bigint, FK)
  reference_type (varchar 50) -- stock_transfer, purchase_return
  reference_id (bigint, nullable)
  ewb_number (varchar 12, unique, nullable) -- from NIC
  ewb_date (timestamp, nullable)
  status (enum: pending, generated, active, expired, cancelled)
  from_gstin (varchar 15)
  to_gstin (varchar 15)
  supply_type (enum: inward, outward)
  total_value (decimal 12,2)
  total_tax (decimal 12,2)
  grand_total (decimal 12,2)
  transporter_id (varchar 15, nullable)
  transporter_name (varchar 200, nullable)
  vehicle_number (varchar 20, nullable)
  distance_km (int, nullable)
  valid_until (timestamp, nullable)
  qr_code_path (varchar, nullable)
  timestamps
```

## Technical Notes
- **Backend**: `EwayBillController.php` + `EwayBillService.php`. NIC API: use `gstin/eway-bill` package or direct HTTP calls to `https://ewaybill.nic.in/api/`. Auth via GST username/password or API key stored securely in `.env`.
- **Auto-detection**: Scheduled job checks all inter-state transfers > 50,000 value -> suggest e-way bill generation.

## Subtasks
1. [ ] Create eway_bills migration
2. [ ] Build EwayBill model
3. [ ] Integrate NIC e-way bill API (auth + generate + cancel + extend)
4. [ ] Build auto-detection for e-way bill requirement
5. [ ] Build QR code storage and print format
6. [ ] Build validity tracking and expiry alerts
7. [ ] Build React e-way bill list, generate, detail pages
8. [ ] Write tests (mock NIC API)

## Testing Criteria
- [ ] Inter-state transfer > 50,000 -> e-way bill required
- [ ] Generate -> NIC returns EWB number + QR -> stored
- [ ] Cancel within 24h -> status cancelled
- [ ] Extend validity -> new valid_until date
- [ ] Print format correct with QR code
