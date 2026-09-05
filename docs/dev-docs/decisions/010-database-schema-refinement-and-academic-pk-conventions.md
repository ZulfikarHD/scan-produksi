# ADR-010: Database Schema Refinement, Academic Primary Key Conventions, and Transactional Hardening

**Date:** 2026-09-06  
**Status:** accepted  
**Supersedes:** None (Complements ADR-003, ADR-006, and ADR-008)  

---

## Context

During the comprehensive database architecture review of the `scan-produksi` application and consultation with academic advisors (dosen pembimbing/penguji skripsi), three core architectural tensions and data integrity challenges were identified:

1. **Academic Primary Key Naming Dilemma (`tablename_id` vs `id`):**  
   Academic thesis standards (based on classical relational database design, Chen's ER notation, and ISO/IEC 11179) dictate that primary keys should be universally qualified by their entity name (e.g., `user_id`, `production_order_id`, `daily_report_id`, `daily_report_item_id`, `sap_import_log_id`) to ensure unambiguous natural joins and transparent relationship tracking in LRS diagrams. Conversely, modern web application frameworks such as Laravel adhere to the *Convention over Configuration* standard where the surrogate primary key is uniformly named `id`. Modifying the physical schema to `tablename_id` incurs framework friction, requiring manual configuration of `$primaryKey`, foreign key overrides, and careful validation/query updates.

2. **Database Cleanliness and "ERP Dump" Sprawl:**  
   The `production_orders` table contains over 30 columns reflecting the flat structure of exported SAP spreadsheets, combining English attributes with legacy factory abbreviations (e.g., `tgl_pakai_bb`, `jml_bb`, `jml_cd`, `total_bb`, `hcts_baik`). Furthermore, `total_bb` represents a stored calculated value (`jml_bb + jml_cd`), violating Third Normal Form (3NF). Migration schemas also exhibited redundant indexes (e.g., `report_code` declared with both `->unique()` and `->index()`), unbounded `string()` definitions defaulting to `VARCHAR(255)`, and column type mismatches (`daily_reports.notes` as `TEXT` vs `daily_report_items.notes` as `VARCHAR(255)`).

3. **Transaction Boundaries & Denormalization Drift:**  
   The barcode scanning loop in `ProductionEntry` inserted items (`DailyReportItem::create()`) and recalculated header totals (`refreshTotals()`) outside of an explicit database transaction (`DB::transaction`). Any network or database failure between these two operations caused denormalization drift. Additionally, `refreshTotals()` omitted `total_reject_quantity`, and `ReportSessionLauncher` was vulnerable to a Time-of-Check to Time-of-Use (TOCTOU) race condition during concurrent shift session initialization.

---

## Decision

We have established the following architectural decisions to balance academic rigor with engineering pragmatism:

### 1. Full Physical Primary Key Refactoring (Pathway 2 — Activated for US-6.1)

**Decision:** Execute Pathway 2. Physical migrations and Eloquent models now use qualified primary keys:

| Table | Physical PK | Eloquent `$primaryKey` |
|-------|-------------|------------------------|
| `users` | `user_id` | `user_id` |
| `production_orders` | `production_order_id` | `production_order_id` |
| `daily_reports` | `daily_report_id` | `daily_report_id` |
| `daily_report_items` | `daily_report_item_id` | `daily_report_item_id` |
| `sap_import_logs` | `sap_import_log_id` | `sap_import_log_id` |

Foreign keys reference parent qualified columns explicitly, for example:

- `production_orders.created_by` → `users.user_id`
- `daily_reports.user_id` / `supervisor_id` → `users.user_id`
- `daily_report_items.daily_report_id` → `daily_reports.daily_report_id`
- `daily_report_items.production_order_id` → `production_orders.production_order_id`
- `sap_import_logs.user_id` → `users.user_id`

**Compatibility bridge:** each domain model exposes `getIdAttribute()` returning `$this->getKey()` so Blade/Livewire call sites and `Auth::id()` continue to work without a full UI rewrite.

**Academic thesis representation (Bab III):** ERD Chen, LRS, and File Specification tables document the same physical names (`user_id (PK)`, etc.), eliminating the cognitive mapping gap that Pathway 1 would have left between docs and phpMyAdmin.

**Implementation Status (US-6.1):** Completed — see `tests/Feature/MasterData/PrimaryKeyArchitectureTest.php` and `docs/dev-docs/features/dual-path-primary-key-and-model-alignment.md`.

Pathway 1 (keep physical `id`, document academic names only) remains documented historically as the lower-risk alternative that was superseded for sidang demonstrability.

### 2. Physical Schema Cleanup & Portability Hardening
- **Index Deduplication:** Remove the redundant `->index()` call on `daily_reports.report_code`, retaining only `$table->string('report_code', 50)->unique();`.
- **Data Type Synchronization:** Convert `daily_report_items.notes` from `string()` (VARCHAR 255) to `text()->nullable()` to match the parent `daily_reports.notes` column, preventing string truncation on detailed operator notes.
- **Bounded VARCHAR Allocations:** Apply explicit length constraints to key lookup columns (`po_number: 50`, `no_obc: 50`, `machine_number: 50`, `product_code: 50`, `unit: 30`, plus `perso_type`, `seri`, `status`, and matching `daily_reports` / `daily_report_items` bounds) to optimize B-Tree index memory efficiency under MySQL `utf8mb4`.
- **Validation of the Snapshot Pattern:** Reaffirm the intentional denormalization of product specifications in `daily_report_items` alongside a nullable `production_order_id` with `nullOnDelete()`, guaranteeing immutable audit archives for security printing compliance.
- **Implementation Status (US-6.2):** Completed — see `tests/Feature/MasterData/SchemaDeclutteringAndBoundsTest.php`, `docs/dev-docs/features/schema-decluttering-and-data-dictionary.md`, and `SapImportService::boundString()`.
- **Data Dictionary Categorization:** `production_orders` columns are documented (migration header + `ProductionOrder` PHPDoc) as Operational Packaging Attributes vs Passthrough SAP Metadata without a physical table split.

### 3. Transactional Hardening & Concurrency Protection
- **Atomic Barcode Scanning:** Wrap the item snapshot creation and totals recalculation inside `DB::transaction()` within `ProductionEntry::saveEntry()` (also applied to inline update/delete).
- **Comprehensive Aggregate Synchronization:** Refactor `refreshTotals()` in `ProductionEntry` and `EditReport` to execute an atomic `selectRaw` calculating `total_items_count`, `total_good_quantity`, and `total_reject_quantity` simultaneously, eliminating aggregate drift.
- **Atomic Session Launcher:** Refactor `ReportSessionLauncher::launchSession()` to intercept `QueryException` gracefully, preventing duplicate key crashes during concurrent session starts.
- **Preconditioned Verification:** `ReportDetail::verifyReport()` performs `UPDATE ... WHERE status = submitted` so draft/already-verified reports cannot be sealed incorrectly.
- **Implementation Status (US-6.3):** Completed — see `tests/Feature/Reports/ReportConcurrencyAndTransactionTest.php` and `docs/dev-docs/features/transactional-hardening-and-concurrency-protection.md`.

### 4. Academic Thesis Synchronization (US-6.5)
- **Bab III Documentation Synchronization:** Synchronize `docs/BAB_III_Draft.md` to reflect the refined database architecture across the Chen ERD (Gambar 3.20), LRS diagram (Gambar 3.21), and File Specification tables (Tabel 3.10 s.d. Tabel 3.14).
- **Physical-to-Academic Alignment:** Ensure that every field name, data type, bounded VARCHAR length, foreign key constraint, and index in Bab III matches the physical database schema and migrations.
- **Academic Writing Rigor:** Enforce industrial-paper-writing standards throughout the Bab III database documentation (flowing narrative paragraphs, grounded shop-floor terminology, no AI clichés or fluff).
- **Implementation Status (US-6.5):** Completed — see `tests/Feature/MasterData/AcademicThesisSynchronizationTest.php`, `docs/BAB_III_Draft.md`, and `docs/dev-docs/features/academic-thesis-database-synchronization.md`.

---

## Consequences

### Positive
- **Academic Approval:** Physical schema and thesis documentation both present qualified primary keys for sidang inspection.
- **Demonstrable Consistency:** phpMyAdmin/DBeaver, ERD, and LRS show the same PK names.
- **Data Integrity & Consistency:** Guarantees zero denormalization drift between report item lines and header aggregates via ACID transactional boundaries.
- **Production Portability:** Prepares the database schema for migration from local SQLite to MySQL 8.0+ / PostgreSQL 16+ without index size errors or truncation faults.
- **Memory & Storage Optimization:** Eliminates redundant B-Tree indexes and over-allocated VARCHAR storage pages.
- **UI Compatibility:** `$model->id` accessor limits blast radius of the PK rename across Blade and Livewire.

### Negative
- **Framework Friction:** Every relationship, `unique` ignore column, and raw `join`/`orderBy` on PK columns must name the qualified key explicitly.
- **Transaction Overhead:** Wrapping each barcode scan persistence in `DB::transaction()` incurs minor database lock overhead, though well within the capacity of the packaging station's throughput (~300 scans per shift).
- **Fresh Migrate Required:** Existing local databases must run `migrate:fresh` (or equivalent) after pulling US-6.1.

### Neutral
- **ERP Dump Retention:** Retaining flat SAP fields in `production_orders` maintains rapid spreadsheet import performance while requiring field categorization in developer documentation.
- **Laravel System Tables:** `jobs`, `failed_jobs`, `sessions`, and `cache` retain framework-default `id` columns; they are outside the academic domain model.
