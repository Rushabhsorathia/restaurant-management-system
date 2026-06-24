# RMS-033: SMS, WhatsApp & Email Campaign Engine

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-033 |
| **Type** | Story |
| **Epic** | CRM & Marketing Automation |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 (Customer Database), RMS-028 (Customer Labels & Segmentation), RMS-032 (Coupons) |

## User Story
As a marketing manager, I want a multi-channel campaign engine that builds customer segments, sends SMS via MSG91, WhatsApp via Gupshup and email via AWS SES with merge-tag templates, scheduled sends, opt-out compliance, A/B testing and delivery analytics, so that I can run data-driven retention campaigns across the Indian restaurant customer base.

## Description
This story delivers the full marketing automation layer that builds on the customer master (RMS-024) and segmentation engine (RMS-028). The campaign engine exposes a unified interface to compose, target, send and measure outbound communications on three channels: transactional SMS (via MSG91 for Indian DLT compliance), WhatsApp Business messages (via Gupshup using pre-approved templates), and email (via AWS SES with bounce/complaint handling).

The audience builder accepts saved segments, dynamic filters (last visit, spend, tier, tags, city) and CSV imports. The template editor supports merge tags (`{{name}}`, `{{points}}`, `{{last_visit}}`, `{{coupon_code}}`, `{{restaurant_name}}`) and a live preview pane. Templates are stored once and reused across campaigns with per-campaign overrides for sender ID, timing and coupon attachments.

Campaigns can be sent immediately, scheduled for a future date/time, or set on a recurring cadence (daily, weekly, monthly). The engine respects quiet hours (default 9 AM to 9 PM in customer's timezone), throttles per-minute send rate to stay within gateway limits, and automatically excludes opted-out customers (RMS-024 marketing_consent=false). A/B testing enables two creative variants with auto-pick of the winning variant based on a configurable metric (open rate, click rate, redemption rate).

Delivery tracking captures sent, delivered, read (WhatsApp), failed (with reason code), and unsubscribed events via provider webhooks. Analytics dashboards show funnel: send → deliver → open → click → coupon redemption → repeat visit attribution. Cost per campaign is computed from per-message gateway pricing, helping managers monitor campaign ROI.

## Acceptance Criteria
- [ ] Create campaign: name, channel (SMS/WhatsApp/Email), audience, template, schedule, sender ID
- [ ] Audience built from saved segment, dynamic filter, or CSV upload with deduplication
- [ ] Template editor with merge tags: `{{name}}`, `{{phone}}`, `{{points}}`, `{{last_visit}}`, `{{coupon_code}}`, `{{restaurant_name}}`
- [ ] Live preview pane shows sample render with real customer data (test mode)
- [ ] SMS dispatched via MSG91 with DLT template ID, sender ID and route selection
- [ ] WhatsApp dispatched via Gupshup using pre-approved template namespace and language
- [ ] Email dispatched via AWS SES with bounce and complaint handling via SNS
- [ ] Schedule options: immediate, future date/time, recurring (daily/weekly/monthly)
- [ ] Quiet hours respected per customer timezone; default 9 AM - 9 PM
- [ ] Opted-out customers automatically excluded; STOP keyword auto-unsubscribes
- [ ] A/B testing: two variants, split ratio, auto-pick winner after N sends based on metric
- [ ] Delivery tracking: sent, delivered, read (WhatsApp), failed with reason, unsubscribed
- [ ] Campaign analytics dashboard with funnel and cost breakdown
- [ ] Coupon attachment: select coupon from RMS-032 to include unique code per recipient
- [ ] Campaign history list with status (draft/scheduled/sending/completed/failed) and resend option

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Campaign List | /crm/campaigns | All campaigns with status and KPIs |
| Campaign Builder | /crm/campaigns/create | Step-by-step campaign wizard |
| Campaign Detail | /crm/campaigns/{id} | Delivery report and analytics |
| Template Library | /crm/campaigns/templates | Reusable templates per channel |
| A/B Test Setup | /crm/campaigns/{id}/ab-test | Configure variant split and winner metric |

### Screen Details

**Campaign List (/crm/campaigns)**
- Layout: Top KPI cards (total campaigns this month, messages sent, delivery rate, redemption count). Below, a data table.
- Filters: Channel, Status, Date Range, Created By, Audience Segment.
- Table columns: Name | Channel icon | Audience size | Sent | Delivered | Open/Read | Click | Redemptions | Cost | Status | Actions.
- Row actions: View, Duplicate, Pause, Cancel.
- Buttons: "New Campaign", "View Templates".
- Pagination: 25/50/100.

**Campaign Builder (/crm/campaigns/create)**
- Multi-step wizard: Audience → Template → Schedule → A/B Test → Review.
- Audience step: Choose source (Saved Segment, Dynamic Filter, CSV Upload). Show live audience size with breakdown by tier/channel. Exclusion list (e.g. recent purchasers, already contacted in last 7 days).
- Template step: Pick from Template Library or create inline. Channel-specific editor (SMS has 160 char limit counter, WhatsApp shows template namespace, Email has rich-text editor with subject preview). Live preview on right pane with sample customer.
- Schedule step: Send now / Schedule for date-time / Recurring (cron helper). Quiet hours toggle. Send rate throttle (e.g. 100/min).
- A/B Test step: Enable toggle, variant B template, split percentage, winner metric, auto-pick threshold.
- Coupon attachment: Dropdown to select coupon from RMS-032; engine generates unique code per recipient.
- Review step: Summary of all settings; "Save as Draft", "Schedule", "Send Test to Me".

**Campaign Detail (/crm/campaigns/{id})**
- Header: Campaign name, channel, status badge, schedule info, creator.
- KPI cards: Audience Size, Sent, Delivered, Read/Open, Clicked, Redemptions, Total Cost, Cost per Redemption.
- Funnel chart: Send → Deliver → Open → Click → Redeem → Repeat Visit.
- Tabs: Delivery Report, Audience, A/B Results, Cost Breakdown.
- Delivery Report tab: Table per recipient with Timestamp | Customer | Status | Reason (if failed) | Provider Message ID.
- A/B Results tab: Side-by-side variant performance with confidence indicator.
- Cost Breakdown tab: Gateway cost per recipient, total, comparison vs revenue from coupon redemptions.

**Template Library (/crm/campaigns/templates)**
- Layout: Channel filter tabs (All, SMS, WhatsApp, Email) and category filter.
- Table: Name | Channel | Category | Last Used | Status | Actions.
- Actions: Edit, Duplicate, Archive.
- "New Template" button opens template editor.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/campaigns | List campaigns with filters |
| POST | /api/v1/campaigns | Create new campaign |
| GET | /api/v1/campaigns/{id} | Get campaign detail |
| PUT | /api/v1/campaigns/{id} | Update draft campaign |
| DELETE | /api/v1/campaigns/{id} | Delete/cancel campaign |
| POST | /api/v1/campaigns/{id}/send | Trigger immediate send |
| POST | /api/v1/campaigns/{id}/pause | Pause in-flight sending |
| POST | /api/v1/campaigns/{id}/resume | Resume paused campaign |
| POST | /api/v1/campaigns/{id}/test | Send test to specified phone/email |
| GET | /api/v1/campaigns/{id}/report | Get delivery report and analytics |
| GET | /api/v1/campaigns/{id}/audience | List recipients and per-recipient status |
| POST | /api/v1/campaigns/audience/preview | Preview audience size from filter |
| GET | /api/v1/campaign-templates | List templates |
| POST | /api/v1/campaign-templates | Create template |
| POST | /api/v1/webhooks/msg91 | MSG91 delivery webhook |
| POST | /api/v1/webhooks/gupshup | Gupshup WhatsApp webhook |
| POST | /api/v1/webhooks/ses | AWS SES bounce/complaint webhook |

## Database Tables

**campaigns**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- name (string)
- channel (enum: sms, whatsapp, email)
- audience_type (enum: segment, filter, csv)
- audience_segment_id (foreignId, customer_segments, nullable)
- audience_filter_json (json, nullable)
- audience_csv_path (string, nullable)
- audience_size (unsignedInteger, default 0)
- template_id (foreignId, campaign_templates)
- template_overrides_json (json, nullable)
- coupon_id (foreignId, coupons, nullable)
- sender_id (string, nullable) -- SMS sender or email from
- schedule_type (enum: immediate, scheduled, recurring)
- scheduled_at (timestamp, nullable)
- recurring_pattern (string, nullable) -- cron expression
- quiet_hours_start (time, nullable)
- quiet_hours_end (time, nullable)
- send_rate_per_minute (unsignedSmallInteger, nullable)
- ab_test_enabled (boolean, default false)
- ab_variant_b_template_id (foreignId, campaign_templates, nullable)
- ab_split_percentage (unsignedTinyInteger, default 50)
- ab_winner_metric (enum: open_rate, click_rate, redemption_rate, nullable)
- ab_winner_threshold (unsignedSmallInteger, nullable)
- ab_winner_picked (enum: a, b, none, default none)
- status (enum: draft, scheduled, sending, paused, completed, failed, cancelled)
- created_by (foreignId, users)
- timestamps, softDeletes

**campaign_templates**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- name (string)
- channel (enum: sms, whatsapp, email)
- category (string, nullable) -- promotional, transactional, winback, birthday
- subject (string, nullable) -- for email
- body (text) -- supports merge tags
- whatsapp_template_namespace (string, nullable)
- whatsapp_template_name (string, nullable)
- whatsapp_language (string, default en)
- dlt_template_id (string, nullable) -- for SMS DLT
- variables_json (json, nullable) -- declared merge tags for validation
- is_approved (boolean, default false) -- for WhatsApp/DLT pre-approved
- status (enum: active, archived)
- timestamps, softDeletes

**campaign_messages**
- id (bigIncrements, PK)
- campaign_id (foreignId, campaigns)
- customer_id (foreignId, customers)
- recipient (string) -- phone or email
- variant (enum: a, b, nullable)
- merged_body (text) -- final rendered content
- coupon_code (string, nullable)
- provider_message_id (string, nullable)
- status (enum: queued, sent, delivered, read, failed, unsubscribed)
- failure_reason (string, nullable)
- sent_at (timestamp, nullable)
- delivered_at (timestamp, nullable)
- read_at (timestamp, nullable)
- clicked_at (timestamp, nullable)
- cost (decimal(8,4), default 0)
- timestamps
- indexes: [campaign_id, status], [customer_id]

**campaign_unsubscribes**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- channel (enum: sms, whatsapp, email, all)
- source (enum: stop_keyword, manual, complaint)
- reason (string, nullable)
- unsubscribed_at (timestamp)
- timestamps
- unique([customer_id, channel])

## Technical Notes

**Laravel Backend:**
- Services: `CampaignComposerService` (assemble audience + merge), `MessageDispatcher` (routes to MSG91/Gupshup/SES adapters), `AbTestEngine` (variant assignment + winner selection), `DeliveryTracker` (webhook processor).
- Adapters: `Msg91Adapter`, `GupshupAdapter`, `SesAdapter` implement `MessageGatewayInterface` with `send()`, `getStatus()`, `getCost()`.
- Jobs: `DispatchCampaignJob` (chunked dispatch), `ProcessDeliveryWebhookJob` (idempotent per provider_message_id), `CampaignAnalyticsRollupJob` (nightly).
- Queue: Use Redis-backed queues; chunk audience into batches of 500 for parallel sending; honor rate limiter per gateway.
- A/B winner: After `ab_winner_threshold` messages delivered, statistical comparison picks winner; remaining recipients get winning variant.
- Opt-out: STOP keyword from inbound SMS sets `campaign_unsubscribes` and flips `customers.marketing_consent=false` for that channel.
- Webhook signing: validate provider signatures (MSG91, Gupshup, SES SNS).
- Routes: `/api/v1/campaigns`, `/api/v1/campaign-templates`, webhooks under `/api/v1/webhooks/`.

**React Frontend:**
- Components: `CampaignListPage`, `CampaignBuilderWizard`, `AudienceStep`, `TemplateStep`, `ScheduleStep`, `AbTestStep`, `ReviewStep`, `LivePreviewPane`, `CampaignDetailPage`, `FunnelChart`, `DeliveryReportTable`, `TemplateLibrary`, `TemplateEditor` (channel-aware), `AbTestResultsPanel`.
- State: Zustand `useCampaignStore` for wizard draft.
- Hook: `useAudiencePreview(filter)` debounces server-side audience size estimate.
- Editor: TipTap for rich text in email; plain textarea with char counter for SMS; structured fields for WhatsApp.

## Subtasks
1. [ ] Create `campaigns`, `campaign_templates`, `campaign_messages`, `campaign_unsubscribes` migrations and models
2. [ ] Implement `MessageGatewayInterface` and adapters for MSG91, Gupshup, SES
3. [ ] Build audience composer service with segment/filter/CSV sources
4. [ ] Build template merge engine with tag substitution and validation
5. [ ] Build CampaignController CRUD and scheduling logic
6. [ ] Implement DispatchCampaignJob with chunked sending and rate limiting
7. [ ] Build webhook handlers for delivery events from each provider
8. [ ] Implement A/B testing engine with variant assignment and winner picking
9. [ ] Implement quiet-hours and timezone-aware scheduling
10. [ ] Build opt-out management with STOP keyword processing
11. [ ] Build analytics rollup job and report API
12. [ ] Build React CampaignListPage with KPIs
13. [ ] Build React CampaignBuilderWizard
14. [ ] Build React CampaignDetailPage with funnel chart
15. [ ] Build React TemplateLibrary and channel-aware editors
16. [ ] Integrate coupon attachment from RMS-032 with unique code generation
17. [ ] Write tests for dispatch, delivery tracking, A/B logic, opt-out, webhook idempotency

## Testing Criteria
- [ ] Campaign with dynamic filter returns correct audience size
- [ ] Template merge tags render correctly with real customer data
- [ ] Immediate send dispatches via correct gateway adapter
- [ ] Scheduled campaign runs at the configured time respecting quiet hours
- [ ] Webhook updates message status from sent → delivered → read without duplication
- [ ] Opted-out customers are excluded from campaign audience
- [ ] STOP keyword unsubscribes customer and updates consent flag
- [ ] A/B test assigns variants by configured split and picks winner after threshold
- [ ] Coupon attachment generates unique code per recipient
- [ ] Campaign analytics reflect accurate delivery, open and redemption metrics
- [ ] Recurring campaign creates next instance at the correct schedule