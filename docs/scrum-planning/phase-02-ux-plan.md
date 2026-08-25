# UX Plan — Phase 02: SAP Master Data Import & Management (Update Database)

> Created before implementation. This document is a hard constraint for all work in Phase 02.
> Epic file: `docs/scrum-planning/phase-02-sap-master-data-import.md`

---

## 1. Navigation Footprint

Perancangan antarmuka pada Phase 02 berfokus pada penyederhanaan alur pembaruan basis data (*Update Database*) dari berkas ekspor SAP mentah menjadi data master yang siap digunakan oleh lini pengemasan dan pemindaian barcode. Pendekatan navigasi dirancang sangat ringkas agar supervisor dan operator dapat memproses dokumen impor harian maupun mencari spesifikasi order cetak tanpa berpindah-pindah halaman.

### Navigation Hierarchy & Routes Summary

- **Total Sidebar Menu Items untuk Phase 02**: **1 item** pada sidebar navigasi utama, yaitu menu **"Update Database"** (dengan ikon database/upload, hak akses khusus Supervisor). Menu ini merupakan bagian dari 6 menu global aplikasi (Dashboard, Update Database, Buat Laporan, Lihat Laporan, Data Petugas, Pengaturan).
- **Total Dedicated Routes/Pages**: **1 rute utama** (`/update-database`).
- **Single Page Consolidation (Tabbed Interface)**:
  - Seluruh fungsionalitas impor berkas SAP, pengunduhan template, riwayat log impor, penjelajah master data (*Master Data Explorer*), pencarian instan nomor PO, dan penambahan order manual disatukan sepenuhnya dalam **1 halaman** (`/update-database`).
  - Halaman ini dibagi menjadi **2 tab kerja interaktif**:
    1. **Tab 1: Impor Berkas SAP** — Berisi area unggah berkas (*drag & drop* atau pemilih berkas), tombol unduh template Excel standar, kartu ringkasan status impor terakhir, serta tabel riwayat log audit impor file SAP (`sap_import_logs`).
    2. **Tab 2: Master Data Order Produksi** — Berisi tabel penjelajah seluruh order produksi aktif, kotak pencarian instan berbasis nomor PO atau nama produk, filter status/mesin, tombol penambahan order manual untuk kondisi darurat, serta tombol aksi detail spesifikasi dan ubah order.
- **Drawer / Sheet vs. Modal**:
  - **Modal Pratinjau Impor (`<flux:modal name="import-preview">`)**: Menggunakan modal dialog berukuran lebar (*Large Modal*) di tengah layar agar supervisor dapat memvalidasi sampel 10–20 baris pertama data SAP, memeriksa rekapitulasi baris valid/error, dan mengonfirmasi penyimpanan ke database.
  - **Modal Form Tambah / Edit Order Manual (`<flux:modal name="order-form">`)**: Menggunakan modal dialog formulir terpusat untuk pengisian cepat order darurat yang belum tercatat di SAP.
  - **Modal / Drawer Detail Spesifikasi Order (`<flux:modal name="order-detail">`)**: Menampilkan lembar spesifikasi cetak lengkap, nomor mesin, dan target kuantitas siap kemas.
  - **Modal Rincian Error Impor (`<flux:modal name="log-error-detail">`)**: Modal ringkas untuk meninjau daftar baris yang gagal diproses pada riwayat impor sebelumnya.
- **Backend-Only (No Dedicated UI Surface)**:
  - **US-2.1 (Production Order Master Data Schema & Model)**: Perancangan skema tabel `production_orders`, indeks komposit cepat pada kolom `po_number` untuk memastikan waktu respon pemindaian barcode berada di bawah 20 ms, casting tipe data, serta definisi Enum `ProductionOrderStatus`.
  - Logika streaming parser `SapImportService` menggunakan `openspout` dengan alokasi memori rendah dan pembagian chunk di latar belakang saat membaca ribuan baris berkas spreadsheet.

---

## 2. Screen Inventory

Inventaris seluruh layar, tab kerja, dan komponen modal interaktif pada Phase 02:

| Screen / Panel | Location | What's visible on first open | Further triggers | Click depth |
|---|---|---|---|---|
| **Halaman Utama Update Database** | `/update-database` (Khusus Supervisor) | Header halaman ("Update Database SAP"), kartu informasi sinkronisasi terakhir, tab navigasi aktif (*Impor Berkas SAP* vs *Master Data Order Produksi*). | Mengganti tab navigasi beralih antara area unggah berkas dan tabel penjelajah master data secara instan tanpa reload halaman. | 1 klik dari Sidebar (menu "Update Database") |
| **Tab 1: Area Unggah & Riwayat Impor** | `/update-database` (Tab default) | **Area Unggah**: Kotak unggah file SAP (*drag & drop area*), tombol "Unduh Template Excel", tombol "Pilih Berkas" (`.xlsx`, `.xls`, `.csv`), batas ukuran berkas (maks. 10 MB).<br>**Tabel Riwayat**: Log riwayat impor (Tanggal & Jam, Nama Berkas, Total Baris, Berhasil, Diperbarui, Gagal, Status badge, Supervisor pengunggah, tombol Aksi). | Menjatuhkan file atau memilih file dari komputer langsung membuka modal pratinjau data (`import-preview`); klik baris riwayat yang gagal membuka modal detail error. | 0 klik (Langsung terlihat saat membuka menu) |
| **Tab 2: Master Data Explorer** | `/update-database` (Tab kedua) | Toolbar pencarian instan (input No PO / Nama Produk dengan debounce 300ms), filter dropdown (Status, Nomor Mesin, Tanggal), tombol "+ Tambah Order Manual", tabel data master (No PO, Kode & Nama Produk, Pelanggan, Nomor Mesin, Target Kemas, Satuan, Status badge, Tanggal Order, Menu Aksi), dan kontrol paginasi. | Mengetik kata kunci pencarian menyaring data tabel seketika; klik tombol "+ Tambah Order Manual" memicu modal form; klik baris tabel memicu modal detail spesifikasi. | 1 klik (Beralih ke tab Master Data) |
| **Modal Pratinjau Impor SAP** | Modal `<flux:modal name="import-preview">` di dalam `/update-database` | Header modal berisi nama berkas yang diunggah, kartu ringkasan status (*Total Baris*, *Baris Valid* [badge hijau], *Baris Bermasalah* [badge merah]), tabel pratinjau 10–20 baris pertama dengan penanda warna pada kolom yang tidak valid, tombol "Batal Impor", tombol "Lewati Baris Error & Simpan Baris Valid", dan tombol "Simpan Semua ke Database". | Menekan tombol simpan memicu indikator loading (*spinner*) dan progress bar Livewire, lalu menutup modal setelah selesai. | 1 interaksi (Otomatis muncul setelah memilih berkas) |
| **Modal Form Order Manual (Tambah / Edit)** | Modal `<flux:modal name="order-form">` di dalam `/update-database` | Header modal ("Tambah Order Produksi Manual" atau "Ubah Data Order Produksi"), kolom input: No PO (read-only saat mode edit), Kode Barang, Nama Produk, Nama Pelanggan, Nomor Mesin Cetak, Spesifikasi Kertas & Sekuriti, Target Kuantitas Siap Kemas, Satuan (lembar/rim/buku), Tanggal Order, Batas Waktu Kirim, tombol "Batal", dan tombol "Simpan Order". | Menampilkan pesan validasi jika No PO duplikat atau target kuantitas bernilai nol/negatif; tombol simpan menampilkan animasi loading saat penulisan database. | 1 klik dari tombol "+ Tambah Order Manual" atau 2 klik dari menu aksi baris tabel ("Ubah Data") |
| **Modal / Drawer Detail Spesifikasi Order** | Modal `<flux:modal name="order-detail">` di dalam `/update-database` | Header modal menampilkan No PO dan Nama Produk dengan badge status, kartu rincian teknis (Nama Pelanggan, Mesin Produksi, Jenis Kertas, Target Cetak, Tanggal Masuk Order, Batas Pengiriman, Identitas Akun Pembuat/Pengunggah), dan informasi progres pemenuhan packaging. | Tombol "Tutup" untuk kembali ke tabel data. | 1 klik dari ikon/tombol "Detail" pada baris tabel |
| **Modal Rincian Error Impor** | Modal `<flux:modal name="log-error-detail">` di dalam `/update-database` | Header modal ("Rincian Kesalahan Impor Berkas"), daftar baris yang gagal diurai beserta nomor baris dan keterangan kesalahan spesifik dalam Bahasa Indonesia yang mudah dipahami, tombol "Tutup Modal". | Tombol tutup mengembalikan fokus ke tabel riwayat impor. | 1 klik dari tombol "Lihat Error" pada baris log yang berstatus partial/failed |
| **Global Toast Feedback** | Komponen `<flux:toast />` (Pojok kanan atas) | Muncul otomatis setelah aksi berhasil (contoh: *"Berhasil mengimpor 250 data order dari SAP"* atau *"Order manual PO-2026-9012 berhasil ditambahkan"*) atau ketika terjadi kegagalan sistem. | Dapat ditutup manual dengan klik tombol silang (x) atau hilang otomatis setelah 4 detik. | 0 (Otomatis dipicu oleh event Livewire) |

---

## 3. User Journey Maps

Pemetaan langkah kerja pengguna untuk seluruh skenario utama pada modul Update Database & Master Data Produksi:

### Journey 1: Supervisor Mengunggah & Memperbarui Master Data dari File Ekspor SAP
- **Goal**: Memperbarui basis data order produksi harian menggunakan file spreadsheet hasil ekspor sistem SAP (.xlsx / .csv).
- **Starts at**: Halaman Update Database (`/update-database`) — Tab "Impor Berkas SAP".
- **Steps**:
  1. Supervisor memilih berkas spreadsheet dari komputer atau menyeret (*drag-and-drop*) berkas ke dalam area unggah.
  2. Sistem membaca berkas secara cepat di latar belakang dan langsung membuka Modal Pratinjau Impor (`import-preview`) yang menampilkan rekapitulasi total baris, jumlah baris valid, dan tabel sampel data.
  3. Supervisor memeriksa ringkasan data untuk memastikan kolom dan baris sudah terbaca dengan benar.
  4. Supervisor menekan tombol "Simpan ke Database".
- **Done**: Seluruh baris data tersimpan ke database melalui mekanisme upsert, modal tertutup secara otomatis, muncul notifikasi toast sukses "Berhasil memperbarui X order produksi dari SAP", dan baris riwayat baru langsung tercatat pada tabel riwayat impor.
- **Step count**: 4 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 2: Supervisor Menangani Berkas SAP dengan Sebagian Baris Rusak (Partial Import)
- **Goal**: Melanjutkan impor untuk baris data yang valid tanpa terhambat oleh beberapa baris data yang formatnya tidak lengkap.
- **Starts at**: Modal Pratinjau Impor (`import-preview`).
- **Steps**:
  1. Supervisor melihat peringatan bahwa terdapat baris tidak lengkap (misal: "3 baris memiliki Target Qty kosong") yang ditandai dengan badge merah pada pratinjau.
  2. Supervisor menekan tombol "Lewati Baris Error & Simpan Baris Valid".
- **Done**: Sistem memproses seluruh baris valid, mencatat rincian baris yang gagal ke dalam log audit dengan status `partial`, menutup modal, dan menampilkan notifikasi toast peringatan informatif "X order berhasil disimpan, Y baris dilewati karena format tidak lengkap".
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 3: Supervisor / Petugas Mencari dan Memeriksa Spesifikasi Order Cetak
- **Goal**: Memastikan rincian spesifikasi kertas, nomor mesin, dan target kuantitas suatu order sebelum memulai proses pengemasan atau pemindaian barcode.
- **Starts at**: Halaman Update Database (`/update-database`) — Tab "Master Data Order Produksi".
- **Steps**:
  1. Pengguna mengetik nomor PO (contoh: "PO-2026-8891") atau nama produk pada kolom pencarian instan (tabel langsung menyaring data secara reaktif).
  2. Pengguna mengklik tombol "Detail" pada baris order yang sesuai.
- **Done**: Modal detail spesifikasi terbuka menampilkan seluruh informasi spesifikasi kertas, target kemas, dan data pelanggan secara lengkap dan jelas.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 4: Supervisor Mendaftarkan Order Produksi Manual (Order Darurat / Ad-Hoc)
- **Goal**: Memasukkan data order cetak mendesak ke dalam sistem ketika data belum sempat diekspor dari SAP pusat agar proses pengemasan di lini produksi tetap dapat berjalan.
- **Starts at**: Halaman Update Database (`/update-database`) — Tab "Master Data Order Produksi".
- **Steps**:
  1. Supervisor mengklik tombol "+ Tambah Order Manual" pada toolbar halaman.
  2. Supervisor mengisi informasi esensial pada formulir (No PO, Nama Produk, Pelanggan, Nomor Mesin, Spesifikasi Kertas, Target Kemas).
  3. Supervisor menekan tombol "Simpan Order".
- **Done**: Data tervalidasi dan tersimpan ke tabel `production_orders` dengan status "Siap Kemas", modal tertutup otomatis, order baru muncul di urutan teratas tabel, dan notifikasi toast sukses ditampilkan.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 5: Supervisor Memeriksa Rincian Kesalahan dan Mengunduh Berkas Impor Sebelumnya
- **Goal**: Mengaudit riwayat unggahan berkas SAP pada shift sebelumnya dan memeriksa alasan kegagalan baris data tertentu.
- **Starts at**: Halaman Update Database (`/update-database`) — Tab "Impor Berkas SAP".
- **Steps**:
  1. Supervisor melihat tabel "Riwayat Pembaruan Database" dan menemukan baris log dengan status `Sebagian Gagal` (*partial*).
  2. Supervisor mengklik tombol "Lihat Error" pada baris tersebut (atau mengklik "Unduh Berkas Asli" jika ingin mengunduh salinan berkas).
- **Done**: Modal rincian error terbuka menampilkan daftar nomor baris serta penjelasan detail penyebab kesalahan (misal: "Baris 14: Target Kuantitas harus berupa angka bulat").
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

## 4. Indonesian UX Guardrails

Panduan antarmuka dan interaksi yang disesuaikan secara ketat dengan karakteristik operasional staf pabrik dan supervisor lini produksi:

### Patience Thresholds (Batas Toleransi Langkah Kerja)

1. **Aktivitas Rutin Harian (Unggah File SAP Harian & Cek Pratinjau)**
   <br>
   Maksimal **2–4 langkah**. Area unggah berkas harus langsung terlihat saat menu dibuka, mendukung mekanisme *drag-and-drop*, dan otomatis menampilkan modal verifikasi pratinjau data tanpa menuntut pengguna melakukan banyak konfigurasi awal.
2. **Aktivitas Pencarian & Pemeriksaan Spesifikasi (Lookups)**
   <br>
   Maksimal **1–2 langkah**. Input pencarian dilengkapi *autofocus* dan *live debounce* (300 ms) sehingga supervisor atau operator dapat langsung mengetik nomor PO dan melihat hasil spesifikasi secara instan.
3. **Aktivitas Kondisi Darurat (Input Order Manual / Penyesuaian Target)**
   <br>
   Maksimal **3 langkah** melalui satu modal formulir ringkas tanpa reload halaman.

### Cognitive Load Budget (Beban Kognitif Ringan)

1. **Maksimal 3–4 Elemen Aksi Utama per Tampilan**
   <br>
   Pada tab impor: hanya ada tombol *Pilih Berkas*, tombol *Unduh Template*, dan tabel riwayat. Pada tab master data: hanya ada kotak pencarian, dropdown filter status, dan tombol *Tambah Order Manual*.
2. **Formulir Terstruktur & Mudah Dipahami**
   <br>
   Formulir order manual dikelompokkan menjadi 3 bagian visual yang jelas:
   - *Identitas Order*: No PO, Kode Produk, Nama Produk, Nama Pelanggan.
   - *Lini Produksi*: Nomor Mesin Cetak, Tanggal Order, Batas Waktu Kirim.
   - *Spesifikasi & Target*: Spesifikasi Kertas/Sekuriti, Target Kuantitas Siap Kemas, Satuan.
3. **Progressive Disclosure & Pencegahan Kebingungan Format**
   <br>
   Pengguna tidak perlu menghafal susunan kolom Excel SAP karena sistem menyediakan tombol unduh contoh template (*Sample Template*) yang sesuai dengan skema parser.
4. **Maksimal 1 Tingkat Modal (*No Nested Modals*)**
   <br>
   Dilarang keras memunculkan modal di atas modal lain. Jika sedang membuka modal pratinjau impor, seluruh interaksi validasi atau pembatalan diselesaikan di dalam modal tersebut.

### Trust Signals & Umpan Balik Operasional

1. **Konfirmasi yang Lugas & Informatif**
   <br>
   - Saat konfirmasi impor: *"Sistem mendeteksi 350 baris data order dari berkas [nama-file.xlsx]. Sebanyak 348 baris valid dan 2 baris tidak lengkap. Apakah Anda ingin melanjutkan proses penyimpanan data valid?"*
   - Saat konfirmasi duplikasi PO: Sistem secara cerdas menggunakan logika *upsert* (memperbarui data yang sudah ada tanpa menduplikasi baris) dan memberi tahu pengguna: *"120 order baru ditambahkan, 30 order yang sudah ada berhasil diperbarui datanya."*
2. **Umpan Balik Visual Instan (Flux Toast & Loading Indicator)**
   <br>
   - Setiap kali proses parsing atau penyimpanan data berlangsung, tombol submit menampilkan status memuat (*loading spinner*) dengan teks *"Sedang memproses berkas..."* (`wire:loading`).
   - Setelah selesai, notifikasi toast berwarna hijau muncul dengan pesan yang menyebutkan jumlah data secara transparan.
3. **Pesan Kesalahan Bahasa Indonesia yang Jelas dan Edukatif**
   <br>
   - ❌ *"SQLSTATE[23000]: Integrity constraint violation: Column 'po_number' cannot be null"* $\rightarrow$ ✅ *"Nomor PO pada baris 14 tidak boleh kosong. Harap lengkapi kolom Nomor PO pada berkas Excel Anda."*
   - ❌ *"The file failed to upload / Maximum execution time of 30 seconds exceeded"* $\rightarrow$ ✅ *"Berkas terlalu besar atau koneksi terputus saat mengunggah. Silakan unggah kembali berkas dengan ukuran di bawah 10 MB."*
   - ❌ *"Invalid MIME type application/octet-stream"* $\rightarrow$ ✅ *"Format berkas tidak sesuai. Harap unggah berkas spreadsheet dengan ekstensi .xlsx, .xls, atau .csv."*

### Risks Specific to Phase 02 & Mitigations

| Sub-epic | Potensi Risiko UX / Operasional | Solusi & Mitigasi Desain |
|---|---|---|
| **US-2.2 SAP File Upload & Parser** | Format nama kolom header pada berkas ekspor SAP dari kantor pusat berubah-ubah (misal: kadang bertuliskan `No PO`, `Order Number`, atau `No Order`). | Parser `SapImportService` dilengkapi dengan pemetaan alias multi-header yang toleran terhadap spasi, huruf besar-kecil, dan susunan kolom alternatif. |
| **US-2.3 Import Preview & Validation** | Supervisor membatalkan seluruh file impor karena panik melihat 1 atau 2 baris data bermasalah dari total ratusan baris. | Sediakan tombol aksi transparan: "Lewati Baris Error & Simpan Baris Valid" serta tampilkan rekapitulasi jumlah baris sukses vs baris bermasalah dengan jelas. |
| **US-2.4 Import History & Audit Log** | Berkas impor yang sangat besar menyebabkan browser mengalami lag (*freezing*) saat proses unggah. | Gunakan streaming reader (`openspout`) di server untuk memproses data secara bertahap (*chunked stream*), disertai tampilan animasi indikator proses unggah (*Livewire progress*). |
| **US-2.5 Master Data Explorer** | Operator kesulitan mencari order di antara ribuan baris master data yang menumpuk. | Implementasikan pencarian teks instan pada nomor PO dan nama produk dengan *debounce* 300 ms dan paginasi yang cepat (20 data per halaman). |

---

## 5. Implementation Boundaries (Anti-Splitting Rules)

Batasan arsitektur dan antarmuka yang wajib dipatuhi oleh tim pengembang/AI saat mengimplementasikan Phase 02:

### Do Not Split — Combine Into One Surface:
- **Pusat Update Database & Master Data**: Seluruh fungsionalitas unggah file SAP, pratinjau data, riwayat log audit, dan penjelajah data order produksi **wajib berada di dalam satu halaman rute `/update-database`** dengan tab kerja interaktif.
- Dilarang memecah modul ini ke dalam halaman-halaman terpisah seperti `/import-sap`, `/sap-logs`, atau `/production-orders`.

### Make a Tab, Not a New Route:
- Tab **"Impor Berkas SAP"** dan Tab **"Master Data Order Produksi"** dibuat sebagai tab reaktif di dalam komponen Livewire `/update-database`, **BUKAN** rute terpisah seperti `/update-database/import` atau `/update-database/orders`.

### Make a Modal / Drawer, Not a Full Page:
- Pratinjau data impor SAP $\rightarrow$ Gunakan `<flux:modal name="import-preview">`, **BUKAN** halaman pratinjau baru.
- Formulir Tambah / Edit Order Manual $\rightarrow$ Gunakan `<flux:modal name="order-form">`, **BUKAN** rute `/orders/create` atau `/orders/{id}/edit`.
- Rincian Spesifikasi Order Cetak $\rightarrow$ Gunakan `<flux:modal name="order-detail">` atau `<flux:drawer>`, **BUKAN** rute `/orders/{id}`.
- Rincian Kesalahan Baris Impor $\rightarrow$ Gunakan `<flux:modal name="log-error-detail">`, **BUKAN** halaman log terpisah.

### Backend-Only — No Dedicated UI:
- **Pengindeksan Database untuk Pencarian Barcode**: Indexing komposit pada kolom `po_number` berjalan murni di tingkat migrasi MySQL/database tanpa komponen antarmuka.
- **Streaming Parser & Database Transaction**: Penggunaan library `openspout` dan pembungkusan transaksi `DB::beginTransaction()` berjalan sepenuhnya di dalam `SapImportService`.
- **Manajemen Status Enum**: Enum `ProductionOrderStatus` (`ready`, `in_progress`, `completed`, `cancelled`) dipetakan secara otomatis ke badge warna UI tanpa memerlukan halaman konfigurasi status tersendiri.

### Strictly Forbidden:
- **Dilarang** membuat lebih dari 1 item menu sidebar untuk modul ini. Seluruh alur kerja Master Data berpusat pada menu **"Update Database"**.
- **Dilarang** menampilkan pesan kesalahan mentah sistem (*raw exception*, *stack trace*, *SQL query*) kepada pengguna; semua kesalahan harus dikonversi menjadi kalimat deskriptif dalam Bahasa Indonesia.
- **Dilarang** membuat alur multi-langkah (*multi-step wizard*) yang rumit untuk proses unggah file; alur wajib ringkas (Pilih Berkas $\rightarrow$ Pratinjau Ringkasan $\rightarrow$ Simpan ke Database).
- **Dilarang** menggunakan library JavaScript sisi klien yang berat untuk mengurai spreadsheet; penguraian file wajib dilakukan di sisi server menggunakan streaming PHP yang efisien.
