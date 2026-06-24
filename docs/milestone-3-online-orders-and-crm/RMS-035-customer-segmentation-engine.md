# RMS-035: Customer Segmentation Engine

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-035 |
| **Type** | Story |
| **Epic** | CRM & Marketing Automation |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 (Customer Database), RMS-025 (Loyalty), RMS-028 (Customer Labels) |

## User Story
As a marketing manager, I want a segmentation engine that performs RFM analysis, identifies behavioural cohorts (churn risk, high-value, lapsed), supports a custom rule builder, and syncs segments to campaigns, so that I can target the right customers with the right message instead of blasting my entire database.

## Description
This story delivers the analytical brain behind all CRM targeting. Without segmentation, every campaign becomes a mass broadcast -- expensive, low-converting, and increasingly compliant-fragile. The segmentation engine analyses the customer master (RMS-024), loyalty data (RMS-025) and order history to classify customers along three dimensions: Recency (when did they last visit?), Frequency (how often do they visit?) and Monetary (how much do they spend?). These RFM scores combine into behavioural segments such as Champions, Loyal Customers, At Risk, Hibernating and Lost.

Beyond the fixed behavioural segments, managers can build custom segments using a visual rule builder: conditions on fields like total visits, average spend, last visit date, loyalty tier, dietary preferences, tags, channel of last order, item purchased, city and outlet. Rules combine with AND/OR groups and support comparisons like ">", "<", "between", "contains" and "is empty". Each rule produces a live audience-size estimate so the manager can preview reach before saving.

The engine supports two modes: static segments (a snapshot of customers at a moment in time, ideal for one-off campaigns) and dynamic segments (a saved rule that re-evaluates on each use, ideal for ongoing journeys like "all customers with last visit > 30 days"). Automatic tag assignment propagates segment membership into the labels system (RMS-028), so segments and tags stay in sync. The segment audience can be pushed directly into a campaign (RMS-033) without re-defining the audience.

## Acceptance Criteria
- [ ] RFM analysis computed nightly for every customer with recency, frequency, monetary scores (1-5 quintiles)
- [ ] Predefined behavioural segments: Champions, Loyal, Potential Loyalists, Recent, Promising, Need Attention, About to Sleep, At Risk, Cannot Lose Them, Hibernating, Lost
- [ ] Each customer assigned to exactly one behavioural segment based on RFM
- [ ] Custom rule builder with field, operator, value conditions combined via AND/OR groups
- [ ] Supported fields: total visits, avg spend, lifetime value, last visit date, loyalty tier, dietary preference, tags, channels, items purchased, city, outlet, signup date
- [ ] Live audience size estimate shown as rules are edited
- [ ] Static segments: snapshot of customers at save time; audience is frozen
- [ ] Dynamic segments: rule re-evaluated on each campaign execution
- [ ] Automatic tag assignment: customers entering a segment receive the configured tag
- [ ] Segment list page shows name, type, audience size, last refreshed, used-in-campaigns count
- [ ] Segment analytics: size over time, average LTV, conversion rate vs non-segment
- [ ] One-click "Sync to Campaign" pushes audience into new RMS-033 campaign draft
- [ ] Manual refresh button recomputes dynamic segment on demand

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Segment List | /crm/segments | All segments with type and size |
| Segment Builder | /crm/segments/create | Visual rule builder with live preview |
| RFM Analysis | /crm/segments/rfm | RFM distribution and behavioural map |
| Segment Detail | /crm/segments/{id} | Audience list, analytics, sync options |
| Segment Compare | /crm/segments/compare | Compare two segments side-by-side |

### Screen Details

**Segment List (/crm/segments)**
- Layout: Top filter (Type: All/Static/Dynamic/Behavioural), search bar. Then data table.
- Table columns: Name | Type badge | Source (Behavioural/Manual) | Audience Size | Last Refreshed | Used In Campaigns | Created By | Actions.
- Row actions: View, Edit, Refresh, Duplicate, Sync to Campaign, Delete.
- Buttons: "New Segment", "View RFM Map".
- Side panel: Quick stats -- Total Customers, Segments Defined, Customers Tagged.

**Segment Builder (/crm/segments/create)**
- Multi-step: Basics → Rules → Audience Preview → Tags & Sync → Review.
- Basics: Name, Description, Type (Static/Dynamic), Source (Custom Rule / Behavioural Template picker).
- Rules: Visual rule builder.
  - Each rule row: Field (dropdown), Operator (depends on field), Value (input).
  - Group rules with AND/OR; nested groups allowed up to 3 levels.
  - Add row, add group, remove controls.
- Audience Preview: Live count + sample (first 20 customers), breakdown by tier/channel.
- Tags & Sync: Toggle "Auto-assign tag" with tag picker. Toggle "Refresh nightly".
- Review: Summary; Save as Static / Save as Dynamic.

**RFM Analysis (/crm/segments/rfm)**
- Layout: Three distribution charts side-by-side (R, F, M histograms with quintile cutoffs).
- Behavioural segment grid: 11 cards (one per segment) with audience size, avg LTV, color-coded status.
- Click segment card → drills into Segment Detail for that predefined segment.
- Heatmap: R x F grid with bubble size = monetary value.

**Segment Detail (/crm/segments/{id})**
- Header: Name, type, source, audience size, last refreshed.
- KPI cards: Audience Size, Avg LTV, Avg Visits, Avg Days Since Last Visit.
- Charts: Size over time (line), tier breakdown (donut), top items purchased (bar).
- Tabs: Audience, Analytics, Campaigns Used In, Activity Log.
- Audience tab: Paginated customer table with Export option.
- Campaigns Used In tab: List of campaigns that targeted this segment.
- Buttons: "Edit", "Refresh Now", "Sync to Campaign", "Export Audience".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/segments | List segments with filters |
| POST | /api/v1/segments | Create new segment |
| GET | /api/v1/segments/{id} | Get segment detail |
| PUT | /api/v1/segments/{id} | Update segment |
| DELETE | /api/v1/segments/{id} | Delete segment |
| POST | /api/v1/segments/{id}/refresh | Manually recompute dynamic segment |
| GET | /api/v1/segments/{id}/audience | List customers in segment |
| POST | /api/v1/segments/preview | Preview audience size from rule payload |
| GET | /api/v1/segments/rfm | Get RFM analysis and behavioural distribution |
| GET | /api/v1/segments/fields | Get available rule fields with types and operators |
| POST | /api/v1/segments/{id}/sync-campaign | Create campaign draft with this segment as audience |
| GET | /api/v1/segments/templates | List behavioural segment templates |
| POST | /api/v1/segments/{id}/export | Export segment audience to CSV |

## Database Tables

**customer_segments**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- name (string)
- description (text, nullable)
- type (enum: static, dynamic, behavioural)
- source (enum: custom, template)
- template_key (string, nullable) -- for behavioural templates (champions, at_risk, etc.)
- rule_json (json, nullable) -- AND/OR tree of conditions
- audience_size (unsignedInteger, default 0) -- cached count
- audience_snapshot_path (string, nullable) -- CSV path for static segments
- auto_assign_tag_id (foreignId, customer_tags, nullable)
- refresh_cron (string, nullable) -- nightly default
- last_refreshed_at (timestamp, nullable)
- created_by (foreignId, users)
- timestamps, softDeletes
- indexes: [restaurant_id, type]

**segment_customers** (pivot for static segments and snapshots)
- id (bigIncrements, PK)
- segment_id (foreignId, customer_segments)
- customer_id (foreignId, customers)
- added_at (timestamp)
- indexes: [segment_id], [customer_id]
- unique([segment_id, customer_id])

**customer_rfm_scores**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- restaurant_id (foreignId, restaurants)
- recency_days (unsignedInteger) -- days since last visit
- frequency_count (unsignedInteger) -- visits in last 365 days
- monetary_value (decimal(12,2)) -- spend in last 365 days
- r_score (unsignedTinyInteger) -- 1-5 quintile
- f_score (unsignedTinyInteger)
- m_score (unsignedTinyInteger)
- rfm_segment (string, nullable) -- e.g. "555", "111"
- behavioural_segment (string, nullable) -- champions, at_risk, etc.
- computed_at (timestamp)
- index: [customer_id, computed_at], [restaurant_id, behavioural_segment]

**segment_activity_log**
- id (bigIncrements, PK)
- segment_id (foreignId, customer_segments)
- user_id (foreignId, users, nullable)
- action (enum: created, updated, refreshed, synced, deleted)
- audience_size_before (unsignedInteger, nullable)
- audience_size_after (unsignedInteger, nullable)
- metadata (json, nullable)
- created_at

## Technical Notes

**Laravel Backend:**
- Controllers: `SegmentController` (CRUD), `SegmentRuleController` (preview, fields metadata), `SegmentRfmController`, `SegmentSyncController`.
- Services: `RuleEvaluator` (translates JSON rule tree to Eloquent query), `RfmCalculator` (assigns quintile scores using NTILE-style ranking), `BehaviouralSegmentClassifier` (maps RFM to behavioural label), `SegmentRefreshService`, `SegmentSyncService` (creates campaign draft).
- Job: `ComputeRfmScoresJob` runs nightly per restaurant; `RefreshDynamicSegmentsJob` runs hourly; `RefreshStaticSegmentsJob` only on manual trigger or scheduled cron.
- Rule structure: nested AND/OR tree `{op: 'AND', children: [{field, operator, value}, ...]}`. Recursive walker translates to query builder.
- Quintile assignment: SQL window functions or PHP-side sort and split; cache quintile boundaries per restaurant.
- Behavioural mapping: standard RFM-to-segment matrix (e.g. R>=4 & F>=4 & M>=4 → Champions).
- Tag sync: when auto_assign_tag_id set, refresh adds tag to new members and removes from those who left; uses `customer_tag_map` (RMS-028).
- Performance: rule evaluation uses indexed columns (total_visits, lifetime_value, last_visit_at, loyalty_tier); for item-level filters, a denormalised `customer_last_items` table is read.

**React Frontend:**
- Components: `SegmentListPage`, `SegmentBuilderWizard`, `RuleBuilder` (recursive component), `RuleRow`, `RuleGroup`, `AudiencePreviewPanel`, `RfmAnalysisPage`, `RfmHistogram`, `BehaviouralSegmentGrid`, `SegmentDetailPage`, `SegmentComparePage`.
- State: Zustand `useSegmentStore` for builder draft and audience preview cache.
- Hook: `useRulePreview(rule)` debounces API call to /segments/preview (250 ms).
- Form: React Hook Form with dynamic field arrays for rule groups; Zod schema validates operator compatibility per field type.

## Subtasks
1. [ ] Create `customer_segments`, `segment_customers`, `customer_rfm_scores`, `segment_activity_log` migrations and models
2. [ ] Implement `RfmCalculator` with quintile assignment logic
3. [ ] Build `BehaviouralSegmentClassifier` mapping RFM to 11 standard segments
4. [ ] Implement `ComputeRfmScoresJob` with nightly scheduling
5. [ ] Build `RuleEvaluator` service supporting nested AND/OR trees
6. [ ] Implement rule field metadata endpoint with operator-per-type info
7. [ ] Build SegmentController CRUD with type (static/dynamic/behavioural) handling
8. [ ] Implement live audience preview endpoint
9. [ ] Build `SegmentRefreshService` for dynamic re-evaluation
10. [ ] Implement automatic tag assignment via RMS-028 customer_tag_map
11. [ ] Build `SegmentSyncService` to push audience into campaign draft
12. [ ] Implement segment activity log
13. [ ] Build React SegmentListPage with filters and KPIs
14. [ ] Build React SegmentBuilderWizard with visual rule builder
15. [ ] Build React RfmAnalysisPage with histograms and segment grid
16. [ ] Build React SegmentDetailPage with charts
17. [ ] Write tests for rule evaluation, RFM classification, refresh, tag sync, campaign sync

## Testing Criteria
- [ ] RFM quintiles are evenly distributed across customers
- [ ] Behavioural segment classification matches standard matrix
- [ ] Rule with single AND group returns correct audience
- [ ] Rule with nested OR/AND returns correct audience
- [ ] Live preview updates audience count as rules change
- [ ] Static segment snapshot freezes membership at save time
- [ ] Dynamic segment refresh picks up new matching customers
- [ ] Auto-assign tag adds tag to segment members only
- [ ] Sync to campaign creates a campaign draft with correct audience
- [ ] Comparison view shows distinct customers between two segments
- [ ] Performance: 100k customers evaluated in under 5 seconds for typical rule