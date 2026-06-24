# RMS-055: Offline Mode and Sync Engine (PWA)

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-055 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-001 (Auth), RMS-008 (POS Order Capture), RMS-013 (Inventory), RMS-049 (i18n) |

## User Story
As a restaurant POS operator in a Tier-2/3 Indian city where internet drops multiple times a day, I want the POS to keep working seamlessly during network outages -- capturing orders, accepting payments (queued), printing KOTs, deducting inventory -- and automatically sync to the server when connectivity returns, with a clear offline indicator and zero data loss, so that service never stops and reconciliation is clean.

## Description
Real Indian restaurant operations suffer frequent internet disruptions: ISP outages, switch reboots, fiber cuts during monsoon, village-area 4G dead zones. A POS that fails on network loss is unusable -- it forces staff to handwrite orders, lose tickets, and creates reconciliation nightmares. This story delivers a production-grade offline-first PWA so the POS, KDS, and basic admin continue to work fully offline.

The frontend is a Progressive Web App (PWA) with a Service Worker that caches the application shell, all static assets, and the last-known state of menu, inventory, customers, and active orders. Local data is stored in IndexedDB via Dexie.js (a friendly wrapper) with a normalized schema mirroring the server. The Service Worker uses Workbox for precaching and runtime caching strategies (StaleWhileRevalidate for menu, NetworkFirst for live data, CacheFirst for assets).

A mutation queue (`outbox`) records every create/update/delete operation while offline. Each queued mutation carries: idempotency key (UUID), entity, action, payload, timestamp, attempts, last error, conflict status. The Background Sync API (with polling fallback for browsers without SW Background Sync) drains the queue when `navigator.onLine` flips to true, sending in priority order: orders > inventory > menu > analytics. Each successful sync is removed from the queue; failures are retried with exponential backoff.

Conflict resolution follows a tiered strategy: (1) For orders and payments, last-write-wins is too dangerous -- we use a server-side idempotency check that detects duplicate order numbers and merges payments by transaction ID. (2) For inventory, the server reconciles via FIFO consumption log; offline deductions reapply on reconnect and conflicts are reported for manual merge. (3) For menu/admin edits, last-write-wins with manual merge UI for the loser.

A persistent Offline Indicator UI shows connectivity state, pending mutation count, last sync time, and a "Force Sync" button. On reconnect, the dashboard surfaces any conflicts needing human attention. The system supports warm-up prefetching (nightly when online) to seed the local cache for the next day.

## Acceptance Criteria
- [ ] PWA installable on iOS/Android with proper manifest, icons, splash screen.
- [ ] Service Worker caches app shell; app launches fully offline from cold start.
- [ ] IndexedDB via Dexie.js stores menu, inventory, customers, active orders, users, settings.
- [ ] POS can capture dine-in, takeaway, and delivery orders fully offline.
- [ ] KDS receives orders locally (via BroadcastChannel + service worker) and prints/queues KOT.
- [ ] Payment recorded offline with `is_offline=true` flag; marked `is_synced=false` until reconciliation.
- [ ] Outbox queue persists mutations across browser refreshes and crashes.
- [ ] Background Sync API drains outbox on `online` event; polling fallback every 30s for unsupported browsers.
- [ ] Sync priority: orders > inventory > menu > analytics; configurable per environment.
- [ ] Conflict resolution: orders/payments use idempotency (no duplicates); inventory uses server reconciliation + manual merge UI; menu uses last-write-wins with conflict report.
- [ ] Offline Indicator UI shows: connection state, pending count, last sync time, force sync button.
- [ ] Manual merge UI surfaces conflicts for manager review before overwrite.
- [ ] Nightly warm-up prefetch when online refreshes the local cache.
- [ ] Storage quota monitoring warns at 80% and 95% of IndexedDB quota.
- [ ] Lighthouse PWA score > 90; installable; offline-ready.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Offline Indicator | Global (top bar) | Connectivity + queue status |
| Sync Status Detail | /offline/sync-status | Full outbox view with per-item status |
| Conflict Resolution | /offline/conflicts | Manual merge UI for conflicts |
| Offline Storage | /offline/storage | IndexedDB usage, cache controls |
| Connectivity Diagnostics | /offline/diagnostics | Latency, last sync, queue health |

### Screen Details

**Offline Indicator (Global Component)**
- Position: Top-right, persistent across all routes.
- States: Online (green dot, last sync time); Offline (red dot, "Working offline, N pending"); Syncing (yellow spinner, "Syncing N items"); Partial Sync (orange, "M of N synced, K failed").
- Click: Opens a popover with quick stats and a "Force Sync" / "View Details" button.
- Badge: Pending count next to dot when > 0.

**Sync Status Detail (/offline/sync-status)**
- Top stats: Total pending, Failed, Synced today, Avg sync time, Last successful sync.
- Tabs: Pending, Synced, Failed, Conflicts.
- Table: Timestamp | Entity | Action | Status | Attempts | Last Error | Sync Priority.
- Filters: Entity type, Date range.
- Bulk actions: "Retry all failed", "Cancel pending", "Export diagnostics".
- Per-row actions: "Retry now", "View payload", "Discard (with confirmation)".

**Conflict Resolution (/offline/conflicts)**
- Layout: List of conflicts with side-by-side detail.
- Table: Entity | Field | Local Value (offline edit) | Server Value (current) | Detected At | Action.
- Action buttons per row: "Keep Local", "Keep Server", "Merge Manually" (opens field editor).
- Bulk: "Accept All Server", "Accept All Local" (with confirmation).
- Right panel: Entity history showing all changes.

**Offline Storage (/offline/storage)**
- Storage usage: Visual gauge (used / quota) with breakdown by table.
- Per-table row: Name | Record count | Last refresh | Size.
- Controls: "Clear cache", "Refresh now", "Warm-up schedule settings".
- Export: "Download offline snapshot (for support)".

**Connectivity Diagnostics (/offline/diagnostics)**
- Latency test: Pings server every 5s and shows ms graph.
- Connection info: navigator.onLine, effectiveType, downlink, rtt.
- Last sync timestamp per entity type.
- Service worker status: registered, active, version.
- Force actions: "Re-register service worker", "Clear all and re-sync".

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/sync/batch | Batch sync of multiple offline mutations (idempotent) |
| GET | /api/v1/sync/changes | Pull server changes since `last_sync_at` (delta sync) |
| GET | /api/v1/sync/snapshot | Full data snapshot for cold-start (paginated) |
| POST | /api/v1/sync/heartbeat | Client liveness signal |
| GET | /api/v1/sync/conflicts | List unresolved conflicts |
| POST | /api/v1/sync/conflicts/{id}/resolve | Resolve conflict (local/server/manual) |
| GET | /api/v1/sync/status | Server-side sync health |
| GET | /api/v1/sync/diagnostics | Per-client diagnostics (admin) |
| POST | /api/v1/sync/warmup-trigger | Manually trigger cache warm-up for a client |
| GET | /api/v1/sync/menu-snapshot | Menu+inventory+customers snapshot for prefetch |

## Database Tables

**sync_clients**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets)
- device_id (string, unique) -- generated client-side
- device_name (string, nullable)
- user_agent (text, nullable)
- app_version (string)
- last_seen_at (timestamp)
- last_sync_at (timestamp, nullable)
- last_heartbeat_at (timestamp, nullable)
- pending_mutations_count (unsignedInteger, default 0)
- failed_mutations_count (unsignedInteger, default 0)
- conflict_count (unsignedInteger, default 0)
- ip_address (string(45), nullable)
- timestamps
- unique([outlet_id, device_id])

**sync_mutations_log** (server-side audit of accepted mutations)
- id (bigIncrements, PK)
- sync_client_id (foreignId, sync_clients)
- idempotency_key (string(36), unique)
- entity_type (string) -- `App\Models\Order`, `App\Models\Payment`, etc.
- entity_id (unsignedBigInteger, nullable) -- set after apply
- action (enum: create, update, delete)
- payload_json (json)
- response_json (json, nullable)
- applied_at (timestamp)
- offline_created_at (timestamp, nullable)
- duration_ms (unsignedInteger, nullable)
- created_at
- index([sync_client_id, applied_at])
- index([entity_type, entity_id])

**sync_conflicts**
- id (bigIncrements, PK)
- sync_client_id (foreignId, sync_clients)
- entity_type (string)
- entity_id (unsignedBigInteger)
- field (string, nullable) -- null for whole-record conflicts
- local_value (text, nullable)
- server_value (text, nullable)
- local_updated_at (timestamp, nullable)
- server_updated_at (timestamp, nullable)
- resolution (enum: local_kept, server_kept, manual_merged, dismissed, default pending)
- resolved_by (foreignId, users, nullable)
- resolved_at (timestamp, nullable)
- detected_at (timestamp)
- index([resolution, detected_at])
- index([entity_type, entity_id])

**sync_warmup_schedules**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets, unique)
- enabled (boolean, default true)
- run_at (time, default '02:00:00')
- include_tables_json (json) -- array of tables to prefetch
- last_run_at (timestamp, nullable)
- last_status (enum: success, failed, partial, nullable)
- created_at, updated_at

**sync_server_changes** (delta tracking, optional)
- id (bigIncrements, PK)
- entity_type (string, indexed)
- entity_id (unsignedBigInteger, indexed)
- change_type (enum: create, update, delete)
- changed_at (timestamp, indexed)
- changed_by (foreignId, users, nullable)
- payload_hash (string(64), nullable) -- for diff
- outlet_id (foreignId, outlets, nullable)
- index([changed_at, entity_type])

## Technical Notes

**Laravel Backend:**
- `SyncService::processBatch($clientId, $mutations)` accepts a batch of offline mutations; for each: check idempotency key, apply via service layer, write to `sync_mutations_log`. Idempotency keys stored in Redis with 48h TTL for fast dup detection.
- Conflict detection: when applying a mutation, if entity's `updated_at` > mutation's `local_updated_at` (with grace 5s), create `sync_conflicts` row and skip apply. Caller can poll and resolve.
- Delta sync endpoint `/sync/changes?since={ts}` returns changes from `sync_server_changes` (or directly from entity `updated_at` index) paginated.
- Snapshot endpoint `/sync/snapshot` paginates all bootstrap data (menu, customers, settings) for cold-start.
- Heartbeat endpoint updates `sync_clients.last_heartbeat_at`; a daily cron flags clients with no heartbeat >24h.
- Eloquent observers on key entities (Order, Payment, Inventory, MenuItem) write to `sync_server_changes` to support delta sync.
- All sync mutations go through normal API authorization (Sanctum + RBAC). The `device_id` is bound to the token.
- Outbox endpoint `/sync/batch` is rate-limited (per device) to prevent runaway queues.

**React Frontend (PWA):**
- Vite + Workbox plugin generates service worker with precache manifest for app shell and assets.
- Dexie schema mirrors server: `db.menuItems`, `db.menuCategories`, `db.orders`, `db.payments`, `db.customers`, `db.inventoryItems`, `db.outbox` (id, entity, action, payload, idempotencyKey, attempts, lastError, status, priority, createdAt).
- Outbox drain: on `online` event + polling fallback (30s) + manual trigger. Sends in priority order (orders=1, payments=1, inventory=2, menu=3, analytics=4).
- Mutation flow: any write goes through `useOfflineMutation` hook that (a) optimistically updates local store, (b) enqueues in outbox, (c) returns optimistic result. If online, attempts immediate send.
- KDS uses BroadcastChannel for in-browser cross-tab updates; SW Background Sync for service-worker-initiated pushes.
- PWA install prompt handled via `beforeinstallprompt`.
- Connectivity detection: `navigator.onLine` + heartbeat ping every 30s (configurable).
- Conflict merge UI uses side-by-side diff editor with per-field radio.
- Storage quota: `navigator.storage.estimate()` polled; warn UI at 80% and 95%.
- Warm-up: scheduled via Workbox Background Sync registered from main thread; or initiated by server push (e.g., scheduled task via Web Push).

## Subtasks
1. [ ] Configure PWA manifest, icons, splash, install prompt
2. [ ] Set up Workbox precache + runtime caching strategies
3. [ ] Build Dexie schema covering menu, inventory, customers, orders, payments
4. [ ] Build outbox queue with priority, attempts, idempotency, persistence
5. [ ] Build `useOfflineMutation` hook with optimistic updates
6. [ ] Build outbox drainer with online event + polling fallback
7. [ ] Implement BroadcastChannel for cross-tab KDS updates
8. [ ] Build backend `SyncService::processBatch` with idempotency check
9. [ ] Build `/sync/changes` delta endpoint using `sync_server_changes` table
10. [ ] Build `/sync/snapshot` cold-start endpoint
11. [ ] Build `/sync/conflicts` endpoints and `sync_conflicts` model
12. [ ] Build sync_clients tracking with heartbeat cron
13. [ ] Build Offline Indicator global component
14. [ ] Build Sync Status Detail UI with filters and bulk actions
15. [ ] Build Conflict Resolution UI with side-by-side merge
16. [ ] Build Offline Storage diagnostics UI
17. [ ] Implement warm-up scheduled task
18. [ ] Add Lighthouse CI gating PWA score > 90

## Testing Criteria
- [ ] POS captures order fully offline; outbox grows; refresh preserves queue
- [ ] Reconnect drains outbox in priority order; orders sync before menu edits
- [ ] Duplicate mutation (same idempotency key) is rejected with 200 + original entity, not duplicated
- [ ] Conflict on inventory detected and surfaced in Conflict Resolution UI
- [ ] KDS receives order locally within 1s across tabs via BroadcastChannel
- [ ] Cold-start offline launches full app from cache
- [ ] Lighthouse PWA score > 90 across Performance, Accessibility, Best Practices, SEO, PWA
- [ ] IndexedDB quota warning fires at 80% and 95%
- [ ] Heartbeat detected within 60s of online; admin sees live device list
- [ ] Manual merge of inventory conflict keeps both adjustments with audit trail
- [ ] Warm-up prefetch on schedule refreshes local cache for next-day boot
- [ ] Service worker re-registers cleanly on app version upgrade
