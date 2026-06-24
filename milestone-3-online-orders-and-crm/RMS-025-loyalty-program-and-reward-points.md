# RMS-025: Loyalty Program & Reward Points

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-025 |
| **Type** | Story |
| **Epic** | Customer Relationship Management |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-024 (Customer Database), RMS-014 (Billing & Payment) |

## User Story
As a restaurant owner, I want a loyalty program that rewards customers with points for their spending and lets them redeem points for discounts, so that I can increase repeat visits and customer lifetime value.

## Description
This story implements a configurable loyalty points system integrated with the customer database and billing module. Customers earn points based on configurable accrual rules (e.g., 1 point per Rs. 100 spent). Points are accrued automatically when a bill is settled and can be redeemed at the time of billing for a discount (e.g., 100 points = Rs. 50 off). The system maintains a complete points ledger for audit and transparency.

A tier system (Silver, Gold, Platinum) automatically upgrades customers based on cumulative spend or visit thresholds within a rolling 12-month window. Each tier offers enhanced benefits: faster accrual rates, birthday bonus points, priority service, and exclusive offers. Tier demotion occurs if thresholds are not maintained.

The module includes expiry rules (points expire after N months if unused), birthday bonus points (auto-credited on the customer's birthday), and referral points (when a referred customer makes their first order). A points ledger provides a complete transaction history with reason codes (earned, redeemed, expired, bonus, referral, adjustment). Managers can manually adjust points with reason logging.

## Acceptance Criteria
- [ ] Configurable accrual rule: admin sets "Earn X points per Rs. Y spent" (default: 1 point per Rs. 100); rule is editable and versioned with effective dates.
- [ ] Points are automatically accrued when a bill is settled; a ledger entry with reason "earned" is created referencing the bill.
- [ ] Tier system: Silver (default), Gold (Rs. 25,000+ spend in 12 months), Platinum (Rs. 75,000+ spend in 12 months); thresholds are configurable.
- [ ] Tier benefits include multiplier: Silver 1x, Gold 1.25x, Platinum 1.5x points accrual rate.
- [ ] Points redemption at billing: cashier enters points to redeem; system calculates discount value (configurable conversion rate, default 100 points = Rs. 50) and applies to bill total.
- [ ] Redemption does not allow exceeding available balance; validates minimum redemption threshold.
- [ ] Points expiry job runs daily and expires points older than the configured retention period (default 12 months); ledger entry with reason "expired".
- [ ] Birthday bonus: auto-credits configurable bonus points (default 500) on customer's birthday if DOB is on file and customer is opted in.
- [ ] Referral points: when a new customer's first order is completed, referrer receives configurable points (default 200).
- [ ] Points ledger shows complete history with date, type (credit/debit), points, reason, reference, and running balance.
- [ ] Manager can manually adjust points (add/deduct) with mandatory reason note.
- [ ] Customer profile (RMS-024) displays current points balance, tier, and progress to next tier.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Loyalty Dashboard | /crm/loyalty | Program overview, stats, configuration |
| Points Ledger | /crm/loyalty/ledger | Transaction history for all or specific customers |
| Tier Management | /crm/loyalty/tiers | Configure tier thresholds and benefits |
| Accrual & Redemption Rules | /crm/loyalty/rules | Configure earning and redemption rates |
| Customer Points View | /crm/customers/{id}/loyalty | Per-customer points, tier, and ledger |
| Manual Adjustment | modal | Add/deduct points for a customer |

### Screen Details

**Loyalty Dashboard (/crm/loyalty)**
- Layout: Top summary cards + charts + recent activity table.
- Summary cards: Total Members, Active Points Outstanding, Points Issued (This Month), Points Redeemed (This Month), Tier Distribution (Silver/Gold/Platinum counts).
- Charts: Points Trend (line chart, earned vs redeemed over 6 months), Tier Distribution (donut chart).
- Recent Activity table: Top 10 recent ledger entries: Date | Customer | Type | Points | Reason | Reference.
- Buttons: "Configure Rules", "Configure Tiers", "View Full Ledger", "Run Expiry Job".

**Tier Management (/crm/loyalty/tiers)**
- Layout: Three cards (Silver, Gold, Platinum) each with editable configuration.
- Silver card fields: Min Spend Threshold (0, locked), Points Multiplier (1.0, locked), Birthday Bonus (points, default 200), Benefits description (textarea).
- Gold card fields: Min Spend Threshold (default 25000), Min Visits (default 20), Points Multiplier (default 1.25), Birthday Bonus (default 500), Benefits description.
- Platinum card fields: Min Spend Threshold (default 75000), Min Visits (default 50), Points Multiplier (default 1.5), Birthday Bonus (default 1000), Benefits description.
- Buttons: "Save Tier Config", "Reset to Defaults".
- Validation: Thresholds must be ascending (Silver < Gold < Platinum); multipliers must be ascending.

**Accrual & Redemption Rules (/crm/loyalty/rules)**
- Fields:
  - Accrual: Points per Rs. Spent (e.g., 1 point per 100), Minimum Bill for Accrual (e.g., Rs. 200), Round Points (dropdown: floor/ceil/round).
  - Redemption: Points to Re. Value (e.g., 100 points = Rs. 50), Minimum Redemption Points (e.g., 100), Maximum Redemption per Bill (e.g., 50% of bill or N points).
  - Expiry: Points Validity in Months (default 12), Expiry Action (forfeit), Expiry Warning Days (default 7, send reminder).
  - Birthday: Enable (toggle), Bonus Points (default 500), Lead Days (send reminder N days before, default 3).
  - Referral: Enable (toggle), Referrer Points (default 200), Referred Customer Min Order Value (default 300).
- Buttons: "Save Rules", "Preview Calculation" (opens modal showing sample bill accrual).

**Points Ledger (/crm/loyalty/ledger)**
- Filters: Customer (search), Type (All/Credit/Debit), Reason (All/Earned/Redeemed/Expired/Birthday/Referral/Adjustment), Date Range.
- Table columns: Date/Time | Customer Name | Type (credit green/debit red badge) | Points (signed +/-) | Reason | Reference (bill number or note) | Running Balance.
- Buttons: "Export CSV", "Manual Adjustment".

**Customer Points View (/crm/customers/{id}/loyalty)**
- Header: Current Points Balance (large), Tier badge with multiplier, Progress bar to next tier (e.g., "Rs. 12,000 more for Gold").
- Info cards: Points Expiring Soon (within 30 days), Total Earned (all-time), Total Redeemed (all-time), Birthday Bonus Eligible (yes/no).
- Mini ledger table (last 20 entries): same columns as full ledger.
- Buttons: "Adjust Points" (opens modal), "Redeem at Billing" (navigates to billing).

**Manual Adjustment Modal**
- Fields: Adjustment Type (Add/Deduct radio), Points (number input), Reason (dropdown: Goodwill/Complaint resolution/Promo credit/Error correction/Other), Notes (textarea).
- Buttons: "Apply Adjustment", "Cancel".
- Validation: Points > 0; reason required; creates audit log entry.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/loyalty/dashboard | Get loyalty program dashboard stats |
| GET | /api/v1/loyalty/rules | Get current accrual/redemption rules |
| PUT | /api/v1/loyalty/rules | Update rules |
| GET | /api/v1/loyalty/tiers | Get tier configuration |
| PUT | /api/v1/loyalty/tiers | Update tier configuration |
| GET | /api/v1/loyalty/ledger | Get points ledger with filters |
| GET | /api/v1/customers/{id}/loyalty | Get customer points balance and tier |
| GET | /api/v1/customers/{id}/loyalty/ledger | Get customer-specific points ledger |
| POST | /api/v1/customers/{id}/loyalty/adjust | Manual points adjustment |
| POST | /api/v1/customers/{id}/loyalty/redeem | Redeem points (called from billing) |
| GET | /api/v1/customers/{id}/loyalty/redemption-value | Calculate Rs. value for given points |
| POST | /api/v1/loyalty/process-expiry | Trigger expiry job (admin) |
| POST | /api/v1/loyalty/process-birthdays | Trigger birthday bonus job (admin/cron) |

## Database Tables

**loyalty_rules**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- accrual_points (unsignedInteger, default 1) -- points earned
- accrual_per_amount (decimal(10,2), default 100.00) -- per Rs. spent
- min_bill_for_accrual (decimal(10,2), default 200.00)
- round_mode (enum: floor, ceil, round, default round)
- redemption_points (unsignedInteger, default 100) -- points needed
- redemption_value (decimal(10,2), default 50.00) -- Rs. value
- min_redemption_points (unsignedInteger, default 100)
- max_redemption_percent (unsignedTinyInteger, default 50) -- % of bill
- points_validity_months (unsignedInteger, default 12)
- expiry_warning_days (unsignedInteger, default 7)
- birthday_enabled (boolean, default true)
- birthday_bonus_points (unsignedInteger, default 500)
- birthday_lead_days (unsignedInteger, default 3)
- referral_enabled (boolean, default true)
- referrer_points (unsignedInteger, default 200)
- referred_min_order_value (decimal(10,2), default 300.00)
- effective_from (date)
- timestamps

**loyalty_tiers**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- name (enum: silver, gold, platinum)
- min_spend_12m (decimal(12,2))
- min_visits_12m (unsignedInteger)
- points_multiplier (decimal(3,2), default 1.00)
- birthday_bonus_points (unsignedInteger)
- benefits_description (text)
- display_order (unsignedTinyInteger)
- timestamps

**customer_loyalty**
- id (bigIncrements, PK)
- customer_id (foreignId, customers)
- restaurant_id (foreignId, restaurants)
- current_tier (enum: silver, gold, platinum, default silver)
- points_balance (integer, default 0)
- total_earned (integer, default 0)
- total_redeemed (integer, default 0)
- spend_12m (decimal(12,2), default 0) -- rolling for tier calc
- visits_12m (unsignedInteger, default 0)
- tier_assigned_at (timestamp)
- tier_review_at (date) -- next tier review date
- referred_by_customer_id (foreignId, customers, nullable)
- referral_code (string, unique, nullable)
- timestamps

**loyalty_points_ledger**
- id (bigIncrements, PK)
- customer_loyalty_id (foreignId, customer_loyalty)
- customer_id (foreignId, customers)
- type (enum: credit, debit)
- points (integer, unsigned) -- absolute value
- reason (enum: earned, redeemed, expired, birthday_bonus, referral_bonus, adjustment_add, adjustment_deduct, tier_bonus)
- reference_type (string, nullable: bill, order, manual)
- reference_id (unsignedBigInteger, nullable)
- expires_at (timestamp, nullable) -- for earned points
- balance_after (integer)
- notes (text, nullable)
- user_id (foreignId, users, nullable) -- who made the change
- timestamps

## Technical Notes

**Laravel Backend:**
- Controllers: `LoyaltyDashboardController` (dashboard), `LoyaltyRuleController` (show, update), `LoyaltyTierController` (show, update), `LoyaltyLedgerController` (index), `CustomerLoyaltyController` (show, ledger, adjust, redeem, redemptionValue).
- Service: `LoyaltyService` with methods: `accrueOnBillSettlement($bill)`, `redeemPoints($customerId, $points)`, `calculateRedemptionValue($points)`, `adjustPoints($customerId, $points, $reason, $notes)`, `checkAndUpgradeTier($customerId)`, `processExpiry()`, `processBirthdayBonuses()`, `processReferralBonus($newCustomerId)`.
- Accrual flow: `BillSettled` event fires -> listener dispatches `AccrueLoyaltyPointsJob` -> resolves customer loyalty record, calculates points = floor(bill_amount / accrual_per_amount) * accrual_points * tier_multiplier, creates ledger entry (credit, earned), updates balance, sets `expires_at`.
- Tier check: Runs on every accrual; recomputes `spend_12m` from last 12 months of bills; if threshold crossed, upgrades tier, creates bonus entry if configured, fires `CustomerTierUpgraded` event.
- Expiry: `app:ProcessLoyaltyExpiry` daily scheduled command; queries ledger entries where `expires_at < now()` and `type=credit` and `reason=earned` and not fully redeemed (FIFO matching), creates debit "expired" entries, updates balance.
- Birthday: `app:ProcessBirthdayBonuses` daily; queries customers with DOB = today, credits bonus, dispatches SMS via RMS-026 integration.
- Referral: On first order completion, checks `referred_by_customer_id`, validates order value >= minimum, credits referrer.
- Redemption: Atomic transaction with row lock on `customer_loyalty` to prevent race conditions; validates balance, min threshold, max % of bill; creates debit entry, updates balance, returns discount amount.
- Events: `PointsAccrued`, `PointsRedeemed`, `CustomerTierUpgraded`, `PointsExpired` for WebSocket and notifications.
- Routes in `routes/api.php`.

**React Frontend:**
- Components: `LoyaltyDashboardPage`, `LoyaltyStatsCards`, `PointsTrendChart` (Recharts), `TierDistributionChart`, `RecentActivityTable`, `TierManagementPage`, `TierConfigCard`, `RulesPage`, `RulesForm`, `PreviewCalculationModal`, `PointsLedgerPage`, `LedgerFilters`, `LedgerTable`, `CustomerLoyaltyView`, `TierBadge`, `TierProgressBar`, `ManualAdjustmentModal`.
- Billing integration: `LoyaltyRedemptionPanel` component embedded in billing screen; calls `GET /customers/{id}/loyalty/redemption-value?points=X` to show live discount preview.
- State: Zustand `useLoyaltyStore`.

## Subtasks
1. [ ] Create `loyalty_rules`, `loyalty_tiers`, `customer_loyalty`, `loyalty_points_ledger` migrations and models
2. [ ] Build `LoyaltyService` with accrue, redeem, adjust, tier check methods
3. [ ] Implement `AccrueLoyaltyPointsJob` triggered by BillSettled event
4. [ ] Build tier upgrade logic with 12-month rolling spend calculation
5. [ ] Implement redemption with row locking and validation
6. [ ] Create `app:ProcessLoyaltyExpiry` scheduled command with FIFO expiry matching
7. [ ] Create `app:ProcessBirthdayBonuses` scheduled command
8. [ ] Implement referral bonus on first order completion
9. [ ] Build LoyaltyDashboardController and stats aggregation
10. [ ] Build LoyaltyRuleController and LoyaltyTierController
11. [ ] Build LoyaltyLedgerController with filtering
12. [ ] Create React LoyaltyDashboardPage with charts and stats
13. [ ] Build React TierManagementPage and RulesPage
14. [ ] Build React PointsLedgerPage with filters and export
15. [ ] Build React CustomerLoyaltyView with tier progress bar
16. [ ] Build LoyaltyRedemptionPanel for billing screen integration
17. [ ] Build ManualAdjustmentModal with audit logging
18. [ ] Write tests for accrual, redemption, expiry, tier upgrade, birthday, referral

## Testing Criteria
- [ ] Bill settlement accrues correct points based on rule and tier multiplier
- [ ] Gold tier (1.25x) earns more points than Silver (1.0x) for same bill
- [ ] Tier auto-upgrades when 12-month spend crosses threshold
- [ ] Redemption validates balance, min threshold, and max % of bill
- [ ] Redemption applies correct discount to bill total
- [ ] Daily expiry job expires old points with correct FIFO matching
- [ ] Birthday bonus auto-credits on customer's birthday
- [ ] Referral bonus credits referrer when referred customer's first order completes
- [ ] Manual adjustment creates ledger entry and updates balance atomically
- [ ] Points ledger shows complete history with correct running balances
- [ ] Concurrent redemption requests do not overdraw balance (row lock test)
