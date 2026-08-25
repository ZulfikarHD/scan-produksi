# ADR-006: Daily Production Report Schema, Series Categorization, and Session Launcher Architecture

**Date:** 2026-08-26  
**Status:** accepted  

## Context
Operators and packaging supervisors at the security printing facility produce daily packaging reports categorized by excise stamp (*Pita Cukai*) product series:
- **PCHT**: Pita Cukai Hasil Tembakau (Tobacco / Cigarettes)
- **MMEA**: Minuman Mengandung Etil Alkohol (Alcohol / Spirits)
- **HPTL**: Hasil Pengolahan Tembakau Lainnya / Rokok Elektrik (E-cigarettes & Vape)

Each reporting session represents packaging output from a specific date and work shift (Shift 1, Shift 2, Shift 3). Without strict database-level constraints and session identification, duplicate reports could be created simultaneously by different workstations during shift handovers, causing data fragmentation and inaccurate aggregation.

Furthermore, operators require a frictionless initiation flow that automatically detects the current shift and opens any existing draft report without manual search or duplicate insertion errors.

## Decision
1. **Relational Schema Design**:
   - Create `daily_reports` and `daily_report_items` tables with composite unique constraints on `(product_series, report_date, shift)`.
   - Store report snapshots of SAP order attributes (`po_number`, `product_name`, `specification`, `machine_number`, `target_quantity`, `good_quantity`) in `daily_report_items` so historical daily reports remain immutable even if master SAP records are re-imported later.
2. **Standardized Code Generator (`ReportCodeGenerator`)**:
   - Establish report code naming conventions: `[SERIES]-[YYYY-MM-DD]-S[SHIFT]` (e.g. `PCHT-2026-08-26-S1` or base code `PCHT-2026-08-26`).
3. **Smart Session Launcher (`ReportSessionLauncher`)**:
   - Consolidate session initiation into a single reactive Livewire component at `/reports/session`.
   - Pre-populate current date (WIB) and auto-detect current work shift from local Jakarta time (`Shift 1: 07:00-15:00`, `Shift 2: 15:00-23:00`, `Shift 3: 23:00-07:00`).
   - Implement automatic session resolution: if a report for the selected category, date, and shift already exists, resume it immediately; otherwise, create a new draft and route to `/reports/{report_code}/entry`.
4. **Lean Model Architecture**:
   - Avoid repository or micro-scope boilerplate on `DailyReport` and `DailyReportItem` models, keeping database interactions direct in the Livewire component in compliance with project development guidelines.

## Consequences

### Positive
- **Guaranteed Data Integrity**: Database-level unique constraints prevent duplicate report containers per shift and category.
- **Audit Immutability**: Item records store data snapshots alongside foreign keys, preserving historical reporting integrity.
- **Frictionless Operator Experience**: Automatic shift detection and smart session resuming eliminate redundant navigation and modal dialogues.

### Negative
- **Shift Splitting**: Reports spanning across midnight (Shift 3: 23:00 - 07:00 WIB) belong to the date on which the shift was initiated.

### Neutral
- Route Model Binding in Livewire uses the custom key `report_code` rather than internal primary key `id` to provide human-readable, bookmarkable report URLs.
