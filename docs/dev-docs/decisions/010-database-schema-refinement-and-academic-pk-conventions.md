# ADR-010: Database Schema Refinement, Academic Primary Key Conventions, and Transactional Hardening

**Date:** 2026-09-06  
**Status:** accepted  
**Supersedes:** None (Complements ADR-003, ADR-006, and ADR-008)  

---

## Context

During the comprehensive database architecture review of the `scan-produksi` application and consultation with academic advisors (dosen pembimbing/penguji skripsi), three core architectural tensions and data integrity challenges were identified:

1. **Academic Primary Key Naming Dilemma (`tablename_id` vs `id`):**  
   Academic thesis standards (based on classical relational database design, Chen's ER notation, and ISO/IEC 11179) dictate that primary keys should be universally qualified by their entity name (e.g., `user_id`, `production_order_id`, `daily_report_id`, `daily_report_item_id`, `sap_import_log_id`) to ensure unambiguous natural joins and transparent relationship tracking in LRS diagrams. Conversely, modern web application frameworks such as Laravel adhere to the *Convention over Configuration* standard where the surrogate primary key is uniformly named `id`. Modifying the physical schema to `tablename_id` incurs significant framework friction, requiring manual configuration of `$primaryKey`, foreign key overrides, and third-party package adjustments (e.g., Fortify, session handlers).

2. **Database Cleanliness and "ERP Dump" Sprawl:**  
   The `production_orders` table contains over 30 columns reflecting the flat structure of exported SAP spreadsheets, combining English attributes with legacy factory abbreviations (e.g., `tgl_pakai_bb`, `jml_bb`, `jml_cd`, `total_bb`, `hcts_baik`). Furthermore, `total_bb` represents a stored calculated value (`jml_bb + jml_cd`), violating Third Normal Form (3NF). Migration schemas also exhibited redundant indexes (e.g., `report_code` declared with both `->unique()` and `->index()`), unbounded `string()` definitions defaulting to `VARCHAR(255)`, and column type mismatches (`daily_reports.notes` as `TEXT` vs `daily_report_items.notes` as `VARCHAR(255)`).

3. **Transaction Boundaries & Denormalization Drift:**  
   The barcode scanning loop in `ProductionEntry` inserted items (`DailyReportItem::create()`) and recalculated header totals (`refreshTotals()`) outside of an explicit database transaction (`DB::transaction`). Any network or database failure between these two operations caused denormalization drift. Additionally, `refreshTotals()` omitted `total_reject_quantity`, and `ReportSessionLauncher` was vulnerable to a Time-of-Check to Time-of-Use (TOCTOU) race condition during concurrent shift session initialization.

---

## Decision

We have established the following architectural decisions to balance academic rigor with engineering pragmatism:

### 1. Dual-Representation Strategy for Primary Key Conventions
- **Academic Thesis Representation (Bab III):** In conceptual ERD diagrams (Chen notation), Logical Record Structure (LRS) diagrams, and File Specification data dictionaries, the primary keys are formally documented as `user_id (PK)`, `production_order_id (PK)`, `daily_report_id (PK)`, `daily_report_item_id (PK)`, and `sap_import_log_id (PK)`. This satisfies the academic requirements of examiners without inducing application instability.
- **Physical Codebase & Eloquent Implementation:** The physical migrations and Eloquent models retain the standard `id` primary key convention to maintain compatibility with Laravel's built-in authentication, sessions, factory generators, and relationship conventions. A full physical refactoring pathway is documented in Phase 06 Scrum Planning should physical database inspection be mandated by examiners.

### 2. Physical Schema Cleanup & Portability Hardening
- **Index Deduplication:** Remove the redundant `->index()` call on `daily_reports.report_code`, retaining only `$table->string('report_code', 50)->unique();`.
- **Data Type Synchronization:** Convert `daily_report_items.notes` from `string()` (VARCHAR 255) to `text()->nullable()` to match the parent `daily_reports.notes` column, preventing string truncation on detailed operator notes.
- **Bounded VARCHAR Allocations:** Apply explicit length constraints to key lookup columns (`po_number: 50`, `no_obc: 50`, `machine_number: 50`, `product_code: 50`, `unit: 30`) to optimize B-Tree index memory efficiency under MySQL `utf8mb4`.
- **Validation of the Snapshot Pattern:** Reaffirm the intentional denormalization of product specifications in `daily_report_items` alongside a nullable `production_order_id` with `nullOnDelete()`, guaranteeing immutable audit archives for security printing compliance.

### 3. Transactional Hardening & Concurrency Protection
- **Atomic Barcode Scanning:** Wrap the item snapshot creation and totals recalculation inside `DB::transaction()` within `ProductionEntry::saveEntry()` (also applied to inline update/delete).
- **Comprehensive Aggregate Synchronization:** Refactor `refreshTotals()` in `ProductionEntry` and `EditReport` to execute an atomic `selectRaw` calculating `total_items_count`, `total_good_quantity`, and `total_reject_quantity` simultaneously, eliminating aggregate drift.
- **Atomic Session Launcher:** Refactor `ReportSessionLauncher::launchSession()` to intercept `QueryException` gracefully, preventing duplicate key crashes during concurrent session starts.
- **Preconditioned Verification:** `ReportDetail::verifyReport()` performs `UPDATE ... WHERE status = submitted` so draft/already-verified reports cannot be sealed incorrectly.
- **Implementation Status (US-6.3):** Completed — see `tests/Feature/Reports/ReportConcurrencyAndTransactionTest.php` and `docs/dev-docs/features/transactional-hardening-and-concurrency-protection.md`.

---

## Consequences

### Positive
- **Academic Approval:** Seamlessly fulfills the thesis advisor's explicit requirement for qualified primary key notation in documentation and defense presentations.
- **Zero Framework Friction:** Avoids introducing boilerplate `$primaryKey` configurations or breaking built-in Laravel relationship resolution.
- **Data Integrity & Consistency:** Guarantees zero denormalization drift between report item lines and header aggregates via ACID transactional boundaries.
- **Production Portability:** Prepares the database schema for migration from local SQLite to MySQL 8.0+ / PostgreSQL 16+ without index size errors or truncation faults.
- **Memory & Storage Optimization:** Eliminates redundant B-Tree indexes and over-allocated VARCHAR storage pages.

### Negative
- **Cognitive Mapping:** Developers reading the academic documentation alongside the raw database schema must recognize that documentation `tablename_id` corresponds to physical surrogate `id`.
- **Transaction Overhead:** Wrapping each barcode scan persistence in `DB::transaction()` incurs minor database lock overhead, though well within the capacity of the packaging station's throughput (~300 scans per shift).

### Neutral
- **ERP Dump Retention:** Retaining flat SAP fields in `production_orders` maintains rapid spreadsheet import performance while requiring field categorization in developer documentation.
