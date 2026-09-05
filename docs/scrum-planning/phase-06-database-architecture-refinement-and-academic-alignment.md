# Phase 06 - Database Architecture Refinement & Academic Alignment

**Sprint Goal:** Menyempurnakan arsitektur basis data, membersihkan skema migrasi dari indeks redundan dan tipe data inkonsisten, memperkuat batas transaksi atomik (*ACID transactions*) pada pemindaian barcode, serta menyelaraskan konvensi penamaan Primary Key sesuai standar akademis dosen (`tablename_id`) tanpa merusak stabilitas framework Laravel.

**Estimated Duration:** 4-5 days  
**Depends on:** Phase 01–Phase 05 (Core Features Completed)  
**Total Story Points:** 32 pts  

---

## Architecture Context & Problem Analysis

Dari hasil audit menyeluruh terhadap arsitektur basis data `scan-produksi`, ditemukan sejumlah area perbaikan struktural:

1. **Permintaan Dosen Pembimbing / Penguji Skripsi:** Dosen mewajibkan setiap Primary Key tidak hanya bernama `id`, melainkan `tablename_id` (misalnya `user_id`, `production_order_id`, `daily_report_id`, `daily_report_item_id`, `sap_import_log_id`) agar memenuhi kaidah teori relasional klasik (Chen / ISO/IEC 11179) untuk diagram ERD dan LRS.
2. **Kerapian Skema (*Database Messiness*):**
   - Kolom `production_orders` menumpuk lebih dari 30 kolom dengan gaya penamaan campur aduk (bahasa Inggris vs singkatan SAP Indonesia) dan redundansi nilai kalkulasi (`total_bb = jml_bb + jml_cd`).
   - Indeks ganda redundan pada `daily_reports.report_code` (`->unique()->index()`).
   - Ketidakcocokan tipe kolom catatan: `daily_reports.notes` bertipe `text`, sedangkan `daily_report_items.notes` bertipe `string(255)` yang rawan terpotong (*truncate*).
   - Penggunaan `string()` tanpa batas panjang default `VARCHAR(255)` memboroskan memori halaman indeks pohon B-Tree pada MySQL `utf8mb4`.
3. **Celah Transaksi & Drift Agregat:**
   - Penyimpanan pemindaian barcode pada `ProductionEntry` tidak dibungkus `DB::transaction`.
   - Method `refreshTotals()` tidak memperhitungkan `total_reject_quantity`, menimbulkan desinkronisasi kolom agregat.
   - Pemeriksaan sesi laporan pada `ReportSessionLauncher` rentan terhadap *race condition* (TOCTOU).

---

## User Stories

### US-6.1: Dual-Path Primary Key & Model Architecture Alignment

**As a** software engineer & thesis author (Zulfikar Hidayatullah)  
**I want** to implement a robust primary key strategy that satisfies the academic advisor's requirement for `tablename_id` while preserving seamless Laravel Eloquent ORM operations  
**So that** the thesis defense (sidang skripsi) passes academic evaluation with perfect marks without causing breaking changes or maintenance friction in the application codebase  

**Acceptance Criteria:**
- [x] **Pathway 1 (Recommended Academic Bridge):** Documented historically; superseded by Pathway 2 for sidang demonstrability.
  - Physical database retains standard `id` surrogate primary keys for Laravel ecosystem harmony. *(Not used — Pathway 2 activated.)*
  - Model Eloquent classes expose virtual accessor or alias methods (e.g., `getUserIdAttribute()`, `getProductionOrderIdAttribute()`) where needed for transparency. *(Implemented as `$model->id` → `getKey()` alias on Pathway 2.)*
  - Naskah Bab III Skripsi (ERD Chen, diagram LRS, dan tabel spesifikasi file) mendokumentasikan PK sebagai `user_id (PK)`, `production_order_id (PK)`, `daily_report_id (PK)`, `daily_report_item_id (PK)`, dan `sap_import_log_id (PK)`.
- [x] **Pathway 2 (Full Physical Database Refactoring — Activated):**
  - Migration schemas updated to define primary keys explicitly:
    - `users`: `$table->id('user_id');`
    - `production_orders`: `$table->id('production_order_id');`
    - `daily_reports`: `$table->id('daily_report_id');`
    - `daily_report_items`: `$table->id('daily_report_item_id');`
    - `sap_import_logs`: `$table->id('sap_import_log_id');`
  - Eloquent Models configure `protected $primaryKey = 'tablename_id';`.
  - All foreign keys updated with explicit references:
    - `production_orders.created_by` $\rightarrow$ `users.user_id`
    - `daily_reports.user_id` $\rightarrow$ `users.user_id`
    - `daily_reports.supervisor_id` $\rightarrow$ `users.user_id`
    - `daily_report_items.daily_report_id` $\rightarrow$ `daily_reports.daily_report_id`
    - `daily_report_items.production_order_id` $\rightarrow$ `production_orders.production_order_id`
    - `sap_import_logs.user_id` $\rightarrow$ `users.user_id`
  - Route model binding updated where route keys are involved. *(Reports continue to bind on `report_code`; numeric PK call sites use `$model->id` accessor.)*
  - Full test suite (`php artisan test --compact`) passes 100% without regression.

**Technical Specs:**
```php
// Pathway 2 (activated):
class DailyReport extends Model
{
    protected $primaryKey = 'daily_report_id';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyReportItem::class, 'daily_report_id', 'daily_report_id');
    }
}
```

**Tasks:**
1. [x] Document the dual-path strategy in [docs/architecture.md](../architecture.md) and [docs/dev-docs/decisions/010-database-schema-refinement-and-academic-pk-conventions.md](../dev-docs/decisions/010-database-schema-refinement-and-academic-pk-conventions.md).
2. [x] Validate whether Pathway 1 or Pathway 2 is preferred for immediate physical execution. *(Pathway 2 selected.)*
3. [x] If Pathway 2 is triggered, update migration definitions and run fresh migration rollback/up tests.
4. [x] Update Model `$primaryKey` properties, relationship definitions, factories, and seeders.
5. [x] Execute Pest test suite to guarantee zero regression across all existing endpoints.

---

### US-6.2: Schema Decluttering, String Bounds & Index Deduplication

**As a** system administrator and database practitioner  
**I want** a clean, uncluttered, and storage-efficient database schema with eliminated redundant indexes and standardized data types  
**So that** the database minimizes memory consumption, prevents index bloat, avoids note truncation errors, and remains performant at scale  

**Acceptance Criteria:**
- [x] Remove duplicate B-Tree index on `daily_reports.report_code` by dropping the redundant `->index()` statement, preserving only `$table->string('report_code', 50)->unique();`.
- [x] Convert `daily_report_items.notes` from `string()` (VARCHAR 255) to `text()->nullable()` to match the parent `daily_reports.notes` column.
- [x] Apply explicit, bounded VARCHAR lengths to prevent over-allocation on MySQL `utf8mb4`:
  - `production_orders.po_number`: `VARCHAR(50)`
  - `production_orders.no_obc`: `VARCHAR(50)`
  - `production_orders.perso_type`: `VARCHAR(50)`
  - `production_orders.seri`: `VARCHAR(50)`
  - `production_orders.product_code`: `VARCHAR(50)`
  - `production_orders.machine_number`: `VARCHAR(50)`
  - `production_orders.unit`: `VARCHAR(30)`
  - `production_orders.status`: `VARCHAR(30)`
  - `daily_reports.product_series`: `VARCHAR(30)`
  - `daily_reports.shift`: `VARCHAR(30)`
  - `daily_reports.status`: `VARCHAR(30)`
  - `daily_report_items.po_number`: `VARCHAR(50)`
  - `daily_report_items.machine_number`: `VARCHAR(50)`
  - `daily_report_items.unit`: `VARCHAR(30)`
- [x] Document data dictionary categorization in `production_orders` separating Operational Packaging Attributes from Passthrough SAP Metadata.
- [x] Validate that existing SAP import files parse and upsert cleanly into bounded columns without truncation errors.

**Tasks:**
1. [x] Create or adjust migration `database/migrations/2026_08_26_000004_create_daily_reports_and_items_tables.php` to remove redundant index and widen notes to `text`.
2. [x] Apply explicit lengths on VARCHAR definitions across `production_orders` and `daily_report_items`.
3. [x] Verify that `SapImportService` handles bounded strings properly.
4. [x] Run `php artisan test --compact --filter=ProductionOrderTest` and `php artisan test --compact --filter=ReportSessionTest`.

---

### US-6.3: Transactional Hardening & Concurrency Protection for Barcode Scanning

**As a** packaging station operator (Petugas Kemas)  
**I want** every scanned barcode item and report total recalculation to execute atomically within a database transaction  
**So that** unexpected network dropouts or database interruptions never cause denormalization drift or inconsistent shift totals  

**Acceptance Criteria:**
- [x] Wrap `DailyReportItem::create()` and header aggregation update inside `DB::transaction()` in `App\Livewire\Reports\ProductionEntry::saveEntry()`.
- [x] Refactor `refreshTotals()` in `ProductionEntry` and `EditReport` to compute all three aggregates simultaneously in a single atomic SQL query:
  - `total_items_count`
  - `total_good_quantity`
  - `total_reject_quantity`
- [x] Protect `App\Livewire\Reports\ReportSessionLauncher::launchSession()` against TOCTOU race conditions:
  - Utilize `DailyReport::firstOrCreate()` or wrap session creation in a `try-catch` catching `QueryException` on duplicate composite key `['product_series', 'report_date', 'shift']`.
  - Automatically redirect the second operator to the already-created session without showing an unhandled SQL error toast.
- [x] Ensure that supervisor verification in `ReportDetail::verifyReport()` uses atomic update with state precondition check (`where('status', ReportStatus::SUBMITTED)->update(...)`).

**Technical Specs:**
```php
// app/Livewire/Reports/ProductionEntry.php
DB::transaction(function () use ($qty) {
    DailyReportItem::create([
        'daily_report_id' => $this->report->id,
        'production_order_id' => $this->scannedOrder->id,
        'po_number' => $this->scannedOrder->po_number,
        'product_name' => $this->scannedOrder->product_name,
        'specification' => $this->scannedOrder->specification,
        'machine_number' => $this->scannedOrder->machine_number,
        'target_quantity' => $this->scannedOrder->target_quantity,
        'good_quantity' => $qty,
        'reject_quantity' => 0,
        'unit' => $this->scannedOrder->unit ?? 'lembar',
        'notes' => $this->itemNotes ?: null,
        'scanned_at' => now(),
    ]);

    $totals = DailyReportItem::where('daily_report_id', $this->report->id)
        ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(good_quantity), 0) as total_good, COALESCE(SUM(reject_quantity), 0) as total_reject')
        ->first();

    $this->report->update([
        'total_items_count' => (int) $totals->total_items,
        'total_good_quantity' => (int) $totals->total_good,
        'total_reject_quantity' => (int) $totals->total_reject,
    ]);
});
```

**Tasks:**
1. [x] Update `ProductionEntry.php` to wrap item creation and aggregate recalculation in `DB::transaction()`.
2. [x] Refactor `refreshTotals()` to include `total_reject_quantity` via single `selectRaw`.
3. [x] Update `ReportSessionLauncher.php` to handle duplicate session creation race conditions gracefully.
4. [x] Write Pest feature test simulating concurrent scan insertions and verifying aggregate consistency.

---

### US-6.4: Production Portability Verification (SQLite to MySQL & PostgreSQL)

**As a** DevOps engineer & database architect  
**I want** to verify that all migration files and queries execute seamlessly across SQLite, MySQL 8.0+, and PostgreSQL 16+  
**So that** transitioning the application from local Laragon/SQLite to the enterprise production server occurs with zero downtime or syntax incompatibilities  

**Acceptance Criteria:**
- [ ] Confirm no MySQL-only syntax (such as relying on physical column positioning from `->after()`) causes functional breakage when ignored by PostgreSQL or SQLite.
- [ ] Ensure all composite index key lengths comply with MySQL `utf8mb4` 3072-byte prefix limits.
- [ ] Verify JSON column storage and casting for `sap_import_logs.error_log` across all database drivers.
- [ ] Document database connection configuration guidelines and environment variables in [docs/architecture.md](../architecture.md).

**Tasks:**
1. Audit all migration files for cross-database compatibility.
2. Validate foreign key constraint behaviors (`cascadeOnDelete`, `nullOnDelete`).
3. Document production database configuration recommendations in architecture docs.

---

### US-6.5: Academic Thesis Synchronization (Bab III ERD, LRS & File Specifications)

**As a** student researcher and developer (Zulfikar Hidayatullah)  
**I want** the academic thesis documentation in `docs/BAB_III_Draft.md` to be fully synchronized with the refined, clean database architecture  
**So that** the thesis report accurately depicts the system design, satisfies all academic standards, and matches the live demonstration during the thesis examination  

**Acceptance Criteria:**
- [x] Update Chen Entity Relationship Diagram (ERD) in [docs/BAB_III_Draft.md](../BAB_III_Draft.md) to clearly highlight Primary Keys as `user_id (PK)`, `production_order_id (PK)`, `daily_report_id (PK)`, `daily_report_item_id (PK)`, and `sap_import_log_id (PK)`.
- [x] Update Logical Record Structure (LRS) class diagram to reflect precise bounded VARCHAR data types and eliminated redundant indexes.
- [x] Update File Specifications (Tabel 3.10 s.d. Tabel 3.14):
  - Tabel 3.10: `users`
  - Tabel 3.11: `production_orders`
  - Tabel 3.12: `daily_reports`
  - Tabel 3.13: `daily_report_items`
  - Tabel 3.14: `sap_import_logs`
- [x] Ensure that academic writing tone adheres strictly to `industrial-paper-writing` standards (flowing narrative paragraphs, no AI clichés, grounded operational terminology).

**Tasks:**
1. [x] Review [docs/BAB_III_Draft.md](../BAB_III_Draft.md) around line 1160–1600 (ERD, LRS, File Specifications).
2. [x] Align diagram attributes and tables with the refined architecture.
3. [x] Validate that academic descriptions maintain professional and rigorous Indonesian technical phrasing.

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | **US-6.3: Transactional Hardening & Concurrency Protection** | 8 | Critical (Must) |
| 2 | **US-6.2: Schema Decluttering & Index Deduplication** | 5 | High (Must) |
| 3 | **US-6.1: Dual-Path Primary Key & Model Alignment** | 8 | High (Must) |
| 4 | **US-6.5: Academic Thesis Synchronization (Bab III)** | 6 | High (Must) |
| 5 | **US-6.4: Production Portability Verification** | 5 | Medium (Should) |

**Total Story Points: 32 pts**

---

## Dependencies & Risk Analysis

| Dependency / Risk | Severity | Mitigation Strategy |
|---|---|---|
| **Breaking Existing Tests During PK Rename** | High | Adopt Pathway 1 (Academic Bridge) by default. If Pathway 2 is required, execute step-by-step with immediate Pest test execution after each model update. |
| **Data Truncation on Bounded Strings** | Medium | Generously bound VARCHARs (e.g., 50 for PO numbers when SAP POs are typically 10–12 digits). Validate with real-world SAP test datasets. |
| **Lock Contention in Livewire Scan Loop** | Low | Keep `DB::transaction` blocks strictly limited to item insert and aggregate calculation (< 10 ms execution time). |
