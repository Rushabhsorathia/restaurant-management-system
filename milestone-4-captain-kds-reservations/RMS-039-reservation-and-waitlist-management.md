# RMS-039: Reservation & Waitlist Management

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-039 |
| **Type** | Story |
| **Epic** | Reservations, Waitlist & Guest Experience |
| **Milestone** | Milestone 4 - Captain App, KDS & Reservations |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-012 (Table Management), RMS-024 (Customer Database), RMS-026 (SMS/WhatsApp Marketing) |

## User Story
As a host/manager of a busy Indian restaurant, I want to manage reservations on a calendar, see live table availability, communicate confirmations and reminders by SMS, and run a waitlist with estimated wait times for walk-ins, so that we maximize covers during peak hours, reduce no-shows, and never turn away a guest who would otherwise wait 20 minutes for a table.

## Description
Reservations and waitlist management is the front door of any sit-down restaurant. This module gives hosts a real-time view of bookings by date and time slot, table-availability heatmap, and a waitlist queue with live estimated wait. The module integrates deeply with the customer database (RMS-024) so repeat guests are recognized, and with SMS/WhatsApp marketing (RMS-026) for automated confirmations and reminders.

Reservations can be created from three sources: (1) host entering walk-in phone reservation at the podium, (2) a public booking widget embedded on the restaurant website (RMS-029) or Google Business Profile, and (3) WhatsApp-based booking via a chatbot. Each reservation captures guest name, phone, email, guest count, requested date/time, special requests (birthday, anniversary, dietary needs, high-chair, accessibility), and optional pre-order. The system auto-assigns the best-fit table based on party size and seating preference (indoor, outdoor, AC, window), with the host able to override.

SMS confirmations fire immediately via MSG91 (Indian SMS gateway); reminder SMS fires 4 hours and 1 hour before the slot. No-show detection: if a guest hasn't arrived 15 minutes past slot start, the system flags "No Show Risk" and offers the table to the waitlist queue. Cancellation policy is configurable per outlet (free cancel up to 2 hours before, no-show fee option).

The waitlist is the critical piece for Indian restaurants where walk-in demand exceeds reservation capacity. The host adds a walk-in to the queue with name, phone, party size, quoted wait time, and quote timestamp. The queue orders by quote timestamp; estimated wait recalculates dynamically based on seated parties and upcoming reservations freeing up. When a table becomes available, the host assigns it; the system sends an SMS "Your table is ready, please come to the host desk" (single ping, no spam). If the guest doesn't arrive in 10 minutes, the system releases the table and offers it to the next in queue.

## Acceptance Criteria
- [ ] Host can create a reservation with name, phone, email, guest count, date/time, special requests, and optional pre-order
- [ ] Calendar view shows reservations by date, time slot, and table; supports day / week / month views
- [ ] Table availability heatmap overlays bookings on the floor plan (RMS-012)
- [ ] Auto-assignment suggests best-fit tables based on guest count and seating preference; host can override
- [ ] SMS confirmation sent immediately via MSG91 on reservation creation
- [ ] Reminder SMS sent 4 hours before slot and 1 hour before slot (configurable)
- [ ] No-show detection flags reservation as "No Show Risk" 15 minutes past slot start
- [ ] Cancellation policy configurable per outlet (e.g., free cancel ≥2 hrs before; no-show fee tracking)
- [ ] Public booking widget embeddable on restaurant website; submits to same reservation endpoint
- [ ] Waitlist queue orders by quote timestamp; estimated wait recalculates dynamically
- [ ] "Table Ready" SMS sent when walk-in is assigned; if guest doesn't arrive in 10 min, table auto-released
- [ ] Special requests surfaced on reservation card (birthday cake, dietary needs, accessibility)
- [ ] Pre-orders attached to reservation flow into kitchen as part of the seated order
- [ ] Recognition: returning guests pulled from customer database (RMS-024) by phone — shows visit history and LTV
- [ ] All reservation lifecycle events logged (created, confirmed, reminded, seated, completed, cancelled, no_show)

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Reservation Calendar | /reservations/calendar | Day/week/month calendar view of bookings |
| Reservation Detail | /reservations/{id} | Full reservation record with edits and lifecycle |
| New Reservation | /reservations/new | Form for host to create a booking |
| Floor Availability | /reservations/floor | Floor plan with time-slot overlay |
| Waitlist Queue | /reservations/waitlist | Live queue with estimated waits |
| Reservation Settings | /reservations/settings | Slot config, SMS templates, cancellation policy |

### Screen Details

**Reservation Calendar (/reservations/calendar)**
- Layout: Full-width calendar grid; rows = time slots (15-min intervals by default), columns = tables (or table sections: Indoor / Outdoor / AC / Window).
- View toggle: Day | Week | Month; Date picker jumps to specific day.
- Time range: Configurable per outlet (e.g., 11:00 to 23:30 in 15-min slots).
- Each reservation block: Color-coded by status (confirmed=blue, seated=green, completed=gray, no-show=red, cancelled=strikethrough); shows guest name + party size + time.
- Drag-and-drop: Drag a reservation to a different table or time slot to reassign.
- Sidebar (right): Filters — Section (Indoor/Outdoor), Status, Source (Phone/Widget/WhatsApp), Search by name/phone.
- Buttons: "New Reservation", "Walk-in to Waitlist", "Print Day Sheet", "Export CSV".
- Live updates: WebSocket pushes new reservations, cancellations, status changes within 2 seconds.

**Reservation Detail (/reservations/{id})**
- Header: Guest name, phone, party size, slot time, status badge, table assignment (with reassign button).
- Sections: Guest Info (name, phone, email, returning guest indicator with visit count + LTV from RMS-024), Special Requests (chips: 🎂 Birthday, ♿ Accessible, 🪑 High chair, 🌶️ Spice level, 🚫 Jain), Pre-Order (items summary if any), Notes (free-text), Source (Phone/Widget/WhatsApp with link to original).
- Lifecycle timeline: Booked → Confirmed (SMS sent) → Reminder 4h sent → Reminder 1h sent → Seated → Completed, with timestamps.
- Buttons: "Send Confirmation SMS", "Send Reminder Now", "Mark Seated" (assigns table and triggers order creation in RMS-037), "Edit", "Cancel", "Mark No-Show", "Print", "Email Guest".
- Returning guest detection: If phone matches customer database, show "Welcome back — 12th visit, ₹8,400 LTV" with link to profile.

**New Reservation (/reservations/new)**
- Layout: Step-by-step form (4 steps): Guest → Slot → Table → Confirm.
- Step 1 — Guest: Name (autofills from customer DB on phone entry), Phone (required, 10-digit validation), Email, Guest count (stepper), Returning guest auto-detected.
- Step 2 — Slot: Date picker + time slot grid (today/tomorrow/next 7 days; available slots in green, fully booked in gray).
- Step 3 — Table: Suggested tables highlighted; seating preference (Indoor/Outdoor/AC/Window) filter; manual override possible.
- Step 4 — Confirm: Review all fields, special requests checkbox group, optional pre-order (links to menu), cancellation policy shown, "Confirm & Send SMS" button.
- Validation: Phone format, slot must be in future and within operating hours, guest count ≤ available table capacity.
- Buttons: "Back", "Next", "Confirm Booking" (final step), "Save as Draft".

**Floor Availability (/reservations/floor)**
- Layout: Floor plan (from RMS-012) overlaid with time-slider; drag slider through the day to see availability heatmap.
- Color coding per table: Green (free), Yellow (reserved later), Orange (occupied now or reserved soon), Red (no-show risk), Gray (dirty/cleaning).
- Time slider: Snap to 15-min slots; default shows current time; jump-to-now button.
- Sidebar: Selected time slot's full reservation list (party name, size, contact, special requests).
- Buttons: "New Reservation at this time", "Filter Section", "Print This View".
- Live updates: Reservation changes reflect in heatmap within 2 seconds.

**Waitlist Queue (/reservations/waitlist)**
- Layout: Vertical list of waiting parties, sorted by quoted time (oldest first), with current estimated wait per party.
- Each row: Quote time | Party name | Phone (tap to call) | Party size | Quoted wait | Current estimated wait | Status badge (Waiting / Notified / Seated / No-Show / Left).
- Top bar: Active waitlist count, average actual wait time (rolling 7-day), longest current wait.
- Quick-add: "Add Walk-in" floating action button — form slides up with name, phone, party size, quoted wait.
- Actions per row: "Notify" (sends Table Ready SMS), "Seat" (assigns table), "Edit", "Remove" (with reason: Cancelled / Left / Duplicate).
- Filters: All / Waiting / Notified / Seated Today / No-Show.
- Auto-refresh: Estimated wait recalculates every 30 seconds based on reservation/seating changes.

**Reservation Settings (/reservations/settings)**
- Sections: Operating Hours (per day-of-week), Slot Duration (default 15 min, configurable), SMS Templates (Confirmation / Reminder / Cancellation / Table Ready / No-Show with merge fields), Cancellation Policy (free cancel window, no-show fee), Reminder Schedule (4h + 1h before default, customizable), Waitlist Settings (auto-release timer default 10 min), Pre-Order Settings (allow/disallow, cutoff time), Deposit Settings (optional deposit for large parties).
- Buttons: "Save", "Test SMS", "Reset to Defaults", "Preview Templates".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/reservations | List reservations (filter by date, status, source) |
| POST | /api/v1/reservations | Create new reservation (host or public widget) |
| GET | /api/v1/reservations/{id} | Get reservation detail |
| PATCH | /api/v1/reservations/{id} | Update reservation (time, party size, special requests) |
| DELETE | /api/v1/reservations/{id} | Cancel reservation (with reason) |
| POST | /api/v1/reservations/{id}/confirm | Re-send confirmation SMS |
| POST | /api/v1/reservations/{id}/remind | Send reminder SMS now |
| POST | /api/v1/reservations/{id}/seat | Mark as seated, assign table, trigger order creation |
| POST | /api/v1/reservations/{id}/complete | Mark as completed |
| POST | /api/v1/reservations/{id}/no-show | Flag as no-show |
| GET | /api/v1/reservations/availability | Query available slots for date and party size |
| GET | /api/v1/reservations/floor-availability | Floor plan with time-slot heatmap data |
| GET | /api/v1/reservations/settings | Get outlet reservation settings |
| PUT | /api/v1/reservations/settings | Update outlet settings (slot, templates, cancellation) |
| GET | /api/v1/waitlist | List waitlist queue (active today) |
| POST | /api/v1/waitlist | Add walk-in to waitlist |
| PATCH | /api/v1/waitlist/{id} | Update waitlist entry (party size, quoted wait) |
| DELETE | /api/v1/waitlist/{id} | Remove from waitlist (cancelled / left) |
| POST | /api/v1/waitlist/{id}/notify | Send Table Ready SMS |
| POST | /api/v1/waitlist/{id}/seat | Assign table and mark seated |
| GET | /api/v1/waitlist/estimated-waits | Recompute estimated waits (cron + on-demand) |
| POST | /api/v1/reservations/public-booking | Public widget endpoint (no auth, rate-limited) |

## Database Tables

**reservations**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets, indexed)
- customer_id (foreignId, customers, nullable) — link to RMS-024 if returning
- guest_name (string)
- guest_phone (string, indexed)
- guest_email (string, nullable)
- party_size (unsignedSmallInteger)
- slot_datetime (timestamp, indexed)
- duration_minutes (unsignedSmallInteger, default 90)
- table_id (foreignId, restaurant_tables, nullable) — assigned table
- seating_preference (enum: any, indoor, outdoor, ac, window, default any)
- special_requests_json (json) — array of structured tags
- notes (text, nullable)
- pre_order_json (json, nullable) — pre-ordered items
- source (enum: phone, widget, whatsapp, admin, default phone)
- status (enum: draft, confirmed, reminded, seated, completed, cancelled, no_show, default confirmed, indexed)
- confirmation_sms_sent_at (timestamp, nullable)
- reminder_sms_sent_at (timestamp, nullable)
- reminder_1h_sent_at (timestamp, nullable)
- seated_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- cancelled_at (timestamp, nullable)
- cancelled_reason (string, nullable)
- no_show_at (timestamp, nullable)
- created_by_user_id (foreignId, users, nullable) — host who entered it
- timestamps
- indexes([outlet_id, slot_datetime]), ([status, slot_datetime])

**waitlist_entries**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- customer_id (foreignId, customers, nullable)
- guest_name (string)
- guest_phone (string)
- party_size (unsignedSmallInteger)
- quoted_wait_minutes (unsignedSmallInteger) — initial estimate given to guest
- quoted_at (timestamp, indexed)
- current_estimated_wait_minutes (unsignedSmallInteger, nullable) — recomputed
- notified_at (timestamp, nullable) — Table Ready SMS sent
- notification_expires_at (timestamp, nullable) — auto-release timer
- seated_table_id (foreignId, restaurant_tables, nullable)
- seated_at (timestamp, nullable)
- status (enum: waiting, notified, seated, no_show, left, cancelled, default waiting, indexed)
- remove_reason (string, nullable)
- created_by_user_id (foreignId, users)
- timestamps
- indexes([outlet_id, status, quoted_at])

**reservation_settings**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets, unique)
- operating_hours_json (json) — per-day open/close
- slot_duration_minutes (unsignedSmallInteger, default 15)
- default_reservation_duration_minutes (unsignedSmallInteger, default 90)
- confirmation_sms_template (text)
- reminder_4h_template (text, nullable)
- reminder_1h_template (text)
- cancellation_sms_template (text)
- table_ready_sms_template (text)
- no_show_sms_template (text, nullable)
- free_cancel_minutes_before (unsignedSmallInteger, default 120)
- no_show_fee_enabled (boolean, default false)
- no_show_fee_amount (decimal(10,2), nullable)
- reminder_offsets_json (json) — e.g., [240, 60] minutes
- allow_pre_order (boolean, default true)
- pre_order_cutoff_minutes (unsignedSmallInteger, default 60)
- require_deposit_above_party_size (unsignedSmallInteger, nullable) — e.g., 8
- deposit_amount (decimal(10,2), nullable)
- waitlist_notification_window_minutes (unsignedSmallInteger, default 10) — auto-release timer
- timestamps

**reservation_audit_trail**
- id (bigIncrements, PK)
- reservation_id (foreignId, reservations, indexed)
- user_id (foreignId, users, nullable) — null for system events (SMS sent)
- action (enum: created, edited, confirmed_sms_sent, reminder_sent, seated, completed, cancelled, no_show, public_booked, table_reassigned)
- metadata_json (json, nullable) — old/new values, SMS gateway response, etc.
- occurred_at (timestamp)
- timestamps
- index([reservation_id, occurred_at])

## Technical Notes

**Laravel Backend:**
- Controllers: `ReservationController` (CRUD, seat, complete, no-show), `ReservationAvailabilityController` (slot query, floor heatmap), `ReservationSettingsController` (per-outlet config), `WaitlistController` (CRUD, notify, seat), `PublicBookingController` (widget, no-auth with rate limit).
- Services: `ReservationService` (create/update with auto-table assignment via `TableAssignmentService`), `SlotAvailabilityService` (queries tables vs existing reservations, suggests best slot), `TableAssignmentService` (greedy best-fit by party size + seating preference), `ReservationSmsService` (MSG91 dispatch with template merge fields and DLT template registration for India), `WaitlistService` (queue ordering, estimated wait recalculation, auto-release), `NoShowDetectionService` (cron job flags no-show risk 15 min after slot).
- Events: `ReservationCreated`, `ReservationConfirmed` (after SMS), `ReservationReminderDue`, `ReservationSeated` (also triggers RMS-037 table assignment), `ReservationCancelled`, `ReservationNoShow`, `WaitlistEntryAdded`, `WaitlistEntryNotified` (Table Ready SMS), `WaitlistEntrySeated`, `WaitlistAutoReleased`. Broadcast on `outlet.{id}.reservations` and `outlet.{id}.waitlist` private channels.
- Jobs: `SendReservationConfirmationSmsJob`, `SendReservationReminderJob` (queued at reminder_offsets_json times), `SendTableReadySmsJob`, `RecomputeWaitlistEstimatesJob` (runs every 5 min), `DetectNoShowJob` (runs every 1 min, flags overdue slots), `AutoReleaseWaitlistJob` (runs every 1 min, releases notified entries past notification_window).
- SMS Gateway: MSG91 with DLT template IDs (Indian regulatory requirement for transactional SMS); template merge fields: {{guest_name}}, {{restaurant_name}}, {{slot_time}}, {{party_size}}, {{table_number}}.
- Cron / Scheduler: 
  - Every minute: `DetectNoShowJob`, `AutoReleaseWaitlistJob`
  - Every 5 minutes: `RecomputeWaitlistEstimatesJob`
  - At reservation creation: schedule `SendReservationReminderJob` at configured offsets
- Public widget endpoint: Rate-limited (10 req/min per IP), CAPTCHA-aware, returns reservation ID + confirmation code (SMS to guest).
- Customer recognition: On phone entry, `CustomerResolutionService::resolveByPhone()` from RMS-024 returns existing customer or creates new — reservation links via `customer_id`.
- Multi-outlet: Every query scoped by `outlet_id`; settings per outlet allow different hours, slot duration, SMS templates per location.

**React Frontend:**
- Components: `ReservationCalendar` (day/week/month views), `ReservationBlock` (draggable), `ReservationDetailPage`, `NewReservationWizard` (4-step), `FloorAvailabilityHeatmap`, `TimeSlider`, `WaitlistQueue`, `WaitlistEntryRow`, `WaitlistAddForm`, `ReservationSettingsPanel`, `SmsTemplateEditor`, `CustomerRecognitionBadge`.
- State: Zustand `useReservationStore` (filters, selected date, calendar data), `useWaitlistStore` (queue, recompute trigger), `useReservationWebSocket` (Reverb subscription).
- Calendar: `react-big-calendar` or custom build; drag-and-drop via `react-dnd` for table/time reassignment.
- WebSocket: Reverb private channel `outlet.{id}.reservations` for live calendar updates; `outlet.{id}.waitlist` for queue updates.
- Customer recognition: Debounced phone lookup on `NewReservation` step 1; shows "Welcome back — N visits" inline.
- Phone formatting: Auto-format Indian mobile (10-digit with +91 prefix); validates with regex.
- Time handling: Store UTC in DB, display in outlet timezone (Indian outlets typically IST); uses Luxon for timezone math.
- SMS template editor: WYSIWYG with merge field chips ({{guest_name}}, {{slot_time}}); preview with sample data.

## Subtasks
1. [ ] Create migrations for `reservations`, `waitlist_entries`, `reservation_settings`, `reservation_audit_trail`
2. [ ] Implement `SlotAvailabilityService` querying tables × existing reservations
3. [ ] Implement `TableAssignmentService` with seating preference and party-size best-fit
4. [ ] Build `ReservationController` CRUD with lifecycle endpoints (seat, complete, no-show)
5. [ ] Implement MSG91 SMS integration with DLT template registration
6. [ ] Build `ReservationSmsService` with template merge field rendering
7. [ ] Implement reminder scheduling via Laravel Scheduler at `reminder_offsets_json` times
8. [ ] Implement `NoShowDetectionJob` cron (runs every 1 min, flags 15-min-overdue slots)
9. [ ] Build `WaitlistController` (CRUD, notify, seat, auto-release)
10. [ ] Implement `RecomputeWaitlistEstimatesJob` recalculating dynamic waits
11. [ ] Build `AutoReleaseWaitlistJob` releasing tables 10 min after notification
12. [ ] Build `ReservationSettingsController` (slot, templates, cancellation, deposit config)
13. [ ] Build public booking widget endpoint with rate limiting and CAPTCHA
14. [ ] Build React `ReservationCalendar` with day/week/month views and drag-and-drop
15. [ ] Build React `NewReservationWizard` (4-step: Guest → Slot → Table → Confirm)
16. [ ] Build React `FloorAvailabilityHeatmap` with time-slider overlay
17. [ ] Build React `WaitlistQueue` with live estimated-wait recalculation
18. [ ] Build React `ReservationSettingsPanel` with SMS template editor
19. [ ] Integrate customer recognition (RMS-024) in `NewReservation` step 1
20. [ ] Write unit + integration tests for slot math, table assignment, SMS templates, no-show cron, waitlist auto-release

## Testing Criteria
- [ ] Creating a reservation for 4 guests at 19:00 with 4 tables available returns a confirmed booking + SMS sent
- [ ] Attempting to book when no table available for party size returns 409 with closest available slots
- [ ] Returning guest phone auto-populates name, email, and links to customer record
- [ ] Reminder SMS fires at the configured offset (test with offset of 1 minute)
- [ ] No-show flag triggers 15 minutes after slot start if not marked seated
- [ ] Cancelling within free-cancel window sends cancellation SMS; outside window sends different template
- [ ] Waitlist queue orders correctly when entries added out of order
- [ ] Estimated wait recalculates within 5 minutes of a table freeing up
- [ ] Table Ready SMS sends when host clicks Notify; auto-releases 10 min later if not seated
- [ ] Floor availability heatmap correctly reflects reservations across multiple sections
- [ ] Public widget endpoint rate-limits at 10 req/min per IP and rejects bad phone format
- [ ] All reservation lifecycle events recorded in `reservation_audit_trail` with correct actor (user or system)