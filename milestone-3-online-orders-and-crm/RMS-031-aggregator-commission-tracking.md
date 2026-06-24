# RMS-031: Aggregator Commission Tracking

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-031 |
| **Type** | Story |
| **Epic** | CRM |
| **Milestone** | M3 - Online Orders & CRM |
| **Priority** | Medium |
| **Story Points** | 5 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-022 |

## User Story
As a restaurant owner, I want to track and reconcile aggregator commissions, so that I know my true net revenue from each platform and can identify discrepancies in payouts.

## Description
Food aggregators (Swiggy, Zomato) charge commission on each order and send weekly payouts. This module tracks commission rates per aggregator, auto-calculates expected commission on each order, reconciles with actual payout amounts, and flags discrepancies. Reports show gross vs net revenue per platform.

## Acceptance Criteria
- [ ] Configure commission rates per aggregator (percentage + fixed fee per order)
- [ ] Auto-calculate commission on each online order: order_value x commission_rate
- [ ] Track expected payout: sum of (order_value - commission) for payout period
- [ ] Enter actual payout received: amount + bank reference + date
- [ ] Auto-reconcile: compare expected vs actual -> flag discrepancy
- [ ] Chargeback/dispute tracking: deduct from payout with reason
- [ ] Net revenue report: per aggregator, per day/week/month
- [ ] Commission trend: is effective commission rate increasing?
- [ ] Payout cycle tracking: weekly/fortnightly per aggregator

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Commission Dashboard | /crm/commissions | Overview per aggregator |
| Payout Reconciliation | /crm/commissions/payouts | Match payouts with orders |
| Commission Settings | /crm/commissions/settings | Rates per aggregator |
| Net Revenue Report | /crm/commissions/report | Gross vs net analysis |

### Screen Details

**Commission Dashboard (/crm/commissions)**
- Cards per aggregator: Swiggy (Gross, Commission, Net, Pending Payout), Zomato (same)
- This period summary: Total Online Revenue, Total Commission, Net Received, Pending
- Discrepancy alerts: "Swiggy payout for Week 23: Expected 45,200, Received 44,800, Variance: -400"

**Payout Reconciliation (/crm/commissions/payouts)**
- Table: Payout Period | Aggregator | Order Count | Gross Amount | Expected Commission | Net Expected | Actual Received | Variance | Status
- Status: Pending / Received / Reconciled / Discrepancy
- Click payout -> detailed order list with per-order commission breakdown
- Record payout: amount, bank reference, date
- Auto-reconcile button: matches orders to payout period

**Commission Settings (/crm/commissions/settings)**
- Per aggregator:
  - Commission Rate (%)
  - Fixed Fee per Order (optional)
  - GST on Commission (toggle - aggregators charge 18% GST on commission)
  - Payment Cycle: Weekly (which day) / Fortnightly
  - TDS Rate (% - tax deduction at source)
- Rate change history (with effective date)

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/commissions/dashboard | Dashboard data |
| GET | /api/v1/commissions/payouts | List payouts |
| POST | /api/v1/commissions/payouts | Record actual payout |
| GET | /api/v1/commissions/payouts/{id} | Payout detail with orders |
| POST | /api/v1/commissions/payouts/{id}/reconcile | Auto-reconcile |
| GET | /api/v1/commissions/settings | Commission rates |
| PUT | /api/v1/commissions/settings | Update rates |
| GET | /api/v1/commissions/report | Net revenue report |

## Database Tables

```
aggregator_commission_settings:
  id (bigint, PK)
  outlet_id (bigint, FK)
  aggregator (enum: swiggy, zomato, magicpin, others)
  commission_rate (decimal 5,2) -- percentage
  fixed_fee_per_order (decimal 8,2, default 0)
  gst_on_commission (boolean, default true)
  tds_rate (decimal 5,2, default 0)
  payout_cycle (enum: weekly, fortnightly)
  payout_day (int) -- day of week (1-7) for weekly
  effective_from (date)
  timestamps

aggregator_payouts:
  id (bigint, PK)
  outlet_id (bigint, FK)
  aggregator (varchar 50)
  payout_period_start (date)
  payout_period_end (date)
  order_count (int)
  gross_amount (decimal 12,2)
  expected_commission (decimal 12,2)
  gst_on_commission (decimal 12,2, default 0)
  tds_amount (decimal 12,2, default 0)
  net_expected (decimal 12,2)
  actual_received (decimal 12,2, nullable)
  bank_reference (varchar 100, nullable)
  received_date (date, nullable)
  variance (decimal 12,2, nullable)
  status (enum: pending, received, reconciled, discrepancy)
  timestamps

commission_disputes:
  id (bigint, PK)
  payout_id (bigint, FK -> aggregator_payouts)
  order_id (bigint, FK -> online_orders, nullable)
  dispute_type (enum: chargeback, rate_mismatch, missing_order, excess_commission, other)
  amount (decimal 12,2)
  reason (text)
  status (enum: open, resolved, rejected)
  resolution_notes (text, nullable)
  timestamps
```

## Technical Notes
- **Backend**: `CommissionController.php`. Auto-calculate on each online order: `commission = (order_total * rate / 100) + fixed_fee`. GST on commission: `gst = commission * 0.18`. Net expected: `gross - commission - gst + tds`. Payout reconciliation job: when payout received, sum all orders in period -> compare.

## Subtasks
1. [ ] Create aggregator_commission_settings, aggregator_payouts, commission_disputes migrations
2. [ ] Build Commission model and service
3. [ ] Implement auto-calculation on online order
4. [ ] Build payout reconciliation engine
5. [ ] Build dispute tracking
6. [ ] Build commission rate change history
7. [ ] Build React commission dashboard
8. [ ] Build payout reconciliation page
9. [ ] Build settings page
10. [ ] Build net revenue report
11. [ ] Write tests

## Testing Criteria
- [ ] Online order 1000 -> Swiggy commission 20% -> 200 deducted -> net 800
- [ ] Weekly payout: 50 orders, gross 25,000, commission 5,000, net expected 20,000
- [ ] Actual received 19,500 -> variance -500 -> status discrepancy
- [ ] Dispute raised for missing order -> resolved -> payout adjusted
- [ ] Rate change from 20% to 22% -> only applies from effective date
