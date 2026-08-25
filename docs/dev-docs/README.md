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

---

## Architectural Decision Records (ADRs)

| ADR | Title | Date | Status |
|-----|-------|------|--------|
| [ADR-001](decisions/001-fortify-livewire-and-np-authentication.md) | Fortify Livewire & Multi-Identifier NP Authentication | 2026-08-26 | Accepted |
| [ADR-002](decisions/002-role-based-access-control-and-single-surface-user-management.md) | Role-Based Access Control & Single-Surface Petugas Management | 2026-08-26 | Accepted |
| [ADR-003](decisions/003-production-order-indexing-and-schema-architecture.md) | Production Order Indexing and Schema Architecture | 2026-08-26 | Accepted |
| [ADR-004](decisions/004-streaming-sap-import-parser-and-modal-preview.md) | Streaming SAP Import Parser and Modal Preview Architecture | 2026-08-26 | Accepted |
| [ADR-005](decisions/005-master-data-explorer-audit-log-and-manual-order-management.md) | Master Data Explorer, Audit Logging, and Manual Order Management Architecture | 2026-08-26 | Accepted |
