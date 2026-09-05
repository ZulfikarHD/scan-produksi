# Schema Decluttering, String Bounds & Data Dictionary

## Overview

This module cleans the physical database schema for Phase 06 US-6.2: remove the redundant B-Tree index on `daily_reports.report_code`, widen `daily_report_items.notes` from `VARCHAR(255)` to `TEXT` to match the parent report notes column, apply explicit bounded `VARCHAR` lengths on indexed lookup columns to reduce MySQL `utf8mb4` index memory pressure, document the `production_orders` data dictionary split between Operational Packaging Attributes and Passthrough SAP Metadata, and harden `SapImportService` so overlong spreadsheet cells are truncated before upsert.

## Architecture Diagram

```mermaid
flowchart TD
    A[SAP Spreadsheet Upload] --> B[SapImportService mapAndValidateRow]
    B --> C[boundString VARCHAR 50 or 30]
    C --> D[ProductionOrder updateOrCreate]
    D --> E[(production_orders bounded columns)]

    F[Barcode Scan Save] --> G[DailyReportItem create]
    G --> H[(daily_report_items TEXT notes)]
    H --> I[(daily_reports unique report_code)]
```

```mermaid
flowchart LR
    subgraph operational [Operational Packaging Attributes]
        OP1[po_number]
        OP2[product_name]
        OP3[machine_number]
        OP4[target_quantity]
        OP5[unit status]
    end

    subgraph passthrough [Passthrough SAP Metadata]
        PS1[tgl_pakai_bb tgl_cetak]
        PS2[jml_bb jml_cd total_bb]
        PS3[hcts_baik kemas_quantity]
    end

    operational --> ScanLoop[Barcode and Report Entry]
    passthrough --> Audit[Import Audit Fidelity]
```

## Data Model

```mermaid
erDiagram
    PRODUCTION_ORDERS ||--o{ DAILY_REPORT_ITEMS : snapshots
    DAILY_REPORTS ||--o{ DAILY_REPORT_ITEMS : contains

    PRODUCTION_ORDERS {
        bigint id PK
        string po_number UK "VARCHAR(50)"
        string no_obc "VARCHAR(50)"
        string perso_type "VARCHAR(50)"
        string seri "VARCHAR(50)"
        string product_code "VARCHAR(50)"
        string machine_number "VARCHAR(50)"
        string unit "VARCHAR(30)"
        string status "VARCHAR(30)"
    }

    DAILY_REPORTS {
        bigint id PK
        string report_code UK "VARCHAR(50) unique only"
        string product_series "VARCHAR(30)"
        string shift "VARCHAR(30)"
        string status "VARCHAR(30)"
        text notes
    }

    DAILY_REPORT_ITEMS {
        bigint id PK
        string po_number "VARCHAR(50)"
        string machine_number "VARCHAR(50)"
        string unit "VARCHAR(30)"
        text notes "aligned with parent"
    }
```

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Migration | `database/migrations/2026_08_26_000002_create_production_orders_table.php` | Bounded VARCHAR lengths + data dictionary comments |
| Migration | `database/migrations/2026_08_26_000004_create_daily_reports_and_items_tables.php` | Unique-only `report_code`, TEXT item notes, bounded item strings |
| Model | `app/Models/ProductionOrder.php` | PHPDoc data dictionary (Operational vs Passthrough) |
| Service | `app/Services/SapImportService.php` | `boundString()` sanitization before upsert |
| Feature Tests | `tests/Feature/MasterData/SchemaDeclutteringAndBoundsTest.php` | Index, TEXT notes, bounds, SAP import validation |

## Flow Explanation

1. **User triggers** — Supervisor imports a SAP spreadsheet, or an operator saves a scanned line with optional notes.
2. **Request handling** — `SapImportService::mapAndValidateRow()` trims and bounds string fields (`po_number`, `no_obc`, `perso_type`, `seri`, `product_code`, `machine_number`, `unit`) before persistence.
3. **Business logic** — Eloquent writes land in schema columns sized for MySQL `utf8mb4` index pages. Item notes use `TEXT` so long operator remarks are not truncated at 255 characters. `report_code` relies on a single unique index.
4. **Response** — Import preview / upsert succeeds without MySQL 1406 data-too-long errors for runaway cells; report entry stores full notes.

## API Endpoints (if applicable)

None found — schema and import/service hardening only; existing Livewire routes unchanged (`database.update`, `reports.entry`, `reports.session`).

## Decisions & Trade-offs

- **Why edit create migrations instead of additive alter migrations?** Phase 06 targets a pre-production academic schema; scrum US-6.2 explicitly adjusts the create migration files. Local/dev databases use `migrate:fresh`.
- **Why truncate in `SapImportService` rather than fail the row?** Real SAP exports occasionally contain padded cells; generous bounds (50/30) already exceed typical PO lengths (10–12 digits). Soft truncation preserves import throughput while protecting MySQL strict mode.
- **Why keep flat SAP columns?** Retaining Passthrough SAP Metadata preserves one-pass spreadsheet import performance and audit fidelity; categorization is documentary rather than a physical table split.

## Related

- [ADR-010: Database Schema Refinement, Academic Primary Key Conventions, and Transactional Hardening](../decisions/010-database-schema-refinement-and-academic-pk-conventions.md)
- [Production Order Master Data](./production-order-master-data.md)
- [SAP File Import & Modal Preview](./sap-file-import-and-preview.md)
- [Transactional Hardening & Concurrency Protection](./transactional-hardening-and-concurrency-protection.md)
- [System Architecture — Redundant Indexes & Data Types](../../architecture.md)
