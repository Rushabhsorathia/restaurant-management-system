# RMS-036: Birthday & Anniversary Automation

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-036 |
| **Type** | Story |
| **Epic** | CRM & Customer Experience |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 (Customer Database), RMS-025 (Loyalty), RMS-032 (Coupons), RMS-033 (Campaign Engine) |

## User Story
As a restaurant manager, I want automatic birthday and anniversary reminders that generate personalised coupons, award bonus loyalty points and respect customer opt-out preferences, so that I can delight customers on their special day without manual effort and drive a measurable lift in occasion visits.

## Description
This story automates the highest-impact CRM moment: the customer's personal occasion. Birthdays and anniversaries generate ~3x the average ticket size when handled well, but require timely, personalised outreach at scale. The module runs a nightly job that scans the customer master (RMS-024) for upcoming occasions, generates a unique coupon for each customer via RMS-032, awards bonus loyalty points via RMS-025, and schedules a personalised message via the campaign engine (RMS-033).

The default reminder cadence is T-3 days (a pre-occasion teaser with the coupon) and T-0 (the day-of message). Restaurants can configure additional triggers (T-7, T-1, post-occasion thank-you) and choose between SMS, WhatsApp, email or all three per occasion type. Messages use merge tags for first name, occasion type, the auto-generated coupon code and the points bonus -- so each customer feels personally invited rather than mass-messaged.

A calendar view helps managers visualise upcoming occasions across the next 30/60/90 days, plan staffing for expected spikes, and confirm that reminder jobs executed successfully. Each generated coupon is single-use, valid for the occasion week, and excluded from stacking unless explicitly enabled. The points bonus (default 2x the customer's tier multiplier) is credited automatically upon the customer's next visit within the validity window, encouraging redemption.

Customer opt-out preferences (per occasion type and per channel) are respected: a customer who opted out of birthday emails but kept SMS still receives SMS. The module also enforces quiet hours per customer timezone, just like the campaign engine.

## Acceptance Criteria
- [ ] Capture date_of_birth and anniversary_date on customer profile (already in RMS-024)
- [ ] Nightly job identifies customers with occasions in T-3 days (configurable: T-7, T-3, T-1, T-0)
- [ ] Auto-generate unique coupon per customer via RMS-032 with occasion-specific template
- [ ] Auto-award bonus loyalty points via RMS-025 upon next visit within validity window
- [ ] Schedule personalised SMS via MSG91 / WhatsApp via Gupshup / email via SES (RMS-033)
- [ ] Merge tags in template: first name, occasion label, coupon code, points bonus, valid till
- [ ] Customer opt-out preference respected per occasion type (birthday vs anniversary) and per channel
- [ ] Calendar view of upcoming occasions (30/60/90 days) with filtering by outlet and occasion type
- [ ] Reminder execution log shows scheduled, sent, delivered, failed per customer
- [ ] Manager can preview a sample message before save
- [ ] Coupon validity window defaults to occasion day + 7 days post
- [ ] Points bonus auto-credits on next qualifying visit and is visible in loyalty ledger
- [ ] Configurable reminder schedule per restaurant with multi-trigger support

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Occasion Calendar | /crm/occasions/calendar | Calendar view of upcoming occasions |
| Automation Settings | /crm/occasions/settings | Reminder schedule, templates, opt-out defaults |
| Reminder Log | /crm/occasions/log | Execution history with success/failure |
| Customer Occasion Detail | /crm/customers/{id}/occasions | Per-customer occasion history and preferences |
| Upcoming Occasions | /crm/occasions/upcoming | List view alternative to calendar |

### Screen Details

**Occasion Calendar (/crm/occasions/calendar)**
- Layout: Month view with day cells showing occasion counts by type; right sidebar with selected day's details.
- Filters: Outlet, Occasion Type (Birthday/Anniversary), Date Range, Tier.
- Day cell: Birthday count (cake icon) and Anniversary count (heart icon); click expands.
- Sidebar (selected day): List of customers with occasion, age/year, tier, last visit, scheduled reminder status, "Preview Message" button.
- Buttons: "Settings", "Export", "Refresh".
- View toggles: Month / Week / Day / List.

**Automation Settings (/crm/occasions/settings)**
- Tabs: Birthday, Anniversary, Templates, Points, Opt-out Defaults.
- Reminder Schedule tab:
  - Trigger checkboxes for T-7, T-3, T-1, T-0 (post T+1 thank-you optional).
  - For each trigger: channel (SMS/WhatsApp/Email), send time, quiet hours override.
- Templates tab:
  - Channel-specific template editor with merge tag picker: `{{name}}`, `{{occasion_type}}`, `{{age_or_year}}`, `{{coupon_code}}`, `{{valid_till}}`, `{{points_bonus}}`, `{{restaurant_name}}`.
  - Live preview pane with sample customer.
  - Variables for each occasion type.
- Points tab:
  - Bonus points value (default 2x tier multiplier).
  - Expiry window (default 30 days).
  - Credit-on-visit toggle (manual vs auto on next visit).
- Opt-out Defaults tab:
  - Default opt-in by occasion type.
  - Default opt-in by channel.
- Save button: "Save Settings".

**Reminder Log (/crm/occasions/log)**
- Layout: Filter bar (date range, occasion type, status, customer, channel). Data table.
- Table columns: Scheduled At | Customer | Occasion | Trigger (T-3, T-0) | Channel | Status | Coupon Code | Points | Actions.
- Status: Scheduled, Sent, Delivered, Failed, Skipped (opted-out).
- Row actions: View Message, Resend, Cancel.
- Buttons: "Export Log".

**Customer Occasion Detail (/crm/customers/{id}/occasions)**
- Header: Customer name, birthday, anniversary date, age (turning N).
- Tabs: Upcoming, Past, Preferences, Points Ledger.
- Preferences tab: Toggles for Birthday SMS/WhatsApp/Email opt-in, same for Anniversary, do-not-disturb date range (e.g. "skip until 15 Jan" for travel).
- Past tab: List of past occasions with redemption and coupon use.
- Buttons: "Test Send", "Reset Preferences".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/occasions/upcoming | List upcoming occasions in date range |
| GET | /api/v1/occasions/calendar | Calendar-payload with counts per day |
| GET | /api/v1/occasions/settings | Get automation settings |
| PUT | /api/v1/occasions/settings | Update automation settings |
| GET | /api/v1/occasions/log | Get reminder execution log |
| POST | /api/v1/occasions/log/{id}/resend | Resend failed reminder |
| POST | /api/v1/occasions/preview | Preview rendered message for sample customer |
| GET | /api/v1/customers/{id}/occasions | Get customer's occasion history and preferences |
| PUT | /api/v1/customers/{id}/occasions/preferences | Update customer opt-in/opt-out preferences |
| POST | /api/v1/customers/{id}/occasions/test-send | Send test reminder to manager phone |
| GET | /api/v1/occasions/templates | List per-occasion templates |
| PUT | /api/v1/occasions/templates | Update templates |

## Database Tables

**occasion_automation_settings**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- occasion_type (enum: birthday, anniversary)
- triggers_json (json) -- array of {offset_days, channels[], send_time}
- default_coupon_id (foreignId, coupons, nullable) -- template source
- points_bonus (unsignedInteger, default 100)
- points_expiry_days (unsignedSmallInteger, default 30)
- coupon_validity_days (unsignedSmallInteger, default 7) -- post occasion
- opt_in_default_json (json) -- default opt-in by channel
- quiet_hours_start (time, default 09:00)
- quiet_hours_end (time, default 21:00)
- is_active (boolean, default true)
- timestamps
- unique([restaurant_id, occasion_type])

**occasion_reminder_log**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- customer_id (foreignId, customers)
- occasion_type (enum: birthday, anniversary)
- occasion_date (date) -- the actual occasion date
- trigger_offset_days (signedSmallInteger) -- e.g. -3, 0
- scheduled_for (timestamp)
- sent_at (timestamp, nullable)
- channel (enum: sms, whatsapp, email)
- coupon_id (foreignId, coupons, nullable)
- coupon_code (string, nullable)
- points_bonus (unsignedInteger, nullable)
- status (enum: scheduled, sent, delivered, failed, skipped)
- skip_reason (string, nullable)
- failure_reason (string, nullable)
- provider_message_id (string, nullable)
- timestamps
- indexes: [scheduled_for, status], [customer_id, occasion_type]

**customer_occasion_preferences**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- birthday_sms (boolean, default true)
- birthday_whatsapp (boolean, default true)
- birthday_email (boolean, default true)
- anniversary_sms (boolean, default true)
- anniversary_whatsapp (boolean, default true)
- anniversary_email (boolean, default true)
- do_not_disturb_until (date, nullable)
- updated_at
- timestamps
- unique([customer_id])

**occasion_points_ledger**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- reminder_log_id (foreignId, occasion_reminder_log, nullable)
- occasion_type (enum: birthday, anniversary)
- occasion_date (date)
- points_awarded (unsignedInteger)
- awarded_at (timestamp, nullable)
- redeemed_at (timestamp, nullable)
- redeemed_order_id (foreignId, orders, nullable)
- expires_at (timestamp, nullable)
- status (enum: pending, awarded, redeemed, expired)
- timestamps
- indexes: [customer_id, status], [expires_at]

## Technical Notes

**Laravel Backend:**
- Controllers: `OccasionCalendarController`, `OccasionSettingsController`, `OccasionLogController`, `CustomerOccasionController`.
- Services: `OccasionScheduler` (nightly job that finds matching customers), `OccasionCouponIssuer` (uses CouponEngine to issue per-customer code via RMS-032), `OccasionMessageComposer` (renders template + merge tags + sends via RMS-033), `OccasionPointsCreditor` (awards on visit within window).
- Jobs: `ScheduleOccasionRemindersJob` (nightly, runs at 02:00 server time, processes occasions in next 14 days), `SendOccasionReminderJob` (per reminder, scheduled at exact send time), `CreditOccasionPointsJob` (triggered on order completion if within occasion window).
- Trigger calculation: occasion_date - offset_days = trigger date. For T-3 on a birthday on 15 Oct, trigger fires on 12 Oct at configured send_time.
- Opt-out filtering: load `customer_occasion_preferences` and skip channels where customer opted out, or where do_not_disturb_until > today.
- Coupon issuance: calls `CouponEngine::issueOccasionCoupon($customer, $template)` which clones a configured template, sets per-customer limit = 1, validity window = occasion_date ± N days, and returns the unique code.
- Points credit: on order completion, check for any pending `occasion_points_ledger` rows within valid window; mark `awarded` and credit through RMS-025 loyalty engine.
- Timezone: customer timezone stored on profile (RMS-024) determines quiet hours and trigger date calculation.

**React Frontend:**
- Components: `OccasionCalendarPage`, `MonthCalendar`, `DayDetailPanel`, `AutomationSettingsPage`, `ReminderScheduleEditor`, `TemplateEditor`, `PointsConfigPanel`, `ReminderLogPage`, `CustomerOccasionTab`, `OccasionPreferencesForm`, `UpcomingOccasionsList`.
- State: Zustand `useOccasionStore` for settings draft and calendar data cache.
- Hook: `useOccasionPreview(template, customer)` renders sample message.
- Calendar: FullCalendar or custom grid component with day cells and occasion counts; mobile-friendly list fallback.
- Template editor: same channel-aware pattern as RMS-033 template editor.

## Subtasks
1. [ ] Create `occasion_automation_settings`, `occasion_reminder_log`, `customer_occasion_preferences`, `occasion_points_ledger` migrations and models
2. [ ] Implement `OccasionScheduler` service to find upcoming occasions per trigger offset
3. [ ] Build `OccasionCouponIssuer` integrating with RMS-032 coupon engine
4. [ ] Implement `OccasionMessageComposer` integrating with RMS-033 campaign engine
5. [ ] Build `SendOccasionReminderJob` with scheduled dispatch and quiet hours
6. [ ] Implement opt-out filtering using `customer_occasion_preferences`
7. [ ] Build `OccasionPointsCreditor` triggered on order completion
8. [ ] Implement customer do-not-disturb date range handling
9. [ ] Build settings controller with per-occasion-type configuration
10. [ ] Build calendar API with day-count aggregations
11. [ ] Build reminder log with resend capability
12. [ ] Build React OccasionCalendarPage with month view and day detail panel
13. [ ] Build React AutomationSettingsPage with schedule editor and template editor
14. [ ] Build React ReminderLogPage with filters and resend action
15. [ ] Build React customer occasion preferences UI on customer profile
16. [ ] Schedule nightly `ScheduleOccasionRemindersJob` in console kernel
17. [ ] Write tests for scheduling, coupon issuance, opt-out, points credit, retry

## Testing Criteria
- [ ] Customer with birthday in 3 days receives a T-3 reminder with personalised coupon
- [ ] Customer with anniversary receives anniversary-specific template (not birthday template)
- [ ] Auto-generated coupon is single-use, per-customer, with correct validity window
- [ ] Points bonus credits to customer ledger on next visit within validity
- [ ] Opted-out customer for birthday SMS does not receive SMS but may receive WhatsApp
- [ ] do_not_disturb_until date suppresses all reminders until that date
- [ ] Calendar view shows correct count per day for the next 30 days
- [ ] Reminder log records every scheduled send with status transitions
- [ ] Failed reminder can be resent and reflects updated status
- [ ] Settings change takes effect from next scheduler run, not retroactively
- [ ] Points expiry clears pending ledger entries past expires_at