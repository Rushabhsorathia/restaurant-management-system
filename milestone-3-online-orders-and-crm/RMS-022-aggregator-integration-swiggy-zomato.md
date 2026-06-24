# RMS-022: Aggregator Integration - Swiggy & Zomato

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-022 |
| **Type** | Story |
| **Epic** | Online Ordering & Aggregators |
| **Milestone** | Milestone 3 - Online Orders & CRM |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-010 (Menu Management), RMS-014 (Billing & Payment) |

## User Story
As a restaurant owner, I want to integrate Swiggy and Zomato aggregator APIs into my POS system, so that online orders flow directly into my kitchen and billing system without manual re-entry.

## Description
This story implements bidirectional integration with the two dominant food delivery aggregators in India: Swiggy and Zomato. The integration pulls incoming orders in real time using both push-based webhooks (where supported) and pull-based polling fallback, normalizes the order payloads from both platforms into a unified internal schema, and pushes them into the kitchen queue and billing module automatically. Restaurant operators should never have to re-key an online order.

A second major capability is menu synchronization. The POS is the source of truth for menu items, prices, availability, and images. Changes made in the POS menu module must propagate to Swiggy and Zomato catalogs via their respective Menu Push APIs. This includes item create/update/delete, price changes, out-of-stock toggling, and image uploads. Conflict resolution and throttling must be handled since both aggregators rate-limit these endpoints.

The third capability is order lifecycle management: accept/reject within the SLA timer enforced by each aggregator, status sync (accepted, preparing, ready, dispatched, delivered, cancelled), and rejection reason capture. All aggregator interactions must be logged with full request/response payloads for audit and debugging. A commission tracking layer records the commission amount per order for downstream reconciliation (detailed in RMS-031).

## Acceptance Criteria
- [ ] Restaurant admin can configure Swiggy and Zomato API credentials (client_id, client_secret, restaurant_id on each platform) under Settings > Integrations.
- [ ] OAuth 2.0 authorization flow completes successfully for both Swiggy and Zomato; tokens are stored encrypted in DB and auto-refreshed before expiry.
- [ ] Incoming orders from both platforms are polled every 15 seconds (configurable) and ingested into the unified `online_orders` table within 30 seconds of placement.
- [ ] New orders trigger a real-time push to the Online Order Dashboard (RMS-023) via Laravel Reverb WebSocket.
- [ ] Admin can accept or reject an order; acceptance/rejection is propagated back to the respective aggregator API within the SLA window shown on screen.
- [ ] Order status changes in the POS (Preparing, Ready, Dispatched) are synced back to the originating aggregator in real time.
- [ ] Menu push from POS to both aggregators succeeds for item create, update, price change, availability toggle, and delete; rate-limit headers are respected with exponential backoff retry.
- [ ] All API request/response payloads are logged in `aggregator_api_logs` for at least 90 days with filtering by platform, status, and date range.
- [ ] Connection health indicator (Connected / Token Expired / Rate Limited / Error) is visible in the integrations panel and triggers an alert on failure.
- [ ] If an aggregator API is unreachable, the system queues the sync action in Redis and retries with exponential backoff up to 5 attempts.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Integrations Settings | /settings/integrations | Configure Swiggy/Zomato credentials and view connection status |
| Menu Sync Status | /settings/integrations/menu-sync | View per-item sync status across aggregators |
| API Logs Viewer | /settings/integrations/logs | Searchable log of all API request/response payloads |

### Screen Details

**Integrations Settings (/settings/integrations)**
- Layout: Two-column card layout, one card per aggregator (Swiggy, Zomato). Each card shows platform logo, connection status badge (green/amber/red), and last sync timestamp.
- Components: `IntegrationCard`, `OAuthConnectButton`, `StatusBadge`, `CredentialForm`.
- Swiggy Card form fields:
  - Client ID (text input, required)
  - Client Secret (password input, required)
  - Restaurant ID on Swiggy (text input, required)
  - Webhook Secret (text input, for webhook signature verification)
  - Polling Interval (number input, default 15 seconds)
  - Auto-Accept Orders (toggle switch)
- Zomato Card form fields:
  - Client ID (text input, required)
  - Client Secret (password input, required)
  - Restaurant ID (text input, required)
  - API Key (password input)
  - Polling Interval (number input, default 15 seconds)
  - Auto-Accept Orders (toggle switch)
- Buttons: "Connect via OAuth" (opens redirect flow), "Save Credentials", "Test Connection", "Disconnect".
- Validation: Client ID/Secret required before Save; Test Connection makes a live API call and shows success/failure toast.

**Menu Sync Status (/settings/integrations/menu-sync)**
- Layout: Data table listing every POS menu item with sync status per aggregator.
- Table columns: Item Name | POS Price | Swiggy Price | Swiggy Status (Synced/Pending/Error) | Zomato Price | Zomato Status | Last Synced At | Actions.
- Buttons: "Sync All to Swiggy", "Sync All to Zomato", per-row "Push" button, "View Error" link.
- Modal: "Push Confirmation Modal" showing items to be pushed with price diff summary before confirmation.
- Validation: Warn if POS price differs from aggregator price before push.

**API Logs Viewer (/settings/integrations/logs)**
- Layout: Full-width filterable data table with expandable rows.
- Filters: Platform (Swiggy/Zomato/All), Status (Success/Error/All), Date Range picker, Endpoint search.
- Table columns: Timestamp | Platform | Endpoint | Method | HTTP Status | Duration (ms) | Actions.
- Row expansion: Full request headers, request body, response headers, response body in JSON viewer.
- Buttons: "Export CSV", "Clear Logs Older Than 90 Days".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/integrations | List all configured integrations and statuses |
| POST | /api/v1/integrations/{platform}/credentials | Save API credentials for Swiggy or Zomato |
| DELETE | /api/v1/integrations/{platform}/credentials | Remove credentials and disconnect |
| POST | /api/v1/integrations/{platform}/oauth/connect | Initiate OAuth redirect flow |
| GET | /api/v1/integrations/{platform}/oauth/callback | Handle OAuth callback and store tokens |
| POST | /api/v1/integrations/{platform}/test | Test live API connection |
| GET | /api/v1/integrations/{platform}/menu-sync-status | Get per-item menu sync status |
| POST | /api/v1/integrations/{platform}/menu-push | Push menu items to aggregator |
| POST | /api/v1/integrations/{platform}/menu-push/{itemId} | Push single item to aggregator |
| POST | /api/v1/integrations/swiggy/webhook | Webhook receiver for Swiggy push events |
| POST | /api/v1/integrations/zomato/webhook | Webhook receiver for Zomato push events |
| GET | /api/v1/integrations/logs | List API logs with filters |
| POST | /api/v1/integrations/{platform}/orders/{orderId}/accept | Accept order and notify aggregator |
| POST | /api/v1/integrations/{platform}/orders/{orderId}/reject | Reject order with reason and notify aggregator |
| PUT | /api/v1/integrations/{platform}/orders/{orderId}/status | Update order status and sync to aggregator |

## Database Tables

**aggregator_integrations**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- platform (enum: swiggy, zomato)
- client_id (string, nullable)
- client_secret (string, encrypted, nullable)
- api_key (string, encrypted, nullable)
- restaurant_external_id (string, the aggregator's restaurant ID)
- webhook_secret (string, nullable)
- access_token (string, encrypted, nullable)
- refresh_token (string, encrypted, nullable)
- token_expires_at (timestamp, nullable)
- polling_interval (unsignedInteger, default 15)
- auto_accept (boolean, default false)
- status (enum: connected, disconnected, token_expired, rate_limited, error)
- last_synced_at (timestamp, nullable)
- settings (json, platform-specific config)
- timestamps, softDeletes

**online_orders**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- aggregator_integration_id (foreignId, aggregator_integrations)
- platform_order_id (string, order ID on aggregator platform)
- platform (enum: swiggy, zomato)
- customer_name (string)
- customer_phone (string, nullable)
- delivery_address (text, nullable)
- delivery_lat (decimal(10,7), nullable)
- delivery_lng (decimal(10,7), nullable)
- items_json (json, normalized line items)
- subtotal (decimal(10,2))
- discount (decimal(10,2), default 0)
- packaging_charge (decimal(10,2), default 0)
- delivery_charge (decimal(10,2), default 0)
- tax (decimal(10,2), default 0)
- commission_amount (decimal(10,2), default 0)
- total (decimal(10,2))
- status (enum: pending, accepted, rejected, preparing, ready, dispatched, delivered, cancelled)
- rejection_reason (string, nullable)
- order_placed_at (timestamp)
- order_accepted_at (timestamp, nullable)
- accepted_within_sec (unsignedInteger, nullable)
- raw_payload (json, full original payload from aggregator)
- timestamps, softDeletes

**aggregator_api_logs**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- platform (enum: swiggy, zomato)
- endpoint (string)
- method (enum: GET, POST, PUT, DELETE, PATCH)
- request_headers (json)
- request_body (json, nullable)
- response_status (unsignedSmallInteger, nullable)
- response_body (json, nullable)
- duration_ms (unsignedInteger, nullable)
- error_message (text, nullable)
- timestamps

**menu_sync_status**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- menu_item_id (foreignId, menu_items)
- platform (enum: swiggy, zomato)
- external_item_id (string, nullable)
- sync_status (enum: synced, pending, error, not_pushed)
- last_synced_at (timestamp, nullable)
- error_message (text, nullable)
- timestamps
- unique([menu_item_id, platform])

## Technical Notes

**Laravel Backend:**
- Controllers: `IntegrationController` (index, credentials, oauthConnect, oauthCallback, testConnection), `MenuSyncController` (status, push, pushItem), `AggregatorWebhookController` (swiggy, zomato), `AggregatorOrderController` (accept, reject, updateStatus), `ApiLogController` (index, export).
- Service layer: `SwiggyApiService` and `ZomatoApiService` implementing a common `AggregatorApiServiceInterface` with methods: `fetchNewOrders()`, `acceptOrder($platformOrderId)`, `rejectOrder($platformOrderId, $reason)`, `updateStatus($platformOrderId, $status)`, `pushMenu(array $items)`, `pushSingleItem($item)`, `refreshToken()`.
- Order normalization: `OrderNormalizer` class maps Swiggy/Zomato JSON payloads to unified `online_orders` schema. Map via config file `config/aggregators.php` containing field mappings per platform.
- Polling: Laravel scheduled command `app:PollAggregatorOrders` runs every minute, dispatches `PollPlatformOrdersJob` per connected integration to a Redis queue. Job calls the service's `fetchNewOrders()`, normalizes, and stores. Auto-accept if configured.
- Webhooks: Routes `POST /api/v1/integrations/swiggy/webhook` and `/zomato/webhook` verify HMAC signature using stored webhook_secret, then dispatch `ProcessIncomingOrderJob`.
- Token refresh: `RefreshAggregatorTokenJob` checks `token_expires_at` and refreshes 5 minutes before expiry.
- Retry: Failed API calls use Laravel's `try/catch` with exponential backoff via `retry(5, fn() => ..., 2000)` helper.
- Encryption: Use Laravel `Crypt::encryptString()` for all credential/token columns.
- Events: `OrderReceivedFromAggregator`, `OrderAccepted`, `OrderRejected`, `OrderStatusSynced` broadcast via Reverb.
- Routes defined in `routes/api.php` under `middleware(['auth:sanctum', 'restaurant.scope'])`.

**React Frontend:**
- Components: `IntegrationSettingsPage`, `IntegrationCard`, `OAuthConnectButton`, `StatusBadge`, `MenuSyncTable`, `MenuSyncRow`, `PushConfirmationModal`, `ApiLogsViewer`, `ApiLogDetailExpansion`, `CredentialForm`.
- WebSocket: Listen to `aggregator.order-received` channel via `useEffect` with Reverb Echo to refresh dashboard in real time.
- State: Zustand store `useIntegrationStore` for credentials and statuses.
- API client: Axios instance with interceptors for auth token and restaurant scope header.

**Redis:**
- Queue: `aggregator-polling`, `aggregator-webhooks`, `aggregator-menu-push`.
- Rate-limit tracking keys: `aggregator:{platform}:rate-limit:{window}`.

## Subtasks
1. [ ] Create `aggregator_integrations`, `online_orders`, `aggregator_api_logs`, `menu_sync_status` migrations and models
2. [ ] Implement `AggregatorApiServiceInterface` and `SwiggyApiService` with OAuth, polling, order accept/reject/status, menu push
3. [ ] Implement `ZomatoApiService` with OAuth, polling, order accept/reject/status, menu push
4. [ ] Build `OrderNormalizer` and `config/aggregators.php` field mapping config
5. [ ] Create `app:PollAggregatorOrders` scheduled command and `PollPlatformOrdersJob`
6. [ ] Implement webhook receivers with HMAC signature verification
7. [ ] Build `RefreshAggregatorTokenJob` and `ProcessIncomingOrderJob`
8. [ ] Create IntegrationController and routes for credential management and OAuth flow
9. [ ] Create MenuSyncController and menu push endpoints with rate-limit handling
10. [ ] Build AggregatorOrderController for accept/reject/status sync
11. [ ] Implement ApiLogController with filtering and CSV export
12. [ ] Build React IntegrationSettingsPage with credential forms and OAuth redirect
13. [ ] Build React MenuSyncTable with per-item push and error display
14. [ ] Build React ApiLogsViewer with expandable JSON rows and filters
15. [ ] Write integration tests mocking Swiggy/Zomato API responses
16. [ ] Add connection health monitoring and failure alerting

## Testing Criteria
- [ ] OAuth flow completes for both Swiggy and Zomato mock servers and tokens are stored encrypted
- [ ] Polling ingests a mock order and creates an `online_orders` record within 30 seconds
- [ ] Webhook receiver rejects requests with invalid HMAC signature
- [ ] Accept order call propagates to mock aggregator and updates status
- [ ] Reject order with reason propagates correctly
- [ ] Menu push creates/updates items on mock aggregator and updates `menu_sync_status`
- [ ] Rate-limited response triggers exponential backoff retry without data loss
- [ ] Token auto-refresh triggers 5 minutes before expiry
- [ ] API logs capture full request/response for every outbound call
- [ ] Connection status badge updates correctly on token expiry, rate limit, and error states
