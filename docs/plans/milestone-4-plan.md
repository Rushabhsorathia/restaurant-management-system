# Milestone 4 Plan: Advanced Operations

| Field | Value |
|-------|-------|
| **Milestone** | M4 - Advanced Operations |
| **Objective** | Captain app, KDS, reservations, kiosk |
| **Timeline** | 5 weeks |
| **Dependencies** | M1 complete (menu, billing, orders) |
| **Status** | Planned |
| **Owner** | Rushabh Sorathiya |

## Overview

Milestone 4 extends the restaurant management system with advanced operational modules that improve service speed, kitchen efficiency, and customer experience. This milestone delivers the Captain app for tableside ordering, a Kitchen Display System (KDS) for order routing, a reservation management system, and a self-service kiosk mode for quick-service restaurants.

These modules transform the core POS into a full restaurant operations platform covering front-of-house, back-of-house, and customer self-service touchpoints.

## Goals

1. Build Captain app for waiters to take orders tableside via tablet
2. Implement Kitchen Display System (KDS) with station-based routing
3. Create reservation and waitlist management system
4. Develop self-service kiosk mode for QSR ordering
5. Enable real-time order status sync across all devices

## Scope

### In Scope
- Captain app (React PWA for tablets)
- KDS display with multi-station support
- Reservation management with table mapping
- Waitlist management with SMS notifications
- Self-service kiosk mode
- Order routing engine (KOT to kitchen stations)
- Real-time WebSocket order status updates
- Offline-capable Captain app

### Out of Scope
- Customer-facing mobile app (M3/M6)
- Advanced analytics on KDS/reservation data (M5)
- Multi-language KDS display (M6)

## Timeline (5 Weeks)

| Week | Focus | Deliverables |
|------|-------|--------------|
| Week 1 | Captain App Foundation | Tablet UI, table selection, menu browse, order cart, WebSocket sync |
| Week 2 | Captain App Advanced | Order modification, split bill, payment initiation, offline mode |
| Week 3 | Kitchen Display System | KDS display UI, station routing, order tickets, bump/recall, timing alerts |
| Week 4 | Reservations & Waitlist | Reservation booking, table mapping, waitlist queue, SMS notifications |
| Week 5 | Kiosk Mode | Self-service ordering UI, payment integration, receipt printing, kiosk admin |

## Tickets

| Ticket ID | Title | Priority | Story Points |
|-----------|-------|----------|--------------|
| RMS-037 | Captain App - Tableside Ordering | High | 13 |
| RMS-038 | Kitchen Display System (KDS) | High | 13 |
| RMS-039 | Reservation & Waitlist Management | High | 8 |
| RMS-040 | Self-Service Kiosk Mode | Medium | 13 |

## Technical Approach

### Captain App
- React PWA optimized for 10-inch Android tablets
- WebSocket connection via Laravel Reverb/Pusher for real-time sync
- IndexedDB for offline order caching
- Touch-optimized UI with large tap targets
- PIN-based waiter login

### Kitchen Display System
- React SPA on dedicated kitchen displays (wall-mounted)
- WebSocket real-time order push
- Configurable station routing (grill, salad, bar, etc.)
- Color-coded urgency indicators
- Bump screen with order history recall

### Reservations
- Calendar-based booking interface
- Table availability engine with floor map
- Automated SMS confirmation and reminders
- Walk-in waitlist with estimated wait time

### Kiosk Mode
- Full-screen locked-down React app
- Card/cash payment integration
- Simplified menu with images
- Order ticket print to kitchen
- Remote management and content updates

## Device & Hardware Requirements

| Device | Purpose | Specification | Quantity | Estimated Cost (INR) |
|--------|---------|---------------|----------|---------------------|
| Android Tablet (10-inch) | Captain App for waiters | Samsung Galaxy Tab A9+ / Lenovo Tab M11, 4GB+ RAM, Android 12+ | 4-6 per outlet | 15,000 each |
| Wall-Mounted Display (15-32 inch) | Kitchen Display System | LED monitor with HDMI, 1366x768+ resolution, VESA mount | 2-4 per kitchen (per station) | 8,000-15,000 each |
| Android Mini PC / TV Box | KDS display compute | Android 11+, 2GB+ RAM, WiFi, HDMI output | 1 per KDS display | 5,000 each |
| Touchscreen Kiosk (15-21 inch) | Self-service ordering | Capacitive touch, Android/Windows, card reader integrated | 1-2 per outlet | 35,000-50,000 each |
| Thermal Printer (Kitchen) | KOT printing backup | 80mm, Ethernet/WiFi, ESC/POS, auto-cutter | 1-2 per kitchen | 6,000 each |
| SMS Gateway Credits | Reservation notifications | MSG91 / Twilio | As needed | 0.20 per SMS |
| WiFi Access Points | Reliable tablet connectivity | Dual-band, PoE, commercial grade | 2-4 per outlet | 5,000 each |
| Tablet Rugged Case | Captain app tablet protection | Drop-proof, hand strap, screen protector | Per tablet | 1,500 each |
| UPS / Power Backup | KDS display uptime | 600VA minimum | Per critical display | 3,000 each |

### Network Requirements
- Dedicated WiFi SSID for operations devices (separate from guest WiFi)
- Minimum 50 Mbps symmetric internet for WebSocket stability
- Local network latency < 50ms for real-time sync
- WiFi coverage map ensuring no dead zones in dining and kitchen areas

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| WiFi dead zones cause Captain app disconnection | High | Medium | Site survey, mesh WiFi, offline mode with auto-sync, signal strength monitoring |
| KDS display hardware failure halts kitchen operations | Critical | Low | Thermal printer fallback for KOT, spare display unit, cloud-based KDS accessible from any device |
| Tablet battery drain during peak service | Medium | Medium | Charging stations in waiter areas, swappable batteries, low-battery alerts |
| Kiosk payment device integration complexity | Medium | Medium | Use certified card readers, pre-tested SDKs, vendor support SLA |
| WebSocket server load during peak hours | Medium | Low | Horizontal scaling, Redis pub/sub, connection pooling, load testing |

## Dependencies

- M1 Menu Management (complete) - menu data for ordering
- M1 Table Management (complete) - table mapping for captain app and reservations
- M1 Order Management (complete) - order creation and KOT generation
- M1 Billing (complete) - payment processing integration
- Hardware procurement (business dependency)
- Network infrastructure setup (business dependency)

## Success Metrics

- Order taking time reduced by 40% with Captain app
- Kitchen ticket-to-serve time reduced by 25% with KDS
- Reservation no-show rate < 15%
- Kiosk orders account for 20% of QSR outlet volume
- WebSocket connection uptime > 99.5%

## Exit Criteria

- [ ] Captain app deployed on tablets and tested in live service
- [ ] KDS displays operational in kitchen with all stations configured
- [ ] Reservation system live with SMS notifications working
- [ ] Kiosk mode tested with live payment processing
- [ ] Offline mode validated for Captain app
- [ ] All hardware procured and installed
- [ ] Staff trained on all new modules
