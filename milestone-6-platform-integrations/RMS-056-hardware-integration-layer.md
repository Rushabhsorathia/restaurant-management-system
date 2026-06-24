# RMS-056: Hardware Integration Layer

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-056 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-008 (POS Order Capture), RMS-014 (Billing & Payment), RMS-010 (KDS) |

## User Story
As a restaurant owner, I want the RMS to work with any thermal receipt printer, barcode/QR scanner, cash drawer, weight scale, and customer display pole I have -- across brands like EPSON, Star, Bixolon, TVS -- with a hardware diagnostic panel for troubleshooting, so that I'm not locked into a specific vendor and my existing equipment works out of the box.

## Description
Indian restaurants operate a heterogeneous mix of hardware: an EPSON TM-T82III at billing, a Bixolon SRP-350III in the kitchen, a TVS RP 45 shop, a generic Honeywell barcode scanner, a CAS PD-II weight scale, and a 20x4 VFD customer display. Getting these to talk to a web-based POS is a recurring pain because each brand uses slightly different ESC/POS dialects, USB vendor IDs, or LAN protocols. Today, this means writing per-device glue code every time, with no diagnostic tooling when something goes wrong.

This story delivers a Hardware Integration Layer that abstracts device communication behind a unified API and a Node.js bridge. The bridge runs as a small companion process (systemd service) on a local NUC or Raspberry Pi at the outlet, exposing the device fleet to the web app over WebSocket + REST. The web app sends print/scan/open-drawer commands; the bridge translates to the device-specific protocol and replies with status.

Printer support covers the full ESC/POS command set across EPSON, Star, Bixolon, TVS RP series, and generic 58/80mm thermal printers. Barcode/QR scanners work over two modes: (a) HID mode (keyboard wedge) -- captured by the browser directly, (b) USB Serial -- via the bridge with WebUSB fallback. Cash drawer trigger is sent as an ESC/POS extension. Weight scale reads via serial (CAS, Avery, generic) polled at 1Hz. Customer display (VFD, 20x4 LCD) is driven by standard ESC/POS commands or vendor-specific protocols (CD-7220, etc.).

The Hardware Diagnostic Panel is a UI that shows every connected device, its driver, last test result, paper status, signal strength (for wireless), and lets the operator run a self-test (print test page, beep, open drawer, scan barcode, weigh sample). Per-outlet configuration persists in `hardware_configurations`.

The bridge supports hot-plug detection (udev on Linux, with manual refresh fallback) and degrades gracefully: if a device is offline, the POS prints a "Printer offline - orders queued" message and stores print jobs in `print_jobs` for later flush.

## Acceptance Criteria
- [ ] ESC/POS driver supporting thermal receipt printers: EPSON TM-T series, Star TSP, Bixolon SRP, TVS RP, generic 58/80mm.
- [ ] Print via USB (WebUSB from browser) or LAN (via bridge) or Bluetooth (where supported).
- [ ] Cash drawer trigger via ESC/POS pulse command on receipt print and manual button.
- [ ] Barcode/QR scanner support: HID keyboard wedge (browser-direct) and USB Serial (bridge) with WebUSB fallback.
- [ ] Weight scale support: CAS PD-II, Avery, generic RS-232 serial devices; polled at 1Hz; tare and unit selection.
- [ ] Customer display pole: 20x4 VFD/LCD with line 1 = item, line 2 = total; localized per RMS-049.
- [ ] Node.js bridge runs as systemd service `rms-hardware-bridge` on outlet local server.
- [ ] Bridge exposes REST (`/api/devices`, `/api/devices/{id}/print`) and WebSocket for status events.
- [ ] Hot-plug detection with udev on Linux; manual refresh in admin UI.
- [ ] Hardware Diagnostic Panel: device list, status, last test, run self-test buttons.
- [ ] Print job queue (`print_jobs`) persists failed/offline prints; auto-flush when device returns.
- [ ] Per-outlet hardware configuration persists in `hardware_configurations`.
- [ ] Multi-language receipt template honors locale (RMS-049) and prints item names, tax, totals correctly.
- [ ] All hardware events (connect, disconnect, error, low-paper) logged in `hardware_events`.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Hardware Configuration | /settings/hardware | Per-outlet device setup and assignment |
| Hardware Diagnostic | /settings/hardware/diagnostics | Live status, self-tests, logs |
| Print Queue | /settings/hardware/print-queue | Pending/retried/failed print jobs |
| Scanner Setup | /settings/hardware/scanner | HID/Serial mode, keymap, suffixes |
| Bridge Management | /settings/hardware/bridge | Bridge status, version, restart |

### Screen Details

**Hardware Configuration (/settings/hardware)**
- Layout: Device list with per-device configuration card.
- Devices: Printer (Billing), Printer (Kitchen/KOT), Printer (Bar), Cash Drawer, Barcode Scanner, Weight Scale, Customer Display, Bar Display.
- Each card: Device name, type, connection (USB/LAN/Bluetooth/Serial), vendor, model, status (Connected/Disconnected/Error), "Configure" / "Test" / "Remove" buttons.
- Add device: dropdown of supported types opens vendor/model selection wizard.
- Outlets tabs at top: switch active outlet.

**Hardware Diagnostic (/settings/hardware/diagnostics)**
- Live status grid: Each device card with green/yellow/red status, last heartbeat, last operation, error count.
- Self-test buttons: "Print Test Page", "Open Cash Drawer", "Beep", "Scan Test", "Weigh Test", "Display Test".
- Logs panel: Real-time event stream (connect, disconnect, error, low-paper, jam).
- Filters: Device, Event type, Date range.
- Export logs to CSV.

**Print Queue (/settings/hardware/print-queue)**
- Table: Timestamp | Device | Job Type (Receipt/KOT/Bar) | Status (Pending/Printed/Failed/Retrying) | Attempts | Last Error | Actions.
- Filters: Device, Status, Date.
- Per-row: "Retry", "Cancel", "Reprint".
- Bulk: "Retry All Failed", "Clear Completed".
- Per-device queue depth indicator at top.

**Scanner Setup (/settings/hardware/scanner)**
- Mode: Radio (HID Keyboard, USB Serial, WebUSB).
- HID settings: Suffix character (Enter/Tab/None), Prefix, Beep on scan, Auto-submit delay.
- Serial settings: Port, Baud rate (9600/115200), Data bits, Parity, Stop bits.
- Keymap: Map scanned code to field (menu item SKU, customer phone, table number).
- Test input field with last scanned value display.

**Bridge Management (/settings/hardware/bridge)**
- Bridge info: Version, uptime, host, IP, OS, connected devices count.
- Health: CPU, memory, network latency.
- Actions: "Restart Bridge" (with confirmation), "Update Bridge", "View Logs" (opens log stream), "Download Diagnostics".
- Connected clients: list of web app instances connected via WebSocket.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/hardware/devices | List configured devices for outlet |
| POST | /api/v1/hardware/devices | Register new device |
| PUT | /api/v1/hardware/devices/{id} | Update device config |
| DELETE | /api/v1/hardware/devices/{id} | Remove device |
| POST | /api/v1/hardware/devices/{id}/test | Run self-test |
| POST | /api/v1/hardware/print | Print a job (receipt/KOT) |
| GET | /api/v1/hardware/print-queue | List print jobs |
| POST | /api/v1/hardware/print-queue/{id}/retry | Retry failed job |
| POST | /api/v1/hardware/cash-drawer/open | Trigger cash drawer |
| POST | /api/v1/hardware/scale/read | Read weight from scale |
| POST | /api/v1/hardware/scale/tare | Tare the scale |
| POST | /api/v1/hardware/display/update | Update customer display |
| GET | /api/v1/hardware/events | Get event log with filters |
| GET | /api/v1/hardware/bridge/status | Bridge health (proxied from bridge) |
| POST | /api/v1/hardware/bridge/restart | Trigger bridge restart (admin) |
| GET | /api/v1/hardware/scanner/test | Test scanner input (last value) |

## Database Tables

**hardware_devices**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets)
- name (string)
- type (enum: receipt_printer, kitchen_printer, bar_printer, cash_drawer, barcode_scanner, qr_scanner, weight_scale, customer_display, pole_display)
- vendor (string) -- `epson`, `star`, `bixolon`, `tvs`, `cas`, `avery`, `honeywell`, `generic`
- model (string, nullable)
- connection_type (enum: usb, lan, bluetooth, serial, hid)
- connection_string_encrypted (text, nullable) -- IP, MAC, VID:PID, /dev/ttyUSB0
- port (unsignedSmallInteger, nullable)
- is_primary (boolean, default false) -- primary receipt printer
- capabilities_json (json) -- supported features per device
- status (enum: connected, disconnected, error, paper_low, paper_out, jam)
- last_heartbeat_at (timestamp, nullable)
- last_error (text, nullable)
- paper_width_mm (unsignedTinyInteger, default 80) -- for printers
- character_set (string, default 'utf-8')
- assigned_role (string, nullable) -- e.g., `kitchen_station_id`
- created_at, updated_at
- index([outlet_id, type])

**print_jobs**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets)
- device_id (foreignId, hardware_devices)
- job_type (enum: receipt, kot, bar_ticket, test_page, label, report)
- source_type (string) -- `App\Models\Order`, `App\Models\Kot`, etc.
- source_id (unsignedBigInteger, nullable)
- payload_json (json) -- rendered content + template vars
- template_name (string, nullable) -- `receipt_default`, `kot_80mm`, etc.
- copies (unsignedTinyInteger, default 1)
- status (enum: pending, printing, printed, failed, retrying, cancelled)
- attempts (unsignedTinyInteger, default 0)
- max_attempts (unsignedTinyInteger, default 5)
- last_error (text, nullable)
- scheduled_at (timestamp, nullable)
- started_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- duration_ms (unsignedInteger, nullable)
- is_offline_queued (boolean, default false)
- created_by (foreignId, users, nullable)
- created_at, updated_at
- index([status, scheduled_at])
- index([device_id, created_at])

**hardware_events**
- id (bigIncrements, PK)
- device_id (foreignId, hardware_devices)
- event_type (enum: connected, disconnected, error, paper_low, paper_out, jam, low_battery, scan, weigh, drawer_open, test, restart, config_changed)
- severity (enum: info, warning, error, critical)
- message (text)
- metadata_json (json, nullable)
- created_at
- index([device_id, created_at])
- index([event_type, severity, created_at])

**hardware_bridge_sessions**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets)
- bridge_version (string)
- bridge_host (string)
- bridge_ip (string(45))
- bridge_port (unsignedSmallInteger)
- started_at (timestamp)
- last_heartbeat_at (timestamp)
- status (enum: active, disconnected, error)
- cpu_usage (decimal(5,2), nullable)
- memory_usage_mb (decimal(10,2), nullable)
- uptime_seconds (unsignedBigInteger, nullable)
- connected_clients_count (unsignedInteger, default 0)
- timestamp
- index([outlet_id, last_heartbeat_at])

**scanner_input_log**
- id (bigIncrements, PK)
- device_id (foreignId, hardware_devices)
- scanned_value (string, indexed)
- scan_type (enum: barcode, qrcode, rfid)
- format (string, nullable) -- `EAN13`, `CODE128`, `QR`, etc.
- mapped_to_type (string, nullable) -- `menu_item`, `customer`, `table`
- mapped_to_id (unsignedBigInteger, nullable)
- accepted (boolean, default false)
- user_id (foreignId, users, nullable)
- created_at
- index([device_id, created_at])

## Technical Notes

**Node.js Bridge (`rms-hardware-bridge`):**
- Built with `node-js` (Express + WebSocket via `ws`); packaged as deb/rpm/snap + Windows MSI.
- Uses `node-usb` (libusb) for USB devices, `serialport` for serial, `net` for LAN, `noble` for BLE.
- ESC/POS command builder (`escpos` npm package) with vendor dialect tables (EPSON, Star, Bixolon, TVS).
- Hot-plug via `udev` rules on Linux (sends NETLINK socket events) and `usb-detection` polling fallback.
- WebSocket protocol: `{channel, action, payload, requestId}` with response correlation.
- Bridge authentication: shared secret rotated via API; per-outlet token.
- Auto-restart on crash via systemd; `Restart=always`.
- Local web UI on port 8089 for admin: status, logs, manual commands, update.

**Laravel Backend:**
- `HardwareService` dispatches print jobs to bridge via REST or queues them in `print_jobs` if bridge offline.
- `PrintService::render(Order $order, $template)` builds a renderable payload (text + ESC/POS bytes optional) from templates in `resources/print-templates/{type}.blade.php`.
- Templates: `receipt_default.blade.php`, `kot_80mm.blade.php`, `bar_ticket.blade.php`; use locale-aware helpers for item names (RMS-049).
- `CashDrawerService::open($deviceId)` sends pulse command; can be triggered on every receipt or manual.
- `ScaleService::read()` returns latest weight; client polls; tare button posts tare command.
- `ScannerService::log($deviceId, $value)` writes `scanner_input_log` and looks up mapped entity.
- `HardwareEventLogger` records all events from bridge WebSocket.
- `BridgeSessionService` tracks bridge health; cron `CheckBridgeHealth` flags bridges with no heartbeat >5min.
- Print queue worker `ProcessPrintJob` (queue: `print-jobs`) sends to bridge, retries with backoff.

**React Frontend:**
- Hardware Diagnostic uses WebSocket for real-time device status; falls back to polling.
- Print Queue with optimistic actions; toast on retry success.
- Scanner Setup: live "last scanned" value display via WebSocket.
- Bridge Management: live charts (CPU, memory, latency) via WS.
- Self-test buttons trigger immediate bridge call; loading state with result toast.
- Receipt template preview in browser with mock data.
- Hot-plug toast: "New device detected: EPSON TM-T82III on USB. Add?" with quick-add dialog.

## Subtasks
1. [ ] Scaffold Node.js bridge project with TypeScript, Express, WebSocket
2. [ ] Implement device drivers: `escpos`, `serialport`, `node-usb`, `noble`
3. [ ] Build ESC/POS command builder with vendor dialect tables
4. [ ] Implement USB hot-plug detection (udev + polling fallback)
5. [ ] Build bridge REST + WebSocket protocol with auth
6. [ ] Create `hardware_devices`, `print_jobs`, `hardware_events`, `hardware_bridge_sessions`, `scanner_input_log` migrations
7. [ ] Build `HardwareService` and `PrintService` on Laravel
8. [ ] Build receipt/KOT/bar templates in `resources/print-templates/`
9. [ ] Implement `ProcessPrintJob` queue worker with retry
10. [ ] Implement `CashDrawerService`, `ScaleService`, `ScannerService`
11. [ ] Build bridge health tracking and `CheckBridgeHealth` cron
12. [ ] Build Hardware Configuration UI
13. [ ] Build Hardware Diagnostic UI with live WebSocket status
14. [ ] Build Print Queue UI with retry, cancel, bulk actions
15. [ ] Build Scanner Setup UI with mode/keymap config
16. [ ] Build Bridge Management UI with restart, update, logs
17. [ ] Package bridge as systemd service + Windows service + macOS launchd
18. [ ] Write tests for each driver, print job lifecycle, retry, hot-plug, scanner mapping

## Testing Criteria
- [ ] Print test page produces correct ESC/POS output for each vendor (EPSON, Star, Bixolon, TVS)
- [ ] Cash drawer opens via pulse command on receipt print
- [ ] Barcode scan in HID mode auto-fills menu item SKU field
- [ ] Weight scale reading updates at 1Hz and within 50g of calibrated weight
- [ ] Customer display shows item name and total on order add
- [ ] Bridge restart triggers print job retry from queue
- [ ] Offline printer causes print to queue; reconnect flushes within 30s
- [ ] Hot-plug detection registers new device within 5s of USB insert
- [ ] Receipt prints correctly in hi-IN locale (Devanagari script where supported)
- [ ] Print queue retry with exponential backoff respects max_attempts
- [ ] Bridge health cron flags dead bridge within 10 min
- [ ] Scanner input log captures every scan with timestamp and mapped entity
