# RMS-027: Customer Feedback System

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-027 |
| **Type** | Story |
| **Epic** | CRM |
| **Milestone** | M3 - Online Orders & CRM |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-009, RMS-024 |

## User Story
As a restaurant owner, I want to collect and analyze customer feedback, so that I can identify areas for improvement and respond to negative experiences before they become online reviews.

## Description
The feedback system collects post-dining feedback via QR code on the bill receipt or SMS/WhatsApp link. Customers rate food, service, ambiance, and cleanliness on a 1-5 scale, with optional text comments. Low ratings (1-3) trigger immediate alerts to the manager. Feedback analytics show trends and areas needing attention.

## Acceptance Criteria
- [ ] Feedback form accessible via QR on bill and SMS/WhatsApp link
- [ ] Rating categories: Food Quality, Service Speed, Staff Behavior, Cleanliness, Ambiance, Value for Money
- [ ] 1-5 star rating per category
- [ ] Optional text comment
- [ ] Optional: what they liked most / least (quick tags)
- [ ] Low rating alert (1-3 stars): instant notification to manager via dashboard + SMS
- [ ] Manager can respond to feedback (internal notes + optional customer follow-up)
- [ ] Feedback analytics: avg score per category, trend over time, NPS calculation
- [ ] Feedback linked to bill/order (optional)
- [ ] Anonymous feedback option

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Feedback Form (public) | /feedback/:token | Customer-facing form |
| Feedback Dashboard | /crm/feedback | All feedback with filters |
| Feedback Detail | /crm/feedback/:id | Individual feedback + response |
| Feedback Analytics | /crm/feedback/analytics | Charts and trends |

### Screen Details

**Feedback Form (public, mobile-optimized)**
- Restaurant name + logo header
- "How was your experience?" prompt
- Star rating rows: Food, Service, Cleanliness, Ambiance, Value (each 5 stars)
- What did you like? (tag chips: Quick Service, Great Taste, Clean, Friendly Staff, Good Music, Other)
- What can we improve? (tag chips: Slow Service, Cold Food, Pricing, Cleanliness, Other)
- Additional comments (textarea, optional)
- Would you recommend us? (Yes/No - for NPS)
- Submit -> Thank You page with loyalty points award if customer identified

**Feedback Dashboard (/crm/feedback)**
- Summary: Avg Rating, Today's Count, Response Rate
- Table: Date | Customer/Anonymous | Overall Rating | Categories (mini-stars) | Comment preview | Status (New/Responded/Resolved) | Actions
- Filter by: rating (1-5), date, category score
- Red highlight for ratings <= 3

**Feedback Analytics (/crm/feedback/analytics)**
- Overall avg rating (large, with star display)
- Category breakdown: bar chart (Food, Service, Cleanliness, Ambiance, Value)
- Rating distribution: pie (5-star, 4-star, 3-star, 1-2 star percentages)
- Trend line: 30-day/90-day avg rating trend
- NPS score: Promoters % - Detractors %
- Top liked tags (word cloud or bar)
- Top improvement tags

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/feedback | Submit feedback (public) |
| GET | /api/v1/feedback | List feedback (staff) |
| GET | /api/v1/feedback/{id} | Feedback detail |
| POST | /api/v1/feedback/{id}/respond | Manager response |
| PATCH | /api/v1/feedback/{id}/status | Update status |
| GET | /api/v1/feedback/analytics | Analytics data |
| GET | /api/v1/feedback/token/{token} | Validate feedback link |

## Database Tables

```
feedback:
  id (bigint, PK)
  outlet_id (bigint, FK)
  bill_id (bigint, FK -> bills, nullable)
  customer_id (bigint, FK -> customers, nullable)
  token (uuid, unique) -- for public link
  is_anonymous (boolean, default false)
  food_rating (int) -- 1-5
  service_rating (int)
  cleanliness_rating (int)
  ambiance_rating (int)
  value_rating (int)
  overall_rating (decimal 3,1) -- calculated avg
  liked_tags (json, nullable)
  improvement_tags (json, nullable)
  comment (text, nullable)
  would_recommend (boolean, nullable)
  manager_response (text, nullable)
  status (enum: new, viewed, responded, resolved, ignored)
  submitted_at (timestamp)
  timestamps
```

## Technical Notes
- **Backend**: `FeedbackController.php`. Token generated on bill settle (optional, configurable). Low rating listener: `FeedbackSubmitted` event -> if overall <= 3 -> alert manager.
- **Public form**: No auth required. Token validates feedback belongs to a real bill/outlet.

## Subtasks
1. [ ] Create feedback migration
2. [ ] Build Feedback model and controller
3. [ ] Generate feedback token on bill settle
4. [ ] Build low-rating alert system (event + notification)
5. [ ] Build analytics aggregation queries
6. [ ] Build React feedback form (mobile-optimized)
7. [ ] Build feedback dashboard with filters
8. [ ] Build analytics page with charts
9. [ ] Write tests

## Testing Criteria
- [ ] Customer submits feedback via token link -> stored with ratings
- [ ] Low rating (2 stars) -> manager alert triggered
- [ ] Analytics: 10 feedbacks, avg 4.2 -> correct calculation
- [ ] Anonymous feedback -> no customer_id, still tracked
- [ ] Manager responds -> status -> responded
