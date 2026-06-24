# RMS-028: Customer Labels & Segmentation

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-028 |
| **Type** | Story |
| **Epic** | CRM |
| **Milestone** | M3 - Online Orders & CRM |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 |

## User Story
As a marketing manager, I want to tag customers with custom labels and build segments based on behavior, so that I can run targeted campaigns and personalize service.

## Description
Labels and segmentation allow the restaurant to categorize customers beyond basic demographics. Labels can be manual (VIP, Corporate, Birthday Month) or auto-applied based on rules (visited 10+ times, spends > 1000 avg, hasn't visited in 60 days). Segments are dynamic groups of customers matching filter criteria, used for campaigns and analytics.

## Acceptance Criteria
- [ ] Create custom labels (free text + color code)
- [ ] Manual label assignment: search customer -> add/remove labels
- [ ] Auto-tagging rules: if condition then apply label (e.g., visits >= 10 -> "Regular")
- [ ] Segment builder: combine filters (labels, visits, spend, last visit, tier, feedback score)
- [ ] Saved segments with auto-refresh counts
- [ ] Segment analytics: count, avg spend, visit frequency, top items
- [ ] Export segment to CSV or send directly to campaign
- [ ] Label analytics: customer count per label, revenue contribution per label

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Labels Management | /crm/labels | CRUD for labels |
| Segment Builder | /crm/segments | Create/manage segments |
| Segment Detail | /crm/segments/:id | Customer list + analytics |

### Screen Details

**Labels Management (/crm/labels)**
- Grid of label cards: Name, Color, Customer Count, Auto-rule (if any)
- Add Label: Name, Color (color picker), Description
- Auto-rule builder: Condition (visits >= X, spend >= X, last visit <= X days, tier = X) -> auto-apply label
- Bulk apply: select customers -> add label

**Segment Builder (/crm/segments)**
- Segment name
- Filter rows (AND conditions):
  - Label: [is/is not] [label dropdown]
  - Visits: [>= / <= / =] [number]
  - Total Spend: [>= / <=] [amount]
  - Avg Spend: [>= / <=] [amount]
  - Last Visit: [within / more than] [X days]
  - Loyalty Tier: [is/is not] [tier]
  - Feedback Score: [>= / <=] [number]
  - Created: [before / after] [date]
- Live count: "Segment matches 342 customers" (updates as filters change)
- Save segment
- Actions: Export CSV, Send to Campaign, View Customers

**Segment Detail (/crm/segments/:id)**
- Filter summary (human-readable: "Customers with 5+ visits AND avg spend >= 500")
- Stats: Count, Avg Spend, Avg Visits, Top Items
- Customer table: Name | Phone | Labels | Visits | Total Spend | Last Visit | Tier

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/labels | List labels |
| POST | /api/v1/labels | Create label |
| PUT | /api/v1/labels/{id} | Update label |
| DELETE | /api/v1/labels/{id} | Delete label |
| POST | /api/v1/labels/{id}/apply | Apply label to customer(s) |
| DELETE | /api/v1/labels/{id}/remove | Remove from customer(s) |
| GET | /api/v1/segments | List segments |
| POST | /api/v1/segments | Create segment |
| PUT | /api/v1/segments/{id} | Update segment |
| GET | /api/v1/segments/{id} | Segment detail + customer list |
| GET | /api/v1/segments/{id}/count | Live count for filters |
| GET | /api/v1/segments/{id}/export | Export to CSV |
| POST | /api/v1/auto-rules | Create auto-tagging rule |

## Database Tables

```
customer_labels:
  id (bigint, PK)
  outlet_id (bigint, FK)
  name (varchar 100)
  color_code (varchar 7, default '#3B82F6')
  description (text, nullable)
  is_auto (boolean, default false)
  timestamps

customer_label_map:
  id (bigint, PK)
  customer_id (bigint, FK -> customers)
  label_id (bigint, FK -> customer_labels)
  is_auto_applied (boolean, default false)
  applied_at (timestamp)
  timestamps
  unique: [customer_id, label_id]

customer_segments:
  id (bigint, PK)
  outlet_id (bigint, FK)
  name (varchar 200)
  filters (json) -- stored filter conditions
  customer_count (int, default 0)
  last_refreshed_at (timestamp, nullable)
  timestamps

auto_tag_rules:
  id (bigint, PK)
  outlet_id (bigint, FK)
  label_id (bigint, FK -> customer_labels)
  conditions (json) -- {"field":"visits","op":">=","value":10}
  is_active (boolean, default true)
  timestamps
```

## Technical Notes
- **Backend**: `LabelController.php`, `SegmentController.php`. Segment count: dynamic query built from filters JSON. Auto-tag: scheduled job (daily) evaluates rules -> applies/removes labels.

## Subtasks
1. [ ] Create customer_labels, customer_label_map, customer_segments, auto_tag_rules migrations
2. [ ] Build Label model with customer relationship
3. [ ] Build Segment model with dynamic query builder
4. [ ] Implement auto-tag rule engine (scheduled job)
5. [ ] Build segment analytics aggregation
6. [ ] Build React labels management page
7. [ ] Build segment builder with live count
8. [ ] Build segment detail with analytics
9. [ ] Write tests

## Testing Criteria
- [ ] Create "VIP" label -> apply to 5 customers -> label shows 5 count
- [ ] Auto-rule: visits >= 10 -> label "Regular" -> customer with 12 visits auto-tagged
- [ ] Segment: visits >= 5 AND spend >= 1000 -> matches correct customers
- [ ] Export segment to CSV -> file downloads
- [ ] Remove label from customer -> count decrements
