# Phase 03 - Barcode Scanning & Daily Production Reporting (Buat Laporan)

**Sprint Goal**: Mengembangkan fitur utama "Buat Laporan" yang mengintegrasikan Barcode Scanner (tipe Code 128) untuk membaca No PO pada Kartu Mesin Produksi, auto-fill spesifikasi produk secara real-time dari master data SAP, pencatatan kuantitas hasil kemas (baik & reject), serta penyimpanan Laporan Produksi Harian multi-item.

**Estimated Duration**: 5-6 days

**Depends on**: Phase 01 (Auth & Petugas) & Phase 02 (Master Data SAP)

---

## User Stories

### US-3.1: Production Report Data Schema & Unique ID Generator

**As a** system developer  
**I want** a normalized database structure for Daily Production Reports and Report Items, complete with an automatic unique Report ID generator  
**So that** all daily packaging outputs are persistently recorded and traceable with a standardized ID format

**Acceptance Criteria:**
- [ ] Table `daily_reports` created with fields:
  - `id` (bigint, primary key)
  - `report_number` (string, unique, indexed, format: `LAP-YYYYMMDD-XXXX`, e.g. `LAP-20260825-0001`)
  - `report_date` (date, indexed)
  - `shift` (enum: `shift_1`, `shift_2`, `shift_3`)
  - `user_id` (foreign key to `users` - Petugas yang bertugas)
  - `supervisor_id` (foreign key to `users`, nullable - yang menyetujui)
  - `total_items_count` (integer, unsigned)
  - `total_good_quantity` (integer, unsigned)
  - `total_reject_quantity` (integer, unsigned)
  - `notes` (text, nullable)
  - `status` (enum: `draft`, `submitted`, `verified`)
  - `timestamps`
- [ ] Table `daily_report_items` created with fields:
  - `id` (bigint, primary key)
  - `daily_report_id` (foreign key to `daily_reports`, cascade on delete)
  - `production_order_id` (foreign key to `production_orders`)
  - `po_number` (string, indexed - snapshot No PO)
  - `product_name` (string - snapshot nama produk)
  - `specification` (text, snapshot spesifikasi sekuriti)
  - `machine_number` (string, nullable)
  - `good_quantity` (integer, unsigned - jumlah kemas baik siap kirim)
  - `reject_quantity` (integer, unsigned, default: 0)
  - `unit` (string, default: "lembar")
  - `item_notes` (string, nullable)
  - `timestamps`
- [ ] Sequence service `ReportNumberGenerator` ensuring atomic, race-condition-safe unique sequential IDs per day.

**Tasks:**
1. Generate migrations for `daily_reports` and `daily_report_items`.
2. Generate Enums `app/Enums/WorkShift.php` and `ReportStatus.php`.
3. Create Models `app/Models/DailyReport.php` and `DailyReportItem.php` with relationships and casts.
4. Create Service `app/Services/ReportNumberGenerator.php`.
5. Write Pest unit tests for models, scopes, and sequential ID generation in `tests/Unit/Services/ReportNumberGeneratorTest.php`.

---

### US-3.2: Barcode Scanner (Code 128) Input Listener & Fast Lookup API

**As a** petugas kemas  
**I want** the system to automatically capture barcode scans from the handheld scanner on the Kartu Mesin Produksi and immediately fetch product specifications  
**So that** I don't have to manually type long PO numbers or search through catalogues

**Acceptance Criteria:**
- [ ] Dedicated Barcode Scanner input component that stays auto-focused and ready for scanner input.
- [ ] Detects hardware scanner keystrokes (Code 128 scanner sends keystrokes terminating with `Enter`).
- [ ] Fast JSON API endpoint `GET /api/production-orders/lookup?po_number=...` returning order specs (< 50ms).
- [ ] Audio/Visual feedback upon scan:
  - Success beep / green glow if PO is found in Master Data.
  - Warning beep / error toast if PO is not found or already completed.
- [ ] Fallback: Petugas can type PO number manually and press Enter or click "Cari".
- [ ] Handles rapid multiple scans without input race conditions.

**UI Specs:**
- Prominent Scan Zone at the top of the form with animated scanner icon and focus indicator: "Arahkan Barcode Scanner ke Kartu Mesin...".
- Floating Quick-Lookup status card displaying matched product name, customer, and machine.

**Tasks:**
1. Create API Controller `app/Http/Controllers/Api/ProductionOrderLookupController.php`.
2. Create reusable Vue component `resources/js/components/Scanner/BarcodeInput.vue` with keyboard listener and sound effects.
3. Add client-side scan debouncing and audio helper (`audio/beep-success.mp3`, `audio/beep-error.mp3`).
4. Write feature tests for lookup API in `tests/Feature/Api/ProductionOrderLookupTest.php`.

---

### US-3.3: Interactive Packaging Data Entry & Staging Table (Buat Laporan)

**As a** petugas kemas  
**I want** to specify the quantity packed (good & reject) for each scanned order and compile them in a dynamic staging table  
**So that** all packaged items for the current shift are reviewed together before saving the daily report

**Acceptance Criteria:**
- [ ] Header Form Fields:
  - Tanggal Laporan (default: today's date in `Asia/Jakarta`).
  - Shift Selection (`Shift 1: 07.00 - 15.00`, `Shift 2: 15.00 - 23.00`, `Shift 3: 23.00 - 07.00`).
  - Nama Petugas (default: logged-in user, editable by Supervisor).
  - Catatan Umum Shift (optional).
- [ ] Adding Item to Staging Table:
  - Scanning PO auto-populates product details.
  - Input field for `Jumlah Kemas Baik` (required, integer > 0).
  - Input field for `Jumlah Rusak / Reject` (optional, integer >= 0).
  - Input field for `Keterangan Item` (optional).
  - "Tambah ke Daftar" button appends row to staging table.
- [ ] Staging Table features:
  - List of scanned items with row index, No PO, Nama Produk, Spesifikasi, No Mesin, Qty Baik, Qty Reject, and Total.
  - Inline edit for quantities in staging table.
  - Delete row button with quick undo option.
  - Auto-calculated summary bar at the bottom (Total Item Barcode, Total Qty Baik, Total Reject).
- [ ] Duplicate warning: if a PO is scanned twice in the same report session, ask whether to add to existing row or reject.

**UI Specs:**
- 2-Column or Top-Bottom responsive layout:
  - Top: Header & Scan Input Box.
  - Bottom: Scanned Items Data Table with real-time totals footer.
- Action Buttons: "Simpan Laporan Harian", "Simpan & Cetak", "Kosongkan Form".

**Tasks:**
1. Create Inertia Page `resources/js/pages/ProductionReports/Create.vue`.
2. Create Vue components `resources/js/components/Reports/ReportHeaderForm.vue` and `ReportItemTable.vue`.
3. Implement client-side draft recovery using `localStorage` to prevent data loss on accidental browser close.
4. Implement input validation rules and quantity limits against target quantity.

---

### US-3.4: Save & Store Production Report Service

**As a** petugas kemas  
**I want** to submit the compiled production report to the database with transaction integrity  
**So that** all report header details and item entries are saved together and updated in the system records

**Acceptance Criteria:**
- [ ] Form Request `StoreDailyReportRequest` validating:
  - `report_date` (required, date format)
  - `shift` (required, valid enum)
  - `notes` (nullable, string, max 500)
  - `items` (required, array, min 1 item)
  - `items.*.production_order_id` (required, exists:production_orders,id)
  - `items.*.good_quantity` (required, integer, min: 1)
  - `items.*.reject_quantity` (nullable, integer, min: 0)
- [ ] Service `DailyReportService` executes within DB transaction:
  - Generates sequential unique `report_number`.
  - Creates `daily_reports` master record.
  - Inserts all `daily_report_items`.
  - Updates `production_orders` status (e.g. increments fulfilled qty and marks `completed` if target is reached).
- [ ] Redirects to Report Detail or List view with success toast containing the generated `report_number`.

**Tasks:**
1. Create Form Request `app/Http/Requests/Reports/StoreDailyReportRequest.php`.
2. Create Service `app/Services/DailyReportService.php`.
3. Create Controller `app/Http/Controllers/DailyReportController.php` with `create` and `store` methods.
4. Write Pest feature tests for valid report submission, validation failures, and order status updates in `tests/Feature/Reports/StoreDailyReportTest.php`.

---

## Technical Notes

### DailyReportService & Store Logic

```php
// app/Services/DailyReportService.php
namespace App\Services;

use App\Enums\ProductionOrderStatus;
use App\Enums\ReportStatus;
use App\Models\DailyReport;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DailyReportService
{
    public function __construct(
        protected ReportNumberGenerator $numberGenerator
    ) {}

    public function createReport(array $data, User $petugas): DailyReport
    {
        return DB::transaction(function () use ($data, $petugas) {
            $reportNumber = $this->numberGenerator->generate($data['report_date']);

            $totalGood = 0;
            $totalReject = 0;

            foreach ($data['items'] as $item) {
                $totalGood += (int) $item['good_quantity'];
                $totalReject += (int) ($item['reject_quantity'] ?? 0);
            }

            $report = DailyReport::create([
                'report_number' => $reportNumber,
                'report_date' => $data['report_date'],
                'shift' => $data['shift'],
                'user_id' => $petugas->id,
                'notes' => $data['notes'] ?? null,
                'total_items_count' => count($data['items']),
                'total_good_quantity' => $totalGood,
                'total_reject_quantity' => $totalReject,
                'status' => ReportStatus::SUBMITTED,
            ]);

            foreach ($data['items'] as $itemData) {
                $order = ProductionOrder::findOrFail($itemData['production_order_id']);

                $report->items()->create([
                    'production_order_id' => $order->id,
                    'po_number' => $order->po_number,
                    'product_name' => $order->product_name,
                    'specification' => $order->specification,
                    'machine_number' => $order->machine_number,
                    'good_quantity' => $itemData['good_quantity'],
                    'reject_quantity' => $itemData['reject_quantity'] ?? 0,
                    'unit' => $order->unit ?? 'lembar',
                    'item_notes' => $itemData['item_notes'] ?? null,
                ]);

                // Update production order fulfillment status
                $order->update(['status' => ProductionOrderStatus::IN_PROGRESS]);
            }

            return $report->load(['items', 'user']);
        });
    }
}
```

### Store Daily Report Request

```php
// app/Http/Requests/Reports/StoreDailyReportRequest.php
namespace App\Http\Requests\Reports;

use App\Enums\WorkShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'report_date' => ['required', 'date'],
            'shift' => ['required', new Enum(WorkShift::class)],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.production_order_id' => ['required', 'exists:production_orders,id'],
            'items.*.good_quantity' => ['required', 'integer', 'min:1'],
            'items.*.reject_quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.item_notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-3.1 Production Report Data Schema & Unique ID Generator | 8 | Must |
| 2 | US-3.2 Barcode Scanner (Code 128) Input Listener & Fast Lookup API | 8 | Must |
| 3 | US-3.3 Interactive Packaging Data Entry & Staging Table | 10 | Must |
| 4 | US-3.4 Save & Store Production Report Service | 8 | Must |

**Total Story Points: 34**

---

## Dependencies

| Dependency | From |
|---|---|
| Master Data SAP & ProductionOrder model | Phase 02 |
| User authentication & Petugas identity | Phase 01 |
| Hardware barcode scanner (Code 128 standard) | Operating Environment |

---

## Risks

| Risk | Mitigation |
|---|---|
| Accidental page refresh during long scanning session losing unsaved items | Implement `localStorage` auto-draft recovery in Vue client |
| Scanner emitting non-standard prefix/suffix characters | Sanitize input string by stripping non-alphanumeric/hyphen symbols and normalizing barcode string |
