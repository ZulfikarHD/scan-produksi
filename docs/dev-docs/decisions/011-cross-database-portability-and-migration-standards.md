# ADR-011: Cross-Database Portability and Migration Standards

**Date:** 2026-09-06  
**Status:** accepted  
**Supersedes:** N/A  

## Context

The **scan-produksi** application operates in heterogeneous database environments throughout its operational lifecycle:
1. **Local Development & Fast CI:** High-speed development and test-driven development (TDD) utilize SQLite 3 in memory (`:memory:`) to execute the 200+ test suite in under 7 seconds.
2. **Shop-Floor Production Target:** The target packaging unit at PT Percetakan Dokumen Sekuriti runs an on-premise Linux server hosting MySQL 8.0+ with InnoDB storage engine.
3. **Enterprise Scalability:** The system design must accommodate potential migration to PostgreSQL 16+ or cloud managed clusters without application rewrites.

Differences in database engine capabilities create real operational risks:
- MySQL-specific syntax (such as relying on physical column positioning from `->after()`) can cause failure on PostgreSQL or SQLite if application queries assume physical column offsets.
- MySQL `utf8mb4` character encoding limits composite index prefixes to 3072 bytes. Unbounded `VARCHAR(255)` columns in multi-column indexes trigger fatal SQLSTATE `1071 Specified key was too long` errors during migration.
- SQLite handles JSON columns as `TEXT`, whereas MySQL and PostgreSQL provide native JSON binary types.
- Native `ENUM` data types are implemented incompatibly across MySQL, PostgreSQL, and SQLite.

## Decision

We establish four strict cross-database architectural standards across all migrations, models, and queries:

1. **Named Projection Isolation from Column Positioning:**
   - Physical column positioning clauses (`->after('...')`) are permitted in migrations solely for visual organization in MySQL administration tools (phpMyAdmin, Workbench).
   - In SQLite and PostgreSQL, `->after()` is safely ignored by Laravel SchemaGrammar without syntax errors.
   - All Eloquent models and Query Builder calls must query attributes by explicit column name (`select(['daily_report_id', ...])`). Positional numeric array fetching (`PDO::FETCH_NUM`) is strictly banned.

2. **Strict Composite Index Budgeting (< 350 Bytes):**
   - Every column participating in a composite index must have an explicit, bounded length (e.g., `VARCHAR(30)` or `VARCHAR(50)`).
   - The combined length of any composite index must not exceed 350 bytes under `utf8mb4` (4 bytes/char), providing a >88% safety buffer beneath MySQL's 3072-byte ceiling.

3. **Model-Level JSON Serialization Abstraction:**
   - Columns storing structured error logs (`sap_import_logs.error_log`) are defined as `$table->json('error_log')->nullable()`.
   - The corresponding model (`SapImportLog`) declares `'error_log' => 'array'` casting. This guarantees transparent `json_encode` on write and `json_decode` on read across SQLite (`TEXT`), MySQL (`JSON`), and PostgreSQL (`JSON`).

4. **Backed String Enums over Database Native ENUMs:**
   - All state, shift, role, and series fields are defined as bounded `$table->string('field', length)` backed by PHP Backed Enums (`App\Enums\*`), avoiding native database ENUM locking and syntax differences.

5. **Vendor-Neutral ANSI SQL for Aggregations:**
   - All real-time report calculations must use standard ANSI SQL functions (`COUNT(*)`, `SUM()`, `COALESCE(..., 0)`). Vendor-specific functions (`IFNULL`, `DATE_FORMAT`, `STRFTIME`, `TO_CHAR`) are prohibited in raw queries, delegating date manipulations to Carbon and PHP collections.

## Consequences

### Positive
- Zero friction or migration failures when transitioning between SQLite (local dev), MySQL 8.0+ (packaging unit on-premise), and PostgreSQL 16+ (enterprise cloud).
- Fast automated test runs (sub-7 seconds) on SQLite while retaining 100% confidence in production MySQL stability.
- Prevention of MySQL `utf8mb4` 3072-byte index overflow errors through bounded column lengths.
- Resilient JSON error log handling across all relational database storage formats.
- Complete isolation of application business logic from database-specific storage layout quirks.

### Negative
- Developers must explicitly specify lengths on `string()` migration columns when indexing is planned.
- Cannot utilize vendor-specific JSON path operators (e.g., MySQL `->>` or PostgreSQL `#>`) in raw database queries.

### Neutral
- Physical column order differs slightly between MySQL (where `->after()` takes effect) and SQLite/PostgreSQL (where columns append to table ends), with zero functional impact on the application.
