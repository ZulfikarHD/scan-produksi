# Developer Documentation

Welcome to the technical developer documentation for the **Sistem Monitoring & Pencatatan Produksi** application.

---

## Architecture & Standards

- **Backend Framework**: Laravel 13 (PHP 8.5)
- **Frontend / Reactivity**: Livewire 4 + Flux UI + Livewire Blaze
- **CSS / Styling**: Tailwind CSS v4
- **Testing Suite**: Pest PHP 5
- **Timezone**: `Asia/Jakarta` (WIB)

---

## Features

| Feature Doc | Description | Status |
|-------------|-------------|--------|
| [Authentication & Layout Shell](features/authentication-and-layout-shell.md) | Flux UI application layout, live shift banner helper, Fortify & Livewire authentication supporting NP/Username/Email. | Implemented (Phase 01) |
| [Petugas Management & RBAC](features/petugas-management-and-rbac.md) | Single-surface operator management (`/petugas`), Livewire debounced search, role/status filtering, center modal CRUD forms, and `EnsureUserRole` middleware. | Implemented (Phase 01) |
| [Production Order Master Data](features/production-order-master-data.md) | Robust master data schema, `ProductionOrderStatus` enum, composite indexes for <20ms barcode lookups, query scopes, and factory/seeder suite. | Implemented (Phase 02 / US-2.1) |
| [SAP File Import & Modal Preview](features/sap-file-import-and-preview.md) | Streaming OpenSpout spreadsheet reader (`.xlsx`, `.csv`), fuzzy header mapping, interactive validation preview modal (`<flux:modal name="import-preview">`), transactional upsert, and audit history logs. | Implemented (Phase 02 / US-2.2 & US-2.3) |
| [Master Data Explorer & Manual Order Management](features/master-data-explorer-and-manual-order.md) | Tabbed Master Data Explorer on `/update-database`, multi-attribute filtering, debounced search, manual emergency order creation/edit modals, technical specification detail drawer, order deletion guardrails, source file download, and Excel (.xlsx) export. | Implemented (Phase 02 / US-2.4 & US-2.5) |
| [Daily Production Report Session & Code Generator](features/daily-production-report-session.md) | Standardized report schemas (`daily_reports`, `daily_report_items`), series grouping (`PCHT`, `MMEA`, `HPTL`), auto shift detection, collision-free code generator, smart session launcher (`/reports/session`), and high-speed entry route. | Implemented (Phase 03 / US-3.1 & US-3.2) |
| [High-Speed Continuous Barcode Scanning & Inline Production Entry](features/continuous-barcode-scan-and-inline-production-entry.md) | Continuous 100% keyboard scanning loop, instant SAP order lookup, Alpine.js cursor autofocus transfer, Web Audio API sound synthesis, 9-column inline editable table, shift finalization lock, and standard A4 physical print layout. | Implemented (Phase 03 / US-3.3, US-3.4, US-3.5) |
| [Daily Production Report Management, Verification & Export](features/report-management-verification-and-export.md) | Multi-criteria report archive explorer, quick date presets, detailed order audit trail with supervisor verification locking, 24-hour correction window, supervisor deletion guardrails, OpenSpout XLSX streaming, and print-ready PDF views. | Implemented (Phase 04 / US-4.1 - US-4.5) |
| [Dashboard Analytics & 14-Day Production Visualization](features/dashboard-analytics-and-14-day-chart.md) | Unified Operational Command Center (`/dashboard`), daily production KPIs, interactive 14-day zero-filled Chart.js volume/defect trend, shift & machine distribution widgets, role-based quick action hub, and live recent report feed. | Implemented (Phase 05 / US-5.1 - US-5.4) |

---

## Architectural Decision Records (ADRs)

| ADR | Title | Date | Status |
|---|---|---|---|
| [ADR-001](decisions/001-fortify-livewire-and-np-authentication.md) | Fortify Livewire & Multi-Identifier NP Authentication | 2026-08-26 | Accepted |
| [ADR-002](decisions/002-role-based-access-control-and-single-surface-user-management.md) | Role-Based Access Control & Single-Surface Petugas Management | 2026-08-26 | Accepted |
| [ADR-003](decisions/003-production-order-indexing-and-schema-architecture.md) | Production Order Indexing and Schema Architecture | 2026-08-26 | Accepted |
| [ADR-004](decisions/004-streaming-sap-import-parser-and-modal-preview.md) | Streaming SAP Import Parser and Modal Preview Architecture | 2026-08-26 | Accepted |
| [ADR-005](decisions/005-master-data-explorer-audit-log-and-manual-order-management.md) | Master Data Explorer, Audit Logging, and Manual Order Management Architecture | 2026-08-26 | Accepted |
| [ADR-006](decisions/006-daily-production-report-schema-and-session-launcher.md) | Daily Production Report Schema, Series Categorization, and Session Launcher Architecture | 2026-08-26 | Accepted |
| [ADR-007](decisions/007-high-speed-continuous-barcode-scanning-and-inline-table-editing.md) | High-Speed Continuous Barcode Scanning, Focus Management, and Inline Table Editing Architecture | 2026-08-26 | Accepted |
| [ADR-008](decisions/008-daily-report-verification-and-streaming-export-architecture.md) | Daily Report Verification, Correction Window, and Streaming Export Architecture | 2026-08-26 | Accepted |
| [ADR-009](decisions/009-dashboard-analytics-aggregation-and-chartjs-visualization.md) | Dashboard Analytics Aggregation, Zero-Fill Timeseries, and Chart.js Integration | 2026-08-26 | Accepted |
