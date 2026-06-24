# RMS-030: Birthday & Anniversary Campaigns

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-030 |
| **Type** | Story |
| **Epic** | CRM |
| **Milestone** | M3 - Online Orders & CRM |
| **Priority** | Low |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024, RMS-026 |

## User Story
As a restaurant owner, I want to automatically send personalized offers to customers on their birthdays and anniversaries, so that they feel valued and choose my restaurant for their celebration.

## Description
This module auto-detects upcoming birthdays and anniversaries from the customer database and sends pre-configured offers via SMS/WhatsApp. Managers define the offer (discount, free dessert, combo), the message template, and the send timing (X days before). Campaign performance tracks how many celebrants visited and redeemed the offer.

## Acceptance Criteria
- [ ] Auto-scan customer database daily for upcoming birthdays/anniversaries
- [ ] Configurable offer: flat discount %, free item, special combo, gift voucher
- [ ] Offer template with merge fields: {name}, {age}, {restaurant}, {offer_details}, {validity}
- [ ] Send timing: X days before birthday (configurable, default: 1 day before)
- [ ] Auto-attach loyalty bonus points as birthday gift
- [ ] Unique promo code per recipient for tracking redemption
- [ ] Campaign report: sent count, redemption count, revenue from redeemed visits
- [ ] Monthly calendar view of upcoming birthdays/anniversaries

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Birthday Calendar | /crm/birthdays/calendar | Monthly view of celebrants |
| Campaign Settings | /crm/birthdays/settings | Offer config + template |
| Birthday Campaign Report | /crm/birthdays/report | Performance analytics |

### Screen Details

**Birthday Calendar (/crm/birthdays/calendar)**
- Month grid: each day shows count of birthdays/anniversaries
- Click a day -> list: Name | Phone | Type (Birthday/Anniversary) | Age/Years | Offer Status (Sent/Pending/Not Configured)
- "This Month" summary: X birthdays, Y anniversaries, Z already sent

**Campaign Settings (/crm/birthdays/settings)**
- Enable birthday campaign (toggle)
- Enable anniversary campaign (toggle)
- Offer type: Discount (%) / Free Item / Combo / Bonus Points
- Offer value: e.g., 20% off, Free Dessert, 500 bonus points
- Validity: valid for X days from birthday
- Send timing: 1 day before / 3 days before / on the day
- SMS template editor with merge fields
- WhatsApp template (if WhatsApp enabled)
- Auto promo code prefix: BDAY-XXXXX (auto-generated per customer)

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/birthdays/calendar | Monthly calendar data |
| GET | /api/v1/birthdays/upcoming | Upcoming birthdays (next X days) |
| GET | /api/v1/birthdays/settings | Campaign settings |
| PUT | /api/v1/birthdays/settings | Update settings |
| GET | /api/v1/birthdays/report | Performance report |
| POST | /api/v1/birthdays/send | Manually trigger sends |

## Database Tables

```
birthday_campaign_settings:
  id (bigint, PK)
  outlet_id (bigint, FK)
  campaign_type (enum: birthday, anniversary)
  is_enabled (boolean, default true)
  offer_type (enum: discount, free_item, combo, bonus_points)
  offer_value (varchar 200) -- "20" for 20%, "Dessert" for item, "500" for points
  validity_days (int, default 7)
  send_days_before (int, default 1)
  sms_template (text)
  whatsapp_template (text, nullable)
  promo_prefix (varchar 20, default 'BDAY')
  timestamps

birthday_campaign_logs:
  id (bigint, PK)
  outlet_id (bigint, FK)
  customer_id (bigint, FK -> customers)
  campaign_type (enum: birthday, anniversary)
  promo_code (varchar 50, unique)
  sent_at (timestamp)
  channel (enum: sms, whatsapp)
  status (enum: sent, delivered, failed)
  redeemed (boolean, default false)
  redeemed_at (timestamp, nullable)
  redeemed_bill_id (bigint, FK -> bills, nullable)
  timestamps
```

## Technical Notes
- **Backend**: `BirthdayCampaignService.php`. Scheduled job (daily at 9 AM): scan customers where birth_date MONTH/DAY = tomorrow -> send configured offer. Promo code: `{PREFIX}{RANDOM6}` stored in birthday_campaign_logs -> validated at billing.

## Subtasks
1. [ ] Create birthday_campaign_settings, birthday_campaign_logs migrations
2. [ ] Build BirthdayCampaignService
3. [ ] Implement daily scan and send job
4. [ ] Implement promo code generation and validation at billing
5. [ ] Build React calendar view
6. [ ] Build settings page
7. [ ] Build report page
8. [ ] Write tests

## Testing Criteria
- [ ] Customer birthday tomorrow -> scheduled job sends offer today
- [ ] Promo code unique per customer -> redeemable at billing
- [ ] Redemption tracked -> bill linked to campaign log
- [ ] Report: 50 sent, 12 redeemed -> 24% redemption rate
- [ ] Disabled campaign -> no sends
