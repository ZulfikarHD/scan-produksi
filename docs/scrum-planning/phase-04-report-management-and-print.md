# Phase 04 - Production Report Management, Verification & Print/Export (Lihat Laporan)

**Sprint Goal**: Mengembangkan modul "Lihat Laporan" untuk mengelola seluruh arsip Laporan Produksi Harian, pencarian berdasarkan ID Laporan unik, verifikasi & koreksi data oleh Supervisor, serta fitur cetak dokumen fisik standar PT Percetakan Dokumen Sekuriti dan ekspor ke format PDF/Excel.

**Estimated Duration**: 4-5 days

**Depends on**: Phase 03 (Barcode Scan & Buat Laporan)

---

## User Stories

### US-4.1: Production Reports Index & Filter (Lihat Laporan)

**As a** supervisor or petugas  
**I want** to browse all submitted daily production reports with multi-criteria filtering and instant search  
**So that** I can easily find past packaging records by ID Laporan, date, or shift without opening physical filing cabinets

**Acceptance Criteria:**
- [ ] List all reports with columns:
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
  - Quick Date Presets: `Hari Ini`, `Kemarin`, `7 Hari Terakhir`, `Bulan Ini`, `Kustom Rentang Tanggal`.
  - Shift Filter (`Semua`, `Shift 1`, `Shift 2`, `Shift 3`).
  - Petugas Filter (Dropdown list of operators).
  - Status Filter (`Semua`, `Terkirim`, `Terverifikasi`).
- [ ] Global search across ID Laporan, PO numbers, and product names.
- [ ] Pagination with customizable items per page (10, 25, 50).

**UI Specs:**
- Modern data table with status badges (Blue: Terkirim, Green: Terverifikasi, Amber: Draft).
- Filter toolbar with date picker range selector.
- Summary counter header: "Menampilkan X laporan (Total Kemas: Y lembar)".

**Tasks:**
1. Implement `index` method in `app/Http/Controllers/DailyReportController.php`.
2. Create query filter scope in `DailyReport` model.
3. Create Inertia Page `resources/js/pages/ProductionReports/Index.vue`.
4. Create filter bar component `resources/js/components/Reports/ReportFilterBar.vue`.
5. Write Pest feature tests for report listing, filtering, and pagination in `tests/Feature/Reports/DailyReportIndexTest.php`.

---

### US-4.2: Report Detail View & Audit History

**As a** supervisor or petugas  
**I want** to view the complete breakdown of a specific daily production report  
**So that** I can inspect every scanned PO item, customer specifications, and packaging notes

**Acceptance Criteria:**
- [ ] Dedicated Report Detail page (`/reports/{report:report_number}`).
- [ ] Header summary: ID Laporan, Tanggal, Shift, Operator, Verifikator, Waktu Dibuat, Waktu Diperbarui, Catatan.
- [ ] Scanned Items table detailing:
  - No PO & Kode Produk
  - Nama Produk & Spesifikasi Kertas/Sekuriti
  - No Mesin
  - Target Order vs Jumlah Kemas Baik
  - Jumlah Reject & Persentase Reject
  - Keterangan per Item
- [ ] Action buttons: `Cetak Laporan`, `Download PDF`, `Edit Laporan` (if authorized), `Verifikasi Laporan` (Supervisor only).

**UI Specs:**
- Clean card layout with clear visual hierarchy.
- Printable preview toggle.
- Verification banner with timestamp and supervisor signature indicator.

**Tasks:**
1. Implement `show` method in `DailyReportController.php`.
2. Create Inertia Page `resources/js/pages/ProductionReports/Show.vue`.
3. Create printable preview component `resources/js/components/Reports/ReportPrintPreview.vue`.
4. Write Pest feature tests for viewing report details and access authorization in `tests/Feature/Reports/DailyReportShowTest.php`.

---

### US-4.3: Report Edit & Correction (Edit Laporan)

**As a** supervisor (or authoring petugas within grace period)  
**I want** to edit packaging quantities or fix item notes in a submitted report  
**So that** clerical errors or miscounted items can be corrected without corrupting historical records

**Acceptance Criteria:**
- [ ] Edit page populated with existing report header and items.
- [ ] Capability to:
  - Adjust `good_quantity` and `reject_quantity`.
  - Add newly scanned items or remove mis-scanned items.
  - Update shift notes.
- [ ] Validation: same rules as report creation (good_quantity >= 1, valid POs).
- [ ] Recalculates total summary counts upon update.
- [ ] Authorization:
  - Petugas can only edit their own reports within 24 hours.
  - Supervisor can edit any report at any time.
- [ ] Updates audit timestamp and logs editor user ID.

**Tasks:**
1. Create Form Request `app/Http/Requests/Reports/UpdateDailyReportRequest.php`.
2. Create Policy `app/Policies/DailyReportPolicy.php` defining update/delete permissions.
3. Implement `edit` and `update` methods in `DailyReportController.php`.
4. Create Inertia Page `resources/js/pages/ProductionReports/Edit.vue`.
5. Write Pest feature tests for report editing, validation, and policy authorization in `tests/Feature/Reports/UpdateDailyReportTest.php`.

---

### US-4.4: Supervisor Verification & Delete Protection

**As a** supervisor  
**I want** to verify/approve daily reports and safely delete invalid test reports  
**So that** only validated production reports are marked as final and unwanted entries can be removed safely

**Acceptance Criteria:**
- [ ] "Verifikasi Laporan" button for supervisor: updates status to `verified` and stamps `supervisor_id` + `verified_at`.
- [ ] Verified reports become read-only for operators.
- [ ] Delete report action:
  - Only accessible by Supervisor.
  - Requires confirmation modal with warning.
  - Cascades deletion of `daily_report_items`.
  - Reverts associated `production_orders` status.
- [ ] Flash message confirmation upon verification or deletion.

**Tasks:**
1. Add `verify` and `destroy` endpoints in `DailyReportController.php`.
2. Implement authorization checks via `DailyReportPolicy`.
3. Add verification & delete confirmation modals in Vue frontend.
4. Write Pest feature tests for verification and deletion in `tests/Feature/Reports/DailyReportVerificationTest.php`.

---

### US-4.5: Standard Physical Print Layout & PDF/Excel Export

**As a** petugas or supervisor  
**I want** to print the Daily Packaging Report directly to printer or export to PDF/Excel  
**So that** a physical hardcopy can be signed and handed over to the Packaging Supervisor and Management as required by company SOP

**Acceptance Criteria:**
- [ ] Dedicated Print CSS layout (`@media print`):
  - Formatted for standard A4 paper with proper margins.
  - Company Header: "PT PERCETAKAN DOKUMEN SEKURITI - UNIT PENGEMASAN".
  - Subtitle: "LAPORAN HASIL PRODUKSI DAN PENGEMASAN HARIAN".
  - Metadata Table: ID Laporan, Tanggal, Shift, Nama Petugas.
  - Items Table: No, No PO, Nama Produk, Spesifikasi, No Mesin, Qty Baik, Qty Reject, Satuan.
  - Signature Section: Tanda Tangan Petugas Kemas (Pembuat) & Kepala Unit/Supervisor (Mengetahui).
  - Hides web UI buttons, sidebar, and headers during printing.
- [ ] Direct Print Trigger (`window.print()`) accessible with 1-click.
- [ ] PDF Download endpoint (`GET /reports/{report}/pdf`) generating identical official PDF document.
- [ ] Excel Export endpoint (`GET /reports/export?from=...&to=...`) for monthly rekapitulasi data.

**UI Specs:**
- Print View modal / full-page print preview.
- 3-column signature block at the bottom of the printed page (Petugas Kemas, QC Inspector, Supervisor).

**Tasks:**
1. Build print-optimized Blade/Vue template with CSS print media queries.
2. Setup PDF generator service using `barryvdh/laravel-dompdf` or pure CSS print view.
3. Setup Excel export service using `maatwebsite/excel` for bulk report exports.
4. Implement PDF & Excel download endpoints in `DailyReportController.php`.
5. Write Pest feature tests for print and export endpoints in `tests/Feature/Reports/ReportExportTest.php`.

---

## Technical Notes

### Print-Optimized Layout Structure

```html
<!-- Print Layout Template Example -->
<div class="print-container hidden print:block p-8 bg-white text-black">
    <!-- Header -->
    <div class="border-b-2 border-black pb-4 mb-4 text-center">
        <h1 class="text-xl font-bold uppercase tracking-wide">PT PERCETAKAN DOKUMEN SEKURITI</h1>
        <h2 class="text-sm font-semibold uppercase">Unit Pengemasan - Gedung Produksi Ciampel Karawang</h2>
        <h3 class="text-base font-bold mt-2 underline">LAPORAN DOKUMENTASI HASIL PRODUKSI HARIAN</h3>
    </div>

    <!-- Metadata Grid -->
    <div class="grid grid-cols-2 gap-4 text-xs mb-4">
        <div>
            <p><strong>ID Laporan:</strong> {{ report.report_number }}</p>
            <p><strong>Tanggal:</strong> {{ formatDate(report.report_date) }}</p>
            <p><strong>Shift:</strong> {{ report.shift_label }}</p>
        </div>
        <div class="text-right">
            <p><strong>Petugas Kemas:</strong> {{ report.user.name }} ({{ report.user.nip }})</p>
            <p><strong>Waktu Cetak:</strong> {{ currentTimestampWIB }}</p>
            <p><strong>Status:</strong> {{ report.status_label }}</p>
        </div>
    </div>

    <!-- Scanned Items Table -->
    <table class="w-full border-collapse border border-black text-xs mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black p-1">No</th>
                <th class="border border-black p-1">No PO</th>
                <th class="border border-black p-1">Nama Produk / Spesifikasi</th>
                <th class="border border-black p-1">Mesin</th>
                <th class="border border-black p-1 text-right">Qty Baik</th>
                <th class="border border-black p-1 text-right">Qty Reject</th>
                <th class="border border-black p-1">Satuan</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, idx) in report.items" :key="item.id">
                <td class="border border-black p-1 text-center">{{ idx + 1 }}</td>
                <td class="border border-black p-1 font-mono font-bold">{{ item.po_number }}</td>
                <td class="border border-black p-1">{{ item.product_name }} - {{ item.specification }}</td>
                <td class="border border-black p-1 text-center">{{ item.machine_number || '-' }}</td>
                <td class="border border-black p-1 text-right font-bold">{{ item.good_quantity.toLocaleString('id-ID') }}</td>
                <td class="border border-black p-1 text-right">{{ item.reject_quantity.toLocaleString('id-ID') }}</td>
                <td class="border border-black p-1 text-center">{{ item.unit }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="font-bold bg-gray-100">
                <td colspan="4" class="border border-black p-1 text-right">TOTAL</td>
                <td class="border border-black p-1 text-right">{{ report.total_good_quantity.toLocaleString('id-ID') }}</td>
                <td class="border border-black p-1 text-right">{{ report.total_reject_quantity.toLocaleString('id-ID') }}</td>
                <td class="border border-black p-1"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Signature Footer -->
    <div class="grid grid-cols-2 text-center text-xs mt-12 pt-4">
        <div>
            <p>Petugas Kemas,</p>
            <div class="h-16"></div>
            <p class="font-bold underline">{{ report.user.name }}</p>
            <p>NIP: {{ report.user.nip }}</p>
        </div>
        <div>
            <p>Mengetahui,</p>
            <p>Kepala Unit / Supervisor</p>
            <div class="h-12"></div>
            <p class="font-bold underline">{{ report.supervisor?.name || '( ................................... )' }}</p>
            <p>NIP: {{ report.supervisor?.nip || '...................................' }}</p>
        </div>
    </div>
</div>
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
| Excel & PDF export libraries | Composer packages |

---

## Risks

| Risk | Mitigation |
|---|---|
| Inconsistent print pagination across different printer drivers | Use standardized `@page { size: A4 portrait; margin: 15mm; }` and `page-break-inside: avoid` CSS rules |
| Unintended edits to already verified historical reports | Lock report modifications once status is set to `verified` unless unlocked by Supervisor |
