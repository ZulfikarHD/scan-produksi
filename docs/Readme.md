# Project Documentation — Sistem Monitoring & Pencatatan Produksi

PT Percetakan Dokumen Sekuriti (Unit Pengemasan)

---

## Documentation Index

### 1. Developer Documentation (`docs/dev-docs/`)
Technical references, architectural diagrams, data models, and decisions for software engineers.

- [System & Database Architecture Document](architecture.md)
- [Dev Docs Overview](dev-docs/README.md)
- **Features**:
  - [Authentication & Layout Shell](dev-docs/features/authentication-and-layout-shell.md)
  - [Petugas Management & RBAC](dev-docs/features/petugas-management-and-rbac.md)
  - [Production Order Master Data](dev-docs/features/production-order-master-data.md)
  - [SAP File Import & Modal Preview](dev-docs/features/sap-file-import-and-preview.md)
  - [Master Data Explorer & Manual Order Management](dev-docs/features/master-data-explorer-and-manual-order.md)
  - [Daily Production Report Session & Code Generator](dev-docs/features/daily-production-report-session.md)
  - [High-Speed Continuous Barcode Scanning & Inline Production Entry](dev-docs/features/continuous-barcode-scan-and-inline-production-entry.md)
  - [Transactional Hardening & Concurrency Protection](dev-docs/features/transactional-hardening-and-concurrency-protection.md)
  - [Schema Decluttering, String Bounds & Data Dictionary](dev-docs/features/schema-decluttering-and-data-dictionary.md)
  - [Dual-Path Primary Key & Model Architecture Alignment](dev-docs/features/dual-path-primary-key-and-model-alignment.md)
  - [Academic Thesis Database Synchronization](dev-docs/features/academic-thesis-database-synchronization.md)
  - [Production Portability & Multi-Database Verification](dev-docs/features/production-portability-and-multi-database-verification.md)
  - [Daily Production Report Management, Verification & Export](dev-docs/features/report-management-verification-and-export.md)
  - [Dashboard Analytics & 14-Day Production Visualization](dev-docs/features/dashboard-analytics-and-14-day-chart.md)
- **Architecture Decision Records (ADRs)**:
  - [ADR-001: Fortify Livewire & Multi-Identifier NP Authentication](dev-docs/decisions/001-fortify-livewire-and-np-authentication.md)
  - [ADR-002: Role-Based Access Control & Single-Surface Petugas Management](dev-docs/decisions/002-role-based-access-control-and-single-surface-user-management.md)
  - [ADR-003: Production Order Indexing and Schema Architecture](dev-docs/decisions/003-production-order-indexing-and-schema-architecture.md)
  - [ADR-004: Streaming SAP Import Parser and Modal Preview Architecture](dev-docs/decisions/004-streaming-sap-import-parser-and-modal-preview.md)
  - [ADR-005: Master Data Explorer, Audit Logging, and Manual Order Management Architecture](dev-docs/decisions/005-master-data-explorer-audit-log-and-manual-order-management.md)
  - [ADR-006: Daily Production Report Schema, Series Categorization, and Session Launcher Architecture](dev-docs/decisions/006-daily-production-report-schema-and-session-launcher.md)
  - [ADR-007: High-Speed Continuous Barcode Scanning, Focus Management, and Inline Table Editing Architecture](dev-docs/decisions/007-high-speed-continuous-barcode-scanning-and-inline-table-editing.md)
  - [ADR-008: Daily Report Verification, Correction Window, and Streaming Export Architecture](dev-docs/decisions/008-daily-report-verification-and-streaming-export-architecture.md)
  - [ADR-009: Dashboard Analytics Aggregation, Zero-Fill Timeseries, and Chart.js Integration](dev-docs/decisions/009-dashboard-analytics-aggregation-and-chartjs-visualization.md)
  - [ADR-010: Database Schema Refinement, Academic Primary Key Conventions, and Transactional Hardening](dev-docs/decisions/010-database-schema-refinement-and-academic-pk-conventions.md)
  - [ADR-011: Cross-Database Portability and Migration Standards](dev-docs/decisions/011-cross-database-portability-and-migration-standards.md)

### 2. User Guides (`docs/user-docs/`)
Step-by-step guides for unit supervisors and packaging operators.

- [User Docs Overview](user-docs/README.md)
- **Guides**:
  - [Panduan Login & Navigasi Aplikasi](user-docs/guides/panduan-login-dan-navigasi.md)
  - [Panduan Manajemen Data Petugas](user-docs/guides/panduan-manajemen-data-petugas.md)
  - [Panduan Master Data Order Produksi & Input Manual](user-docs/guides/panduan-master-data-order-produksi.md)
  - [Panduan Update Database Master Data SAP & Log Audit](user-docs/guides/panduan-update-database-sap.md)
  - [Panduan Inisiasi Sesi Laporan Produksi (Buat Laporan)](user-docs/guides/panduan-inisiasi-sesi-laporan-produksi.md)
  - [Panduan Pemindaian Barcode & Input Hasil Produksi](user-docs/guides/panduan-pemindaian-barcode-dan-input-produksi.md)
  - [Panduan Arsip Laporan, Koreksi, Verifikasi, dan Cetak Fisik](user-docs/guides/panduan-arsip-laporan-verifikasi-dan-cetak.md)
  - [Panduan Beranda & Analitik Produksi (Dashboard)](user-docs/guides/panduan-beranda-dan-analitik-dashboard.md)
  - [Panduan Verifikasi Struktur Data & Dokumen Laporan Produksi](user-docs/guides/panduan-verifikasi-skema-dan-spesifikasi-basis-data.md)
  - [Panduan Konfigurasi & Portabilitas Basis Data](user-docs/guides/panduan-konfigurasi-dan-portabilitas-basis-data.md)

### 3. Scrum Planning & Specifications
- [Phase 01: Foundation, Auth & User Management](scrum-planning/phase-01-foundation-auth-user-management.md)
- [Phase 01: UX Plan](scrum-planning/phase-01-ux-plan.md)
- [Phase 02: SAP Master Data Import & Management](scrum-planning/phase-02-sap-master-data-import.md)
- [Phase 02: UX Plan](scrum-planning/phase-02-ux-plan.md)
- [Phase 03: Barcode Scanning & Daily Production Entry](scrum-planning/phase-03-barcode-scan-daily-production.md)
- [Phase 03: UX Plan](scrum-planning/phase-03-ux-plan.md)
- [Phase 04: Report Management & Print](scrum-planning/phase-04-report-management-and-print.md)
- [Phase 05: Dashboard Analytics & 14-Day Chart](scrum-planning/phase-05-dashboard-analytics-14-day-chart.md)
- [Phase 06: Database Architecture Refinement & Academic Alignment](scrum-planning/phase-06-database-architecture-refinement-and-academic-alignment.md)
