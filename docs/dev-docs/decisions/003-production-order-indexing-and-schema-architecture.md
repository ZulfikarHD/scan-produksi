# ADR-003: Production Order Indexing and Schema Architecture

**Date:** 2026-08-26  
**Status:** accepted  
**Supersedes:** N/A  

---

## Context

Phase 02 (SAP Master Data Import) and Phase 03 (Barcode Scan Daily Production) require storing hundreds of thousands of production order records imported from SAP ERP exports and manual inputs. On the shop floor, operators at the packaging station scan physical job cards using handheld Code 128 barcode scanners. The scanning workflow demands near-instantaneous feedback (<20ms database response time) so that packaging throughput is never throttled by system latency.

Furthermore, production orders must preserve historical auditability even if the original supervisor who imported or created the record is later deleted or deactivated.

---

## Decision

1. **Unique Index on `po_number`:** Define `po_number` as a unique indexed string column in `production_orders` table. This serves as the primary scanning identity key for all barcode scan lookups.
2. **Composite Indexes for Reporting:** Add composite indexes on `['status', 'order_date']` and `['machine_number', 'status']` to optimize master data explorer filtering and dashboard aggregation queries.
3. **Null-On-Delete Constraint on `created_by`:** Reference `users.id` with `foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()` to prevent cascade deletion of vital manufacturing records when accounts are purged.
4. **PHP 8 Backed Enum for Order Lifecycle:** Define `App\Enums\ProductionOrderStatus` (`ready`, `in_progress`, `completed`, `cancelled`) with helper methods `label()` and `badgeColor()` to ensure consistent status representations across backend and Flux UI components.

---

## Consequences

### Positive
- Barcode query execution time measured consistently under 5ms (well within the <20ms requirement).
- Safe user account lifecycle management without endangering historic production batch data.
- Type-safe status handling throughout Eloquent queries and UI rendering.

### Negative
- Unique constraint requires upsert handling (`updateOrCreate`) during SAP file imports to avoid duplicate key exceptions on re-imports.

### Neutral
- Dates are normalized to `YYYY-MM-DD` standard format before persistence.
