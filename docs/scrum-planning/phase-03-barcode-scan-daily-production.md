# Phase 03 - Barcode Scanning & Daily Production Reporting (Buat Laporan)

**Sprint Goal**: Mengembangkan fitur utama "Buat Laporan" berbasis Livewire 4 & Flux UI yang mengintegrasikan Barcode Scanner (tipe Code 128 via Alpine.js event listener) untuk membaca No PO pada Kartu Mesin Produksi, auto-fill spesifikasi produk secara real-time dari master data SAP, pencatatan kuantitas hasil kemas (baik & reject), serta penyimpanan Laporan Produksi Harian multi-item.

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

### US-3.2: Barcode Scanner (Code 128) Alpine.js Listener & Instant Lookup

**As a** petugas kemas  
**I want** the system to automatically capture barcode scans from the handheld scanner on the Kartu Mesin Produksi and immediately fetch product specifications via Livewire  
**So that** I don't have to manually type long PO numbers or search through catalogues

**Acceptance Criteria:**
- [ ] Alpine.js barcode listener directive `x-barcode-scanner` that keeps the scan input active and captures hardware scanner events (Code 128 carriage return).
- [ ] Livewire action `lookupBarcode($poNumber)` triggered automatically upon scan completion.
- [ ] Instant spec lookup (< 50ms) matching `po_number` in `production_orders`.
- [ ] Audio/Visual feedback upon scan:
  - Success tone & green flash if PO is found in Master Data.
  - Warning tone & Flux toast error if PO is not found or already completed.
- [ ] Fallback: Petugas can type PO number manually and press Enter or click "Cari".
- [ ] Handles rapid multiple scans cleanly without race conditions.

**UI Specs:**
- Prominent Scan Card with animated barcode icon and scanner focus badge: "Arahkan Barcode Scanner ke Kartu Mesin...".
- Preview Card showing matched product name, customer, and machine immediately after scan.

**Tasks:**
1. Create Alpine.js scanner handler script / directive in `resources/js/scanner.js` (or inline Blade Alpine component).
2. Implement Livewire method `lookupBarcode` in `app/Livewire/Reports/CreateReport.php`.
3. Add sound effects (`beep-success`, `beep-error`) triggered via Livewire `$this->dispatch('play-sound', ...)` event.
4. Write Pest feature tests for barcode lookup in `tests/Feature/Reports/BarcodeLookupTest.php`.

---

### US-3.3: Interactive Packaging Data Entry & Staging Table (Buat Laporan)

**As a** petugas kemas  
**I want** to specify the quantity packed (good & reject) for each scanned order and compile them in a dynamic Livewire staging table  
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
  - "Tambah ke Daftar" button appends row to staging array in Livewire component.
- [ ] Staging Table features:
  - List of scanned items with row index, No PO, Nama Produk, Spesifikasi, No Mesin, Qty Baik, Qty Reject, and Total.
  - Inline edit for quantities directly in table rows.
  - Delete row button (`wire:click="removeItem($index)"`).
  - Auto-calculated summary bar at the bottom (Total Item Barcode, Total Qty Baik, Total Reject).
- [ ] Duplicate check: if a PO is already in staging, offer to add quantity to existing row.

**UI Specs:**
- Flux grid layout (`<flux:card>`, `<flux:table>`, `<flux:input>`, `<flux:select>`).
- Bottom summary row with bold totals.
- Action Buttons: `<flux:button variant="primary" wire:click="saveReport">Simpan Laporan Harian</flux:button>` and `<flux:button wire:click="resetForm">Kosongkan Form</flux:button>`.

**Tasks:**
1. Create Livewire Component `app/Livewire/Reports/CreateReport.php` and Blade view `resources/views/livewire/reports/create.blade.php`.
2. Implement reactive item collection `$items = []` with add, update, and remove methods.
3. Optimize table rendering with Blaze (`@blaze`).
4. Write Pest feature tests for adding items, duplicate detection, and quantity calculations in `tests/Feature/Reports/CreateReportTest.php`.

---

### US-3.4: Save & Store Production Report Service

**As a** petugas kemas  
**I want** to submit the compiled production report to the database with transaction integrity  
**So that** all report header details and item entries are saved together and updated in the system records

**Acceptance Criteria:**
- [ ] Validation in Livewire Component:
  - `reportDate` (required, date format)
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
  - Updates `production_orders` status (increments fulfilled qty and marks `in_progress` or `completed`).
- [ ] Redirects to Report Detail / Index with Flux success toast containing the generated `report_number`.

**Tasks:**
1. Create Service `app/Services/DailyReportService.php`.
2. Implement save handler in `app/Livewire/Reports/CreateReport.php`.
3. Register routes in `routes/web.php` (`Route::get('/reports/create', CreateReport::class)->name('reports.create');`).
4. Write Pest feature tests for valid report submission, validation failures, and order status updates in `tests/Feature/Reports/StoreDailyReportTest.php`.

---

## Technical Notes

### Livewire CreateReport Component

```php
// app/Livewire/Reports/CreateReport.php
namespace App\Livewire\Reports;

use App\Enums\WorkShift;
use App\Models\ProductionOrder;
use App\Services\DailyReportService;
use Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateReport extends Component
{
    public string $reportDate = '';
    public string $shift = 'shift_1';
    public string $notes = '';

    // Scanner & Item Form
    public string $scanInput = '';
    public ?ProductionOrder $scannedOrder = null;
    public int $goodQuantity = 0;
    public int $rejectQuantity = 0;
    public string $itemNotes = '';

    // Staging Table
    public array $items = [];

    public function mount()
    {
        $this->reportDate = now()->toDateString();
        $this->shift = $this->determineCurrentShift();
    }

    public function lookupBarcode()
    {
        $poNumber = trim($this->scanInput);
        if (empty($poNumber)) return;

        $order = ProductionOrder::where('po_number', $poNumber)->first();

        if (!$order) {
            Flux::toast("No PO {$poNumber} tidak ditemukan di master data SAP.", variant: 'danger');
            $this->dispatch('play-sound', ['type' => 'error']);
            $this->scanInput = '';
            return;
        }

        $this->scannedOrder = $order;
        $this->goodQuantity = $order->target_quantity;
        $this->rejectQuantity = 0;
        $this->itemNotes = '';
        $this->dispatch('play-sound', ['type' => 'success']);
    }

    public function addItem()
    {
        if (!$this->scannedOrder || $this->goodQuantity <= 0) {
            Flux::toast('Jumlah kemas baik harus lebih dari 0.', variant: 'warning');
            return;
        }

        $this->items[] = [
            'production_order_id' => $this->scannedOrder->id,
            'po_number' => $this->scannedOrder->po_number,
            'product_name' => $this->scannedOrder->product_name,
            'specification' => $this->scannedOrder->specification,
            'machine_number' => $this->scannedOrder->machine_number,
            'good_quantity' => $this->goodQuantity,
            'reject_quantity' => $this->rejectQuantity,
            'unit' => $this->scannedOrder->unit ?? 'lembar',
            'item_notes' => $this->itemNotes,
        ];

        $this->reset(['scanInput', 'scannedOrder', 'goodQuantity', 'rejectQuantity', 'itemNotes']);
        Flux::toast('Item berhasil ditambahkan ke daftar laporan.', variant: 'success');
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function saveReport(DailyReportService $service)
    {
        $this->validate([
            'reportDate' => ['required', 'date'],
            'shift' => ['required'],
            'items' => ['required', 'array', 'min:1'],
        ]);

        $report = $service->createReport([
            'report_date' => $this->reportDate,
            'shift' => $this->shift,
            'notes' => $this->notes,
            'items' => $this->items,
        ], auth()->user());

        Flux::toast("Laporan {$report->report_number} berhasil disimpan.", variant: 'success');
        $this->redirectRoute('reports.show', $report->report_number, navigate: true);
    }

    protected function determineCurrentShift(): string
    {
        $hour = (int) now()->format('H');
        if ($hour >= 7 && $hour < 15) return 'shift_1';
        if ($hour >= 15 && $hour < 23) return 'shift_2';
        return 'shift_3';
    }

    public function render()
    {
        return view('livewire.reports.create');
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-3.1 Production Report Data Schema & Unique ID Generator | 8 | Must |
| 2 | US-3.2 Barcode Scanner (Code 128) Alpine.js Listener & Instant Lookup | 8 | Must |
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
| Accidental page refresh during long scanning session | Store draft state in Livewire or Alpine `x-data` persistent store |
| Scanner emitting non-standard prefix/suffix characters | Sanitize input string by stripping non-alphanumeric/hyphen symbols before lookup |
