# RMS-011: Payment Processing

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-011 |
| **Type** | Story |
| **Epic** | Core POS |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-009 |

## User Story
As a cashier, I want to process customer payments through multiple methods including cash, UPI, card, and split payments, so that bills are settled quickly and accurately.

## Description
The payment processing module handles the final step of the transaction: collecting money from the customer. It supports multiple payment methods simultaneously (split tender), integrates with UPI/payment gateways, calculates change for cash payments, tracks payment status in real-time, and records all payment data for reconciliation. The system supports the Indian payment ecosystem: Cash, UPI (PhonePe, GPay, Paytm, BHIM), Debit/Credit Card, Wallet, and an internal wallet system.

## Acceptance Criteria
- [ ] Payment methods: Cash, UPI (QR dynamic), Card (swipe machine), Wallet, Internal Wallet (RestroPay)
- [ ] Split payment: bill amount can be split across multiple methods (e.g., 500 cash + 350 UPI)
- [ ] Cash payment: enter amount received -> auto-calculate change due
- [ ] UPI payment: generate dynamic QR with exact amount -> poll for payment status -> auto-confirm
- [ ] Card payment: manual entry of last 4 digits + amount (POS terminal integration later)
- [ ] Internal Wallet: check balance -> deduct -> record transaction
- [ ] Payment status tracking: pending -> processing -> success -> failed
- [ ] Failed payment retry without creating duplicate bill
- [ ] Partial payment: customer pays part now, rest later (bill remains partially settled)
- [ ] Tip handling: optional tip addition with method selection
- [ ] Payment receipt: print mini-receipt with payment details
- [ ] End-of-day cash drawer reconciliation
- [ ] Refund to original payment method

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Payment Screen | /pos/payment/:billId | Main payment processing screen |
| Split Payment Modal | (modal) | Configure multi-tender split |
| UPI QR Display | (modal) | Dynamic QR code for UPI payment |
| Payment History | /billing/payments | All payments with filters |
| Cash Drawer | /pos/cash-drawer | Cash reconciliation |
| Payment Settings | /settings/payments | Configure payment methods |

### Screen Details

**Payment Screen (/pos/payment/:billId)**
- Top: Bill summary (Bill#, Amount, Table/Customer)
- Grand Total (large display)
- Tender Amount display

Payment Method Buttons (grid):
- CASH (green) -> opens cash entry:
  - Numeric keypad
  - Quick amounts: Exact, 100, 200, 500, 1000, 2000
  - Amount Received field
  - Change Due display (auto-calculated, highlighted)
  - "CONFIRM CASH PAYMENT" button

- UPI QR (blue) -> opens QR modal:
  - Dynamic QR code generated for exact bill amount
  - UPI deep link: `upi://pay?pa={vpa}&pn={name}&am={amount}&cu=INR`
  - Status: "Waiting for payment..." (animated)
  - Auto-poll every 3 seconds for payment confirmation
  - Timeout: 5 minutes
  - Manual override: "Mark as Paid" (manager only)
  - Cancel button

- CARD (purple) -> opens card entry:
  - Card type: Debit / Credit
  - Last 4 digits
  - Amount (auto-filled, editable if partial)
  - Approval code (from POS terminal)
  - "CONFIRM CARD PAYMENT" button

- WALLET (orange) -> opens wallet:
  - Customer phone lookup -> show wallet balance
  - If balance sufficient -> "Pay from Wallet"
  - If insufficient -> message + suggest split
  - Transaction confirmation

- SPLIT PAYMENT (multi-color) -> opens split modal (see below)

**Split Payment Modal**
- Bill total displayed at top
- Add payment method rows (add/remove):
  - Row 1: [Method Dropdown] | Amount Input | [Add]
  - Row 2: [Method Dropdown] | Amount Input | [Remove]
  - Remaining amount: auto-calculated (Total - sum of rows)
- Visual progress bar: fills as payments are added
- When remaining = 0: "SETTLE BILL" button activates
- Each split payment processes individually

**Cash Drawer (/pos/cash-drawer)**
- Opening balance display
- Cash IN: list of cash payments today (time, amount, bill#)
- Cash OUT: list of cash refunds, petty cash, cash drops (time, amount, reason)
- Expected cash in drawer: Opening + IN - OUT
- Actual count fields: 2000x, 500x, 200x, 100x, 50x, 20x, 10x, coins
- Total counted
- Difference (Expected - Counted): highlighted if non-zero
- Settle shift button -> resets opening balance
- Print Z-Report (end of day)

**Payment Settings (/settings/payments)**
- Enable/disable payment methods: Cash (always on), UPI, Card, Wallet, RestroPay
- UPI VPA: `{restaurant}@okhdfcbank` (configurable)
- UPI Merchant ID (for payment gateway integration)
- Payment Gateway: Razorpay / Cashfree / PayU / PhonePe Business (dropdown)
- Card terminal: Terminal ID, Merchant ID
- Tip enabled (toggle) + suggested percentages (5%, 10%, 15%, custom)
- Auto-settle on UPI success (toggle)
- Cash drawer kick on cash payment (toggle)

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/bills/{id}/payments | Process payment |
| GET | /api/v1/payments | List all payments |
| GET | /api/v1/payments/{id} | Get payment details |
| POST | /api/v1/bills/{id}/payments/split | Process split payment |
| POST | /api/v1/bills/{id}/payments/upi/qr | Generate dynamic UPI QR |
| GET | /api/v1/bills/{id}/payments/upi/status | Poll UPI payment status |
| POST | /api/v1/bills/{id}/payments/upi/manual-confirm | Manual confirm UPI (manager) |
| POST | /api/v1/bills/{id}/payments/cash | Process cash payment |
| POST | /api/v1/bills/{id}/payments/card | Process card payment |
| POST | /api/v1/bills/{id}/payments/wallet | Process wallet payment |
| POST | /api/v1/payments/{id}/refund | Refund payment |
| GET | /api/v1/cash-drawer/today | Get today's cash drawer state |
| POST | /api/v1/cash-drawer/open | Opening balance entry |
| POST | /api/v1/cash-drawer/settle | Settle/end shift |
| POST | /api/v1/cash-drawer/entry | Add manual cash entry (petty, drop) |
| GET | /api/v1/payment-settings | Get payment settings |
| PUT | /api/v1/payment-settings | Update payment settings |

## Database Tables

```
payments:
  id (bigint, PK)
  bill_id (bigint, FK -> bills)
  outlet_id (bigint, FK)
  payment_method (enum: cash, upi, card, wallet, restropay, split)
  amount (decimal 12,2)
  status (enum: pending, processing, success, failed, refunded)
  reference_number (varchar 100, nullable) -- UPI UTR, card approval code
  upi_id (varchar 100, nullable) -- payer VPA
  card_last4 (varchar 4, nullable)
  card_type (enum: debit, credit, nullable)
  tip_amount (decimal 10,2, default 0)
  processed_at (timestamp, nullable)
  processed_by (bigint, FK -> users)
  failure_reason (text, nullable)
  timestamps

payment_splits:
  id (bigint, PK)
  payment_id (bigint, FK -> payments) -- parent split payment
  method (enum: cash, upi, card, wallet, restropay)
  amount (decimal 12,2)
  reference_number (varchar 100, nullable)
  status (enum: pending, success, failed)
  timestamps

cash_drawer_entries:
  id (bigint, PK)
  outlet_id (bigint, FK)
  type (enum: opening, sale, refund, petty_cash, cash_drop, settlement)
  amount (decimal 12,2)
  direction (enum: in, out)
  bill_id (bigint, FK -> bills, nullable)
  reason (varchar 200, nullable)
  user_id (bigint, FK -> users)
  shift_id (bigint, FK -> cash_drawer_shifts)
  timestamps

cash_drawer_shifts:
  id (bigint, PK)
  outlet_id (bigint, FK)
  opened_at (timestamp)
  closed_at (timestamp, nullable)
  opening_balance (decimal 12,2)
  closing_balance (decimal 12,2, nullable)
  expected_closing (decimal 12,2, nullable)
  difference (decimal 12,2, nullable)
  opened_by (bigint, FK -> users)
  closed_by (bigint, FK -> users)
  status (enum: open, closed)
  timestamps

payment_settings:
  id (bigint, PK)
  outlet_id (bigint, FK)
  enable_upi (boolean, default true)
  enable_card (boolean, default true)
  enable_wallet (boolean, default false)
  enable_restropay (boolean, default false)
  upi_vpa (varchar 100, nullable)
  payment_gateway (enum: razorpay, cashfree, payu, phonepe, null)
  gateway_merchant_id (varchar 100, nullable)
  gateway_api_key (varchar 200, nullable)
  enable_tip (boolean, default false)
  tip_suggestions (json, default '["5","10","15"]')
  auto_settle_upi (boolean, default true)
  cash_drawer_kick (boolean, default true)
  timestamps
```

## Technical Notes
- **Backend**: `PaymentController.php` + `PaymentService.php`. UPI status polling: use payment gateway webhook OR manual polling via Razorpay/Cashfree API. Payment gateway integration via `razorpay/razorpay-php` package.
- **UPI Dynamic QR**: Generate using payment gateway API (Razorpay Dynamic QR) or UPI deep-link format. Poll gateway API for payment status every 3 seconds via WebSocket or AJAX.
- **Cash Drawer Kick**: ESC/POS command to cash drawer connected to thermal printer (same as RMS-056 hardware layer).
- **Frontend**: `PaymentScreen.tsx` with method-specific sub-components: `CashPayment.tsx`, `UPIPayment.tsx`, `CardPayment.tsx`, `WalletPayment.tsx`. Split payment modal `SplitPaymentModal.tsx`. Numeric keypad component `NumericKeypad.tsx`.
- **Reconciliation**: Cash drawer shift auto-calculates expected cash from all cash payments during shift. Manual count at close -> difference logged -> manager approval if > threshold.
- **Security**: Card details never stored (PCI compliance). Only last 4 digits + approval code. Payment gateway handles card data.

## Subtasks
1. [ ] Create payments, payment_splits, cash_drawer_entries, cash_drawer_shifts, payment_settings migrations
2. [ ] Build Payment model with bill relationship and split relationship
3. [ ] Build PaymentService: cash, card, wallet processing
4. [ ] Install Razorpay PHP SDK
5. [ ] Implement UPI dynamic QR generation via gateway API
6. [ ] Implement UPI payment status polling (3-second interval, 5-min timeout)
7. [ ] Build payment webhook endpoint for gateway callbacks
8. [ ] Implement split payment logic (multi-tender)
9. [ ] Implement tip handling
10. [ ] Build cash drawer shift management
11. [ ] Build cash reconciliation (expected vs actual)
12. [ ] Implement payment refund to original method
13. [ ] Build React payment screen with method buttons
14. [ ] Build cash payment with numeric keypad + change calculation
15. [ ] Build UPI QR display modal with auto-poll
16. [ ] Build card payment form
17. [ ] Build split payment modal
18. [ ] Build cash drawer reconciliation page
19. [ ] Build payment settings configuration page
20. [ ] Write payment processing tests
21. [ ] Test UPI QR generation and mock callback

## Testing Criteria
- [ ] Cash payment: enter 1000 for 750 bill -> change due = 250
- [ ] UPI payment: QR generated -> mock success callback -> bill settled
- [ ] UPI payment: timeout after 5 min -> payment marked failed -> retry
- [ ] Split payment: 400 cash + 350 UPI -> bill settled, both recorded
- [ ] Wallet payment: insufficient balance -> error message
- [ ] Failed payment -> bill remains unsettled -> retry creates new payment record
- [ ] Cash drawer: opening 5000 + cash sales 12000 - refund 500 = expected 16500
- [ ] Tip: 10% on 1000 = 100 tip -> total payment 1100
- [ ] Refund: original UPI payment -> refund to same UPI
- [ ] End of shift: settle -> Z-report prints -> new shift opens
