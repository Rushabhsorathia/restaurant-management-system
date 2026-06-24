# RMS-007: Table & Floor Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-007 |
| **Type** | Story |
| **Epic** | Core POS |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | High |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-005 |

## User Story
As a restaurant manager, I want to manage my restaurant's floor plan, tables, and seating areas, so that staff can assign orders to tables and track occupancy in real-time.

## Description
This module manages the physical layout of the restaurant. Tables are organized into areas/zones (e.g., Ground Floor, AC Section, Rooftop, Outdoor). Each table has a number, seating capacity, and current status (available, occupied, reserved, dirty). The system generates a unique QR code per table for QR ordering integration. A visual floor plan view lets staff see table status at a glance.

## Acceptance Criteria
- [ ] Manager can create areas/zones (e.g., "AC Section", "Outdoor")
- [ ] Manager can add tables with number, capacity, and area assignment
- [ ] Table status shows: Available (green), Occupied (red), Reserved (yellow), Dirty (orange)
- [ ] Each table generates a unique QR code for QR ordering
- [ ] Floor plan grid view shows all tables with live status colors
- [ ] Table can be merged (combine two tables for large groups)
- [ ] Table can be split (split a large table into two)
- [ ] Table status auto-updates when order is placed (Available -> Occupied)
- [ ] Table status auto-updates when bill is settled (Occupied -> Dirty -> Available)
- [ ] Manager can manually override table status
- [ ] QR code is downloadable as PNG and printable as sticker

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Floor Plan View | /tables | Visual grid of all tables with status colors |
| Area Management | /tables/areas | CRUD for areas/zones |
| Table List | /tables/list | List view with filters and bulk actions |
| Add/Edit Table | /tables/create, /tables/:id/edit | Form to create/edit table |
| QR Code View | /tables/:id/qr | View and download QR code for table |
| Table Status Board | /tables/board | Live status board for staff display |

### Screen Details

**Floor Plan View (/tables)**
- Top bar: Area selector dropdown (All Areas / AC Section / Outdoor / etc.), View toggle (Grid / List), "+ Add Table" button
- Grid of table cards: Table number (large), capacity icon (chairs), current status badge, running order total if occupied
- Click a table card -> opens order screen if occupied, or create new order if available
- Right sidebar: Legend (Available=green, Occupied=red, Reserved=yellow, Dirty=orange), Quick stats (X available, Y occupied)

**Add/Edit Table Form**
- Table Number (text, required, unique per area)
- Area/Zone (dropdown, required)
- Seating Capacity (number, required, default 4)
- Table Type: Square / Round / Booth / Bar Stool (dropdown)
- Shape/Size: 2-seater / 4-seater / 6-seater / 8-seater / Custom (dropdown)
- Deposit Amount (number, optional - for advance booking)
- Sort Order (number, for display ordering)
- Auto-generate QR Code (checkbox, default checked)

**Area Management (/tables/areas)**
- List of areas with: Name, Table Count, Total Capacity, Action buttons (Edit, Delete)
- Add Area form: Area Name (required), Description, Floor/Level (text), Sort Order, Color Code (color picker for floor plan)
- Delete confirmation: "This area has X tables. Move tables to another area or delete all?"

**QR Code View**
- Large QR code display (300x300px)
- URL encoded: `https://order.restaurant.com/t/{table_uuid}`
- Download buttons: PNG, PDF (sticker format), Print directly
- Table info overlay: Table number, Area name, Restaurant name

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/areas | List all areas for outlet |
| POST | /api/v1/areas | Create new area |
| PUT | /api/v1/areas/{id} | Update area |
| DELETE | /api/v1/areas/{id} | Delete area |
| GET | /api/v1/tables | List all tables (filter by area, status) |
| POST | /api/v1/tables | Create new table |
| GET | /api/v1/tables/{id} | Get table details |
| PUT | /api/v1/tables/{id} | Update table |
| DELETE | /api/v1/tables/{id} | Delete table |
| PATCH | /api/v1/tables/{id}/status | Update table status |
| POST | /api/v1/tables/{id}/merge | Merge table with another |
| POST | /api/v1/tables/{id}/split | Split table |
| GET | /api/v1/tables/{id}/qr | Get QR code image/PDF |
| GET | /api/v1/tables/floor-plan | Get floor plan layout data |

## Database Tables

```
areas:
  id (bigint, PK)
  outlet_id (bigint, FK -> outlets)
  name (varchar 100)
  description (text, nullable)
  floor_level (varchar 50, nullable)
  color_code (varchar 7, default '#3B82F6')
  sort_order (int, default 0)
  is_active (boolean, default true)
  timestamps

tables:
  id (bigint, PK)
  outlet_id (bigint, FK -> outlets)
  area_id (bigint, FK -> areas)
  table_number (varchar 20)
  capacity (int, default 4)
  table_type (enum: square, round, booth, bar_stool)
  size_category (enum: 2-seater, 4-seater, 6-seater, 8-seater, custom)
  status (enum: available, occupied, reserved, dirty, maintenance)
  qr_code_uuid (uuid, unique)
  sort_order (int, default 0)
  is_active (boolean, default true)
  timestamps
  unique: [outlet_id, area_id, table_number]

table_merges:
  id (bigint, PK)
  primary_table_id (bigint, FK -> tables)
  merged_table_id (bigint, FK -> tables)
  merged_by (bigint, FK -> users)
  timestamps
```

## Technical Notes
- **Backend**: Laravel controller `TableController.php` with resource routes. Use `TableStatus` enum class. QR generation via `simplesoftwareio/simple-qrcode` package. Store QR UUID on table creation via model `booted()` hook.
- **Frontend**: React component `FloorPlan.tsx` with drag-and-drop positioning (react-grid-layout). Table card component `TableCard.tsx` with status-based color classes. Area tabs component.
- **Real-time**: WebSocket broadcast on table status change for live floor plan updates.
- **QR**: Generate on table creation, encode as `table_uuid` in URL. Use Laravel route model binding by UUID for QR order lookup.

## Subtasks
1. [ ] Create areas migration and model
2. [ ] Create tables migration and model with relationships
3. [ ] Build AreaController with CRUD API
4. [ ] Build TableController with CRUD + status + merge/split API
5. [ ] Install QR code package, generate on table creation
6. [ ] Build React FloorPlan grid view component
7. [ ] Build Table card component with status colors
8. [ ] Build Add/Edit table form with validation
9. [ ] Build Area management page
10. [ ] Build QR code download/print page
11. [ ] Add WebSocket event for table status changes
12. [ ] Write feature tests for table lifecycle

## Testing Criteria
- [ ] Create area -> create table in that area -> verify appears in floor plan
- [ ] Place order on table -> status changes to occupied automatically
- [ ] Settle bill -> status changes to dirty -> staff marks clean -> available
- [ ] Merge two tables -> both show as occupied under primary table
- [ ] QR code URL resolves to table-specific ordering page
- [ ] Delete area with tables -> prevents deletion or forces table migration
