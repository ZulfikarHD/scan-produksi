# Phase 03 - Barcode Scanning & Daily Production Entry (Buat Laporan)

**Sprint Goal**: Mengembangkan alur kerja pemindaian barcode berkecepatan tinggi (*High-Speed Continuous Scan & Entry Loop*) berbasis Livewire 4, Flux UI, dan Alpine.js untuk pencatatan hasil produksi harian. Setiap sesi laporan dikelompokkan berdasarkan tanggal dan kategori produk sekuriti (**PCHT**, **MMEA**, **HPTL/REL**), dengan otomatisasi perpindahan fokus (*autofocus transfer*), pencatatan per baris PO (*singular entry*), serta kemampuan pengeditan kuantitas kemas secara langsung pada baris tabel (*inline editable table*) tanpa modal maupun pengalihan halaman.

**Estimated Duration**: 5-6 days

**Depends on**: Phase 01 (Auth & Petugas) & Phase 02 (Master Data SAP)

---

## Business Requirements & Core Workflow

```
[ Pilih Tanggal & Kategori Produk (PCHT / MMEA / HPTL) ] 
                     │
                     ▼
       [ Buka Sesi Laporan: PCHT-YYYY-MM-DD ]
                     │
 ┌───────────────────┴──────────────────────────────────────────────────────┐
 │               CONTINUOUS SCAN & ENTRY LOOP (100% KEYBOARD)               │
 │                                                                          │
 │  1. Autofocus aktif di [ Scan No PO Barcode ]                           │
 │  2. Operator memindai Kartu Mesin (Code 128 Scanner)                     │
 │  3. Sistem auto-fetch spesifikasi produk dari master SAP (Nama, Spec, Qty)│
 │  4. Autofocus OTOMATIS berpindah ke [ Jumlah Kemas / Produksi ]          │
 │  5. Operator ketik angka hasil kemas lalu tekan [ ENTER ]                │
 │  6. Baris tersimpan ke database & muncul seketika di tabel bawah         │
 │  7. Autofocus OTOMATIS kembali ke [ Scan No PO Barcode ]                 │
 │  8. Ulangi pemindaian PO berikutnya tanpa menyentuh mouse                │
 └──────────────────────────────────────────────────────────────────────────┘
                     │
                     ▼
  [ Tabel Rekapitulasi di Halaman yang Sama ]
  - Edit salah input? Klik langsung di cell input "Kemas" pada baris tabel
  - Tekan Enter / Tab / Blur -> Data otomatis terupdate di database
  - Tanpa modal, tanpa popup, tanpa reload halaman
```

---

## User Stories

### US-3.1: Daily Production Report Schema, Product Series & Report Code Generator

**As a** system developer  
**I want** a robust relational database schema for Daily Reports and Report Items supporting product series grouping (`PCHT`, `MMEA`, `HPTL/REL`) and standardized Report Code generation  
**So that** all scanned PO packaging records are organized cleanly by date and product category

**Acceptance Criteria:**
- [ ] Enum `ProductSeries` created with values:
  - `PCHT` = "Pita Cukai Hasil Tembakau"
  - `MMEA` = "Minuman Mengandung Etil Alkohol"
  - `HPTL` = "Hasil Pengolahan Tembakau Lainnya / REL"
- [ ] Table `daily_reports` created with fields:
  - `id` (bigint, primary key)
  - `report_code` (string, unique, indexed, format: `[SERIES]-[YYYY-MM-DD]`, e.g. `PCHT-2026-08-26` or `PCHT-2026-08-26-S1` if shift-specific)
  - `product_series` (enum: `pcht`, `mmea`, `hptl`)
  - `report_date` (date, indexed)
  - `shift` (enum: `shift_1`, `shift_2`, `shift_3`)
  - `user_id` (foreign key to `users` - Petugas yang bertugas)
  - `supervisor_id` (foreign key to `users`, nullable - Supervisor verifikator)
  - `total_items_count` (integer, unsigned, default: 0)
  - `total_good_quantity` (integer, unsigned, default: 0)
  - `notes` (text, nullable)
  - `status` (enum: `draft`, `submitted`, `verified`)
  - `timestamps`
- [ ] Table `daily_report_items` created with fields:
  - `id` (bigint, primary key)
  - `daily_report_id` (foreign key to `daily_reports`, cascade on delete)
  - `production_order_id` (foreign key to `production_orders`, nullable for manual override)
  - `po_number` (string, indexed - snapshot No PO)
  - `product_name` (string - snapshot nama produk)
  - `specification` (text, snapshot spesifikasi sekuriti/kertas)
  - `machine_number` (string, nullable)
  - `target_quantity` (integer, unsigned, default: 0)
  - `good_quantity` (integer, unsigned - jumlah kemas / hasil produksi aktual)
  - `unit` (string, default: "lembar")
  - `notes` (string, nullable)
  - `scanned_at` (datetime)
  - `timestamps`
- [ ] Unique constraint ensuring one active report per (`product_series`, `report_date`, `shift`).

**Tasks:**
1. Generate migration `database/migrations/xxxx_xx_xx_create_daily_reports_and_items_tables.php`.
2. Generate Enums `app/Enums/ProductSeries.php`, `WorkShift.php`, and `ReportStatus.php`.
3. Create Models `app/Models/DailyReport.php` and `DailyReportItem.php` with Eloquent relationships and query scopes.
4. Create Service `app/Services/ReportCodeGenerator.php`.
5. Write Pest unit tests for models, series casting, and report code generation in `tests/Unit/Models/DailyReportTest.php`.

---

### US-3.2: Daily Report Session Initiation ("Buat / Buka Laporan Harian")

**As a** petugas kemas  
**I want** to select the target Date, Product Series (PCHT, MMEA, HPTL/REL), and Shift to initialize or resume a daily reporting session  
**So that** all subsequent scanned PO items are filed into the correct daily report container

**Acceptance Criteria:**
- [ ] Accessible from menu **"Buat Laporan"** (`/reports/create` or `/reports/session`).
- [ ] Form / Selector card with:
  - **Tanggal Laporan** (default: today's date in `Asia/Jakarta`).
  - **Kategori Produk**: Radio cards / Segmented buttons for `PCHT`, `MMEA`, `HPTL / REL`.
  - **Shift Kerja**: Auto-detected based on current WIB time (`Shift 1: 07.00 - 15.00`, `Shift 2: 15.00 - 23.00`, `Shift 3: 23.00 - 07.00`), editable.
- [ ] Smart Session Handler:
  - If a report with the same series, date, and shift already exists: automatically opens/resumes that session with all previously scanned rows intact.
  - If not: creates a new `daily_reports` record with status `draft` and opens the high-speed entry page.
- [ ] Generated Report Code displayed prominently at the top header (e.g. `PCHT-2026-08-26`).

**UI Specs:**
- Compact launcher card with Flux UI radio buttons for product categories (`PCHT`, `MMEA`, `HPTL/REL`).
- Direct action button: `<flux:button variant="primary" icon="bolt">Buka Sesi Pemindaian PO</flux:button>`.

**Tasks:**
1. Create Livewire Component `app/Livewire/Reports/ReportSessionLauncher.php`.
2. Create Blade view `resources/views/livewire/reports/session-launcher.blade.php`.
3. Register routes in `routes/web.php`.
4. Write Pest feature tests for report initialization and resuming existing session in `tests/Feature/Reports/ReportSessionTest.php`.

---

### US-3.3: High-Speed Singular Barcode Scanning & Auto-Focus Production Entry Loop

**As a** petugas kemas  
**I want** a continuous, keyboard-driven scanning loop where scanning a PO fetches specs, automatically jumps focus to the production quantity input, and immediately returns focus to the scan input after pressing Enter  
**So that** I can record hundreds of packaging cards rapidly without touching the computer mouse

**Acceptance Criteria:**
- [ ] **Input 1: Scan No PO (`#scan-po-input`)**:
  - Auto-focused on page load via Alpine.js `x-init="$refs.scanInput.focus()"`.
  - Captures barcode scanner input (hardware Code 128 scanner sends characters + `Enter`).
  - Triggers instant lookup matching `po_number` in `production_orders`.
- [ ] **Instant Data Fetch & Feedback**:
  - If PO found: displays spec badge/pill (Nama Produk, No Mesin, Spesifikasi, Target Qty SAP), plays pleasant success chime, and **immediately transfers focus to Input 2 (`#production-qty-input`)**.
  - If PO not found: plays error tone, displays Flux toast error *"No PO [xxx] tidak ditemukan di Master SAP"*, clears scan input, and keeps focus on `#scan-po-input`.
- [ ] **Input 2: Jumlah Produksi / Kemas (`#production-qty-input`)**:
  - Pre-filled with SAP target quantity (or blank, easily overwritten).
  - Operator types actual packed amount and presses **`Enter`**.
- [ ] **Commit & Auto-Return Focus**:
  - Upon pressing Enter: item is saved to `daily_report_items` for the current report session.
  - Toast confirmation appears subtly or updates total counts.
  - Form resets and **focus instantly jumps back to `#scan-po-input`** ready for the next barcode scan!
- [ ] Supports manual keyboard-only navigation (`Tab`, `Enter`, `Escape`).

**UI Specs:**
- Top Scan Console: 2 inline connected input fields (Scan Barcode PO $\rightarrow$ Jumlah Kemas) with real-time status pill.
- Audio cues triggered via Alpine / Livewire events (`play-sound: success/error`).

**Tasks:**
1. Create Livewire Component `app/Livewire/Reports/ProductionEntry.php` (`/reports/{report:report_code}/entry`).
2. Implement Alpine.js focus management directive in `resources/views/livewire/reports/production-entry.blade.php`.
3. Implement `lookupPo` and `addProductionItem` methods with database persistence.
4. Add audio sound effects (`beep-success.mp3`, `beep-error.mp3`).
5. Write Pest feature tests for singular PO lookup, focus transitions, and item persistence in `tests/Feature/Reports/ProductionEntryTest.php`.

---

### US-3.4: Real-Time Inline Editable Table for Scanned Production Entries (No Modals)

**As a** petugas kemas  
**I want** to see all scanned PO rows in a table on the same page and edit the "Jumlah Kemas" directly within any table row by typing and pressing Enter  
**So that** I can correct miscounts or adjust quantities instantly without opening modals or leaving the page

**Acceptance Criteria:**
- [ ] Production items table displayed directly below the scan console on the same page.
- [ ] Table Columns:
  1. `No`
  2. `No PO / Barcode` (monospace font, bold)
  3. `Nama Produk` & `Spesifikasi Kertas/Sekuriti`
  4. `No Mesin`
  5. `Target SAP` (lembar)
  6. **`Jumlah Kemas (Produksi)` — Inline Editable Input Cell**
  7. `Satuan`
  8. `Waktu Scan`
  9. `Aksi` (Hapus baris)
- [ ] **Inline Editing Behavior**:
  - The "Jumlah Kemas" cell contains an active number input field (`<input type="number" ... />`).
  - When the user edits the value and presses **`Enter`** (or clicks away/blurs), Livewire action `updateItemQuantity($itemId, $newQty)` executes immediately.
  - Instant visual feedback: cell briefly flashes soft green or shows a checkmark indicating data is saved.
  - Recalculates total summary values instantly without whole page reload.
  - No modal, no popup, no redirection required.
- [ ] **Row Deletion**:
  - Quick delete button with confirmation tooltip / instant undo toast.
- [ ] **Live Summary Footer**:
  - Total PO Terscan: `X order`
  - Total Target SAP: `Y lembar`
  - Total Hasil Kemas: `Z lembar`
  - Persentase Realisasi: `((Z / Y) * 100)%`

**UI Specs:**
- Flux data table with sticky header and scrollable items body.
- Inline input styled with subtle border that highlights on focus.
- Summary banner at the bottom with bold aggregations.

**Tasks:**
1. Implement inline editable table in `resources/views/livewire/reports/production-entry.blade.php`.
2. Add `updateItemQuantity` and `deleteItem` methods in `ProductionEntry.php`.
3. Implement debounced auto-save on blur/enter with validation (quantity must be integer $\ge 1$).
4. Write Pest feature tests for inline row editing, quantity update, item deletion, and summary calculations in `tests/Feature/Reports/InlineRowEditTest.php`.

---

### US-3.5: Finalization, Shift Summary & Print Readiness

**As a** petugas kemas or supervisor  
**I want** to finalize the daily production report once all packaging cards for the shift are scanned  
**So that** the report is marked as submitted and ready for supervisor review and physical printing

**Acceptance Criteria:**
- [ ] "Selesaikan Laporan" button on the entry page header.
- [ ] Updates `daily_reports.status` from `draft` to `submitted`.
- [ ] Generates audit timestamps and locks default operator edits (Supervisor can still edit/unlock).
- [ ] Direct action link to **"Cetak Dokumen Fisik Laporan"** (standard A4 printable view).
- [ ] Success toast: *"Laporan PCHT-2026-08-26 berhasil diselesaikan dengan total X lembar."*

**Tasks:**
1. Implement `finalizeReport` method in `ProductionEntry.php`.
2. Add status badge (`Draft` vs `Terkirim` vs `Terverifikasi`) on report header.
3. Write Pest feature tests for report finalization in `tests/Feature/Reports/FinalizeReportTest.php`.

---

## Technical Notes

### Livewire Component: `ProductionEntry.php`

```php
// app/Livewire/Reports/ProductionEntry.php
namespace App\Livewire\Reports;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductionEntry extends Component
{
    public DailyReport $report;

    // Scan & Entry Console States
    public string $scanPo = '';
    public ?ProductionOrder $scannedOrder = null;
    public ?int $productionQty = null;
    public string $itemNotes = '';

    public function mount(DailyReport $report)
    {
        $this->report = $report->load(['items' => fn ($q) => $q->latest('id')]);
    }

    /**
     * Step 1: Scan Barcode & Lookup Master Data SAP
     */
    public function lookupPo()
    {
        $po = trim($this->scanPo);
        if (empty($po)) return;

        $order = ProductionOrder::where('po_number', $po)->first();

        if (!$order) {
            Flux::toast("No PO '{$po}' tidak ditemukan pada Master Data SAP.", variant: 'danger');
            $this->dispatch('play-sound', ['type' => 'error']);
            $this->scanPo = '';
            $this->dispatch('focus-scan-input');
            return;
        }

        $this->scannedOrder = $order;
        $this->productionQty = $order->target_quantity; // Default pre-fill
        $this->dispatch('play-sound', ['type' => 'success']);
        $this->dispatch('focus-qty-input'); // Move autofocus to production input!
    }

    /**
     * Step 2: Save Scanned PO Entry & Auto-Return Focus to Scan Input
     */
    public function saveEntry()
    {
        if (!$this->scannedOrder) {
            $this->dispatch('focus-scan-input');
            return;
        }

        $qty = (int) $this->productionQty;
        if ($qty <= 0) {
            Flux::toast('Jumlah kemas harus lebih dari 0.', variant: 'warning');
            $this->dispatch('focus-qty-input');
            return;
        }

        DailyReportItem::create([
            'daily_report_id' => $this->report->id,
            'production_order_id' => $this->scannedOrder->id,
            'po_number' => $this->scannedOrder->po_number,
            'product_name' => $this->scannedOrder->product_name,
            'specification' => $this->scannedOrder->specification,
            'machine_number' => $this->scannedOrder->machine_number,
            'target_quantity' => $this->scannedOrder->target_quantity,
            'good_quantity' => $qty,
            'unit' => $this->scannedOrder->unit ?? 'lembar',
            'notes' => $this->itemNotes,
            'scanned_at' => now(),
        ]);

        $this->refreshTotals();
        $this->reset(['scanPo', 'scannedOrder', 'productionQty', 'itemNotes']);
        
        $this->dispatch('play-sound', ['type' => 'success']);
        $this->dispatch('focus-scan-input'); // Return focus to scan input!
    }

    /**
     * Step 3: Inline Update on Table Row (No Modal!)
     */
    public function updateItemQuantity(int $itemId, $newQty)
    {
        $qty = (int) $newQty;
        if ($qty <= 0) {
            Flux::toast('Jumlah produksi tidak valid.', variant: 'warning');
            return;
        }

        $item = DailyReportItem::where('daily_report_id', $this->report->id)->findOrFail($itemId);
        $item->update(['good_quantity' => $qty]);

        $this->refreshTotals();
        Flux::toast("Jumlah PO {$item->po_number} berhasil diperbarui menjadi {$qty} lembar.", variant: 'success');
    }

    public function deleteItem(int $itemId)
    {
        DailyReportItem::where('daily_report_id', $this->report->id)->where('id', $itemId)->delete();
        $this->refreshTotals();
        Flux::toast('Baris PO berhasil dihapus.', variant: 'info');
    }

    protected function refreshTotals()
    {
        $this->report->update([
            'total_items_count' => $this->report->items()->count(),
            'total_good_quantity' => $this->report->items()->sum('good_quantity'),
        ]);
        $this->report->load(['items' => fn ($q) => $q->latest('id')]);
    }

    public function render()
    {
        return view('livewire.reports.production-entry', [
            'items' => $this->report->items,
        ]);
    }
}
```

### Alpine.js Focus Management & Scanner Listener

```html
<!-- resources/views/livewire/reports/production-entry.blade.php -->
<div x-data="{
    init() {
        this.$nextTick(() => this.$refs.scanInput?.focus());
    }
}" 
@focus-scan-input.window="$nextTick(() => $refs.scanInput?.focus())"
@focus-qty-input.window="$nextTick(() => { $refs.qtyInput?.focus(); $refs.qtyInput?.select(); })"
class="space-y-6">

    <!-- Top Scan Console -->
    <flux:card class="p-6 bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Scan PO Input -->
            <div class="md:col-span-5">
                <flux:field>
                    <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <flux:icon.qr-code class="size-4 text-emerald-600" />
                        Scan / Masukkan No PO Barcode
                    </flux:label>
                    <input 
                        type="text" 
                        x-ref="scanInput"
                        wire:model="scanPo"
                        wire:keydown.enter.prevent="lookupPo"
                        placeholder="Arahkan scanner ke Kartu Mesin..."
                        class="w-full h-11 px-3 text-base font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500"
                    />
                </flux:field>
            </div>

            <!-- Quantity Input -->
            <div class="md:col-span-4">
                <flux:field>
                    <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100">
                        Jumlah Kemas / Produksi (Lembar)
                    </flux:label>
                    <input 
                        type="number" 
                        x-ref="qtyInput"
                        wire:model="productionQty"
                        wire:keydown.enter.prevent="saveEntry"
                        placeholder="Contoh: 25000"
                        class="w-full h-11 px-3 text-base font-bold text-right rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-indigo-500"
                    />
                </flux:field>
            </div>

            <!-- Save Action Button -->
            <div class="md:col-span-3">
                <flux:button variant="primary" wire:click="saveEntry" class="w-full h-11 font-semibold">
                    Simpan Item (Enter)
                </flux:button>
            </div>
        </div>

        <!-- Matched Spec Pill -->
        @if($scannedOrder)
        <div class="mt-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between text-sm">
            <div>
                <span class="font-bold text-emerald-900 dark:text-emerald-300">{{ $scannedOrder->po_number }}</span>
                <span class="mx-2 text-zinc-400">|</span>
                <span class="text-emerald-800 dark:text-emerald-200">{{ $scannedOrder->product_name }}</span>
                <span class="mx-2 text-zinc-400">|</span>
                <span class="text-xs text-zinc-600 dark:text-zinc-400">Mesin: {{ $scannedOrder->machine_number ?? '-' }}</span>
            </div>
            <div class="font-mono font-bold text-emerald-800 dark:text-emerald-200">
                Target SAP: {{ number_format($scannedOrder->target_quantity, 0, ',', '.') }} {{ $scannedOrder->unit }}
            </div>
        </div>
        @endif
    </flux:card>

    <!-- Inline Editable Production Entries Table -->
    <flux:card class="overflow-hidden p-0">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-100 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="p-3 w-12 text-center">No</th>
                    <th class="p-3">No PO / Barcode</th>
                    <th class="p-3">Nama Produk & Spesifikasi</th>
                    <th class="p-3 text-center">Mesin</th>
                    <th class="p-3 text-right">Target SAP</th>
                    <th class="p-3 w-48 text-right font-bold text-indigo-700 dark:text-indigo-400">Jumlah Kemas (Edit Langsung)</th>
                    <th class="p-3 text-center">Waktu Scan</th>
                    <th class="p-3 text-center w-16">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($items as $index => $item)
                <tr wire:key="item-{{ $item->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                    <td class="p-3 text-center text-zinc-500">{{ $index + 1 }}</td>
                    <td class="p-3 font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $item->po_number }}</td>
                    <td class="p-3">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product_name }}</div>
                        <div class="text-xs text-zinc-500">{{ $item->specification }}</div>
                    </td>
                    <td class="p-3 text-center font-mono">{{ $item->machine_number ?? '-' }}</td>
                    <td class="p-3 text-right text-zinc-500">{{ number_format($item->target_quantity, 0, ',', '.') }}</td>
                    
                    <!-- Inline Editable Cell -->
                    <td class="p-2 text-right">
                        <input 
                            type="number"
                            value="{{ $item->good_quantity }}"
                            wire:change="updateItemQuantity({{ $item->id }}, $event.target.value)"
                            wire:keydown.enter.prevent="updateItemQuantity({{ $item->id }}, $event.target.value)"
                            class="w-36 h-9 px-2 text-right font-bold text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </td>

                    <td class="p-3 text-center text-xs text-zinc-500">{{ $item->scanned_at ? \Carbon\Carbon::parse($item->scanned_at)->format('H:i:s') : '-' }}</td>
                    <td class="p-3 text-center">
                        <flux:button 
                            size="sm" 
                            variant="ghost" 
                            icon="trash" 
                            wire:click="deleteItem({{ $item->id }})" 
                            wire:confirm="Yakin ingin menghapus baris PO ini?"
                            class="text-rose-600 hover:text-rose-700" 
                        />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-zinc-400">
                        Belum ada item PO yang dipindai. Silakan scan barcode pada kartu mesin di atas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>
</div>
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-3.1 Daily Production Report Schema, Product Series & Code Generator | 6 | Must |
| 2 | US-3.2 Daily Report Session Initiation (PCHT / MMEA / HPTL) | 6 | Must |
| 3 | US-3.3 High-Speed Singular Barcode Scanning & Auto-Focus Production Loop | 10 | Must |
| 4 | US-3.4 Real-Time Inline Editable Table for Scanned Entries (No Modals) | 8 | Must |
| 5 | US-3.5 Finalization, Shift Summary & Print Readiness | 4 | Should |

**Total Story Points: 34**

---

## Dependencies

| Dependency | From |
|---|---|
| Master Data SAP & `ProductionOrder` model | Phase 02 |
| User authentication & Petugas identity | Phase 01 |
| Hardware barcode scanner (Code 128 standard) | Operating Environment |

---

## Risks

| Risk | Mitigation |
|---|---|
| Operator loses focus during high-speed scanning | Use Alpine.js window-level event listeners to refocus the active input immediately after each step |
| Miscounted production input | Enable frictionless inline editing directly in the table row so operators can fix mistakes in seconds |
