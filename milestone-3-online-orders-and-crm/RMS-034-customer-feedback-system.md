# RMS-034: Customer Feedback System

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-034 |
| **Type** | Story |
| **Epic** | CRM & Customer Experience |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-014 (Billing & Payment), RMS-023 (Online Order Dashboard), RMS-024 (Customer Database) |

## User Story
As a restaurant manager, I want every customer to easily share feedback via a QR code on their receipt, with NPS scoring, item-level ratings and automatic escalation of negative experiences, so that I can recover service failures quickly, learn what works and amplify positive reviews on Google and Zomato.

## Description
This story closes the loop between service delivery and customer perception by capturing structured feedback at the moment of highest signal -- right after the meal. A QR code printed on every dine-in, takeaway and online bill links to a mobile-optimised feedback form that requires no app installation and no login. The form is short (under 60 seconds to complete) but rich enough to drive action: NPS (0-10), overall star rating, optional text comment, and per-item ratings when the order contains identifiable dishes.

The feedback engine classifies each submission into Promoter (9-10), Passive (7-8) and Detractor (0-6) and triggers different workflows for each. Detractors generate immediate manager alerts via the in-app dashboard and an optional SMS/email to the duty manager, with the original bill attached for context. Promoters get a friendly "thank you" message plus a one-tap option to publish their positive experience on Google Reviews and Zomato, with deep links pre-populated where possible. This converts satisfied customers into public advocates at scale.

Item-level feedback ties ratings to specific menu items, enabling the analytics dashboard to surface which dishes are dragging down satisfaction and which are driving delight. Aggregated NPS trends across outlets, time periods and channels feed into manager reviews. Multi-outlet operators get a comparison view. All feedback is stored against the customer record (RMS-024) when the bill's phone number matches, building a longitudinal satisfaction profile.

## Acceptance Criteria
- [ ] QR code printed on every bill (dine-in, takeaway, online) encodes short feedback URL
- [ ] Feedback form loads on mobile without app install; < 2 second first contentful paint
- [ ] NPS question (0-10) is the primary metric; shown with visual slider
- [ ] Overall star rating (1-5) captured alongside NPS for redundancy
- [ ] Optional text comment (max 500 chars)
- [ ] Item-level ratings: per dish in order, 1-5 stars, optional comment per item
- [ ] Submission creates feedback record linked to order, customer (if matched) and outlet
- [ ] NPS classification: Promoter (9-10), Passive (7-8), Detractor (0-6) computed automatically
- [ ] Detractor feedback triggers immediate in-app alert to manager dashboard
- [ ] Optional SMS/email alert to duty manager for detractors with configurable threshold
- [ ] Promoter feedback shows "Thank you" + one-tap links to Google Reviews and Zomato
- [ ] Feedback analytics: NPS trend, average rating, item-level score table, channel breakdown
- [ ] Manager can reply to feedback (internal note + optional response to customer)
- [ ] Negative feedback requires mandatory follow-up status (open/in-progress/resolved)

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Customer Feedback Form | /feedback/{token} | Public mobile-friendly form |
| Feedback Dashboard | /crm/feedback | Manager overview with NPS and alerts |
| Feedback Detail | /crm/feedback/{id} | Single feedback view with reply |
| Item-Level Analytics | /crm/feedback/items | Per-dish ratings and trends |
| Manager Alerts Panel | /crm/feedback/alerts | Real-time detractor notifications |

### Screen Details

**Customer Feedback Form (/feedback/{token})**
- Public page (no login); token-based access validates the bill.
- Layout: Single column mobile-first, restaurant logo header, progress dots.
- Step 1: NPS slider (0-10) with anchors "Not at all likely" to "Extremely likely" and animated emoji feedback.
- Step 2: Overall star rating (1-5), optional quick tags (chips: Food, Service, Ambience, Cleanliness, Value) for one-tap selection.
- Step 3: Item ratings (only if bill has < 30 items); each row shows item name and 5-star input.
- Step 4: Optional text comment textarea (500 char max with counter).
- Step 5 (Promoter only): "Thanks! Mind sharing on Google or Zomato?" with two buttons opening deep links.
- Submit button: "Submit Feedback"; thank-you screen with optional coupon offer.
- Loading and error states handled gracefully.

**Feedback Dashboard (/crm/feedback)**
- Layout: Top KPI strip with NPS score, total responses, response rate, average rating. Below, a detractors alert section and a feedback feed.
- Filters: Date Range, Outlet, Channel, NPS Category, Star Rating, Order ID, Customer Phone, Item, Status.
- KPI cards: Current NPS, NPS Trend (vs last period), Promoter %, Passive %, Detractor %, Total Responses.
- Detractor alerts: Sorted by recency, red badge, shows order summary; "Acknowledge" and "Resolve" buttons.
- Feedback table: Timestamp | NPS | Stars | Order | Customer | Outlet | Channel | Tags | Status | Actions.
- Buttons: "Export Feedback", "Configure Alerts".
- Pagination: 25/50/100.

**Feedback Detail (/crm/feedback/{id})**
- Header: NPS score, stars, customer name/phone, order summary.
- Body: Full comment, item ratings table with mini chart, channel/outlet/time info.
- Sidebar: Manager reply box, internal notes, follow-up status dropdown, alert history.
- Buttons: "Mark In-Progress", "Mark Resolved", "Add Note", "Send Reply to Customer".

**Item-Level Analytics (/crm/feedback/items)**
- Table: Item | Times Rated | Avg Rating | Trend | NPS Contribution | Action.
- Charts: Top 10 best-rated items, Bottom 10 worst-rated items, rating trend per item.
- Drill-down: Click item to see individual feedback mentioning it.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/feedback/by-token/{token} | Resolve bill token to feedback context (public) |
| POST | /api/v1/feedback | Submit feedback (public, token-authenticated) |
| GET | /api/v1/feedback | List feedback with filters |
| GET | /api/v1/feedback/{id} | Get feedback detail |
| POST | /api/v1/feedback/{id}/reply | Manager reply to customer |
| POST | /api/v1/feedback/{id}/notes | Add internal note |
| PUT | /api/v1/feedback/{id}/status | Update follow-up status |
| GET | /api/v1/feedback/analytics | NPS trend and summary stats |
| GET | /api/v1/feedback/items | Item-level ratings analytics |
| GET | /api/v1/feedback/alerts | Active detractor alerts |
| POST | /api/v1/feedback/{id}/acknowledge | Acknowledge alert |
| GET | /api/v1/feedback/config | Get alert configuration |
| PUT | /api/v1/feedback/config | Update alert threshold and recipients |
| POST | /api/v1/feedback/export | Generate feedback CSV export |

## Database Tables

**feedback**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- order_id (foreignId, orders, nullable) -- for item-level linking
- bill_id (foreignId, bills, nullable)
- customer_id (foreignId, customers, nullable)
- outlet_id (foreignId, outlets)
- channel (enum: dine_in, online, takeaway, kiosk, aggregator)
- nps_score (unsignedTinyInteger) -- 0-10
- overall_rating (unsignedTinyInteger) -- 1-5
- comment (text, nullable)
- quick_tags_json (json, nullable) -- ['food','service','ambience','cleanliness','value']
- nps_category (enum: promoter, passive, detractor) -- computed
- status (enum: new, acknowledged, in_progress, resolved, default new)
- alert_sent_at (timestamp, nullable)
- alert_recipients_json (json, nullable)
- submitted_at (timestamp)
- ip_address (string, nullable)
- user_agent (string, nullable)
- timestamps, softDeletes
- indexes: [restaurant_id, submitted_at], [outlet_id, submitted_at], [nps_category]

**feedback_item_ratings**
- id (bigIncrements, PK)
- feedback_id (foreignId, feedback)
- order_item_id (foreignId, order_items, nullable)
- menu_item_id (foreignId, menu_items)
- rating (unsignedTinyInteger) -- 1-5
- comment (string, nullable)
- timestamps
- index: [menu_item_id, created_at]

**feedback_notes**
- id (bigIncrements, PK)
- feedback_id (foreignId, feedback)
- user_id (foreignId, users)
- note (text)
- is_internal (boolean, default true)
- timestamps

**feedback_replies**
- id (bigIncrements, PK)
- feedback_id (foreignId, feedback)
- user_id (foreignId, users)
- reply (text)
- channel (enum: sms, email, none, default none)
- sent_at (timestamp, nullable)
- timestamps

## Technical Notes

**Laravel Backend:**
- Controllers: `FeedbackController` (submit, list, show), `FeedbackReplyController`, `FeedbackAnalyticsController`, `FeedbackAlertController`, `FeedbackConfigController`.
- Services: `FeedbackClassifier` (computes nps_category), `AlertDispatcher` (triggers SMS/email/notification), `ReviewLinkGenerator` (builds Google/Zomato deep links), `FeedbackTokenService` (issues short tokens for bills).
- Token: HMAC-signed token encodes bill_id, customer_id, expires (7 days). Public endpoint validates signature.
- Alert: `DetractorAlertJob` runs in real-time on submission (Laravel queue with high priority); alerts duty manager(s) configured per outlet.
- Review links: Google Place ID lookup per outlet; Zomato deep link via restaurant slug. If unavailable, link to search page.
- Aggregation: `NpsCalculator::computeTrend($restaurantId, $range)` uses snapshot table populated nightly by `NpsRollupJob`.
- Public endpoint rate-limited (e.g. 5 submissions per token) to prevent abuse.
- Routes: `/api/v1/feedback` (mixed public and auth-protected), webhooks and public submission bypass auth via token.

**React Frontend:**
- Components: `PublicFeedbackForm` (mobile-first, no layout chrome), `NpsSlider` with emoji animation, `StarRatingInput`, `ItemRatingList`, `FeedbackDashboardPage`, `FeedbackFilters`, `DetractorAlertCard`, `FeedbackDetailPage`, `ItemAnalyticsPage`, `AlertsPanel`, `ManagerReplyBox`.
- State: Zustand `useFeedbackStore` for dashboard filters; form state local to component for public form.
- Hook: `useFeedbackSubmission(token)` handles retry, optimistic UI, and offline queue (localStorage fallback).
- Charts: Recharts for NPS trend, item rating bars, channel breakdown.
- Mobile feedback form is a separate lightweight bundle to minimise first-load size.

## Subtasks
1. [ ] Create `feedback`, `feedback_item_ratings`, `feedback_notes`, `feedback_replies` migrations and models
2. [ ] Implement HMAC token generation and validation for public feedback URLs
3. [ ] Build public FeedbackController.submit with rate limiting
4. [ ] Implement NPS classification and quick-tag storage
5. [ ] Build manager FeedbackController for list/show with filters
6. [ ] Implement detractor alert dispatcher (in-app + SMS + email)
7. [ ] Build Google Reviews and Zomato deep link generators
8. [ ] Build reply and internal-note APIs
9. [ ] Implement follow-up status state machine
10. [ ] Build NPS analytics rollup job
11. [ ] Add QR code generation to bill print template (RMS-014)
12. [ ] Build React public feedback form (mobile-first)
13. [ ] Build React FeedbackDashboardPage with KPI cards
14. [ ] Build React FeedbackDetailPage with reply UI
15. [ ] Build React ItemAnalyticsPage
16. [ ] Add detractor alerts to manager dashboard notification feed
17. [ ] Write tests for token validation, classification, alerts, link generation

## Testing Criteria
- [ ] Valid bill token opens feedback form with items preloaded
- [ ] Expired or tampered token returns 404
- [ ] Submission classifies NPS 9-10 as promoter, 7-8 as passive, 0-6 as detractor
- [ ] Detractor submission triggers alert within 60 seconds
- [ ] Manager reply persists and is marked as sent
- [ ] Item ratings link to correct menu item and appear in analytics
- [ ] Google and Zomato review links open with correct restaurant context
- [ ] NPS trend chart reflects historical submissions accurately
- [ ] Filters by outlet/channel/date return correct results
- [ ] Public form is reachable without login and rate-limited per token
- [ ] Negative feedback requires status update before being marked resolved