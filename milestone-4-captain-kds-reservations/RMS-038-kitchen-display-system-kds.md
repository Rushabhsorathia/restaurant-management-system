# RMS-038: Kitchen Display System (KDS)

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-038 |
| **Type** | Story |
| **Epic** | Kitchen Operations & Order Flow |
| **Milestone** | Milestone 4 - Captain App, KDS & Reservations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-019 (KOT/Kitchen Order Ticket), RMS-011 (Menu Management), RMS-037 (Captain App) |

## User Story
As a kitchen expediter or station chef working in front of a wall-mounted screen, I want to see incoming orders routed to my station in real time, with order timers, modifier details, and recipe instructions, so that I can prepare dishes accurately, hit service-time targets, and coordinate the pass with minimal shouting.

## Description
The Kitchen Display System (KDS) replaces paper KOTs and dockets with a wall-mounted React SPA running on Android TVs or commercial kitchen displays (typically 32"–55"). Each kitchen station — Hot Kitchen, Tandoor, Bar, Cold/Prep, Dessert, Beverages — runs an independent browser instance subscribed to its own station's order stream. The KDS is the operational backbone of a modern Indian kitchen where high-volume service (200+ covers on a busy Friday night) makes paper illegible, lost, and unsorted.

When a captain (RMS-037) or POS sends a KOT, the system routes items to stations based on `menu_items.station_id`. A tandoori roti goes to the Tandoor station; a cocktail goes to the Bar; a biryani goes to Hot Kitchen. Each station sees only its own items, never the full order, reducing cognitive load. The expediter view (typically at the pass) shows the consolidated order across all stations for course coordination.

Each ticket card on the KDS displays: order number, table number, item name with image, quantity, modifiers (chips for "extra cheese", "no onion"), cooking instructions ("less spicy", "Jain — no onion/garlic"), course sequence (Starter / Main / Dessert), elapsed timer, and a large "Bump" button. The elapsed timer color-codes urgency: green (0-5 min), yellow (5-10 min), orange (10-15 min), red (15+ min), with optional sound alerts at configurable thresholds. Bumping marks the item as ready and removes it from the active queue.

For Indian multi-cuisine outlets, "Jain preparation" instructions must be visually prominent because contamination with onion/garlic is a strict dietary restriction. Vegan and allergen indicators are also surfaced on the ticket card. Recipes (ingredient list, prep steps) are one tap away from each item.

Multiple KDS screens can run concurrently across an outlet, each with independent station subscriptions. KDS supports "Recall" (undo bump within 30 seconds), "Void" (manager PIN required), and "Refire" (re-send to station). A live "Order Queue" summary view gives the kitchen manager throughput visibility.

## Acceptance Criteria
- [ ] KDS subscribes to WebSocket channel per station and receives new orders within 1 second of dispatch
- [ ] Each ticket card displays: order #, table #, item name + image, qty, modifiers, cooking instructions, course, elapsed timer, allergen/diet badges
- [ ] Timer color codes: green 0-5 min, yellow 5-10 min, orange 10-15 min, red 15+ min
- [ ] Optional sound alert plays when an item crosses configured time thresholds (default 10 min, 15 min)
- [ ] "Bump" removes ticket from active queue, fires pass-ready event to expediter and captain app
- [ ] "Recall" restores a bumped ticket within 30 seconds (configurable window)
- [ ] "Void Item" requires manager PIN entry; voids with reason logged in audit trail
- [ ] Expediter view aggregates all stations for one order with course sequencing (Starters, then Mains)
- [ ] Multiple concurrent KDS screens supported (e.g., 2 screens at Hot Kitchen, 1 at Bar)
- [ ] Recipes (ingredient list, prep steps) accessible via tap on item card
- [ ] "Order Queue" view shows throughput: orders in queue, avg prep time, items bumped in last 15/30/60 min
- [ ] KDS persists across transient network drops with auto-reconnect and replay of missed events
- [ ] Headless mode for off-peak hours: dim screen, suppress sounds, but keep queue visible
- [ ] KDS screen auto-locks after 5 min idle; manager PIN to unlock (prevents accidental bumps)
- [ ] All bump/recall/void actions logged with user_id (chef), timestamp, action type

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Station KDS | /kds/station/{stationId} | Live ticket grid for one station (Hot, Tandoor, Bar, etc.) |
| Expediter View | /kds/expediter | All-station view with course sequencing |
| Recipe Detail | /kds/recipes/{itemId} | Full recipe, ingredients, prep steps |
| Queue Analytics | /kds/queue | Throughput KPIs, avg prep times, peak load |
| KDS Settings | /kds/settings | Sound alerts, timers, screen lock config |

### Screen Details

**Station KDS (/kds/station/{stationId})**
- Layout: CSS Grid (3-4 columns on 32"+ display) of ticket cards; header bar with station name + active count + connection status.
- Header: Outlet name | Station name | Active count | Avg prep time (last 1 hr) | Clock | Settings cog | Logout.
- Ticket Card: 
  - Top strip: Order # | Table # | Course badge (Starter/Main/Dessert/Beverage) | Elapsed timer (large, color-coded)
  - Middle: Item name (bold, large font) | Qty (big numeral, e.g., "×3")
  - Modifiers: chips with color coding (red for allergen, yellow for "extra spicy", blue for "no XYZ")
  - Cooking notes: italic line, e.g., "Less oil. Jain — no onion/garlic."
  - Veg/Non-veg/Jain/Vegan badge (Indian FSSAI color dots)
  - Bottom: Bump button (large, green, full-width) | "⋯" menu (Recall, Void, Recipe)
- Sort: Newest first by default; toggle "Oldest First" surfaces aging tickets during rush.
- Filters: Course filter (Starter/Main/All), Diet filter (Veg Only toggle), "Late only" (≥10 min) filter.
- Idle animation: When no active tickets, large clock with kitchen quote of the day (customizable).

**Expediter View (/kds/expediter)**
- Layout: Vertical lanes grouped by course (Starters | Main Course | Breads/Rice | Desserts | Beverages). Each lane shows tickets across all stations.
- Each card includes "Pass Ready" indicator when all stations for that order's items have bumped.
- Audio cue: distinct "ding" when an entire order is pass-ready (configurable).
- Header: Active orders count | Avg table-turn time | Late orders count | Course progress %.
- Drag-and-drop: Manual course reassignment if needed (e.g., "Send this dessert to Hot Kitchen for plating").
- Buttons: "Send to Captain" (notifies via WebSocket), "Print Voids Report".

**Recipe Detail (/kds/recipes/{itemId})**
- Layout: Two-column on large screens: left = ingredients list with quantities; right = step-by-step prep with images/video.
- Header: Item name, image, allergen flags (red banner), prep time estimate, yield (e.g., "1 portion").
- Buttons: "Print Recipe", "Back to KDS" (auto-return after 30 sec if no interaction).
- Behavior: Chef can pin a recipe on-screen while preparing (multi-window support on dual-monitor KDS setups).

**Queue Analytics (/kds/queue)**
- Layout: KPI cards at top (Active orders | Avg prep time | Items in last 15/30/60 min | Late orders) + bar chart (orders per 15-min bucket, last 4 hours) + heatmap (peak load by station and hour).
- Filters: Date range, station, course.
- Export: CSV download for shift-end report.
- Live updates: Numbers refresh every 30 seconds; chart animates with new data points.
- Permission: Kitchen manager / head chef only (PIN gate).

**KDS Settings (/kds/settings)**
- Sections: Audio Alerts (toggle, threshold config, volume slider), Timer Thresholds (green→yellow→orange→red cutoffs), Screen Lock (idle timeout), Display (brightness, font size, theme dark/light), Station Assignment (this screen's station), Network (Reconnect strategy).
- Buttons: "Test Sound", "Save", "Reset to Defaults".
- Behavior: Settings are per-screen device; persisted in localStorage and synced to server.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/kds/stations | List all stations for the outlet |
| GET | /api/v1/kds/station/{id}/active-tickets | Current active tickets for station (initial load) |
| POST | /api/v1/kds/tickets/{id}/bump | Bump ticket — mark as ready, remove from active queue |
| POST | /api/v1/kds/tickets/{id}/recall | Recall (un-bump) within recall window |
| POST | /api/v1/kds/tickets/{id}/void | Void item with manager PIN + reason |
| POST | /api/v1/kds/tickets/{id}/refire | Re-send to station (e.g., printed wrong) |
| GET | /api/v1/kds/recipes/{itemId} | Get recipe (ingredients + steps + images) |
| GET | /api/v1/kds/queue/analytics | Real-time throughput KPIs |
| GET | /api/v1/kds/expediter/active-orders | Expediter consolidated view |
| POST | /api/v1/kds/auth/login | KDS user PIN login (chef / expediter / manager) |
| POST | /api/v1/kds/auth/verify-pin | Verify manager PIN for void/unlock |
| GET | /api/v1/kds/settings | Per-device KDS settings |
| PUT | /api/v1/kds/settings | Update KDS settings |
| GET | /api/v1/kds/tickets/{id}/history | Audit trail for a ticket |
| WS | /ws/kds/station/{stationId} | Real-time order stream for station |
| WS | /ws/kds/expediter | Consolidated all-station stream |

## Database Tables

**kds_stations**
- id (bigIncrements, PK)
- restaurant_id (foreignId, restaurants)
- outlet_id (foreignId, outlets)
- name (string) — "Hot Kitchen", "Tandoor", "Bar", "Cold", "Dessert", "Beverages"
- code (string) — short code for printer routing
- display_color (string, nullable) — UI accent color
- sort_order (unsignedSmallInteger)
- is_active (boolean, default true)
- timestamps
- unique([outlet_id, name])

**kds_active_tickets** (denormalized hot table for fast KDS render)
- id (bigIncrements, PK)
- kot_id (foreignId, kots) — original KOT
- order_id (foreignId, orders)
- table_number (string) — denormalized for display
- station_id (foreignId, kds_stations, indexed)
- item_id (foreignId, menu_items)
- item_name (string) — snapshot
- quantity (unsignedSmallInteger)
- modifiers_json (json) — snapshot at dispatch time
- cooking_notes (text, nullable)
- course (enum: starter, main, bread, rice, dessert, beverage, default main)
- allergen_flags_json (json) — snapshot
- dietary_flags_json (json) — snapshot (jain, vegan, etc.)
- dispatched_at (timestamp)
- bumped_at (timestamp, nullable)
- bumped_by_user_id (foreignId, users, nullable)
- status (enum: active, bumped, recalled, voided, default active)
- voids_reason (text, nullable)
- voids_approved_by (foreignId, users, nullable)
- indexes([station_id, status, dispatched_at]), ([order_id])

**kds_ticket_actions** (audit trail)
- id (bigIncrements, PK)
- ticket_id (foreignId, kds_active_tickets, indexed)
- user_id (foreignId, users) — chef / expediter who acted
- action (enum: dispatched, bumped, recalled, voided, refired, viewed_recipe, screen_unlocked)
- metadata_json (json, nullable) — reason text, manager PIN user, etc.
- occurred_at (timestamp)
- timestamps
- index([ticket_id, occurred_at])

**kds_recipes**
- id (bigIncrements, PK)
- menu_item_id (foreignId, menu_items, unique)
- yield_description (string) — "1 portion", "500g", etc.
- prep_time_minutes (unsignedSmallInteger, nullable)
- ingredients_json (json) — array of {name, quantity, unit, notes}
- steps_json (json) — array of {step_number, description, image_url, video_url, duration_sec}
- allergen_flags_json (json) — cached for display
- version (unsignedInteger, default 1) — for recipe change tracking
- published_at (timestamp, nullable)
- timestamps

## Technical Notes

**Laravel Backend:**
- Controllers: `KdsAuthController` (PIN login, manager PIN verify), `KdsStationController` (list, active tickets), `KdsTicketController` (bump, recall, void, refire, history), `KdsRecipeController` (show), `KdsExpediterController` (consolidated view), `KdsAnalyticsController` (KPI, throughput, queue stats), `KdsSettingsController` (per-device).
- Services: `KdsRoutingService` (maps order items to stations based on `menu_items.station_id`), `KdsTicketService` (bump/recall state machine with recall window), `KdsVoidService` (manager PIN verification, audit logging), `KdsAnalyticsService` (rolling-window aggregations via Redis sorted sets), `KdsRecipeService` (versioning, snapshot-on-dispatch).
- Events: `KotDispatched` (from RMS-019/037, broadcast on `outlet.{id}.kds.station.{stationId}`), `KotBumped`, `KotRecalled`, `KotVoided`, `KotRefired`, `OrderPassReady` (broadcast on expediter channel when all stations bumped).
- Jobs: `DispatchKotJob` (groups by station, persists to `kds_active_tickets`, broadcasts), `SnapshotKotStateJob` (denormalizes order state to `kds_active_tickets` for fast render), `CleanupBumpedTicketsJob` (archives tickets older than 24h).
- WebSocket: Laravel Reverb channels — `outlet.{id}.kds.station.{stationId}` (per-station), `outlet.{id}.kds.expediter` (consolidated). Channel authorization gates by `kds_user` role + outlet scope.
- PIN auth: Same hashing as captain app; manager PIN is a flag on user (`can_approve_kds_voids = true`).
- Recall window: Configurable per outlet (default 30s); enforced server-side, not just client-side.
- Analytics: Redis sorted sets with `dispatched_at`/`bumped_at` scores; O(log N) range queries for "items in last N minutes".

**React Frontend:**
- Components: `StationKdsView`, `TicketCard`, `TicketGrid`, `ModifierChips`, `TimerColor`, `RecallButton`, `VoidDialog`, `RecipeModal`, `ExpediterView`, `CourseLane`, `QueueAnalyticsDashboard`, `KdsSettingsPanel`, `PinLoginOverlay`, `ScreenLockOverlay`.
- State: Zustand `useKdsStore` (tickets[], filters, user, settings), `useKdsWebSocket` (Reverb subscription, event reducer), `useKdsAudio` (Web Audio API for alert tones), `useKdsIdleTimer` (auto-lock after idle).
- Real-time: `useKdsWebSocket` hook reconnects with exponential backoff on drop; on reconnect, calls `GET /kds/station/{id}/active-tickets` for full snapshot then applies any missed events from server-sent event log.
- Audio: `Web Audio API` with preloaded MP3/WAV buffers (notification.mp3, alert.mp3, bump.mp3); respects user gesture unlock on first interaction.
- Display: 32"+ TV optimized layout — 64-96px font sizes for ticket text; supports landscape only; high-contrast dark theme by default (kitchens are bright).
- Drag-and-drop: `react-dnd` for expediter course reassignment.
- Resilience: `BroadcastChannel` API syncs state across multiple browser tabs (so two screens showing same station stay consistent).
- Build: Vite + React 18, PWA installable for kiosk-mode browser launches; CSS Grid for ticket layout, no virtual scrolling needed at typical kitchen volumes (~50 active tickets max).

## Subtasks
1. [ ] Create migrations for `kds_stations`, `kds_active_tickets`, `kds_ticket_actions`, `kds_recipes`
2. [ ] Implement `KdsRoutingService` mapping items to stations via `menu_items.station_id`
3. [ ] Build `DispatchKotJob` to persist tickets and broadcast to station channels
4. [ ] Implement `KdsTicketService` state machine (active → bumped → recalled, with recall window enforcement)
5. [ ] Build `KdsTicketController` (bump, recall, void, refire, history) endpoints
6. [ ] Implement `KdsVoidService` with manager PIN verification and audit logging
7. [ ] Build `KdsExpediterController` aggregating tickets across stations with course sequencing
8. [ ] Build `KdsAnalyticsController` with Redis-backed rolling-window KPIs
9. [ ] Build `KdsRecipeController` with versioned recipe retrieval
10. [ ] Set up Laravel Reverb channels (per-station + expediter) with auth gates
11. [ ] Build React `PinLoginOverlay` with chef / manager PIN variants
12. [ ] Build `StationKdsView` with `TicketCard` grid and timer color coding
13. [ ] Build `ExpediterView` with course lanes and pass-ready detection
14. [ ] Implement `useKdsWebSocket` with reconnect + snapshot-on-reconnect logic
15. [ ] Implement `useKdsAudio` for alert sounds (Web Audio API)
16. [ ] Implement `useKdsIdleTimer` for auto-lock and PIN unlock flow
17. [ ] Build `RecipeModal` with ingredients, steps, allergen flags
18. [ ] Build `QueueAnalyticsDashboard` with KPIs and live charts
19. [ ] Build `KdsSettingsPanel` for per-device configuration
20. [ ] Write unit + integration tests for routing, state machine, recall window, PIN auth, analytics aggregation

## Testing Criteria
- [ ] Order dispatched via captain app appears on correct station KDS within 1 second
- [ ] Tandoor item appears only on Tandoor KDS, not Hot Kitchen
- [ ] Timer transitions from green → yellow → orange → red at configured thresholds
- [ ] Bump removes ticket from active grid and fires pass-ready event for expediter
- [ ] Recall restores ticket within recall window; after window expiry, recall returns error
- [ ] Void requires manager PIN; wrong PIN rejected; correct PIN logs void with manager_id
- [ ] Expediter view shows all stations' items for one order grouped by course
- [ ] Pass-ready audio cue fires when last station bumps an order
- [ ] Network drop + reconnect: missed events replay and KDS state matches server
- [ ] Recipe modal displays correct ingredients and steps for the item
- [ ] Queue analytics shows correct item count for last 15/30/60 minutes
- [ ] All bump/recall/void actions recorded in `kds_ticket_actions` with user_id and timestamp