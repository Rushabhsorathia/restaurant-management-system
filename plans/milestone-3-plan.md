# Milestone 3 Plan: Online Orders & CRM

| Field | Value |
|-------|-------|
| **Milestone** | M3 - Online Orders & CRM |
| **Objective** | Connect to food aggregators and build customer retention engine |
| **Timeline** | 4 weeks |
| **Dependencies** | M1 complete (menu, billing, orders) |
| **Status** | Planned |
| **Owner** | Rushabh Sorathiya |

## Overview

Milestone 3 transforms the restaurant management system from a standalone POS into a connected commerce platform. This milestone integrates third-party food aggregators (Swiggy, Zomato, magicpin) for inbound online orders and builds a full Customer Relationship Management (CRM) engine for retention, loyalty, and marketing automation.

The CRM layer captures customer data from both dine-in and online channels, enabling targeted campaigns, loyalty programs, feedback collection, and personalized offers. This creates a unified view of every customer interaction regardless of ordering channel.

## Goals

1. Integrate Swiggy, Zomato, and magicpin aggregator APIs for real-time order ingestion
2. Build unified order queue merging POS, online, and takeaway orders
3. Implement customer database with 360-degree profile view
4. Launch loyalty and rewards program engine
5. Enable SMS/WhatsApp/Email marketing campaigns
6. Create feedback and rating collection system

## Scope

### In Scope
- Aggregator API integration (Swiggy, Zomato, magicpin)
- Order synchronization and deduplication
- Customer master data management
- Loyalty points engine with tiers
- Coupon and offer management
- Campaign management (SMS, WhatsApp, Email)
- Customer feedback collection
- Birthday/Anniversary automation
- Customer segmentation

### Out of Scope
- Own delivery app (M4)
- Advanced analytics on CRM data (M5)
- Multi-language campaign content (M6)

## Timeline (4 Weeks)

| Week | Focus | Deliverables |
|------|-------|--------------|
| Week 1 | Aggregator Integration | Swiggy API connection, Zomato API connection, order webhook ingestion, order queue unification |
| Week 2 | Customer Database | Customer master table, profile 360 view, order history aggregation, segmentation engine |
| Week 3 | Loyalty & Offers | Points engine, tier management, coupon engine, redemption flow, birthday automation |
| Week 4 | Campaigns & Feedback | SMS/WhatsApp/Email gateway, campaign builder, feedback collection, reporting |

## Tickets

| Ticket ID | Title | Priority | Story Points |
|-----------|-------|----------|--------------|
| RMS-025 | Swiggy Aggregator Integration | High | 13 |
| RMS-026 | Zomato Aggregator Integration | High | 13 |
| RMS-027 | magicpin Aggregator Integration | Medium | 8 |
| RMS-028 | Unified Online Order Queue | High | 8 |
| RMS-029 | Customer Master Database | High | 8 |
| RMS-030 | Customer 360 Profile View | High | 8 |
| RMS-031 | Loyalty Points Engine | High | 13 |
| RMS-032 | Coupon & Offer Management | Medium | 8 |
| RMS-033 | SMS/WhatsApp Campaign Engine | Medium | 8 |
| RMS-034 | Customer Feedback System | Medium | 5 |
| RMS-035 | Customer Segmentation Engine | Medium | 8 |
| RMS-036 | Birthday/Anniversary Automation | Low | 5 |

## Technical Approach

### Aggregator Integration
- Use official partner APIs (Swiggy Partner API, Zomato Base API, magicpin Merchant API)
- Webhook-based real-time order push with polling fallback
- Laravel Queue workers for async order processing
- Redis for order deduplication and idempotency keys
- OAuth 2.0 / API key authentication per aggregator

### CRM Architecture
- Customer master table with phone number as unique key
- Elasticsearch for customer search and segmentation (optional)
- Event-sourced loyalty point ledger for audit trail
- Campaign dispatch via queued jobs (SMS: MSG91, WhatsApp: Gupshup, Email: SES)

### Data Flow
```
Aggregator API --> Webhook Controller --> Order Queue Worker --> Order Table
                                                                --> Customer Match/Create
                                                                --> Loyalty Point Ledger
```

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| API rate limits from Swiggy/Zomato restrict order sync volume | High | Medium | Implement request throttling, cache responses, use webhook push instead of polling, request quota increase from aggregator partner managers |
| Aggregator API downtime causes missed orders | High | Low | Implement polling fallback every 60s when webhook silent, alert on missed heartbeat, manual order entry fallback |
| Customer phone number mismatch across channels creates duplicate profiles | Medium | High | Fuzzy matching on phone + name, deduplication job, manual merge UI |
| SMS/WhatsApp gateway costs escalate with campaign volume | Medium | Medium | Opt-in consent tracking, rate limiting per customer, cost dashboard, bulk pricing negotiation |
| Loyalty point fraud via fake accounts | Medium | Low | Phone OTP verification, rate limiting on redemption, audit trail |

## Dependencies

- M1 Menu Management (complete) - needed for order item mapping
- M1 Billing & Invoicing (complete) - needed for payment reconciliation
- M1 Order Management (complete) - needed for unified order queue
- Aggregator partner API credentials (business dependency)
- MSG91/Gupshup gateway accounts (business dependency)

## Success Metrics

- Online order ingestion latency < 30 seconds from aggregator to POS
- Customer profile match rate > 90% for returning customers
- Loyalty program enrollment > 30% of unique customers
- Campaign delivery rate > 95%
- Zero duplicate orders from aggregator sync

## Exit Criteria

- [ ] All three aggregators integrated and live
- [ ] Customer database populated with 1000+ profiles in staging
- [ ] Loyalty redemption tested end-to-end
- [ ] Campaign sent to test segment successfully
- [ ] Feedback collection form live on receipts
- [ ] Reconciliation report matches aggregator settlements
