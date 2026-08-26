# UX Plan — Phase 04: Production Report Management, Verification & Print/Export (Lihat Laporan)

> Created before implementation. This document is a hard constraint for all work in Phase 04.  
> Epic file: `docs/scrum-planning/phase-04-report-management-and-print.md`

---

## 1. Navigation Footprint

Perancangan antarmuka pada Phase 04 berfokus pada kemudahan penelusuran arsip laporan hasil pengemasan (*Lihat Laporan*), alur verifikasi resmi oleh Kepala Unit / Supervisor, koreksi data hasil kemas (*clerical error correction*), serta penyediaan dokumen cetak fisik standar PT Percetakan Dokumen Sekuriti (PT PDS) dan ekspor data digital (PDF dan Excel). Seluruh interaksi dirancang agar staf pengemasan dan supervisor dapat menemukan, memeriksa, dan mencetak dokumen dalam hitungan detik tanpa hambatan navigasi.

### Navigation Hierarchy & Routes Summary

- **Total Sidebar Menu Items untuk Phase 04**: **1 item** pada sidebar navigasi utama, yaitu menu **"Lihat Laporan"** (ikon dokumen/tabel, dapat diakses oleh Petugas Kemas dan Supervisor). Menu ini melengkapi alur kerja dari menu "Buat Laporan" pada Phase 03.
- **Total Dedicated Routes/Pages**: **4 rute utama**:
  1. `/reports` (alias `/laporan`): **Daftar Arsip Laporan Produksi & Multi-Filter** — Halaman penjelajah seluruh berkas laporan harian dengan filter seri produk (PCHT, MMEA, HPTL/REL), tanggal preset, shift kerja, status verifikasi, dan pencarian instan nomor PO / kode laporan.
  2. `/reports/{report:report_code}`: **Detail Rekapitulasi Laporan & Status Verifikasi** — Halaman penelaahan komprehensif seluruh item PO yang dipindai, persentase reject, identitas pembuat/verifikator, serta tombol aksi cetak dan verifikasi supervisor.
  3. `/reports/{report:report_code}/edit`: **Formulir Koreksi & Penyesuaian Laporan** — Halaman koreksi kuantitas kemas baik / reject, penambahan item susulan, atau penghapusan item salah pindai sebelum laporan dikunci permanen.
  4. `/reports/{report:report_code}/print`: **Tata Letak Cetak Fisik Standar A4** — Tampilan dokumen bersih (*clean print view*) yang dioptimalkan untuk cetak printer langsung (`window.print()`) lengkap dengan kop surat PT PDS dan kolom tanda tangan ganda fisik.
- **Direct Download Endpoints (Tanpa Antarmuka Khusus)**:
  - `GET /reports/{report:report_code}/pdf`: Mengunduh berkas salinan digital dokumen resmi berformat PDF.
  - `GET /reports/export`: Mengunduh rekapitulasi data produksi dalam format spreadsheet Excel (`.xlsx`) berdasarkan filter aktif.
- **Modal vs. Halaman Terpisah**:
  - **Modal Dialog Verifikasi Laporan (`<flux:modal name="verify-report-confirm">`)**: Modal konfirmasi supervisor dengan ringkasan total kuantitas kemas sebelum status laporan resmi diubah menjadi `Terverifikasi` dan dikunci.
  - **Modal Dialog Hapus Laporan (`<flux:modal name="delete-report-confirm">`)**: Modal peringatan destruktif khusus Supervisor untuk menghapus laporan keliru atau laporan uji coba dengan teks konsekuensi yang jelas.
  - **Modal Dialog Rentang Tanggal Ekspor Excel (`<flux:modal name="export-excel-modal">`)**: Modal ringkas untuk memilih periode rekapitulasi (tanggal awal dan tanggal akhir) sebelum mengunduh berkas Excel.
- **Backend-Only (No Dedicated UI Surface)**:
  - Logika otorisasi peran pada `DailyReportPolicy` (aturan bahwa Petugas hanya dapat mengedit laporan miliknya dalam batas toleransi 24 jam dan belum diverifikasi, sedangkan Supervisor memiliki hak verifikasi, edit penuh, dan hapus).
  - Generator berkas PDF dan layanan streaming ekspor Excel menggunakan `openspout` dengan alokasi memori rendah.
  - Sinkronisasi pembaruan status data order produksi pada tabel `production_orders` saat laporan diverifikasi atau dihapus.

---

## 2. Screen Inventory

Inventaris seluruh layar, panel, dan komponen modal interaktif pada modul Lihat Laporan (Phase 04):

| Screen / Panel | Location | What's visible on first open | Further triggers | Click depth |
|---|---|---|---|---|
| **Layar 1: Daftar Arsip Laporan** | `/reports` (Menu "Lihat Laporan") | **Header**: Judul "Laporan Produksi", ringkasan rekapitulasi (Total Laporan, Total Lembar Kemas), tombol "Ekspor Excel".<br>**Toolbar Filter**: Pencarian teks instan (Kode Laporan / No PO / Nama Produk), Filter Seri Produk (Semua, PCHT, MMEA, HPTL), Filter Shift (Shift 1, 2, 3), Preset Tanggal (Hari Ini, Kemarin, 7 Hari Terakhir, Bulan Ini), Filter Status (Semua, Draft, Terkirim, Terverifikasi).<br>**Tabel Arsip**: No, Kode Laporan, Seri Produk badge, Tanggal Laporan, Shift badge, Petugas Kemas (Nama & NP), Total PO, Total Hasil Kemas (Lembar), Status badge, Menu Aksi (Detail, Cetak, Edit, Hapus). | Mengetik di pencarian atau mengubah filter menyaring data seketika; klik baris/tombol "Detail" membuka rute detail; klik "Cetak" membuka lembar cetak di tab baru; klik "Ekspor Excel" memicu modal rentang tanggal. | 1 klik dari Sidebar (menu "Lihat Laporan") |
| **Layar 2: Detail Rekapitulasi Laporan** | `/reports/{report:report_code}` | **Header Laporan**: Kode Laporan (`PCHT-YYYY-MM-DD`), Badge Seri Produk, Badge Shift, Tanggal Kerja, Status Verifikasi.<br>**Banner Informasi**: Identitas Petugas Kemas (Pembuat) beserta jam pembuatan, Status Verifikasi Supervisor (Nama Supervisor, NP, dan waktu verifikasi jika sudah diverifikasi).<br>**Toolbar Aksi**: Tombol "Cetak Dokumen" (Ikon printer), tombol "Download PDF", tombol "Edit Laporan" (jika diizinkan), tombol "Verifikasi Laporan" (khusus Supervisor jika belum diverifikasi).<br>**Tabel Rincian Item**: No, No PO, Nama Produk & Spesifikasi Kertas Sekuriti, Nomor Mesin, Target Order SAP, Hasil Kemas Baik, Kuantitas Reject & Persentase Reject, Keterangan.<br>**Baris Footer Total**: Total Akumulasi Kemas Baik & Total Reject. | Klik "Verifikasi Laporan" memicu modal konfirmasi verifikasi; klik "Cetak Dokumen" membuka jendela cetak; klik "Edit Laporan" mengarahkan ke halaman edit. | 1 klik dari tabel daftar laporan (Layar 1) |
| **Layar 3: Formulir Koreksi Laporan** | `/reports/{report:report_code}/edit` | **Header**: Teks "Koreksi Laporan Produksi - [Kode Laporan]", peringatan status penguncian.<br>**Formulir Catatan**: Input catatan umum shift operasional.<br>**Tabel Item Terdaftar**: Daftar seluruh PO yang sudah ada di laporan dengan input kuantitas kemas baik dan reject yang dapat diubah langsung, serta tombol hapus baris.<br>**Bagian Tambah PO Susulan**: Input scan barcode No PO susulan, nama produk otomatis, dan input hasil kemas.<br>**Tombol Aksi Bawah**: Tombol "Batal" (kembali ke detail) dan tombol "Simpan Perubahan Laporan". | Mengubah angka kuantitas langsung menghitung ulang selisih dan persentase reject; tombol simpan memvalidasi seluruh data dan mengalihkan kembali ke halaman detail dengan notifikasi sukses. | 2 klik (dari halaman detail atau menu aksi tabel index) |
| **Layar 4: Lembar Cetak Fisik Standar A4** | `/reports/{report:report_code}/print` | Tampilan dokumen format resmi putih polos tanpa sidebar web:<br>- Kop Perusahaan PT PDS Unit Pengemasan.<br>- Judul: LAPORAN DOKUMENTASI HASIL PRODUKSI HARIAN.<br>- Tabel metadata (Kode Laporan, Petugas Kemas, Tanggal, Shift).<br>- Tabel rincian hasil kemas (No PO, Produk, Mesin, Qty Baik, Qty Reject).<br>- Baris Total Rekapitulasi.<br>- Blok 2 kolom tanda tangan fisik (Petugas Kemas & Supervisor).<br>- Tombol melayang "Cetak Dokumen" dan "Tutup" (otomatis disembunyikan saat dicetak ke kertas). | Tombol "Cetak Dokumen" memicu dialog print bawaan browser (`window.print()`). | 1 klik dari tombol cetak di halaman index atau detail |
| **Modal Verifikasi Laporan** | Modal `<flux:modal name="verify-report-confirm">` di `/reports/{report_code}` | Judul "Verifikasi Laporan Produksi", rincian ringkas: Kode Laporan, Petugas Kemas, Total PO, dan Total Hasil Kemas (Lembar). Teks penjelasan: *"Setelah diverifikasi, laporan ini akan ditandai resmi dan tidak dapat diubah kembali oleh petugas."*, tombol "Batal", tombol "Ya, Verifikasi Laporan" (warna emerald/hijau). | Klik tombol verifikasi mencatat supervisor_id dan timestamp saat ini, mengunci laporan, menutup modal, dan memperbarui status badge menjadi hijau. | 1 klik dari tombol "Verifikasi Laporan" pada Layar 2 |
| **Modal Hapus Laporan** | Modal `<flux:modal name="delete-report-confirm">` di `/reports` atau `/reports/{report_code}` | Judul "Hapus Berkas Laporan Produksi", peringatan konsekuensi merah: *"Apakah Anda yakin ingin menghapus Laporan [Kode Laporan]? Tindakan ini akan menghapus seluruh data item kemas terkait dan mengembalikan status order produksi ke daftar antrean."*, tombol "Batal", tombol "Hapus Permanen" (merah). | Menghapus data dari database, menutup modal, mengembalikan status PO, dan memperbarui tampilan tabel secara reaktif. | 2 klik dari menu aksi baris laporan |
| **Modal Ekspor Rekapitulasi Excel** | Modal `<flux:modal name="export-excel-modal">` di `/reports` | Judul "Ekspor Rekapitulasi Produksi (Excel)", pilihan rentang tanggal (Tanggal Mulai s/d Tanggal Selesai), pilihan Seri Produk (Semua / PCHT / MMEA / HPTL), tombol "Batal", tombol "Unduh File Excel" (warna hijau). | Mengarahkan browser ke URL endpoint `/reports/export?...` untuk memulai proses download file `.xlsx`. | 1 klik dari tombol "Ekspor Excel" pada Layar 1 |
| **Global Toast Feedback** | Komponen `<flux:toast />` (Pojok kanan atas) | Muncul otomatis selama 4 detik saat ada aksi berhasil (sukses verifikasi, sukses update koreksi, sukses hapus laporan) atau gagal. | Dapat ditutup manual dengan klik tombol silang (x). | 0 (Otomatis dipicu oleh event Livewire) |

---

## 3. User Journey Maps

Pemetaan langkah kerja pengguna untuk seluruh skenario utama pada modul Pengelolaan Laporan, Verifikasi, dan Pencetakan Dokumen:

### Journey 1: Petugas / Supervisor Mencari dan Memeriksa Laporan Masa Lalu
- **Goal**: Menemukan berkas laporan produksi tertentu berdasarkan tanggal, shift, atau nomor PO untuk pengecekan data kemas.
- **Starts at**: Sidebar navigasi $\rightarrow$ Menu "Lihat Laporan" (`/reports`).
- **Steps**:
  1. Pengguna membuka halaman "Lihat Laporan".
  2. Pengguna mengetik nomor PO (misal: "PO-2026-8891") atau kode laporan pada kotak pencarian, atau memilih filter tanggal "Kemarin" / "Bulan Ini".
  3. Tabel secara reaktif menampilkan baris laporan yang cocok tanpa perlu menekan tombol submit.
  4. Pengguna mengklik tombol "Detail" pada baris laporan target.
- **Done**: Halaman rincian laporan (`/reports/{report_code}`) terbuka menampilkan seluruh daftar PO, nomor mesin, spesifikasi kertas, dan kuantitas hasil kemas.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 2: Supervisor Memeriksa dan Memverifikasi Laporan Produksi Shift
- **Goal**: Memvalidasi kebenaran data fisik hasil pengemasan dan memberikan persetujuan resmi digital (*Approval Verification*).
- **Starts at**: Halaman Detail Laporan (`/reports/{report_code}`) yang berstatus `Terkirim` (*Submitted*).
- **Steps**:
  1. Supervisor meneliti tabel rincian item hasil kemas baik dan reject pada layar detail.
  2. Supervisor mengklik tombol "Verifikasi Laporan" di pojok kanan atas toolbar aksi.
  3. Modal konfirmasi verifikasi (`verify-report-confirm`) muncul menampilkan ringkasan akumulasi total lembar.
  4. Supervisor mengklik tombol "Ya, Verifikasi Laporan".
- **Done**: Sistem menyimpan `supervisor_id` dan `verified_at`, status laporan berganti menjadi `Terverifikasi` (badge hijau), banner verifikasi menampilkan nama supervisor, dan toast sukses muncul.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 3: Petugas / Supervisor Mengoreksi Kesalahan Input Angka Hasil Kemas
- **Goal**: Memperbaiki kekeliruan pencatatan jumlah lembar baik atau lembar reject yang salah ketik pada saat pemindaian.
- **Starts at**: Halaman Detail Laporan (`/reports/{report_code}`) atau Menu Aksi pada Tabel Index.
- **Steps**:
  1. Pengguna mengklik tombol "Edit Laporan".
  2. Halaman koreksi (`/reports/{report_code}/edit`) terbuka dengan data form yang sudah terisi.
  3. Pengguna mengubah angka pada kolom "Qty Baik" atau "Qty Reject" pada baris PO yang bersangkutan (sistem menghitung ulang persentase secara otomatis).
  4. Pengguna menekan tombol "Simpan Perubahan Laporan".
- **Done**: Data tersimpan, total akumulasi laporan diperbarui di database, pengguna dialihkan kembali ke halaman detail dengan notifikasi toast hijau "Laporan berhasil diperbarui".
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 4: Petugas Mencetak Dokumen Fisik Laporan untuk Tanda Tangan Basah
- **Goal**: Mencetak lembar laporan fisik ukuran kertas A4 untuk ditandatangani oleh Petugas Kemas dan diserahkan ke Kepala Unit/Supervisor sesuai SOP pabrik.
- **Starts at**: Halaman Detail Laporan (`/reports/{report_code}`) atau Menu Aksi pada Tabel Index.
- **Steps**:
  1. Pengguna mengklik tombol "Cetak Dokumen" (ikon printer).
  2. Jendela baru/halaman cetak (`/reports/{report_code}/print`) terbuka dengan tata letak dokumen formal A4 lengkap dengan kop PT PDS dan kolom tanda tangan.
  3. Pengguna mengklik tombol "Cetak Dokumen" (atau menekan `Ctrl+P`) dan memilih printer fisik.
- **Done**: Dokumen tercetak rapi sesuai batas margin 15mm standar A4 tanpa elemen navigasi website.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 5: Supervisor Mengunduh Rekapitulasi Data Produksi ke Format Excel
- **Goal**: Menarik data rekapitulasi seluruh laporan produksi bulanan untuk keperluan pelaporan manajemen dan rekonsiliasi data.
- **Starts at**: Halaman Daftar Laporan (`/reports`).
- **Steps**:
  1. Supervisor mengklik tombol "Ekspor Excel" pada toolbar atas.
  2. Modal rentang tanggal muncul; supervisor memilih periode (contoh: Tanggal 1 s/d 31 bulan berjalan) dan Seri Produk.
  3. Supervisor mengklik tombol "Unduh File Excel".
- **Done**: Berkas `.xlsx` langsung terunduh secara otomatis melalui browser berisi lembar kerja data laporan dan rincian item PO.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 6: Supervisor Menghapus Laporan Rusak / Laporan Uji Coba
- **Goal**: Membuang berkas laporan uji coba yang tidak sengaja terbuat dan mengembalikan data order ke status siap kemas.
- **Starts at**: Halaman Daftar Laporan (`/reports`) atau Detail Laporan.
- **Steps**:
  1. Supervisor mengklik menu aksi "Hapus Laporan" pada baris laporan target.
  2. Modal konfirmasi bahaya (`delete-report-confirm`) muncul dengan rincian konsekuensi.
  3. Supervisor mengklik tombol merah "Hapus Permanen".
- **Done**: Berkas laporan dan seluruh rincian item terhapus, status relasi order produksi dikembalikan, tabel rekapitulasi terupdate, dan toast konfirmasi ditampilkan.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

## 4. Indonesian UX Guardrails

Panduan antarmuka dan interaksi yang disesuaikan secara ketat dengan karakteristik operasional staf pabrik dan supervisor lini pengemasan dokumen sekuriti:

### Patience Thresholds (Batas Toleransi Langkah Kerja)

1. **Aktivitas Harian Rutin (Mencari Laporan & Cetak Dokumen Fisik)**
   <br>
   Maksimal **2–3 langkah**. Akses tombol cetak harus tersedia langsung dari baris tabel utama (`/reports`) dan dari halaman detail (`/reports/{report_code}`). Pratinjau cetak harus siap tanpa meminta konfigurasi margin rumit di sisi pengguna.
2. **Aktivitas Verifikasi Supervisor (Persetujuan Dokumen Shift)**
   <br>
   Maksimal **2–3 langkah**. Supervisor dapat langsung meninjau data dari layar detail dan menyelesaikan verifikasi melalui satu modal konfirmasi ringkas.
3. **Aktivitas Koreksi Data (Edit Jumlah Lembar / Reject)**
   <br>
   Maksimal **3 langkah**. Form edit menampilkan tabel terisi dengan input angka yang langsung fokus dan dapat diperbarui secara cepat.

### Cognitive Load Budget (Beban Kognitif Ringan)

1. **Maksimal 3–4 Elemen Filter Utama yang Tampak Bersamaan**
   <br>
   Toolbar penyaringan hanya menampilkan: Kotak Cari (Teks Bebas), Pilihan Seri Produk, Preset Tanggal Cepat, dan Filter Shift. Filter lanjutan (seperti filter nama petugas atau status verifikasi) disajikan secara rapi tanpa memenuhi layar.
2. **Keterbacaan Tabel yang Kontras dan Terstruktur**
   <br>
   - Kode Laporan menggunakan font monospace tebal (contoh: `PCHT-2026-08-26`).
   - Kuantitas lembar cetak selalu menggunakan format pemisah ribuan standar Indonesia (contoh: `25.000 lembar`).
   - Status verifikasi menggunakan badge warna Flux UI yang jelas: `Draft` (abu-abu/zinc), `Terkirim` (biru/sky), dan `Terverifikasi` (hijau/emerald).
3. **Pemisahan Jelas Antara Informasi Ringkasan dan Rincian Teknis**
   <br>
   Halaman detail memisahkan blok identitas shift kerja di bagian atas dan tabel item PO di bagian bawah, sehingga supervisor dapat langsung melihat total akumulasi sebelum menelaah baris demi baris.
4. **Maksimal 1 Tingkat Modal (*No Nested Modals*)**
   <br>
   Tidak boleh ada modal di atas modal. Semua aksi konfirmasi verifikasi, ekspor tanggal, atau hapus diselesaikan dalam dialog satu lapis.

### Trust Signals & Umpan Balik Operasional

1. **Transparansi Audit Trail & Identitas Verifikasi**
   <br>
   Pada laporan yang sudah diverifikasi, sistem menampilkan banner verifikasi yang memuat: nama lengkap supervisor, Nomor Pegawai (NP), serta tanggal dan jam tepat saat verifikasi dilakukan (WIB).
2. **Peringatan Batas Akses & Penguncian Data (Lock Indicator)**
   <br>
   - Jika laporan sudah berstatus `Terverifikasi`, tombol "Edit Laporan" bagi akun Petugas disembunyikan atau dinonaktifkan dengan tooltip informatif: *"Laporan telah diverifikasi oleh Supervisor dan dikunci dari perubahan."*
   - Jika laporan sudah melewati masa toleransi 24 jam, sistem memberikan pemberitahuan bahwa koreksi hanya dapat dilakukan melalui akun Supervisor.
3. **Konfirmasi Aksi Destruktif dengan Bahasa Indonesia yang Santun dan Tegas**
   <br>
   - Konfirmasi verifikasi: *"Apakah Anda yakin ingin memverifikasi Laporan Produksi [PCHT-2026-08-26]? Total hasil kemas tercatat sebanyak 125.000 lembar. Setelah diverifikasi, laporan berstatus final."*
   - Konfirmasi hapus: *"Tindakan ini akan menghapus berkas laporan dan mengembalikan 8 Nomor PO ke status antrean pengemasan. Data yang dihapus tidak dapat dipulihkan."*
4. **Pesan Kesalahan Bahasa Indonesia yang Jelas dan Edukatif**
   <br>
   - ❌ *"AuthorizationException: This action is unauthorized."* $\rightarrow$ ✅ *"Anda tidak memiliki hak akses untuk memverifikasi atau menghapus laporan ini. Fitur ini khusus untuk akun Supervisor."*
   - ❌ *"Integrity error on foreign key cascade"* $\rightarrow$ ✅ *"Laporan tidak dapat dihapus karena data terkait sedang digunakan oleh proses lain."*
   - ❌ *"The good_quantity must be at least 1"* $\rightarrow$ ✅ *"Jumlah hasil kemas baik minimal 1 lembar."*

### Risks Specific to Phase 04 & Mitigations

| Sub-epic / Fitur | Potensi Risiko UX / Operasional | Solusi & Mitigasi Desain |
|---|---|---|
| **US-4.1 Reports Index & Filter** | Tabel menjadi sangat lambat ketika data arsip mencapai ribuan baris laporan | Gunakan paginasi Livewire (10, 25, 50 baris per halaman), optimasi query dengan eager loading (`with(['user', 'supervisor', 'items'])`), dan indeks komposit pada kolom tanggal dan kode laporan. |
| **US-4.2 Detail View & Audit History** | Supervisor kesulitan membedakan antara laporan yang baru dikirim dengan yang sudah disetujui | Pasang banner status verifikasi dengan badge warna kontras di bagian atas layar detail serta indikator cap digital supervisor. |
| **US-4.3 Report Edit & Correction** | Petugas tidak sengaja mengedit laporan masa lalu yang sudah diserahkan ke manajemen | Terapkan pembatasan otorisasi (`DailyReportPolicy`): Petugas hanya dapat mengedit dalam jangka waktu 24 jam jika belum diverifikasi. Supervisor memiliki akses koreksi kapan pun. |
| **US-4.4 Supervisor Verification & Delete** | Supervisor tidak sengaja menekan tombol hapus laporan resmi | Tombol hapus hanya muncul untuk Supervisor, diletakkan di menu sekunder, dan dilindungi oleh modal konfirmasi bahaya dengan penegasan nama kode laporan. |
| **US-4.5 Print Layout & Export** | Format cetak terpotong atau halaman kedua hanya berisi kolom tanda tangan saat dicetak ke kertas A4 | Gunakan aturan CSS `@media print` dengan `@page { size: A4 portrait; margin: 15mm; }`, ukuran font 10–11pt yang terukur, dan `page-break-inside: avoid` pada blok tanda tangan agar selalu berada dalam 1 halaman yang sama. |

---

## 5. Implementation Boundaries (Anti-Splitting Rules)

Batasan arsitektur dan antarmuka yang wajib dipatuhi oleh tim pengembang/AI saat mengimplementasikan Phase 04:

### Do Not Split — Combine Into One Surface:
- **Daftar Arsip, Pencarian Multi-Kolom, dan Filter Status/Shift** wajib disatukan di dalam **satu halaman rute `/reports`** (`App\Livewire\Reports\ReportIndex`).
- Dilarang membuat halaman filter terpisah seperti `/reports/filter` atau `/reports/search`.
- Seluruh aksi baris tabel (Lihat Detail, Cetak, Trigger Hapus) terintegrasi langsung pada tabel index.

### Make a Modal, Not a New Route:
- Konfirmasi Verifikasi Laporan $\rightarrow$ Gunakan `<flux:modal name="verify-report-confirm">` di dalam halaman detail, **BUKAN** rute baru `/reports/{code}/verify`.
- Konfirmasi Hapus Laporan $\rightarrow$ Gunakan `<flux:modal name="delete-report-confirm">`, **BUKAN** rute `/reports/{code}/delete`.
- Pemilihan Rentang Tanggal Ekspor Excel $\rightarrow$ Gunakan `<flux:modal name="export-excel-modal">`, **BUKAN** halaman konfigurasi ekspor terpisah.

### Dedicated Route Boundaries (Where Allowed):
- `/reports` $\rightarrow$ Halaman tabel index & filter arsip.
- `/reports/{report:report_code}` $\rightarrow$ Halaman rincian detail laporan dan jejak audit.
- `/reports/{report:report_code}/edit` $\rightarrow$ Halaman koreksi form dan item laporan.
- `/reports/{report:report_code}/print` $\rightarrow$ Halaman tata letak cetak dokumen fisik (Blade murni tanpa komponen navigasi Livewire/Flux header-sidebar).

### Backend-Only — No Dedicated UI:
- **PDF Generation**: Endpoint `/reports/{report_code}/pdf` langsung menghasilkan response stream biner PDF (`application/pdf`) tanpa antarmuka web perantara.
- **Excel Export Streaming**: Endpoint `/reports/export` langsung menghasilkan response download berkas `.xlsx` (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`).
- **Otorisasi Policy**: Kebijakan `DailyReportPolicy` dievaluasi di latar belakang melalui `$this->authorize(...)` tanpa membuat halaman manajemen perizinan khusus.

### Strictly Forbidden:
- **Dilarang** membuat lebih dari 1 item menu sidebar untuk modul ini. Seluruh penelusuran arsip laporan berpusat pada menu **"Lihat Laporan"** (rute `reports.index`).
- **Dilarang** menyertakan elemen navigasi web (sidebar, header, breadcrumbs, tombol kembali) pada tampilan cetak printer fisik (`resources/views/reports/print.blade.php`).
- **Dilarang** menampilkan pesan kesalahan mentah sistem (*raw exception*, *SQL error*, *403 Forbidden page*) kepada pengguna; semua kesalahan otorisasi atau validasi harus dikonversi menjadi notifikasi toast atau pesan deskriptif dalam Bahasa Indonesia.
- **Dilarang** mengizinkan pengeditan pada laporan yang statusnya telah `Terverifikasi` kecuali jika supervisor membuka kembali kunci verifikasi tersebut.
- **Dilarang** menggunakan library JavaScript sisi klien yang berat untuk ekspor Excel atau PDF; pembuatan berkas wajib dilakukan di sisi server (*server-side generation*) agar kompatibel dan andal.
