# Milestone 6 Plan: Platform & Integrations

| Field | Value |
|-------|-------|
| **Milestone** | M6 - Platform & Integrations |
| **Objective** | Scale, multi-language, Tally, offline |
| **Timeline** | 3 weeks |
| **Dependencies** | All previous milestones (M1-M5) |
| **Status** | Planned |
| **Owner** | Rushabh Sorathiya |

## Overview

Milestone 6 is the platform maturation milestone that scales the restaurant management system for broader market adoption. This milestone adds multi-language support for pan-India and international reach, enhances security with granular role-based access control, deepens aggregator integration with menu synchronization, connects to accounting systems via Tally export, and introduces ecosystem features like a supplier marketplace and community forum.

Additionally, this milestone delivers offline-first capabilities ensuring the POS works without internet connectivity, and a comprehensive hardware integration layer supporting printers, scanners, cash drawers, and other peripherals.

## Goals

1. Implement multi-language support with 18+ languages including RTL
2. Enhance RBAC with granular permission matrix and custom roles
3. Build aggregator menu sync for one-click menu push
4. Integrate Tally accounting export (XML/JSON)
5. Launch supplier B2B marketplace
6. Create restaurant owner community platform
7. Deliver offline mode with sync engine for PWA
8. Build hardware integration layer for peripherals

## Scope

### In Scope
- i18n with 18+ language translations
- RTL support for Arabic
- Granular RBAC with permission matrix
- Custom role builder
- Aggregator menu sync (Swiggy, Zomato, magicpin)
- Tally XML/JSON export with voucher mapping
- Supplier marketplace with RFQ system
- Community forum and knowledge sharing
- Offline-first PWA with IndexedDB
- Sync engine with conflict resolution
- ESC/POS printer integration
- Barcode/QR scanner integration
- Cash drawer and weight scale integration
- Hardware diagnostic panel

### Out of Scope
- Native mobile apps (future - currently PWA)
- White-label branding engine (future)
- API marketplace for third-party developers (future)
- AI-powered recommendations (future)

## Timeline (3 Weeks)

| Week | Focus | Deliverables |
|-----------|-------|--------------|
| Week 1 | Platform Foundations | Multi-language i18n, RBAC enhancement, hardware integration layer |
| Week 2 | Integrations | Aggregator menu sync, Tally export, offline mode & sync engine |
| Week 3 | Ecosystem | Supplier marketplace, community feature, integration testing, polish |

## Tickets

| Ticket ID | Title | Priority | Story Points |
|-----------|-------|----------|--------------|
| RMS-049 | Multi-language Support | High | 13 |
| RMS-050 | Role-based Access Control (RBAC) Enhancement | High | 13 |
| RMS-051 | Aggregator Menu Sync | High | 8 |
| RMS-052 | Accounting Integration - Tally Export | High | 8 |
| RMS-053 | Supplier Marketplace | Medium | 13 |
| RMS-054 | Community Feature | Low | 8 |
| RMS-055 | Offline Mode & Sync Engine | High | 13 |
| RMS-056 | Hardware Integration Layer | High | 13 |

## Technical Approach

### Multi-language (i18n)
- react-i18next for frontend translation management
- JSON translation files per language in /resources/lang/
- Laravel trans() helper for backend strings
- Dynamic direction switching (LTR/RTL) with CSS logical properties
- Database columns with JSON multi-language fields for menu items

### RBAC Enhancement
- Spatie Laravel Permission package as foundation
- Permission matrix: module x action (view/create/edit/delete) x role
- Outlet-level scope filtering via policies
- Frontend route guards with React Context
- API middleware for permission verification

### Offline Mode
- IndexedDB via Dexie.js for local data persistence
- Service Worker for asset caching
- Background Sync API for queued mutations
- Conflict resolution: last-write-wins with manual merge for conflicts
- Partial sync priority: orders > inventory > menu > analytics

### Hardware Integration
- ESC/POS command builder library for thermal printers
- WebUSB / Web Bluetooth for browser-based device access
- Node.js bridge service for LAN printer communication
- Hardware abstraction layer with driver pattern

### Tally Export
- Tally Prime XML schema compliance
- Voucher type mapping (Sales, Purchase, Journal, Contra)
- Ledger master sync
- GST classification and HSN mapping
- Scheduled auto-export with email delivery

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Translation quality issues in regional languages | Medium | High | Professional translation services, community contribution model, fallback to English for missing keys |
| Offline sync conflicts cause data inconsistency | High | Medium | Conflict detection on sync, last-write-wins with audit trail, manual merge UI for critical conflicts |
| Hardware driver incompatibility across printer brands | Medium | High | Test with top 5 brands (EPSON, Star, Bixolon, TVS, Citizen), ESC/POS standard compliance, driver abstraction layer |
| Tally XML schema changes in updates | Medium | Medium | Version detection, schema validation, support Tally Prime + ERP 9, follow official Tally TDL documentation |
| RBAC permission complexity causes access issues | Medium | Medium | Comprehensive permission matrix documentation, role templates, testing matrix, audit trail for troubleshooting |
| Browser WebUSB/Web Bluetooth limited browser support | Medium | Medium | Node.js bridge fallback for LAN devices, Electron wrapper option for desktop |

## Dependencies

- M1-M5 all modules (complete) - foundation for platform features
- Tally software access for testing (business dependency)
- Hardware units for integration testing (procurement dependency)
- Translation service provider (business dependency)
- Aggregator partner API menu endpoints (M3 integration)

## Success Metrics

- 18+ languages fully translated and tested
- RBAC covers 100% of application modules with zero privilege escalation
- Offline mode sustains 8+ hours of operation without connectivity
- Tally export matches 100% of trial balance entries
- Hardware integration supports 5+ printer brands
- Sync conflict rate < 1% of synced records

## Exit Criteria

- [ ] All 18+ language translations deployed and verified
- [ ] RBAC permission matrix tested for all roles
- [ ] Aggregator menu sync working for Swiggy and Zomato
- [ ] Tally export validated with accounting team
- [ ] Offline mode tested for 8-hour simulated outage
- [ ] Hardware integration tested with 3+ printer models
- [ ] Supplier marketplace live with 10+ test suppliers
- [ ] Community forum accessible and moderated
- [ ] Sync engine handles 1000+ queued operations successfully
