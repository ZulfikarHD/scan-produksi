# Phase 02 - SAP Master Data Import & Management (Update Database)

**Sprint Goal**: Membangun modul Update Database untuk mengimpor dan mengelola Master Data Order Produksi dari SAP (file Excel/CSV), menyediakan parsing otomatis, validasi skema data, riwayat impor, serta pencarian master data produk untuk persiapan proses scan barcode.

**Estimated Duration**: 4-5 days

**Depends on**: Phase 01 (Foundation & User Management)

---

## User Stories

### US-2.1: Production Order Master Data Schema & Model

**As a** system developer  
**I want** a robust database schema for SAP Master Data (`production_orders` / `master_produk`)  
**So that** all product identity details (No PO, spesifikasi barang, nomor mesin, target cetak siap kirim) are accurately stored and indexed for fast barcode lookups

**Acceptance Criteria:**
- [ ] Table `production_orders` created with fields:
  - `id` (bigint, primary key)
  - `po_number` (string, unique index, e.g. "PO-2026-8891")
  - `product_code` (string, index)
  - `product_name` (string, e.g. "Sertifikat Sekuriti Hologram A4")
  - `customer_name` (string, e.g. "Kementerian / Bank / Lembaga")
  - `specification` (text, detail spesifikasi kertas & sekuriti)
  - `paper_type` (string, nullable)
  - `machine_number` (string, nullable, e.g. "MESIN-02")
  - `target_quantity` (integer, unsigned, target siap kemas)
  - `unit` (string, default: "lembar" / "rim" / "buku")
  - `order_date` (date)
  - `delivery_deadline` (date, nullable)
  - `status` (enum: `ready`, `in_progress`, `completed`, `cancelled`)
  - `created_by` (foreign key to `users`)
  - `timestamps`
- [ ] Model `ProductionOrder` with full type casting, search scopes, and relationships.
- [ ] Fast indexed lookup on `po_number` (< 20ms query time for barcode scanning).

**Tasks:**
1. Generate migration `database/migrations/xxxx_xx_xx_create_production_orders_table.php`.
2. Generate Enum `app/Enums/ProductionOrderStatus.php`.
3. Create Model `app/Models/ProductionOrder.php` with factory and seeder.
4. Write model unit tests and relationship tests in `tests/Unit/Models/ProductionOrderTest.php`.

---

### US-2.2: SAP File Import & Parser Service (Update Database)

**As a** supervisor or operator  
**I want** to upload SAP data export files (.xlsx, .xls, .csv) into the system  
**So that** new daily production orders and barcode identifiers are automatically updated in bulk without manual typing

**Acceptance Criteria:**
- [ ] Dedicated "Update Database" menu page with drag-and-drop file uploader.
- [ ] File validation: required, mimes: `xlsx,xls,csv`, max file size: 10MB.
- [ ] Flexible column header mapping for SAP export formats:
  - `No PO` / `Order Number`
  - `Kode Barang` / `Material Code`
  - `Nama Produk` / `Deskripsi`
  - `Pelanggan` / `Customer`
  - `Spesifikasi` / `Spec`
  - `No Mesin` / `Machine`
  - `Target Qty` / `Target Kemas`
  - `Tgl Order` / `Order Date`
- [ ] Service `SapImportService` parses spreadsheet rows in chunks, handles date normalization, and trims whitespace.
- [ ] Upsert logic: updates existing records by `po_number` or creates new records.
- [ ] Transaction-safe: rollbacks on fatal database errors with detailed error feedback.

**UI Specs:**
- Clean dropzone card with supported format badges (.xlsx, .xls, .csv).
- Download sample template button (Excel template matching SAP export format).
- Progress bar and spinner during file parsing & import processing.

**Tasks:**
1. Install and configure spreadsheet reader (e.g. `maatwebsite/excel` or `openspout/openspout`).
2. Create Service `app/Services/SapImportService.php` with chunked streaming.
3. Create Form Request `app/Http/Requests/MasterData/ImportSapDataRequest.php`.
4. Create Controller `app/Http/Controllers/MasterData/UpdateDatabaseController.php`.
5. Create Inertia Page `resources/js/pages/MasterData/UpdateDatabase.vue`.
6. Write Pest feature tests for valid spreadsheet import, invalid format, and duplicate upsert handling in `tests/Feature/MasterData/SapImportTest.php`.

---

### US-2.3: Import Preview & Validation Error Handling

**As a** supervisor  
**I want** to preview the parsed SAP records and receive clear error alerts for invalid rows  
**So that** I can verify the data accuracy before committing it to the production database

**Acceptance Criteria:**
- [ ] Optional Preview Step: displays summary (Total Rows Detected, Valid Rows, Incomplete/Invalid Rows).
- [ ] Highlight invalid rows with specific error reasons (e.g. "Row 14: Target Qty must be numeric", "Row 22: Missing PO Number").
- [ ] Option to skip invalid rows and import only valid ones, or cancel import.
- [ ] Success toast detailing total imported and updated order records.

**UI Specs:**
- Modal or expandable table previewing first 10-20 rows.
- Badges: Green (Valid), Red (Error with tooltip explanation).
- Action buttons: "Konfirmasi & Simpan ke Database" and "Batalkan".

**Tasks:**
1. Add validation rules mapper in `SapImportService`.
2. Create preview endpoint `POST /update-database/preview`.
3. Add preview modal component `resources/js/components/MasterData/ImportPreviewModal.vue`.
4. Write feature tests for preview validation and partial import handling.

---

### US-2.4: Import History & Audit Log

**As a** supervisor  
**I want** to view a history log of all SAP file imports performed on the system  
**So that** I can track who updated the database, when it was updated, and the source file used

**Acceptance Criteria:**
- [ ] Table `sap_import_logs` storing:
  - `id` (bigint)
  - `user_id` (foreign key to users)
  - `file_name` (original uploaded file name)
  - `file_path` (stored path for audit reference)
  - `total_rows` (integer)
  - `imported_count` (integer)
  - `updated_count` (integer)
  - `failed_count` (integer)
  - `status` (`success`, `partial`, `failed`)
  - `error_log` (json, nullable)
  - `timestamps`
- [ ] Display import history table on the Update Database page.
- [ ] Download audit log / original file feature for supervisor.

**Tasks:**
1. Generate migration `database/migrations/xxxx_xx_xx_create_sap_import_logs_table.php`.
2. Create Model `app/Models/SapImportLog.php`.
3. Implement log creation in `SapImportService`.
4. Add Import History tab in `resources/js/pages/MasterData/UpdateDatabase.vue`.
5. Write feature tests for audit log logging in `tests/Feature/MasterData/ImportLogTest.php`.

---

### US-2.5: Master Data Explorer & Manual Order Management

**As a** supervisor  
**I want** to search, view, and manually add/edit production orders  
**So that** packaging operations can proceed even if an urgent job is not yet exported from SAP

**Acceptance Criteria:**
- [ ] List all master production orders with filters: status, date range, customer name, and machine.
- [ ] Search input by PO Number or Product Name.
- [ ] "Tambah Order Manual" modal form for emergency jobs (same fields as SAP master).
- [ ] Edit order modal for correcting specifications or target quantities.
- [ ] Detail view showing order specs, target qty, and current packaging fulfillment status.

**UI Specs:**
- Tabbed interface or sub-page in Master Data.
- Quick filter chips: `Semua`, `Siap Kemas`, `Sedang Diproses`, `Selesai`.
- Detail drawer / modal showing full print specification.

**Tasks:**
1. Create `app/Http/Controllers/MasterData/ProductionOrderController.php`.
2. Create Form Requests `StoreProductionOrderRequest.php` and `UpdateProductionOrderRequest.php`.
3. Create Inertia Page `resources/js/pages/MasterData/Orders/Index.vue`.
4. Write Pest feature tests for order CRUD and manual order creation.

---

## Technical Notes

### SapImportService Implementation Pattern

```php
// app/Services/SapImportService.php
namespace App\Services;

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\SapImportLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SapImportService
{
    public function import(UploadedFile $file, User $user): array
    {
        $filePath = $file->store('sap-imports', 'private');
        $rows = Excel::toArray([], $file)[0] ?? [];
        
        $totalRows = count($rows) - 1; // excluding header
        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $index => $row) {
                $poNumber = trim($row[0] ?? '');
                $productName = trim($row[1] ?? '');
                $customerName = trim($row[2] ?? '');
                $targetQty = (int) ($row[3] ?? 0);
                $spec = trim($row[4] ?? '');
                $machine = trim($row[5] ?? '');

                if (empty($poNumber) || empty($productName)) {
                    $errors[] = "Baris " . ($index + 2) . ": No PO atau Nama Produk tidak boleh kosong.";
                    continue;
                }

                $order = ProductionOrder::updateOrCreate(
                    ['po_number' => $poNumber],
                    [
                        'product_name' => $productName,
                        'customer_name' => $customerName,
                        'specification' => $spec,
                        'machine_number' => $machine,
                        'target_quantity' => $targetQty,
                        'status' => ProductionOrderStatus::READY,
                        'created_by' => $user->id,
                        'order_date' => now()->toDateString(),
                    ]
                );

                $order->wasRecentlyCreated ? $imported++ : $updated++;
            }

            $log = SapImportLog::create([
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'total_rows' => $totalRows,
                'imported_count' => $imported,
                'updated_count' => $updated,
                'failed_count' => count($errors),
                'status' => empty($errors) ? 'success' : 'partial',
                'error_log' => $errors,
            ]);

            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
                'log_id' => $log->id,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-2.1 Production Order Master Data Schema & Model | 5 | Must |
| 2 | US-2.2 SAP File Import & Parser Service | 8 | Must |
| 3 | US-2.3 Import Preview & Validation Error Handling | 5 | Should |
| 4 | US-2.4 Import History & Audit Log | 5 | Must |
| 5 | US-2.5 Master Data Explorer & Manual Order Management | 6 | Should |

**Total Story Points: 29**

---

## Dependencies

| Dependency | From |
|---|---|
| User authentication & role middleware | Phase 01 |
| Excel/Spreadsheet parser package | Composer setup |
| Storage disk configuration (`private` / `local`) | Laravel setup |

---

## Risks

| Risk | Mitigation |
|---|---|
| Inconsistent Excel column naming from different SAP exports | Support flexible column index and header name alias mapping |
| Large SAP export files causing PHP execution timeout | Use chunked stream processing and database transaction bulk upserts |
