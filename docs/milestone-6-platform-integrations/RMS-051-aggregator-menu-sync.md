# RMS-051: Aggregator Menu Sync (Swiggy, Zomato, magicpin)

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-051 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | High |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-006 (Menu Management), RMS-022 (Aggregator Integration), RMS-049 (i18n) |

## User Story
As a restaurant manager, I want a one-click "Push Menu to All Aggregators" action from the POS menu editor that propagates item create/update/delete, price changes, stock-out toggles, and images to Swiggy, Zomato, and magicpin within 30 seconds, with a sync log I can audit, so that I never have to log in to three different portals to keep my listings accurate.

## Description
Today, every menu change in the RMS -- a new dish, a price bump, marking paneer tikka as 86'd, a category rename -- must be replicated manually on Swiggy's restaurant dashboard, Zomato's partner portal, and magicpin's Merchant Hub. Three logins, three different category taxonomies, three different image size rules, and zero feedback when something fails silently. This causes customer complaints ("ordered on Swiggy, restaurant says it's unavailable") and lost revenue.

This story delivers unified menu push. A new `AggregatorMenuSyncService` translates the canonical `menu_items` table into each platform's format using per-platform `Adapter` classes (`SwiggyAdapter`, `ZomatoAdapter`, `MagicpinAdapter`). Each adapter knows the partner's API contract: Swiggy's catalogue accepts nested categories with `category_id` and `item_id` integers; Zomato uses a 3-level category hierarchy; magicpin requires S3-hosted image URLs. Images are uploaded to each platform's media API and the resulting URL stored back on `menu_item_aggregator_mappings`.

Sync triggers: (1) Manual "Push to Aggregator" button in menu editor; (2) Bulk "Push All" with checkboxes; (3) Auto-sync on `menu_items` save with debounce (5 minutes) and a per-outlet setting; (4) Scheduled nightly re-sync to catch missed changes. Each push generates a `menu_sync_jobs` row queued via Redis, processed by `ProcessMenuSyncJob` with exponential backoff.

The category mapping module lets the manager map RMS categories to each platform's required taxonomy once and reuses. Conflict resolution handles cases where the platform has the item with different fields: the manager chooses `RMS_wins` or `Platform_wins` per field. Stock-out is a fast path -- a toggle on the POS item immediately queues a PATCH to mark it out-of-stock on all platforms within 10s.

The sync log UI shows every push attempt with timestamp, target, item, action, request/response payload, and outcome. Failed items can be retried individually. Webhooks from each platform (catalog change notifications, de-listings) are ingested and surfaced in the audit log.

## Acceptance Criteria
- [ ] One-click "Push to Aggregator(s)" button on menu item edit screen with checkbox list of connected platforms.
- [ ] Bulk "Push All Changed" action on menu list page pushes only items modified since last sync.
- [ ] Item create, update, delete, price change, stock-out toggle propagate to selected platforms within 30 seconds.
- [ ] Per-platform adapter classes translate canonical menu to platform schema (Swiggy, Zomato, magicpin).
- [ ] Image upload to each platform's media API; resulting URL stored on `menu_item_aggregator_mappings`.
- [ ] Category mapping UI: map RMS category to platform category ID/name once, reused thereafter.
- [ ] Conflict resolution: per-field strategy (RMS wins / Platform wins / Manual) with merge preview.
- [ ] Sync log: every push attempt recorded with actor, target platform, item, action, request, response, HTTP status, duration.
- [ ] Failed items can be retried individually from the sync log UI.
- [ ] Auto-sync on menu save (debounced 5min) is configurable per outlet, default on.
- [ ] Scheduled nightly full re-sync cron at 02:00 outlet-local time.
- [ ] Inbound webhooks from each platform de-list and update events are ingested and logged.
- [ ] Stock-out toggle is fastest path (under 10s) with separate queue priority.
- [ ] Rate limiting per platform adapter (Swiggy 60 req/min, Zomato 30 req/min, magicpin 100 req/min) respected.
- [ ] Connection setup wizard: enter API key, OAuth flow, test connection, map categories, push once.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Aggregator Connections | /integrations/aggregators | Manage Swiggy/Zomato/magicpin credentials |
| Menu Item Edit (Sync Tab) | /menu/items/{id}#aggregators | Per-item push controls |
| Category Mapping | /integrations/aggregators/category-mapping | Map RMS categories to platform categories |
| Sync Log | /integrations/aggregators/sync-log | Filterable audit log of all push attempts |
| Conflict Resolution | /integrations/aggregators/conflicts | Review and resolve field conflicts |

### Screen Details

**Aggregator Connections (/integrations/aggregators)**
- Cards: One per platform (Swiggy, Zomato, magicpin) showing connection status, last sync, next sync, error count, total items synced.
- Each card has: Status badge (Connected/Disconnected/Error), "Connect" / "Reconnect" button, "Test Connection" button, "View Logs" link.
- Connection wizard (modal): 1) Enter credentials or OAuth flow, 2) Test connection, 3) Map categories, 4) Push initial menu, 5) Confirm.
- Top: "Push All" button (with last-push timestamp) and platform multi-select.

**Menu Item Edit (Sync Tab)**
- Tab inside existing item editor: "Aggregator Sync".
- Section per connected platform: status (In Sync / Pending / Failed / Never Pushed), last sync timestamp, platform item ID, link to view on platform.
- Buttons: "Push Now" (per platform), "Push to All", "Mark Out of Stock on All", "Mark In Stock on All".
- Mapping fields: Platform category (dropdown from mappings), Platform item name override, Platform description override, Platform price override, Platform tax code, Platform image override.
- Conflict alerts (red banner): "Platform price differs from RMS by ₹20. Click to resolve."

**Category Mapping (/integrations/aggregators/category-mapping)**
- Table: RMS Category | Swiggy Category | Zomato Category | magicpin Category | Status.
- Each cell: dropdown filtered by platform's category list (loaded via API) with search.
- "Fetch Platform Categories" button per platform refreshes the dropdown.
- "Auto-map by name" button: best-effort string match (case-insensitive, prefix).
- Bulk action: "Apply mapping to all items in this category".
- Save button commits all mappings.

**Sync Log (/integrations/aggregators/sync-log)**
- Filter bar: Platform (multi), Action (created/updated/deleted/stock-out/image), Status (success/failed/pending), Date Range, Item search, Outlet.
- Table columns: Timestamp | Platform | Action | Item | Status | HTTP Code | Duration | Retries.
- Row click: opens side drawer with full request/response JSON, error message, "Retry" button.
- Bulk: "Retry All Failed", "Export CSV".

**Conflict Resolution (/integrations/aggregators/conflicts)**
- Layout: Side-by-side RMS value vs platform value per field with radio "Use RMS / Use Platform / Custom".
- Filters: Platform, Item, Field.
- Resolution queue: conflicts detected by webhook or periodic diff job.
- Buttons: "Resolve", "Resolve All (Use RMS)", "Resolve All (Use Platform)".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/aggregator-connections | List configured platforms per outlet |
| POST | /api/v1/aggregator-connections | Create/update connection (credentials) |
| POST | /api/v1/aggregator-connections/{id}/test | Test API connectivity |
| GET | /api/v1/aggregator-connections/{id}/categories | Fetch platform category list |
| GET | /api/v1/aggregator-category-mappings | List category mappings |
| PUT | /api/v1/aggregator-category-mappings | Bulk update mappings |
| POST | /api/v1/menu-items/{id}/push | Push item to selected platforms |
| POST | /api/v1/menu-items/push-bulk | Bulk push changed items |
| POST | /api/v1/menu-items/{id}/stock-out | Fast stock-out push to all platforms |
| POST | /api/v1/menu-items/{id}/stock-in | Mark in-stock |
| GET | /api/v1/menu-sync-jobs | List sync jobs with filters |
| GET | /api/v1/menu-sync-jobs/{id} | Get job detail with request/response |
| POST | /api/v1/menu-sync-jobs/{id}/retry | Retry failed job |
| GET | /api/v1/aggregator-conflicts | List unresolved conflicts |
| POST | /api/v1/aggregator-conflicts/resolve | Resolve conflict |
| POST | /api/v1/webhooks/aggregators/{platform} | Inbound webhook receiver |
| GET | /api/v1/aggregator-stats | Per-platform sync health metrics |

## Database Tables

**aggregator_connections**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets)
- platform (enum: swiggy, zomato, magicpin)
- display_name (string)
- credentials_encrypted (text) -- encrypted API key/OAuth tokens
- restaurant_id_on_platform (string, nullable)
- status (enum: connected, disconnected, error, suspended)
- last_sync_at (timestamp, nullable)
- last_error (text, nullable)
- rate_limit_per_minute (unsignedInteger, default 60)
- auto_sync_enabled (boolean, default true)
- created_by (foreignId, users)
- timestamps
- unique([outlet_id, platform])

**aggregator_category_mappings**
- id (bigIncrements, PK)
- connection_id (foreignId, aggregator_connections)
- rms_category_id (foreignId, menu_categories)
- platform_category_id (string) -- platform's category ID/name
- platform_category_name (string)
- confidence_score (decimal(3,2), nullable) -- for auto-map
- manually_mapped (boolean, default false)
- timestamps
- unique([connection_id, rms_category_id])

**menu_item_aggregator_mappings**
- id (bigIncrements, PK)
- menu_item_id (foreignId, menu_items)
- connection_id (foreignId, aggregator_connections)
- platform_item_id (string, nullable)
- platform_category_id (string, nullable)
- platform_name_override (string, nullable)
- platform_description_override (text, nullable)
- platform_price_override (decimal(10,2), nullable)
- platform_image_url (string, nullable)
- platform_tax_code (string, nullable)
- is_in_stock (boolean, default true)
- last_pushed_at (timestamp, nullable)
- last_pull_at (timestamp, nullable)
- platform_state_json (json, nullable) -- full platform view
- conflict_strategy (enum: rms_wins, platform_wins, manual, default rms_wins)
- timestamps
- unique([menu_item_id, connection_id])

**menu_sync_jobs**
- id (bigIncrements, PK)
- connection_id (foreignId, aggregator_connections)
- menu_item_id (foreignId, menu_items, nullable)
- action (enum: create, update, delete, stock_out, stock_in, image_upload, bulk_push, category_sync)
- payload_json (json)
- response_json (json, nullable)
- http_status (unsignedSmallInteger, nullable)
- status (enum: pending, processing, success, failed, retrying)
- attempts (unsignedTinyInteger, default 0)
- max_attempts (unsignedTinyInteger, default 5)
- priority (unsignedTinyInteger, default 5) -- 1 highest, stock_out=1
- scheduled_at (timestamp, nullable)
- started_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- error_message (text, nullable)
- duration_ms (unsignedInteger, nullable)
- initiated_by (foreignId, users, nullable)
- is_auto (boolean, default false)
- created_at, updated_at
- index([status, priority, scheduled_at])
- index([connection_id, created_at])

**aggregator_conflicts**
- id (bigIncrements, PK)
- connection_id (foreignId, aggregator_connections)
- menu_item_id (foreignId, menu_items)
- field (string) -- e.g., `price`, `name`, `in_stock`
- rms_value (text, nullable)
- platform_value (text, nullable)
- detected_at (timestamp)
- resolution (enum: rms_wins, platform_wins, custom, pending, default pending)
- custom_value (text, nullable)
- resolved_by (foreignId, users, nullable)
- resolved_at (timestamp, nullable)
- timestamps
- index([resolution, detected_at])

## Technical Notes

**Laravel Backend:**
- Adapter pattern: interface `AggregatorAdapter` with `pushItem(MenuItem $item): SyncResult`, `pushStockOut(int $itemId)`, `uploadImage(UploadedFile $file)`, `fetchCategories()`, `verifyCredentials()`. Concrete: `SwiggyAdapter`, `ZomatoAdapter`, `MagicpinAdapter`.
- `AggregatorMenuSyncService::pushItem($item, $platforms)` iterates adapters, creates `menu_sync_jobs` rows, dispatches `ProcessMenuSyncJob` with appropriate priority.
- `ProcessMenuSyncJob` calls adapter, persists response, handles retry with exponential backoff (1m, 5m, 15m, 1h, 6h). Implements `ShouldQueue`, `RateLimited` per connection.
- Stock-out uses high-priority queue (`aggregator-stock-out`) with shorter backoff.
- Image upload: `ImageUploadService` uploads to platform's media endpoint, returns URL, stores on `menu_item_aggregator_mappings.platform_image_url`.
- Category mapping sync: `SyncCategoryMappingJob` runs after connection; calls `fetchCategories()` and stores for dropdown.
- Inbound webhook controller `AggregatorWebhookController` verifies HMAC signature per platform, parses event, creates conflict records or updates mappings.
- Conflict detection cron (`DetectAggregatorConflicts` hourly) compares RMS state vs platform pull.
- Nightly auto-sync: scheduled command at 02:00 outlet-local.
- Encryption: `credentials_encrypted` uses Laravel's encrypted casts.

**React Frontend:**
- Menu editor gains "Aggregator Sync" tab with per-platform card and push buttons.
- Connections page with card grid; React Query handles long-poll for sync status.
- Sync log uses virtualized table (`react-virtual`) for large logs; filter chips.
- Conflict resolution: side-by-side diff component with field-level radio.
- WebSocket (Laravel Echo + Pusher/Soketi) pushes real-time sync status updates to UI.
- Confirmation modals for destructive pushes (delete propagation).

## Subtasks
1. [ ] Create `aggregator_connections`, `aggregator_category_mappings`, `menu_item_aggregator_mappings`, `menu_sync_jobs`, `aggregator_conflicts` migrations and models
2. [ ] Define `AggregatorAdapter` interface and base response DTO
3. [ ] Implement `SwiggyAdapter` (auth, push item, stock, image, categories)
4. [ ] Implement `ZomatoAdapter` (auth, push item, stock, image, categories)
5. [ ] Implement `MagicpinAdapter` (auth, push item, stock, image, categories)
6. [ ] Build `AggregatorMenuSyncService` orchestrating adapters + job creation
7. [ ] Build `ProcessMenuSyncJob` with retry, backoff, rate limiting
8. [ ] Implement stock-out fast-path queue with priority
9. [ ] Build image upload service to platform media endpoints
10. [ ] Build category mapping UI and auto-suggest endpoint
11. [ ] Build connection setup wizard with OAuth where supported
12. [ ] Implement `AggregatorWebhookController` with HMAC verification
13. [ ] Build conflict detection cron and resolution UI
14. [ ] Build sync log UI with filters, retry, and export
15. [ ] Add WebSocket broadcast on job state changes
16. [ ] Implement auto-sync observer on `menu_items` with debounce
17. [ ] Add scheduled nightly full re-sync command
18. [ ] Write tests for each adapter (using sandbox creds), conflict resolution, retry, rate limit, webhook signature

## Testing Criteria
- [ ] Push to all three platforms from one button completes in under 30s for single item
- [ ] Stock-out toggle reaches all platforms within 10s
- [ ] Image upload succeeds and resulting URL renders on platform
- [ ] Category mapping persists and is applied to all items in that category
- [ ] Failed push creates a `failed` job and surfaces in sync log
- [ ] Retry on a failed job succeeds when platform is back online
- [ ] Rate limiter pauses dispatch when platform quota exhausted, resumes after window
- [ ] Inbound webhook with valid HMAC updates mapping; invalid HMAC returns 401
- [ ] Conflict resolution with `rms_wins` propagates corrected value on next push
- [ ] Nightly auto-sync pushes all modified items in batches
- [ ] Credentials are encrypted at rest and never appear in logs
- [ ] Bulk push of 100 items queues and processes without timeout
