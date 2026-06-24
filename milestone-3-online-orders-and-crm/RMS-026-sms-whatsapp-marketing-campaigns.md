# RMS-026: SMS & WhatsApp Marketing Campaigns

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-026 |
| **Type** | Story |
| **Epic** | CRM |
| **Milestone** | M3 - Online Orders & CRM |
| **Priority** | Medium |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 |

## User Story
As a marketing manager, I want to send targeted SMS and WhatsApp campaigns to customer segments, so that I can drive repeat visits and increase revenue.

## Description
The campaign builder lets restaurants create, schedule, and track marketing campaigns via SMS and WhatsApp Business API. Campaigns target customer segments (by visit frequency, spend, preferences, tags). Templates support personalization (name, last visit, loyalty points). Delivery reports track sent/delivered/read/failed. Opt-out compliance ensures customers can unsubscribe.

## Acceptance Criteria
- [ ] Campaign builder: name, channel (SMS/WhatsApp), audience segment, template, schedule
- [ ] Audience segmentation: by labels, visit count, avg spend, last visit date, loyalty tier
- [ ] Template editor with merge fields: {name}, {phone}, {points}, {last_visit}, {restaurant_name}
- [ ] SMS via gateway (Twilio, MSG91, Textlocal - configurable)
- [ ] WhatsApp Business API (via provider: Gupshup, WATI, Interakt)
- [ ] Schedule: immediate, date/time, recurring
- [ ] Delivery reports: sent, delivered, read (WhatsApp), failed with reason
- [ ] Opt-out management: auto-unsubscribe on "STOP" reply, exclude opted-out customers
- [ ] Campaign cost tracking: per-message cost, total campaign cost
- [ ] Campaign performance: response rate (for WhatsApp), redemption tracking (promo codes)
- [ ] A/B testing: 2 template variants, auto-pick winner after X sends

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Campaign List | /crm/campaigns | All campaigns with status |
| Campaign Builder | /crm/campaigns/create | Create campaign step-by-step |
| Campaign Detail | /crm/campaigns/:id | Delivery report + analytics |
| Templates | /crm/campaigns/templates | SMS/WhatsApp templates library |
| Audience Segments | /crm/campaigns/segments | Saved segments |

### Screen Details

**Campaign Builder (/crm/campaigns/create)**
- Step 1 - Channel: SMS / WhatsApp (radio)
- Step 2 - Audience:
  - Select segment (saved) OR build dynamic segment
  - Filters: labels (multi), tier, min visits, last visit within X days, min spend
  - Estimated reach: "This segment has 1,250 customers"
  - Exclude opted-out (auto)
- Step 3 - Message:
  - Template selector (saved templates) OR custom
  - Editor with merge field buttons: [Name] [Points] [Last Visit] [Restaurant]
  - Character count (SMS: 160 per segment, WhatsApp: 4096)
  - Preview panel: shows message as customer would see it
  - Promo code field (optional): auto-generate or select existing
- Step 4 - Schedule:
  - Send Now / Schedule for date-time / Recurring
  - Throttle: messages per minute (avoid gateway rate limit)
- Step 5 - Review & Launch
  - Total recipients, estimated cost, send button

**Campaign Detail (/crm/campaigns/:id)**
- Status: Draft / Scheduled / Sending / Completed / Paused / Failed
- Stats: Sent | Delivered | Read | Failed | Response Rate
- Pie chart: delivery breakdown
- Recipient table: Customer | Phone | Status | Sent Time | Delivered Time | Error (if failed)
- Pause/Resume buttons (if sending)
- Duplicate campaign button

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/campaigns | Create campaign |
| GET | /api/v1/campaigns | List campaigns |
| GET | /api/v1/campaigns/{id} | Campaign detail + report |
| PATCH | /api/v1/campaigns/{id}/launch | Launch/send campaign |
| PATCH | /api/v1/campaigns/{id}/pause | Pause campaign |
| PATCH | /api/v1/campaigns/{id}/resume | Resume campaign |
| POST | /api/v1/campaigns/segments | Create audience segment |
| GET | /api/v1/campaigns/segments | List segments |
| GET | /api/v1/campaigns/templates | List templates |
| POST | /api/v1/campaigns/templates | Create template |
| POST | /api/v1/campaigns/{id}/webhook | Delivery status webhook from gateway |

## Database Tables

```
campaigns:
  id (bigint, PK)
  outlet_id (bigint, FK)
  name (varchar 200)
  channel (enum: sms, whatsapp)
  segment_id (bigint, FK -> campaign_segments, nullable)
  template_id (bigint, FK -> campaign_templates, nullable)
  message_body (text)
  promo_code (varchar 50, nullable)
  status (enum: draft, scheduled, sending, completed, paused, failed)
  scheduled_at (timestamp, nullable)
  started_at (timestamp, nullable)
  completed_at (timestamp, nullable)
  total_recipients (int, default 0)
  total_sent (int, default 0)
  total_delivered (int, default 0)
  total_read (int, default 0)
  total_failed (int, default 0)
  total_cost (decimal 10,2, default 0)
  throttle_per_minute (int, default 50)
  created_by (bigint, FK -> users)
  timestamps

campaign_segments:
  id (bigint, PK)
  outlet_id (bigint, FK)
  name (varchar 200)
  filters (json) -- {"labels":["vip"],"min_visits":5,"min_spend":1000}
  is_dynamic (boolean, default true)
  customer_count (int, default 0)
  last_calculated_at (timestamp, nullable)
  timestamps

campaign_templates:
  id (bigint, PK)
  outlet_id (bigint, FK)
  name (varchar 200)
  channel (enum: sms, whatsapp)
  body (text)
  merge_fields (json)
  timestamps

campaign_recipients:
  id (bigint, PK)
  campaign_id (bigint, FK -> campaigns)
  customer_id (bigint, FK -> customers)
  phone (varchar 20)
  message_body (text) -- personalized
  status (enum: pending, sent, delivered, read, failed)
  gateway_message_id (varchar 100, nullable)
  sent_at (timestamp, nullable)
  delivered_at (timestamp, nullable)
  read_at (timestamp, nullable)
  error_message (text, nullable)
  cost (decimal 6,2, default 0)
  timestamps

campaign_optouts:
  id (bigint, PK)
  customer_id (bigint, FK -> customers)
  channel (enum: sms, whatsapp, all)
  opted_out_at (timestamp)
  timestamps
```

## Technical Notes
- **Backend**: `CampaignController.php` + `CampaignService.php`. SMS Gateway: configurable via .env (MSG91 or Textlocal). WhatsApp: Gupshup API. Use Laravel queues (`campaign-sending` queue) with throttle: dispatch messages with `release()` for rate limiting.
- **Opt-out**: Inbound SMS/WhatsApp "STOP" -> webhook -> add to optouts table -> exclude from future campaigns.
- **Merge Fields**: Replace {name}, {points} etc. before sending. Store personalized body per recipient.

## Subtasks
1. [ ] Create campaigns, campaign_segments, campaign_templates, campaign_recipients, campaign_optouts migrations
2. [ ] Build Campaign model and service
3. [ ] Integrate SMS gateway (MSG91/Textlocal)
4. [ ] Integrate WhatsApp Business API (Gupshup)
5. [ ] Build segment engine (dynamic query from filters JSON)
6. [ ] Implement campaign sending via queues with throttle
7. [ ] Implement delivery webhook endpoints
8. [ ] Implement opt-out management
9. [ ] Build React campaign builder (multi-step)
10. [ ] Build template editor with merge fields
11. [ ] Build segment builder UI
12. [ ] Build campaign detail with delivery report
13. [ ] Write tests

## Testing Criteria
- [ ] Create SMS campaign targeting "VIP" label (250 customers) -> send -> all marked sent
- [ ] Merge field {name} replaced with actual customer name in each message
- [ ] Opted-out customer excluded from campaign
- [ ] Failed delivery -> status failed with error message
- [ ] WhatsApp campaign -> delivery + read status tracked
- [ ] Throttle: 50 msg/min -> 100 recipients takes 2 minutes
