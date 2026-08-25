# ADR-005: Master Data Explorer, Audit Logging, and Manual Order Management Architecture

**Date:** 2026-08-26  
**Status:** accepted  

---

## Context

In security printing and packaging operations (Unit Pengemasan), master data for production orders is primarily sourced from SAP ERP exports in bulk. However, two operational challenges arise on the shop floor:
1. **Urgent Ad-Hoc Jobs:** High-priority or emergency print jobs sometimes arrive at packaging stations before administrative SAP export batches are processed by the central IT office. Without a mechanism to register orders manually, operators cannot scan barcodes or generate shift reports.
2. **Audit & Traceability Requirements:** Factory supervisors need complete visibility into previous SAP import logs, the ability to download uploaded source spreadsheets, inspect row-level error reports, and export filtered datasets for coordination meetings.
3. **UX Single-Surface Constraint:** The Phase 02 UX Plan explicitly forbids creating multi-route sprawl (such as `/orders/create`, `/orders/edit`, or `/sap-logs`). All functionalities must reside on a single consolidated route (`/update-database`).

---

## Decision

We implemented a unified, tabbed interface within the `App\Livewire\MasterData\UpdateDatabase` component with the following architecture:
1. **Consolidated Tabbed Surface:** Tab 1 manages SAP spreadsheet uploads, validation previews, and audit logs. Tab 2 manages the Master Data Explorer, search, multi-attribute filtering, manual order CRUD modals, and XLSX exports.
2. **Modal-Driven Workflows:** 
   - `<flux:modal name="order-form">` handles both manual creation and editing without page reloads.
   - `<flux:modal name="order-detail">` provides a full-width technical print specification card with fulfillment percentage calculation.
   - `<flux:modal name="delete-order-confirm">` enforces status guardrails (blocking deletion of `in_progress` orders).
   - `<flux:modal name="log-error-detail">` displays row-by-row error logs with TXT report export.
3. **Permanent Audit Archiving:** Confirmed imports copy temporary upload files to private storage (`sap-imports/`), storing the relative path in `sap_import_logs.file_path` for historical download.
4. **Streaming XLSX Export:** `SapImportService::exportOrdersToXlsx()` streams filtered datasets with standard 24 SAP header columns using OpenSpout, preventing server memory spikes.

---

## Consequences

### Positive
- **Zero Route Sprawl:** Single-point entry `/update-database` reduces cognitive load and simplifies supervisor navigation.
- **Operational Resilience:** Emergency print orders can be created in under 30 seconds with immediate readiness for barcode scanners.
- **Shop-Floor Safety:** Preventing deletion of active (`in_progress`) orders protects real-time packaging scan data from accidental loss.
- **Traceability:** Permanent source file storage and downloadable error reports provide full audit compliance.

### Negative
- **Component Breadth:** The `UpdateDatabase` Livewire component manages multiple modal states and filters. Strict method organization (placing `render()` at top, followed by hooks and actions) is maintained to preserve readability.

### Neutral
- Filter states are synchronized with the browser URL query string (`#[Url]`) to allow supervisors to bookmark or share specific search queries.
