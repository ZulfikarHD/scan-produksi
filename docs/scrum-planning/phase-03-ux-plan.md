# UX Plan — Phase 03: Barcode Scanning & Daily Production Entry (Buat Laporan)

> Created before implementation. This document is a hard constraint for all work in Phase 03.  
> Epic file: `docs/scrum-planning/phase-03-barcode-scan-daily-production.md`

---

## 1. Navigation Footprint

Perancangan antarmuka pada Phase 03 berfokus pada kecepatan dan efisiensi pemindaian (*High-Speed Continuous Scanning*) untuk operator di lini pengemasan dokumen sekuriti (Pita Cukai). Seluruh proses dirancang agar operator dapat memproses puluhan hingga ratusan Kartu Mesin Produksi secara berurutan menggunakan pemindai barcode fisik (*Code 128 Scanner*) dan tombol **Enter** pada keyboard tanpa perlu menyentuh mouse sama sekali.

### Navigation Hierarchy & Routes Summary

- **Total Sidebar Menu Items untuk Phase 03**: **1 item** pada sidebar navigasi utama, yaitu menu **"Buat Laporan"** (dengan ikon barcode/clipboard, dapat diakses oleh Petugas dan Supervisor).
- **Total Dedicated Routes/Pages**: **2 rute kerja**:
  1. `/reports/session` (alias `/reports/create`): **Inisiasi / Pemilih Sesi Laporan** — Formulir ringkas untuk memilih tanggal kerja, kategori seri produk sekuriti (**PCHT**, **MMEA**, **HPTL/REL**), dan shift kerja. Jika laporan untuk kombinasi tanggal + kategori tersebut sudah ada, sistem langsung membuka dan melanjutkan sesi tersebut.
  2. `/reports/{report:report_code}/entry`: **Konsol Pemindaian Berkelanjutan & Tabel Rekapitulasi** — Halaman kerja utama pemindaian barcode dengan loop fokus otomatis dan pengeditan baris langsung (*inline editing*).
- **Single Page Consolidation (Konsol & Tabel Terpadu)**:
  - Pada halaman pemindaian (`/reports/{report_code}/entry`), seluruh proses pemindaian barcode, penarikan data master SAP, penginputan jumlah hasil kemas, pratinjau spesifikasi, tabel rincian seluruh PO terscan, pengeditan kuantitas kemas instan, hingga tombol finalisasi laporan disatukan dalam **1 halaman yang sama tanpa reload**.
- **Drawer / Sheet vs. Modal (Bebas Modal untuk Input & Edit Data Inti)**:
  - ❌ **Tidak ada modal popup** untuk setiap kali scan PO (karena merusak ritme kerja cepat operator).
  - ❌ **Tidak ada modal popup / halaman terpisah** untuk mengedit jumlah produksi yang salah input.
  - ✅ **Wajib**: Pengeditan kuantitas produksi dilakukan **langsung pada kolom tabel (*inline editable input cell*)** di baris yang bersangkutan, dan tersimpan otomatis saat menekan **Enter** atau saat kursor berpindah (*blur*).
- **Backend-Only (No Dedicated UI Surface)**:
  - **US-3.1 (Daily Production Report Schema, Product Series & Code Generator)**: Perancangan skema tabel `daily_reports` dan `daily_report_items`, Enum `ProductSeries` (`pcht`, `mmea`, `hptl`), indeks unik komposit pada (`product_series`, `report_date`, `shift`), serta generator format kode laporan standar `[SERIES]-[YYYY-MM-DD]` (contoh: `PCHT-2026-08-26`).

---

## 2. Screen Inventory

Inventaris seluruh layar, panel, dan komponen antarmuka interaktif pada Phase 03:

| Screen / Panel | Location | What's visible on first open | Further triggers | Click depth |
|---|---|---|---|---|
| **Layar 1: Inisiasi Sesi Laporan** | `/reports/session` | Pilihan Tanggal Laporan (default hari ini WIB), Radio Cards Kategori Seri Produk (`PCHT`, `MMEA`, `HPTL/REL`), Pilihan Shift (Shift 1, 2, 3), dan tombol "Buka Sesi Pemindaian PO". | Menekan tombol "Buka Sesi" memeriksa apakah laporan sudah ada; jika sudah ada langsung membuka sesi aktif, jika belum membuat draft baru dan membuka konsol pemindaian. | 1 klik dari Sidebar (menu "Buat Laporan") |
| **Layar 2: Konsol Pemindaian & Tabel Produksi** | `/reports/{report_code}/entry` | **Header**: Kode Laporan (`PCHT-YYYY-MM-DD`), Badge Seri Produk, Badge Shift, Status Laporan.<br>**Konsol Atas**: Field Scan No PO Barcode (autofocus aktif), Field Jumlah Kemas/Produksi, Tombol "Simpan Item (Enter)", dan Status Pill Spesifikasi SAP.<br>**Tabel Bawah**: Tabel rekapitulasi PO dengan kolom input edit kuantitas langsung, tombol hapus baris, dan baris total kalkulasi di bagian bawah. | Memindai barcode otomatis mengisi data SAP dan memindahkan autofocus ke kuantitas kemas; menekan Enter menyimpan baris dan mengembalikan autofocus ke scan barcode; mengedit angka di baris tabel langsung memperbarui database. | 0 klik tambahan (Otomatis diarahkan dari Layar 1) |
| **Pill Pratinjau Spesifikasi SAP** | Bagian bawah konsol scan pada `/reports/{report_code}/entry` | Muncul seketika (<50ms) setelah No PO terdeteksi: menampilkan No PO, Nama Produk, Spesifikasi Kertas/Sekuriti, Nomor Mesin, dan Target Cetak SAP. | Berubah warna/hilang saat item tersimpan ke tabel. | 0 klik (Otomatis muncul dari pemindaian) |
| **Cell Input Edit Kuantitas Langsung** | Kolom "Jumlah Kemas" pada setiap baris tabel di `/reports/{report_code}/entry` | Kotak input angka yang menampilkan jumlah kemas aktual per PO. | Pengguna mengklik cell atau menekan Tab ke cell tersebut, mengetik angka baru, lalu menekan Enter atau klik di luar kotak untuk menyimpan seketika. | 0 modal (Langsung mengetik di dalam tabel) |
| **Global Toast & Audio Feedback** | Pojok kanan atas & Browser Audio Player | Tersembunyi saat awal; bunyi nada *Chime* sukses saat PO valid dan tersimpan; bunyi *Buzzer* peringatan saat PO tidak ditemukan; toast notifikasi informatif. | Dipicu otomatis oleh event Livewire/Alpine `$this->dispatch('play-sound', ...)` dan `Flux::toast(...)`. | 0 klik (Otomatis) |

---

## 3. User Journey Maps

Pemetaan langkah kerja pengguna untuk seluruh tugas utama pada Phase 03:

### Journey 1: Operator Membuka / Melanjutkan Sesi Laporan Harian
- **Goal**: Membuka sesi kerja pemindaian hasil kemas harian untuk kategori pita cukai tertentu (PCHT / MMEA / HPTL).
- **Starts at**: Menu "Buat Laporan" (`/reports/session`).
- **Steps**:
  1. Operator memilih Kategori Seri Produk (contoh: klik radio card **PCHT**).
  2. Sistem memeriksa tanggal hari ini dan shift aktif secara otomatis.
  3. Operator menekan tombol "Buka Sesi Pemindaian PO" (atau tekan `Enter`).
- **Done**: Sistem membuka halaman konsol pemindaian dengan kode laporan `PCHT-2026-08-26`, kursor langsung berada di dalam input scan barcode, dan seluruh riwayat PO yang sudah discan sebelumnya langsung tampil di tabel bawah.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 2: Operator Memindai Kartu Mesin & Menginput Hasil Kemas (Continuous Scan Loop)
- **Goal**: Memasukkan data hasil produksi kartu mesin ke dalam laporan secara cepat tanpa menyentuh mouse.
- **Starts at**: Halaman Konsol Pemindaian (`/reports/{report_code}/entry`).
- **Steps (Loop 100% Keyboard & Scanner)**:
  1. Operator mengarahkan pemindai barcode ke Kartu Mesin $\rightarrow$ Scanner membaca No PO dan mengirim *carriage return* (`Enter`).
  2. Sistem seketika menarik data spesifikasi dari master SAP, memutar nada sukses, dan **memindahkan fokus kursor secara otomatis ke kolom "Jumlah Kemas"**.
  3. Operator mengetik angka hasil produksi (atau menyetujui target bawaan SAP) lalu menekan tombol **`Enter`**.
  4. Baris data tersimpan ke database, muncul di baris teratas tabel bawah, total kalkulasi bertambah seketika, dan **fokus kursor otomatis kembali ke kolom "Scan No PO"**.
- **Done**: Operator langsung memindai kartu mesin berikutnya. 0 klik mouse, 0 jeda konfirmasi modal.
- **Step count**: 2 aksi fisik per PO (Scan Barcode $\rightarrow$ Ketik Jumlah + Enter).
- **Status**: ✅ OK (Sangat cepat dan efisien).

---

### Journey 3: Operator Mengoreksi Kesalahan Jumlah Kemas (Inline Table Edit)
- **Goal**: Mengubah angka hasil kemas pada salah satu baris PO yang salah ketik tanpa membuka modal atau meninggalkan halaman.
- **Starts at**: Tabel data di halaman yang sama (`/reports/{report_code}/entry`).
- **Steps**:
  1. Operator mengklik langsung pada kotak input angka "Jumlah Kemas" di baris PO yang ingin dikoreksi.
  2. Operator mengganti angka (contoh: dari `20000` menjadi `25000`) lalu menekan **`Enter`** atau tombol **`Tab`**.
- **Done**: Nilai tersimpan langsung ke database, total rekapitulasi di baris footer terhitung ulang secara instan, dan muncul indikator hijau halus / toast konfirmasi tanpa ada popup yang menutupi layar.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (Bebas modal / dialog).

---

### Journey 4: Operator Menghapus Baris PO yang Salah Scan
- **Goal**: Membuang baris PO yang tidak sengaja terpindai dari laporan saat ini.
- **Starts at**: Tabel data di halaman yang sama (`/reports/{report_code}/entry`).
- **Steps**:
  1. Operator mengklik ikon tempat sampah pada baris PO yang ingin dibuang.
  2. Operator mengonfirmasi dialog konfirmasi inline cepat (*browser confirm*).
- **Done**: Baris PO terhapus dari tabel dan database, total kuantitas laporan terupdate otomatis.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 5: Operator / Supervisor Menyelesaikan Sesi Laporan (Finalisasi)
- **Goal**: Menutup sesi draft laporan harian setelah seluruh pengemasan shift selesai agar siap dicetak fisik.
- **Starts at**: Header halaman konsol pemindaian (`/reports/{report_code}/entry`).
- **Steps**:
  1. Operator memeriksa total kuantitas kemas pada baris ringkasan tabel bawah.
  2. Operator mengklik tombol "Selesaikan Laporan" pada header halaman.
- **Done**: Status laporan berubah menjadi `Terkirim` (Submitted), data terkunci dari edit kasual operator, dan muncul tombol cepat "Cetak Dokumen Fisik".
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

## 4. Indonesian UX Guardrails

### Patience Thresholds
- **Tugas Pemindaian & Input PO (Harian / Rutin Tinggi)**: Harus diselesaikan dalam **1–2 langkah keyboard** per kartu mesin (Scan $\rightarrow$ Input Qty $\rightarrow$ Enter). Tidak boleh ada dialog perantara.
- **Tugas Koreksi Angka Kemas**: Maksimal **2 langkah** (Klik angka di tabel $\rightarrow$ Edit + Enter).
- **Tugas Inisiasi Sesi Laporan**: Maksimal **2 langkah** (Pilih Kategori Seri Produk $\rightarrow$ Buka Sesi).

### Cognitive Load Budget
- **Konsol Scan Atas**: Maksimal **2 kolom input aktif yang saling terhubung** (*Scan No PO* dan *Jumlah Kemas*), dilengkapi dengan pil rincian spesifikasi produk yang ringkas.
- **Tabel Data Bawah**: Menampilkan informasi esensial yang diperlukan operator lantai kerja (*No*, *No PO*, *Nama Produk & Spesifikasi*, *Mesin*, *Target SAP*, *Jumlah Kemas [Editable]*, *Waktu Scan*, *Aksi*).
- **Kedalaman Modal**: **0 modal untuk input pemindaian dan pengeditan**. Modal hanya digunakan jika ada dialog peringatan destruktif fatal.

### Trust Signals & Umpan Balik
- **Feedback Audio Dual-Tone**:
  - *Chime Melodi Tinggi*: Penanda sukses saat barcode terbaca dan data master SAP ditemukan.
  - *Buzzer Rendah / Nada Peringatan*: Penanda jika barcode tidak terdaftar di database SAP.
- **Feedback Visual Real-Time**:
  - Pil hijau lembut menampilkan spesifikasi seketika setelah scan berhasil.
  - Kotak input yang sedang diedit pada tabel memiliki border fokus biru/hijau dan indikator centang singkat saat data tersimpan.
- **Pesan Kesalahan Bahasa Indonesia yang Manusiawi**:
  - ❌ *"Error 404: Production order entity not found"*
  - ✅ *"No PO PO-2026-8891 tidak ditemukan pada Master Data SAP. Pastikan file SAP sudah diimpor pada menu Update Database."*

### Risks Specific to This Epic

| Sub-epic / Fitur | Risiko | Mitigasi UX |
|---|---|---|
| **High-Speed Barcode Scanning** | Operator kehilangan fokus kursor saat scanner mengirim karakter cepat atau saat jeda network Livewire. | Gunakan directive Alpine.js dengan event window `@focus-scan-input` dan `@focus-qty-input` untuk memastikan kursor selalu berada di input yang tepat. |
| **Pencatatan Duplikasi PO** | Operator tidak sengaja memindai kartu mesin yang sama dua kali dalam satu sesi. | Sistem mendeteksi jika No PO sudah ada di tabel; memberikan peringatan audio dan opsi untuk menambahkan kuantitas ke baris yang sudah ada. |
| **Kesalahan Ketik Angka Produksi** | Operator salah mengetik angka kuantitas kemas (misal kelebihan angka nol). | Sediakan inline editable field langsung di tabel agar operator dapat memperbaikinya dalam hitungan detik tanpa modal. |
| **Pemisahan Kategori Produk (PCHT vs MMEA vs HPTL)** | Data tercampur antara pita cukai rokok (PCHT) dan minuman alkohol (MMEA). | Terapkan isolasi sesi berbasis kode laporan `[SERIES]-[YYYY-MM-DD]` dengan visual badge warna berbeda untuk setiap seri produk. |

---

## 5. Implementation Boundaries (Anti-Splitting Rules)

### Do Not Split — Combine Into One Surface:
- **Konsol Scan Barcode, Form Input Kuantitas, Pratinjau Spesifikasi SAP, Tabel Rekapitulasi Baris PO, dan Edit Kuantitas Inline** wajib berada di **1 halaman yang sama** (`/reports/{report_code}/entry`). Jangan memisahkannya ke dalam sub-halaman atau wizard multi-step.

### Make a Tab / Filter, Not a New Route:
- Jangan membuat rute terpisah untuk setiap seri produk seperti `/reports/pcht`, `/reports/mmea`, atau `/reports/hptl`. Gunakan parameter dinamis `/reports/{report_code}/entry` dengan identifikasi enum `product_series`.

### Make an Inline Row Input, Not a Modal / Drawer:
- Pengeditan jumlah produksi pada baris PO **wajib berupa input inline di dalam cell tabel**. Jangan membuat modal dialog `<flux:modal name="edit-item">` atau drawer samping untuk mengubah kuantitas.

### Backend-Only — No Dedicated UI:
- **US-3.1 (Schema & Report Code Generator)**: Struktur tabel `daily_reports`, `daily_report_items`, validasi relasi, dan format string kode laporan `[SERIES]-[YYYY-MM-DD]` adalah backend logic murni.

### Strictly Forbidden:
- **Dilarang** membuat item sidebar baru untuk "Scan Barcode" terpisah dari "Buat Laporan" (keduanya adalah satu kesatuan alur kerja).
- **Dilarang** menampilkan modal konfirmasi popup setiap kali pemindaian barcode selesai.
- **Dilarang** mengharuskan operator menggunakan mouse untuk berpindah dari input scan ke input kuantitas kemas (wajib otomatis berpindah fokus via Alpine/Livewire).
