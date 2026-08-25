# Phase 04 - Production Report Management, Verification & Print/Export (Lihat Laporan)

**Sprint Goal**: Mengembangkan modul "Lihat Laporan" berbasis Livewire 4 & Flux UI untuk mengelola seluruh arsip Laporan Produksi Harian, pencarian berdasarkan ID Laporan unik, verifikasi & koreksi data oleh Supervisor, serta fitur cetak dokumen fisik standar PT Percetakan Dokumen Sekuriti dan ekspor ke format PDF/Excel.

**Estimated Duration**: 4-5 days

**Depends on**: Phase 03 (Barcode Scan & Buat Laporan)

---

## User Stories

### US-4.1: Production Reports Index & Filter (Lihat Laporan)

**As a** supervisor or petugas  
**I want** to browse all submitted daily production reports with multi-criteria filtering and instant search  
**So that** I can easily find past packaging records by ID Laporan, date, or shift without opening physical filing cabinets

**Acceptance Criteria:**
- [ ] Livewire Component `App\Livewire\Reports\ReportIndex` listing all reports.
- [ ] Columns:
  - No
  - ID Laporan (`report_number`, e.g. `LAP-20260825-0001`)
  - Tanggal Laporan
  - Shift (`Shift 1`, `Shift 2`, `Shift 3`)
  - Petugas Kemas (Nama & NIP)
  - Total PO / Item
  - Total Qty Kemas Baik
  - Total Qty Reject
  - Status (`Draft`, `Terkirim`, `Terverifikasi`)
  - Aksi (`Lihat Detail`, `Cetak`, `Edit`, `Hapus`)
- [ ] Filter controls:
  - Date Presets: `Hari Ini`, `Kemarin`, `7 Hari Terakhir`, `Bulan Ini`, `Kustom Rentang Tanggal`.
  - Shift Filter (`Semua`, `Shift 1`, `Shift 2`, `Shift 3`).
  - Petugas Filter (Dropdown list of operators).
  - Status Filter (`Semua`, `Terkirim`, `Terverifikasi`).
- [ ] Real-time search across ID Laporan, PO numbers, and product names with `wire:model.live.debounce.300ms`.
- [ ] Pagination using Livewire `WithPagination` (10, 25, 50).

**UI Specs:**
- Flux data table (`<flux:table>`) with status badges (`<flux:badge color="blue">Terkirim</flux:badge>`, `<flux:badge color="green">Terverifikasi</flux:badge>`).
- Filter toolbar with Flux select and date picker components.
- Summary counter header: "Menampilkan X laporan (Total Kemas: Y lembar)".

**Tasks:**
1. Create Livewire Component `app/Livewire/Reports/ReportIndex.php` and Blade view `resources/views/livewire/reports/index.blade.php`.
2. Add query filter scopes in `DailyReport` model.
3. Optimize table rendering with Blaze (`@blaze`).
4. Write Pest feature tests for report listing, filtering, and pagination in `tests/Feature/Reports/DailyReportIndexTest.php`.

---

### US-4.2: Report Detail View & Audit History

**As a** supervisor or petugas  
**I want** to view the complete breakdown of a specific daily production report  
**So that** I can inspect every scanned PO item, customer specifications, and packaging notes

**Acceptance Criteria:**
- [ ] Livewire Component `App\Livewire\Reports\ReportDetail` (`/reports/{report:report_number}`).
- [ ] Header summary: ID Laporan, Tanggal, Shift, Operator, Verifikator, Waktu Dibuat, Waktu Diperbarui, Catatan.
- [ ] Scanned Items table detailing:
  - No PO & Kode Produk
  - Nama Produk & Spesifikasi Kertas/Sekuriti
  - No Mesin
  - Target Order vs Jumlah Kemas Baik
  - Jumlah Reject & Persentase Reject
  - Keterangan per Item
- [ ] Action buttons: `<flux:button icon="printer" wire:click="printReport">Cetak Laporan</flux:button>`, `<flux:button icon="arrow-down-tray" wire:click="exportPdf">Download PDF</flux:button>`, `Edit Laporan`, `Verifikasi Laporan` (Supervisor only).

**UI Specs:**
- Clean Flux card layout (`<flux:card>`) with clear visual hierarchy.
- Verification banner with timestamp and supervisor signature indicator.

**Tasks:**
1. Create Livewire Component `app/Livewire/Reports/ReportDetail.php` and Blade view `resources/views/livewire/reports/show.blade.php`.
2. Implement route binding with `report_number`.
3. Write Pest feature tests for viewing report details and access authorization in `tests/Feature/Reports/DailyReportShowTest.php`.

---

### US-4.3: Report Edit & Correction (Edit Laporan)

**As a** supervisor (or authoring petugas within grace period)  
**I want** to edit packaging quantities or fix item notes in a submitted report  
**So that** clerical errors or miscounted items can be corrected without corrupting historical records

**Acceptance Criteria:**
- [ ] Livewire Component `App\Livewire\Reports\EditReport` (`/reports/{report:report_number}/edit`).
- [ ] Form populated with existing report header and items.
- [ ] Capability to:
  - Adjust `good_quantity` and `reject_quantity`.
  - Add newly scanned items or remove mis-scanned items.
  - Update shift notes.
- [ ] Validation: same rules as report creation (good_quantity >= 1, valid POs).
- [ ] Recalculates total summary counts upon update.
- [ ] Authorization:
  - Petugas can only edit their own reports within 24 hours.
  - Supervisor can edit any report at any time.

**Tasks:**
1. Create Livewire Component `app/Livewire/Reports/EditReport.php` and Blade view `resources/views/livewire/reports/edit.blade.php`.
2. Create Policy `app/Policies/DailyReportPolicy.php` defining update/delete permissions.
3. Write Pest feature tests for report editing, validation, and policy authorization in `tests/Feature/Reports/UpdateDailyReportTest.php`.

---

### US-4.4: Supervisor Verification & Delete Protection

**As a** supervisor  
**I want** to verify/approve daily reports and safely delete invalid test reports  
**So that** only validated production reports are marked as final and unwanted entries can be removed safely

**Acceptance Criteria:**
- [ ] "Verifikasi Laporan" action button for supervisor: updates status to `verified` and stamps `supervisor_id` + `verified_at`.
- [ ] Verified reports become read-only for operators.
- [ ] Delete report action:
  - Only accessible by Supervisor via confirmation modal (`<flux:modal name="delete-report-confirm">`).
  - Cascades deletion of `daily_report_items`.
  - Reverts associated `production_orders` status.
- [ ] Toast confirmation on verification or deletion (`Flux::toast(...)`).

**Tasks:**
1. Implement `verify` and `delete` methods in `ReportDetail.php` and `ReportIndex.php`.
2. Implement authorization checks via `DailyReportPolicy`.
3. Write Pest feature tests for verification and deletion in `tests/Feature/Reports/DailyReportVerificationTest.php`.

---

### US-4.5: Standard Physical Print Layout & PDF/Excel Export

**As a** petugas or supervisor  
**I want** to print the Daily Packaging Report directly to printer or export to PDF/Excel  
**So that** a physical hardcopy can be signed and handed over to the Packaging Supervisor and Management as required by company SOP

**Acceptance Criteria:**
- [ ] Dedicated Blade Print Layout (`resources/views/reports/print.blade.php`) with CSS `@media print`:
  - Formatted for standard A4 paper with proper margins.
  - Company Header: "PT PERCETAKAN DOKUMEN SEKURITI - UNIT PENGEMASAN".
  - Subtitle: "LAPORAN DOKUMENTASI HASIL PRODUKSI HARIAN".
  - Metadata Grid: ID Laporan, Tanggal, Shift, Nama Petugas.
  - Items Table: No, No PO, Nama Produk, Spesifikasi, No Mesin, Qty Baik, Qty Reject, Satuan.
  - Signature Section: Tanda Tangan Petugas Kemas (Pembuat) & Kepala Unit/Supervisor (Mengetahui).
  - Hides web UI buttons, sidebar, and headers during printing.
- [ ] Direct Print Trigger (`window.print()`) with clean print preview.
- [ ] PDF Download endpoint (`GET /reports/{report:report_number}/pdf`) generating official PDF document.
- [ ] Excel Export endpoint (`GET /reports/export?from=...&to=...`) for monthly rekapitulasi data.

**UI Specs:**
- Print View modal / standalone printable route.
- 2-column signature block at the bottom of the printed page (Petugas Kemas & Supervisor).

**Tasks:**
1. Build print-optimized Blade template `resources/views/reports/print.blade.php`.
2. Setup PDF generator service using `barryvdh/laravel-dompdf` (or pure HTML print).
3. Setup Excel export service for bulk report exports.
4. Implement PDF & Excel download controller endpoints in `app/Http/Controllers/ReportExportController.php`.
5. Write Pest feature tests for print and export endpoints in `tests/Feature/Reports/ReportExportTest.php`.

---

## Technical Notes

### Print-Optimized Blade Layout (`resources/views/reports/print.blade.php`)

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Produksi - {{ $report->report_number }}</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 15mm; }
            body { font-family: sans-serif; font-size: 11pt; color: #000; background: #fff; }
            .no-print { display: none !important; }
        }
        body { font-family: sans-serif; margin: 0; padding: 20px; color: #111; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header h2 { font-size: 11pt; margin: 4px 0; font-weight: normal; }
        .header h3 { font-size: 13pt; margin: 8px 0 0; font-weight: bold; text-decoration: underline; }
        .meta-table { width: 100%; margin-bottom: 15px; font-size: 10pt; }
        .meta-table td { padding: 3px 0; vertical-align: top; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; }
        .data-table th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .signatures { width: 100%; margin-top: 30px; font-size: 10pt; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .sign-space { height: 60px; }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Cetak Dokumen (Print)</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>

    <div class="header">
        <h1>PT PERCETAKAN DOKUMEN SEKURITI</h1>
        <h2>Unit Pengemasan - Gedung Produksi Ciampel Karawang</h2>
        <h3>LAPORAN DOKUMENTASI HASIL PRODUKSI HARIAN</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>ID Laporan</strong></td>
            <td style="width: 35%;">: {{ $report->report_number }}</td>
            <td style="width: 15%;"><strong>Petugas Kemas</strong></td>
            <td style="width: 35%;">: {{ $report->user->name }} ({{ $report->user->nip }})</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>: {{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d F Y') }}</td>
            <td><strong>Shift Kerja</strong></td>
            <td>: {{ ucfirst(str_replace('_', ' ', $report->shift->value)) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">No PO / Barcode</th>
                <th style="width: 35%;">Nama Produk & Spesifikasi</th>
                <th style="width: 10%;">Mesin</th>
                <th style="width: 15%;">Qty Baik</th>
                <th style="width: 15%;">Qty Reject</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $item->po_number }}</td>
                <td>{{ $item->product_name }} - {{ $item->specification }}</td>
                <td class="text-center">{{ $item->machine_number ?? '-' }}</td>
                <td class="text-right font-bold">{{ number_format($item->good_quantity, 0, ',', '.') }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->reject_quantity, 0, ',', '.') }} {{ $item->unit }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($report->total_good_quantity, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($report->total_reject_quantity, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <p>Petugas Kemas,</p>
                <div class="sign-space"></div>
                <p class="font-bold" style="text-decoration: underline;">{{ $report->user->name }}</p>
                <p>NIP: {{ $report->user->nip }}</p>
            </td>
            <td>
                <p>Mengetahui,<br>Kepala Unit / Supervisor</p>
                <div class="sign-space"></div>
                <p class="font-bold" style="text-decoration: underline;">{{ $report->supervisor?->name ?? '( .......................................... )' }}</p>
                <p>NIP: {{ $report->supervisor?->nip ?? '..........................................' }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-4.1 Production Reports Index & Filter | 8 | Must |
| 2 | US-4.2 Report Detail View & Audit History | 5 | Must |
| 3 | US-4.3 Report Edit & Correction | 5 | Must |
| 4 | US-4.4 Supervisor Verification & Delete Protection | 4 | Should |
| 5 | US-4.5 Standard Physical Print Layout & PDF/Excel Export | 6 | Must |

**Total Story Points: 28**

---

## Dependencies

| Dependency | From |
|---|---|
| DailyReport & DailyReportItem models | Phase 03 |
| User role authorization & policies | Phase 01 |
| Livewire 4 & Flux UI tables | Project stack |

---

## Risks

| Risk | Mitigation |
|---|---|
| Inconsistent print pagination across different printer drivers | Use standardized `@page { size: A4 portrait; margin: 15mm; }` CSS rules |
| Unintended edits to already verified historical reports | Lock report modifications once status is set to `verified` unless unlocked by Supervisor |
