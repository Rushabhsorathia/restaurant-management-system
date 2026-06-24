# RMS-040: Self-Service Kiosk Mode

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-040 |
| **Type** | Story |
| **Epic** | Self-Service Ordering & Digital Payments |
| **Milestone** | Milestone 4 - Captain App, KDS & Reservations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-011 (Menu Management), RMS-014 (Billing & Payment), RMS-018 (Payment Gateway Integration), RMS-019 (KOT), RMS-038 (KDS) |

## User Story
As a restaurant operator deploying self-service kiosks in QSR and fast-casual outlets, I want a fullscreen-locked React kiosk app with simplified image-driven menu, multi-language UI, cart, integrated UPI/card/QR payments, thermal receipt printing, and age verification — plus a remote admin panel for content updates — so that I can reduce counter queues, increase average order value via suggestive selling, and operate 24/7 with minimal staff.

## Description
The Self-Service Kiosk is a fullscreen-locked React app designed to run on dedicated Android/iOS kiosk hardware (21"–32" touchscreen stands) at quick-service restaurants, food courts, cinemas, and large-format casual dining. It replaces or augments the counter queue with an always-available, image-driven ordering experience. Indian QSR chains (Subway, Domino's-style, Haldiram's, Chaayos, Chai Point) and multiplex food courts are the primary users.

The kiosk boots into locked-down mode (no browser chrome, no navigation, no system menus, admin PIN to exit). The idle screen displays rotating promo videos and high-margin "Today's Specials" cards; a touch or proximity sensor launch the menu — category-first UI optimised for non-technical users with large tiles (200×200px+), hero images, ₹ pricing, and FSSAI veg/non-veg/Jain badges.

Cart building mirrors RMS-037 but with simplified gestures (tap-add, swipe-remove, qty stepper). Mandatory modifiers are enforced; the cart shows running totals with CGST/SGST breakdown per Indian GST law. Payments support UPI (dynamic QR per txn via customer's app), Card (insert/swipe/tap via connected PIN pad), and Cash (kiosk issues a token, collected at counter). All flows route through RMS-018 (Razorpay/PayU) with PCI-DSS compliance — card data never touches the kiosk.

After payment, the kiosk prints a thermal receipt and displays an Order Ready screen; the KOT fires to KDS (RMS-038) like any order. Age verification (18+ gate) blocks restricted items (alcohol where licensed, energy drinks). The separate Kiosk Admin Panel lets ops remotely 86 items, upload promo videos, change language defaults, schedule time-of-day pricing, and pull diagnostics — all pushed to kiosks via WebSocket so the next idle cycle loads new content.

## Acceptance Criteria
- [ ] Kiosk runs in fullscreen locked mode (no browser chrome, no system gestures, no escape without admin PIN)
- [ ] Idle screen displays rotating promo videos (MP4) and "Today's Specials" cards; transitions to menu on touch/proximity
- [ ] Menu UI optimized for touch: tile size ≥200×200px, image-led, large fonts (24pt+), veg/Jain badges visible
- [ ] Multi-language support: English, Hindi, and at least one regional language (Tamil/Telugu/Bengali/Marathi) with runtime switcher
- [ ] Cart with line items, quantity stepper, modifier chips, cooking notes; running total with GST breakdown (CGST + SGST)
- [ ] Mandatory modifier enforcement blocks "Add to Cart" until selection
- [ ] Suggestive selling: "Add a drink for ₹50?" upsell prompts at cart and checkout
- [ ] Payment options: UPI (dynamic QR per txn), Card (via connected PIN pad), Cash (token-based)
- [ ] Card data never touches the kiosk — routed via PIN pad or payment gateway SDK
- [ ] Age verification gate (18+) for restricted items; blocks order if guest declines
- [ ] Thermal receipt prints with order #, items, GST, payment summary, token number, "Order Ready" instruction
- [ ] KOT dispatched to KDS (RMS-038) like any order; ticket appears on relevant station within 1 second
- [ ] Order Ready screen displays token number and "Please collect at counter" until order bumped complete
- [ ] Admin panel allows: 86/uncross items, upload promo videos, change language defaults, push real-time updates via WebSocket
- [ ] Diagnostic data: per-kiosk uptime, transaction count, error log, payment success rate, last sync time
- [ ] Offline fallback: cached menu + queue orders to backend when reconnected

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Idle / Attract | /kiosk | Promo videos + Today's Specials tiles (rotating) |
| Welcome | /kiosk/welcome | Language picker + "Start Order" CTA |
| Menu Browse | /kiosk/menu | Category-driven image grid with search |
| Item Detail | /kiosk/items/{id} | Modifier selection, quantity, special instructions |
| Cart | /kiosk/cart | Line items, totals with GST, upsell prompts |
| Checkout | /kiosk/checkout | Payment method picker (UPI / Card / Cash) |
| Payment | /kiosk/payment/{method} | Method-specific flow (QR, PIN pad, token) |
| Order Ready | /kiosk/ready | Token display, "collect at counter", pickup instructions |
| Admin Panel | /admin/kiosks | Remote content management dashboard |

### Screen Details

**Idle / Attract Loop (/kiosk)**
- Layout: Fullscreen video background (muted, autoplay, looping); overlay of "Today's Specials" tile carousel at bottom.
- Auto-rotate: 8-12 second video clips uploaded via admin panel; tiles change every 5 seconds.
- Trigger: Touch anywhere or proximity sensor (if supported by hardware) transitions to Welcome.
- Branding: Restaurant logo top-left, current time top-right, WiFi indicator bottom-right.
- No navigation: PIN-protected exit button hidden in corner (long-press 3 seconds to reveal admin login).

**Welcome (/kiosk/welcome)**
- Layout: Centered hero with restaurant branding; large language picker (3-4 flag icons: 🇬🇧 English, हिं Hindi, தமிழ் Tamil, etc.); "Start Order" CTA button (full-width, 100px tall).
- Welcome message: Localized greeting (e.g., "Welcome! Tap to begin" / "स्वागत है! शुरू करने के लिए टैप करें").
- Buttons: Language flags (radio selection), "Start Order" (primary), "Accessibility" (large-text toggle).
- Behavior: Language choice persists for session; admin-configured default language shown first.

**Menu Browse (/kiosk/menu)**
- Layout: Top horizontal category strip (scrollable, 6-8 categories visible); main area = item grid (2 columns on 21", 3 columns on 32").
- Each item tile (200×200px+): Hero image, item name (24pt+), price (₹ in large font), Veg/Non-veg/Jain/Vegan badge (FSSAI color dot).
- Top bar: Restaurant logo | Search input (large) | Cart icon (with badge count) | Language switcher | Accessibility toggle.
- Filters: Veg only toggle, "Combos/Deals" quick filter, search-as-you-type.
- On tap: Item Detail modal opens with full-screen image, description, modifier groups, quantity stepper, "Add to Cart" button.
- Out-of-stock: Greyed out with "Currently Unavailable" badge (crossed image); cannot be tapped.
- Live updates: WebSocket pushes 86/uncross changes within 1 second — affected tiles grey out instantly.

**Item Detail (/kiosk/items/{id})**
- Layout: Full-screen modal with image (60% height), item info below.
- Image: Large hero photo, swipeable if multiple.
- Info: Name, description, price (₹), calorie info (optional), allergen flags, prep time estimate.
- Modifier groups: radio (single-select), checkbox (multi-select), or quantity add-ons; mandatory groups show red asterisk.
- Special instructions: Textarea for "less spicy", "no onion".
- Quantity: Stepper (- 1 +), default 1.
- Buttons: "Add to Cart" (primary, full-width), "Cancel" (back to menu).
- Sticky CTA: Bottom-anchored price summary "₹280 × 2 = ₹560" with "Add to Cart" always visible.

**Cart (/kiosk/cart)**
- Layout: Header with "Your Order" + total count; scrollable line items; sticky bottom totals + checkout button.
- Each line: thumbnail, name, qty stepper, modifiers (chips), line total, swipe-left to remove.
- Totals: Subtotal, Discount (if any), CGST (2.5%), SGST (2.5%), Grand Total — Indian ₹ with comma grouping.
- Upsell prompts: "Add a beverage for ₹50?" carousel below cart (max 3 items, dismissible).
- Empty state: friendly illustration + "Add something delicious!" + back-to-menu button.
- Buttons: "Continue Shopping", "Proceed to Checkout" (primary).

**Checkout (/kiosk/checkout)**
- Layout: Order summary on left, payment method picker on right (2-column on 32", stacked on 21").
- Payment options: Three large tiles — UPI, Card, Cash — each with icon and short description.
- Age verification: if cart contains age-restricted items, modal prompts "Are you 18 or older?" with Yes/No before payment selection. No blocks order.
- GST invoice toggle: "Get GST Invoice" — prompts for phone (B2C invoice with optional GSTIN).
- Buttons: "Back to Cart", "Pay Now" (primary, per method).

**Payment — UPI (/kiosk/payment/upi)**
- Layout: Large QR code (centered, 300×300px), order summary above, instructions below.
- QR: Dynamic per txn (UPI intent string with merchant VPA, amount, txn ref).
- Instructions: "Open any UPI app (PhonePe, GPay, Paytm) and scan" + scanning animation.
- Polling: Backend polls gateway every 3 seconds (or receives webhook → WebSocket push to kiosk).
- States: "Waiting for payment..." → "Payment received! ✓" → "Order placed!" → print receipt.
- Timeout: 5 minutes; on timeout, "Payment not received — please try again" with retry.
- Buttons: "Cancel Payment", "I've Paid" (manual confirmation), "Help".

**Payment — Card (/kiosk/payment/card)**
- Layout: "Insert/Swipe/Tap your card" with animated card icon.
- Flow: External PIN pad handles the actual transaction; kiosk displays status ("Reading card..." → "Processing..." → "Approved" / "Declined — Try another card").
- Receipt: optional email/SMS prompt before payment.

**Payment — Cash (/kiosk/payment/cash)**
- Layout: Order summary + "Pay ₹560 at the counter. Here's your token: **A-247**" + QR code for token lookup.
- Behavior: Order held in `pending_cash`; counter staff confirms via admin panel; on confirmation, order fires to KDS.
- Buttons: "Print Token", "Cancel".

**Order Ready (/kiosk/ready)**
- Layout: Centered hero — large token number (96pt+), "Your order is being prepared" message, animated cooking icon.
- Sub-messages: "Average wait: 12-15 minutes" / "We'll notify you when ready" / "Show this token at the counter".
- QR code: Token QR for counter staff to scan if customer returns.
- Auto-return: After 60 seconds of no interaction, returns to Idle / Attract loop.
- Buttons: "Need Help?" (calls staff via WebSocket alert), "Start New Order" (returns to menu).

**Admin Panel (/admin/kiosks)**
- Layout: Sidebar with kiosk list (each kiosk with online status, uptime, txn count); main panel for selected kiosk's content management.
- Tabs: Menu Availability (toggle 86/uncross), Promotions (upload MP4, schedule, preview), Languages (default language, fallback chain), Pricing (time-of-day rules), Diagnostics (logs, payment success rate, uptime chart).
- Real-time: Push changes via WebSocket; affected kiosks update within 2 seconds.
- Buttons per tab: Save, Preview, Push to All Kiosks, Schedule, Export Report.
- Multi-kiosk: Bulk operations across selected kiosks (e.g., 86 an item chain-wide during stockout).

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/kiosks | List all kiosks for outlet (admin) |
| POST | /api/v1/kiosks | Register new kiosk device |
| PATCH | /api/v1/kiosks/{id} | Update kiosk config (name, location, default language) |
| GET | /api/v1/kiosks/{id}/menu | Get kiosk-optimized menu (image URLs, prices, badges) |
| GET | /api/v1/kiosks/{id}/items/{itemId}/modifiers | Modifier groups for an item |
| POST | /api/v1/kiosks/{id}/orders | Place kiosk order (cart → order creation) |
| GET | /api/v1/kiosks/{id}/orders/{orderId}/status | Check order status (for Order Ready polling) |
| POST | /api/v1/kiosks/{id}/orders/{orderId}/receipt | Trigger thermal receipt reprint |
| POST | /api/v1/kiosks/{id}/payments/upi/initiate | Generate dynamic UPI QR for txn |
| GET | /api/v1/kiosks/{id}/payments/{txnId}/status | Poll UPI payment status |
| POST | /api/v1/kiosks/{id}/payments/{txnId}/cancel | Cancel pending payment |
| POST | /api/v1/kiosks/{id}/payments/card/confirm | Confirm card payment (PIN pad callback) |
| POST | /api/v1/kiosks/{id}/payments/cash/token | Generate cash token, hold order pending |
| POST | /api/v1/admin/kiosks/{id}/menu/availability | 86 / uncross items remotely |
| POST | /api/v1/admin/kiosks/{id}/promotions/upload | Upload promo video |
| GET | /api/v1/admin/kiosks/{id}/promotions | List active promotions |
| DELETE | /api/v1/admin/kiosks/{id}/promotions/{promoId} | Remove promotion |
| PUT | /api/v1/admin/kiosks/{id}/settings | Update default language, pricing, accessibility |
| GET | /api/v1/admin/kiosks/{id}/diagnostics | Uptime, txn count, error log, payment success rate |
| POST | /api/v1/admin/kiosks/bulk-update | Apply updates to multiple kiosks |
| WS | /ws/kiosk/{kioskId} | Real-time menu/availability/order status pushes |

## Database Tables

**kiosks**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets, indexed)
- name (string) — "Kiosk 1 — Entrance", "Kiosk 2 — Food Court"
- device_id (string, unique) — hardware ID for pairing
- api_key_hash (string) — kiosk authentication
- default_language (string, default 'en') — ISO code
- fallback_languages_json (json) — array of language codes
- accessibility_large_text (boolean, default false)
- is_active (boolean, default true)
- last_seen_at (timestamp, nullable) — heartbeat
- uptime_started_at (timestamp, nullable)
- software_version (string, nullable)
- timestamps
- indexes([outlet_id, is_active])

**kiosk_orders**
- id (bigIncrements, PK)
- kiosk_id (foreignId, kiosks, indexed)
- order_id (foreignId, orders) — links to standard order
- token_number (string, indexed) — e.g., "A-247"
- customer_phone (string, nullable) — for receipt SMS/email
- age_verified (boolean, default false)
- payment_method (enum: upi, card, cash)
- payment_status (enum: pending, success, failed, cancelled, refunded, default pending)
- payment_txn_id (string, nullable) — gateway txn ref
- payment_gateway_response_json (json, nullable)
- receipt_printed (boolean, default false)
- receipt_printed_at (timestamp, nullable)
- receipt_printed_count (unsignedTinyInteger, default 0)
- order_ready_displayed_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- timestamps
- indexes([kiosk_id, payment_status]), ([token_number])

**kiosk_promotions**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets, nullable) — null for chain-wide
- title (string)
- video_url (string)
- thumbnail_url (string, nullable)
- duration_seconds (unsignedSmallInteger)
- priority (unsignedSmallInteger, default 0) — higher plays first
- start_at (timestamp, nullable)
- end_at (timestamp, nullable)
- active_days_json (json, nullable) — array of weekday numbers [1,2,3,4,5,6,7]
- active_hours_json (json, nullable) — {start: "11:00", end: "14:00"}
- target_item_ids_json (json, nullable) — show only with specific items in cart
- is_active (boolean, default true)
- uploaded_by_user_id (foreignId, users)
- timestamps
- index([outlet_id, is_active, priority])

**kiosk_availability_overrides** (86/uncross state, time-bound)
- id (bigIncrements, PK)
- kiosk_id (foreignId, kiosks, nullable) — null for outlet-wide
- outlet_id (foreignId, outlets, indexed)
- menu_item_id (foreignId, menu_items, indexed)
- is_available (boolean, default true)
- available_from (timestamp, nullable)
- available_until (timestamp, nullable) — null = indefinite
- reason (string, nullable) — "Stockout", "End of day", etc.
- set_by_user_id (foreignId, users)
- timestamps
- indexes([outlet_id, menu_item_id]), ([kiosk_id, is_available])

**kiosk_diagnostics**
- id (bigIncrements, PK)
- kiosk_id (foreignId, kiosks, indexed)
- recorded_at (timestamp, indexed)
- uptime_seconds (unsignedInteger)
- transactions_count (unsignedInteger)
- payment_success_count (unsignedInteger)
- payment_failure_count (unsignedInteger)
- errors_count (unsignedInteger)
- avg_order_value (decimal(10,2))
- last_payment_error (string, nullable)
- metadata_json (json, nullable)
- timestamps
- index([kiosk_id, recorded_at])

## Technical Notes

**Laravel Backend:**
- Controllers: `KioskController`, `KioskMenuController`, `KioskOrderController`, `KioskPaymentController` (UPI/card/cash), `AdminKioskController`, `AdminBulkKioskController`.
- Services: `KioskOrderService` (order+KOT+token), `KioskPaymentService` (Razorpay/PayU UPI dynamic QR + PIN pad SDK + cash token), `KioskAvailabilityService` (merges menu + time-bound overrides), `KioskPromotionService` (time/day/priority selection), `KioskDiagnosticService`, `KioskThermalPrintService` (ESC/POS + PDF fallback).
- Events: `KioskOrderPlaced` (broadcast to KDS), `KioskPaymentReceived`, `KioskReceiptPrinted`, `KioskMenuUpdated`, `KioskPromotionUpdated`, `KioskOfflineDetected`, `KioskHealthAlert`.
- Jobs: `PollUpiPaymentJob` (3s polling), `GenerateThermalReceiptJob`, `AggregateKioskDiagnosticsJob` (hourly), `KioskHeartbeatCheckJob` (offline after 2 missed beats).
- Payments reuse RMS-018 Razorpay/PayU; UPI dynamic QR via `/payments/upi/qr`; card via PIN pad callback webhook.
- Tokens: daily-rotating alphanumeric (A-001 to Z-999), reset midnight per outlet.
- Age verification: items flagged `is_age_restricted` (alcohol, energy drinks); modal enforced before cart add; audited via `kiosk_orders.age_verified`.
- Multi-language: `default_language` on kiosk; menu `translations_json`; runtime i18n switch.
- Bulk ops: `AdminBulkKioskController` accepts array of kiosk IDs; broadcasts via WebSocket.

**React Frontend:**
- Components: `IdleAttractLoop`, `VideoCarousel`, `WelcomeScreen`, `LanguagePicker`, `MenuBrowse`, `ItemDetailModal`, `ModifierGroupForm`, `CartScreen`, `UpsellCarousel`, `CheckoutScreen`, `PaymentMethodPicker`, `AgeVerificationModal`, `UpiQrScreen`, `CardPinPadScreen`, `CashTokenScreen`, `OrderReadyScreen`, `AdminKioskPanel`, `MenuAvailabilityEditor`, `PromotionUploader`, `DiagnosticsDashboard`.
- State: Zustand `useKioskStore`, `useKioskMenu` (cached + availability), `useKioskWebSocket`, `useKioskAudio`, `useAccessibility`.
- Locked-down: Kiosk browser (KioWare/SureLock/Android kiosk app) blocks exit; web fallback uses fullscreen API + right-click blocked + admin PIN exit gate.
- Touch UX: tappable elements ≥80×80px, custom tap feedback, swipe cart edit, `navigator.vibrate(50)` haptics.
- WebSocket: Reverb channel `kiosk.{kioskId}` for menu/availability/order status pushes.
- Performance: preload hero images, lazy category grids, GPU-accelerated transforms only.
- PWA: service worker caches menu shell + last 7 days of promo videos; offline queues orders on reconnect.
- Admin panel: separate React app at `/admin/kiosks`, role-gated; multipart video upload with progress; `recharts` for diagnostics.
- i18n: `react-i18next` JSON locale files (en, hi, ta, te, bn, mr); `Intl.NumberFormat('en-IN')` for currency.

## Subtasks
1. [ ] Create migrations for `kiosks`, `kiosk_orders`, `kiosk_promotions`, `kiosk_availability_overrides`, `kiosk_diagnostics`
2. [ ] Implement `KioskAuthService` (device_id + api_key handshake, JWT) and `KioskController` (registration, heartbeat, status)
3. [ ] Build `KioskMenuController` returning simplified menu payload and `KioskAvailabilityService` for time-bound overrides
4. [ ] Implement `KioskOrderService` (order + KOT + daily-rotating token) and `KioskOrderController`
5. [ ] Build `KioskPaymentService` integrating Razorpay/PayU for UPI dynamic QR
6. [ ] Implement card payment via PIN pad SDK callback + webhook and cash token (held order) flow
7. [ ] Build `KioskThermalPrintService` with ESC/POS + PDF fallback for receipt printing
8. [ ] Implement `KioskPromotionService` with time/day/priority selection and admin upload endpoint
9. [ ] Build `AdminKioskController` and `AdminBulkKioskController` for remote content management
10. [ ] Set up WebSocket channels `kiosk.{id}` for menu/availability/promotion/order pushes
11. [ ] Build React `IdleAttractLoop` with video carousel + Today's Specials tiles
12. [ ] Build React `WelcomeScreen` with language picker and `MenuBrowse` with touch-optimized UI
13. [ ] Build React `CartScreen` with upsell carousel and GST breakdown
14. [ ] Build React payment flow screens (UPI QR, Card, Cash token) and `OrderReadyScreen`
15. [ ] Build React `AdminKioskPanel` with menu/promo/diagnostics management
16. [ ] Implement kiosk locked-down mode (fullscreen, no chrome, admin PIN exit) and proximity/wake
17. [ ] Set up multi-language i18n with English, Hindi, and one regional language
18. [ ] Integrate thermal printer driver for ESC/POS via WebUSB or network printer
19. [ ] Implement offline fallback (cached menu + order queue) and reconnect sync
20. [ ] Write unit + integration tests for menu, payment flows, token generation, availability overrides, admin bulk updates

## Testing Criteria
- [ ] Kiosk boots into idle/attract loop with promo videos playing; touch transitions to Welcome screen with persistent language
- [ ] Menu browse loads within 2 seconds; 86'd items are greyed out and live override propagates within 2 seconds via WebSocket
- [ ] Item with mandatory modifier blocks "Add to Cart" until selection; age verification modal blocks restricted items on decline
- [ ] Cart shows correct subtotal, CGST (2.5%), SGST (2.5%), grand total in Indian ₹ format
- [ ] UPI QR generates correctly per transaction; payment confirmation triggers receipt print; 5-min timeout cancels order
- [ ] Card payment flow handles PIN pad callback (success/failure); cash token holds order until counter confirms
- [ ] Receipt prints with correct order #, items, GST, payment summary, token; KOT appears on relevant KDS station within 1 second
- [ ] Order Ready screen shows token for 60 seconds then auto-returns to idle
- [ ] Admin panel 86 update pushes to all selected kiosks via WebSocket within 2 seconds; promo upload appears on idle loop after refresh
- [ ] All kiosk orders recorded with kiosk_id, payment details, age_verified flag; diagnostic aggregates (uptime, txn count, payment success rate) are correct