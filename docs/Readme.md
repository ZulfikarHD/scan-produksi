# Project Documentation — Sistem Monitoring & Pencatatan Produksi

PT Percetakan Dokumen Sekuriti (Unit Pengemasan)

---

## Documentation Index

### 1. Developer Documentation (`docs/dev-docs/`)
Technical references, architectural diagrams, data models, and decisions for software engineers.

- [Dev Docs Overview](dev-docs/README.md)
- **Features**:
  - [Authentication & Layout Shell](dev-docs/features/authentication-and-layout-shell.md)
  - [Petugas Management & RBAC](dev-docs/features/petugas-management-and-rbac.md)
  - [Production Order Master Data](dev-docs/features/production-order-master-data.md)
  - [SAP File Import & Modal Preview](dev-docs/features/sap-file-import-and-preview.md)
  - [Master Data Explorer & Manual Order Management](dev-docs/features/master-data-explorer-and-manual-order.md)
  - [Daily Production Report Session & Code Generator](dev-docs/features/daily-production-report-session.md)
- **Architecture Decision Records (ADRs)**:
  - [ADR-001: Fortify Livewire & Multi-Identifier NP Authentication](dev-docs/decisions/001-fortify-livewire-and-np-authentication.md)
  - [ADR-002: Role-Based Access Control & Single-Surface Petugas Management](dev-docs/decisions/002-role-based-access-control-and-single-surface-user-management.md)
  - [ADR-003: Production Order Indexing and Schema Architecture](dev-docs/decisions/003-production-order-indexing-and-schema-architecture.md)
  - [ADR-004: Streaming SAP Import Parser and Modal Preview Architecture](dev-docs/decisions/004-streaming-sap-import-parser-and-modal-preview.md)
  - [ADR-005: Master Data Explorer, Audit Logging, and Manual Order Management Architecture](dev-docs/decisions/005-master-data-explorer-audit-log-and-manual-order-management.md)
  - [ADR-006: Daily Production Report Schema, Series Categorization, and Session Launcher Architecture](dev-docs/decisions/006-daily-production-report-schema-and-session-launcher.md)

### 2. User Guides (`docs/user-docs/`)
Step-by-step guides for unit supervisors and packaging operators.

- [User Docs Overview](user-docs/README.md)
- **Guides**:
  - [Panduan Login & Navigasi Aplikasi](user-docs/guides/panduan-login-dan-navigasi.md)
  - [Panduan Manajemen Data Petugas](user-docs/guides/panduan-manajemen-data-petugas.md)
  - [Panduan Master Data Order Produksi & Input Manual](user-docs/guides/panduan-master-data-order-produksi.md)
  - [Panduan Update Database Master Data SAP & Log Audit](user-docs/guides/panduan-update-database-sap.md)
  - [Panduan Inisiasi Sesi Laporan Produksi (Buat Laporan)](user-docs/guides/panduan-inisiasi-sesi-laporan-produksi.md)

### 3. Scrum Planning & Specifications
- [Phase 01: Foundation, Auth & User Management](scrum-planning/phase-01-foundation-auth-user-management.md)
- [Phase 01: UX Plan](scrum-planning/phase-01-ux-plan.md)
- [Phase 02: SAP Master Data Import & Management](scrum-planning/phase-02-sap-master-data-import.md)
- [Phase 02: UX Plan](scrum-planning/phase-02-ux-plan.md)
- [Phase 03: Barcode Scanning & Daily Production Entry](scrum-planning/phase-03-barcode-scan-daily-production.md)
- [Phase 03: UX Plan](scrum-planning/phase-03-ux-plan.md)
- [Phase 04: Report Management & Print](scrum-planning/phase-04-report-management-and-print.md)
- [Phase 05: Dashboard Analytics & 14-Day Chart](scrum-planning/phase-05-dashboard-analytics-14-day-chart.md)
