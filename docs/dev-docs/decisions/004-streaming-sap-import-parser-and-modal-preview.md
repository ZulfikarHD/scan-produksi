# ADR-004: Streaming SAP Import Parser and Modal Preview Architecture

**Date:** 2026-08-26  
**Status:** accepted  
**Supersedes:** N/A  

---

## Context

Phase 02 requires importing daily SAP ERP master production order files containing thousands of rows. The system needs to:
1. Parse multiple spreadsheet formats (`.xlsx`, `.xls`, `.csv`) reliably without exhausting server memory.
2. Accommodate varying column header namings produced by different SAP export routines.
3. Provide unit supervisors with a preview modal (US-2.3) to review data sanity and error alerts before writing to the database.
4. Execute bulk upserts safely within atomic database transactions and log audit trails.

---

## Decision

1. **Adopt OpenSpout for Streaming File Processing:** Utilize `openspout/openspout` for reading and generating spreadsheet files. OpenSpout operates on XML/CSV streams row-by-row, ensuring low memory consumption and high throughput regardless of file size.
2. **Fuzzy Header Alias Mapping with Fallback:** Implement a dictionary of aliases for each column (e.g., `po_number` matches `No PO`, `po_number`, `Order Number`, etc.) with case and punctuation normalization, falling back to standard 24-column index positioning when headers are unmapped.
3. **Interactive Flux Modal Preview (`<flux:modal name="import-preview">`):** Separate file parsing from database writes. The file is temporarily cached and parsed to show the first 20 rows with validation badges (`Valid` vs `Error`). Supervisors can review anomalies and choose whether to proceed with valid records or abort.
4. **Single Route Surface (`/update-database`):** Adhere to the Phase 02 UX Plan by placing the upload interface, download sample template, import history log, and master data explorer within interactive tabs on `/update-database`, avoiding route sprawl.

---

## Consequences

### Positive
- Predictable and bounded memory usage during large bulk imports.
- Robust against SAP export header variances across shifts.
- Immediate visual feedback and transparent error handling in Bahasa Indonesia before data commit.
- Full transactional rollback prevents database corruption during upload failures.

### Negative
- OpenSpout does not parse legacy binary `.xls` (Excel 97-2003 BIFF8) files natively; users are guided to save files as standard `.xlsx` or `.csv` if binary format errors occur.

### Neutral
- Temporary upload files are stored on disk during modal preview and cleaned up immediately after import or cancellation.
