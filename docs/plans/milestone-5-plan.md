# Milestone 5 Plan: Analytics & Reporting

| Field | Value |
|-------|-------|
| **Milestone** | M5 - Analytics & Reporting |
| **Objective** | Business intelligence layer |
| **Timeline** | 3 weeks |
| **Dependencies** | M1-M3 (needs sales, inventory, online data) |
| **Status** | Planned |
| **Owner** | Rushabh Sorathiya |

## Overview

Milestone 5 adds a comprehensive business intelligence and reporting layer to the restaurant management system. This milestone transforms raw transactional data from POS, online orders, inventory, and CRM into actionable insights through real-time dashboards, standardized reports, and a custom report builder.

The analytics layer serves four key personas: outlet managers monitoring daily operations, head office executives tracking multi-outlet performance, accountants handling tax compliance, and business owners making strategic decisions based on profitability and trend analysis.

## Goals

1. Build real-time sales analytics dashboard with multi-granularity views
2. Implement online order reconciliation with aggregator settlement matching
3. Create head office consolidated multi-outlet dashboard
4. Enable geographic grouping (city/zone) for multi-outlet chains
5. Deliver GST/Tax compliance reporting suite
6. Build staff performance measurement system
7. Implement profit margin and food cost analysis
8. Create dynamic custom report builder for ad-hoc analysis

## Scope

### In Scope
- Sales analytics dashboard (real-time, historical, comparative)
- Online order reconciliation engine
- Head office multi-outlet consolidation
- City/zone geographic grouping
- GST tax reports (GSTR-1, GSTR-3B, e-invoice)
- Staff performance scorecards
- Food cost and margin analysis
- Custom report builder with drag-and-drop
- Scheduled email report delivery
- PDF/Excel/CSV export

### Out of Scope
- Predictive ML forecasting (future milestone)
- External BI tool integration (Tableau, PowerBI) - future
- Real-time streaming analytics pipeline - future

## Timeline (3 Weeks)

| Week | Focus | Deliverables |
|------|-------|--------------|
| Week 1 | Core Analytics | Sales dashboard, tax reports, staff performance, export engine |
| Week 2 | Reconciliation & Multi-Outlet | Online order reconciliation, head office dashboard, city/zone grouping |
| Week 3 | Advanced Analytics | Profit margin analysis, custom report builder, scheduled reports, polish |

## Tickets

| Ticket ID | Title | Priority | Story Points |
|-----------|-------|----------|--------------|
| RMS-041 | Sales Reports & Analytics Dashboard | High | 13 |
| RMS-042 | Online Order Reconciliation | High | 13 |
| RMS-043 | Head Office Multi-Outlet Dashboard | High | 13 |
| RMS-044 | City-wise & Zone-wise Grouping | Medium | 8 |
| RMS-045 | Tax Reports | High | 8 |
| RMS-046 | Staff Performance Reports | Medium | 8 |
| RMS-047 | Profit Margin & Food Cost Analysis | High | 8 |
| RMS-048 | Dynamic Custom Reports Builder | Medium | 13 |

## Technical Approach

### Analytics Architecture
- Read-optimized aggregation tables populated via scheduled jobs
- Materialized views for common query patterns
- Redis caching for dashboard widgets (5-minute TTL)
- Laravel Excel (Maatwebsite) for Excel/CSV exports
- DomPDF / wkhtmltopdf for PDF report generation
- Chart.js / Recharts for frontend visualization

### Data Aggregation Strategy
- Hourly aggregation job for daily dashboards
- Nightly rollup job for weekly/monthly summaries
- On-demand computation for custom reports
- Pre-computed comparison tables for YoY/MoM/WoW

### Multi-Outlet Consolidation
- Federated queries across outlet databases or single multi-tenant DB
- Outlet-scoped data with consolidated rollup views
- Head office role with cross-outlet data access

### Report Scheduling
- Laravel Scheduler for cron-based report generation
- Queued email delivery with PDF/Excel attachment
- Configurable recipient lists per report

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Large data volumes cause slow dashboard queries | High | Medium | Aggregation tables, Redis caching, query optimization, pagination, read replicas |
| Custom report builder complexity leads to SQL injection risk | Critical | Low | Parameterized queries, whitelist of allowed columns, ORM-based query building, input sanitization |
| Aggregator settlement data mismatch causes reconciliation false positives | Medium | High | Tolerance threshold configuration, manual review queue, discrepancy flagging with reason codes |
| Multi-outlet query load impacts POS transaction performance | High | Medium | Separate analytics database/read replica, off-peak scheduling, connection pooling isolation |
| Scheduled report emails flagged as spam | Low | Medium | SPF/DKIM configuration, SES integration, unsubscribe links, recipient verification |

## Dependencies

- M1 Sales/Billing data (complete) - transactional data source
- M1 Inventory data (complete) - food cost analysis input
- M2 Purchase/GRN data (complete) - cost analysis input
- M3 Online order data - reconciliation source
- M3 Customer data - segmentation analytics
- Email infrastructure (SMTP/SES) - scheduled report delivery

## Success Metrics

- Dashboard load time < 3 seconds for any date range
- 100% of GST reports match accounting system trial balance
- Reconciliation automation reduces manual effort by 80%
- Custom report builder adopted by 50%+ of power users
- Scheduled report delivery success rate > 98%

## Exit Criteria

- [ ] Sales dashboard live with real-time and historical views
- [ ] Reconciliation engine matching 95%+ of aggregator orders automatically
- [ ] Head office dashboard consolidating all pilot outlets
- [ ] GSTR-1 JSON export validated with GST portal
- [ ] Staff performance reports generated for at least one full pay cycle
- [ ] Food cost analysis matching actual ingredient costs within 2% variance
- [ ] Custom report builder tested with 10+ report templates
- [ ] Scheduled email reports delivered successfully to test recipients
