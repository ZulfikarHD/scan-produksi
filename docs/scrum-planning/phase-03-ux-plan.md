# UX Plan — Phase 03: Barcode Scanning & Daily Production Entry (Buat Laporan)

> Created before implementation. This document is a hard constraint for all work in Phase 03.  
> Epic file: `docs/scrum-planning/phase-03-barcode-scan-daily-production.md`

---

## 1. Navigation Footprint

Perancangan antarmuka pada Phase 03 berfokus pada kecepatan dan efisiensi pemindaian (*High-Speed Continuous Scanning*) untuk operator lini pengemasan. Operator harus dapat memindai puluhan hingga ratusan Kartu Mesin Produksi secara berurutan hanya menggunakan pemindai barcode fisik (*Code 128 Scanner*) dan tombol **Enter** pada keyboard tanpa perlu menyentuh mouse sama sekali.

### Navigation Hierarchy & Routes Summary

- **Total Sidebar Menu Items untuk Phase 03**: **1 item** pada sidebar navigasi utama, yaitu menu **"Buat Laporan"** (dengan ikon barcode/clipboard, dapat diakses oleh Petugas dan Supervisor).
- **Total Dedicated Routes/Pages**: **2 rute kerja**:
  1. `/reports/session` (atau `/reports/create`): **Inisiasi / Pemilih Sesi Laporan** — Formulir ringkas untuk memilih tanggal kerja, kategori seri produk sekuriti (**PCHT**, **MMEA**, **HPTL/REL**), dan shift kerja. Jika laporan untuk kombinasi tanggal + kategori tersebut sudah ada, sistem langsung membuka/melanjutkan sesi tersebut.
  2. `/reports/{report:report_code}/entry`: **Konsol Pemindaian Berkelanjutan & Tabel Rekapitulasi** — Halaman kerja utama pemindaian barcode dengan loop fokus otomatis dan pengeditan baris langsung (*inline editing*).
- **Single Page Consolidation (Konsol & Tabel Terpadu)**:
  - Pada halaman pemindaian (`/reports/{report_code}/entry`), seluruh proses pemindaian barcode, penarikan data master SAP, penginputan jumlah hasil kemas, pratinjau spesifikasi, tabel rincian seluruh PO terscan, pengeditan kuantitas kemas instan, hingga tombol finalisasi laporan berada di **1 halaman yang sama tanpa reload**.
- **No Modals for Core Data Entry & Editing (Bebas Modal & Bebas Popup)**:
  - ❌ **Dilarang**: Modal popup untuk setiap kali scan PO (akan merusak ritme kerja operator).
  - ❌ **Dilarang**: Modal popup / halaman terpisah untuk mengedit jumlah produksi yang salah input.
  - ✅ **Wajib**: Pengeditan kuantitas produksi dilakukan **langsung pada kolom tabel (*inline editable input cell*)** di baris yang bersangkutan, dan tersimpan otomatis saat menekan **Enter** atau saat kursor berpindah (*blur*).
- **Backend & State Architecture**:
  - Model `DailyReport` dan `DailyReportItem` dengan relasi `hasMany`.
  - Kode Laporan standar: format `[SERIES]-[YYYY-MM-DD]` (contoh: `PCHT-2026-08-26`, `MMEA-2026-08-26`, `HPTL-2026-08-26`).

---

## 2. Screen Inventory

Inventaris seluruh layar dan elemen antarmuka interaktif pada Phase 03:

| Screen / Panel | Location | What's visible on first open | Further triggers | Click depth |
|---|---|---|---|---|
| **Layar 1: Inisiasi Sesi Laporan** | `/reports/session` | Pilihan Tanggal Laporan (default hari ini WIB), Radio Button Kategori Seri Produk (`PCHT`, `MMEA`, `HPTL/REL`), Pilihan Shift (Shift 1, 2, 3), dan tombol "Buka Sesi Pemindaian PO". | Menekan tombol "Buka Sesi" memeriksa apakah laporan sudah ada; jika sudah ada langsung membuka sesi aktif, jika belum membuat draft baru dan membuka konsol pemindaian. | 1 klik dari Sidebar (menu "Buat Laporan") |
| **Layar 2: Konsol Pemindaian & Tabel Produksi** | `/reports/{report_code}/entry` | **Header**: Kode Laporan (`PCHT-YYYY-MM-DD`), Badge Seri Produk, Badge Shift, Status Laporan.<br>**Konsol Atas**: Field Scan No PO Barcode (autofocus aktif), Field Jumlah Kemas/Produksi, Tombol "Simpan Item (Enter)", dan Status Pill Spesifikasi SAP.<br>**Tabel Bawah**: Tabel rekapitulasi PO dengan kolom input edit kuantitas langsung, tombol hapus baris, dan baris total kalkulasi di bagian bawah. | Memindai barcode otomatis mengisi data SAP dan memindahkan autofocus ke kuantitas kemas; menekan Enter menyimpan baris dan mengembalikan autofocus ke scan barcode; mengedit angka di baris tabel langsung memperbarui database. | 0 klik tambahan (Otomatis diarahkan dari Layar 1) |
| **Pill Pratinjau Spesifikasi SAP** | Bagian bawah konsol scan pada `/reports/{report_code}/entry` | Muncul seketika (<50ms) setelah No PO terdeteksi: menampilkan No PO, Nama Produk, Spesifikasi Kertas/Sekuriti, Nomor Mesin, dan Target Cetak SAP. | Berubah warna/hilang saat item tersimpan ke tabel. | 0 klik (Otomatis muncul dari pemindaian) |
| **Cell Input Edit Kuantitas Langsung** | Kolom "Jumlah Kemas" pada setiap baris tabel di `/reports/{report_code}/entry` | Kotak input angka yang menampilkan jumlah kemas aktual. | Pengguna mengklik cell atau menekan Tab ke cell tersebut, mengetik angka baru, lalu menekan Enter atau klik di luar kotak untuk menyimpan. | 0 modal (Langsung mengetik di tabel) |
| **Audio Cues (Umpan Balik Suara)** | Browser Audio Player (Latar Belakang) | Bunyi nada *Chime* sukses (tinggi/merdu) saat PO berhasil ditemukan dan disimpan; nada *Buzzer* peringatan saat PO tidak ditemukan di master SAP. | Dipicu otomatis oleh event Livewire `$this->dispatch('play-sound', ...)` | 0 interaksi visual |

---

## 3. User Journey Maps

### Journey 1: Operator Membuka / Melanjutkan Sesi Laporan Harian
- **Goal**: Memulai sesi pemindaian hasil kemas harian untuk kategori pita cukai (PCHT / MMEA / HPTL).
- **Starts at**: Menu "Buat Laporan" (`/reports/session`).
- **Steps**:
  1. Operator memilih Kategori Seri Produk (contoh: klik radio button **PCHT**).
  2. Sistem memeriksa tanggal hari ini dan shift aktif secara otomatis.
  3. Operator menekan tombol "Buka Sesi Pemindaian PO" (atau tekan Enter).
- **Done**: Sistem membuka halaman konsol pemindaian dengan kode laporan `PCHT-2026-08-26`, kursor langsung berada di dalam input scan barcode, dan seluruh riwayat PO yang sudah discan sebelumnya langsung tampil di tabel bawah.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 2: Operator Memindai Kartu Mesin & Menginput Hasil Kemas (Continuous Scan Loop)
- **Goal**: Memasukkan data hasil produksi kartu mesin ke dalam laporan secara cepat tanpa menyentuh mouse.
- **Starts at**: Halaman Konsol Pemindaian (`/reports/{report_code}/entry`).
- **Steps (Loop 100% Keyboard & Scanner)**:
  1. Operator mengarahkan pemindai barcode ke Kartu Mesin $\rightarrow$ Scanner membaca No PO dan mengirim *carriage return* (`Enter`).
  2. Sistem seketika menarik data spesifikasi dari master SAP, memutar bunyi sukses, dan **memindahkan fokus kursor secara otomatis ke kolom "Jumlah Kemas"**.
  3. Operator mengetik angka hasil produksi (atau menyetujui target bawaan SAP) lalu menekan tombol **`Enter`**.
  4. Baris data tersimpan ke database, muncul di baris teratas tabel bawah, total kalkulasi bertambah seketika, dan **fokus kursor otomatis kembali ke kolom "Scan No PO"**.
- **Done**: Operator langsung memindai kartu mesin berikutnya. 0 klik mouse, 0 jeda konfirmasi.
- **Step count**: 2 aksi fisik per PO (Scan Barcode $\rightarrow$ Ketik Jumlah + Enter).
- **Status**: ✅ OK (Sangat cepat dan efisien).

---

### Journey 3: Operator Mengoreksi Kesalahan Jumlah Kemas (Inline Table Edit)
- **Goal**: Mengubah angka hasil kemas pada salah satu baris PO yang salah ketik tanpa mengganggu alur pemindaian.
- **Starts at**: Tabel data di halaman yang sama (`/reports/{report_code}/entry`).
- **Steps**:
  1. Operator mengklik langsung pada kotak angka "Jumlah Kemas" di baris PO yang ingin dikoreksi.
  2. Operator mengganti angka (contoh: dari `20000` menjadi `25000`) lalu menekan **`Enter`** atau menekan tombol **`Tab`**.
- **Done**: Nilai tersimpan langsung ke database, total rekapitulasi di baris footer terhitung ulang secara instan, dan muncul indikator hijau halus / toast konfirmasi tanpa ada popup yang menutupi layar.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (Bebas modal / dialog).

---

### Journey 4: Operator Menghapus Baris PO yang Salah Scan
- **Goal**: Membuang baris PO yang tidak sengaja terpindai dari laporan saat ini.
- **Starts at**: Tabel data di halaman yang sama (`/reports/{report_code}/entry`).
- **Steps**:
  1. Operator mengklik ikon tempat sampah pada baris PO yang ingin dibuang.
  2. Konfirmasi inline cepat (*browser confirm / instant undo action*).
- **Done**: Baris PO terhapus dari tabel dan database, total kuantitas laporan terupdate otomatis.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

## 4. Keyboard & Focus Management Rules (Hard Constraint)

1. **Aturan Autofocus Masuk Halaman**:
   - Saat halaman `/reports/{report_code}/entry` dimuat, kursor wajib langsung aktif di dalam input `#scan-po-input` menggunakan Alpine.js `x-init="$refs.scanInput.focus()"`.
2. **Aturan Alih Fokus Pemindaian (Scan $\rightarrow$ Quantity)**:
   - Setelah event pemindaian PO selesai (`lookupPo`), jika data ditemukan, Livewire/Alpine wajib memicu event `focus-qty-input` yang mengarahkan fokus ke `#production-qty-input` dan memblok (*highlight/select*) teks angka jika ada nilai default.
3. **Aturan Alih Fokus Simpan (Quantity $\rightarrow$ Scan)**:
   - Setelah tombol Enter ditekan pada `#production-qty-input` (`saveEntry`), sistem menyimpan item, mengosongkan nilai input, dan memicu event `focus-scan-input` untuk mengembalikan fokus ke `#scan-po-input`.
4. **Aturan Pemulihan Fokus Saat Error**:
   - Jika barcode tidak ditemukan di database SAP, sistem menampilkan toast peringatan merah, membunyikan nada error, mengosongkan input scan, dan tetap mempertahankan fokus di `#scan-po-input` agar operator bisa langsung memindai ulang.
5. **Navigasi Tombol Escape**:
   - Menekan tombol `Escape` saat berada di input kuantitas membatalkan input saat ini dan mengembalikan fokus ke input scan barcode.

---

## 5. Indonesian UI Guardrails & Shop-Floor Terminology

- **Identifikasi Seri Produk**:
  - `PCHT` $\rightarrow$ Pita Cukai Hasil Tembakau
  - `MMEA` $\rightarrow$ Minuman Mengandung Etil Alkohol
  - `HPTL / REL` $\rightarrow$ Hasil Pengolahan Tembakau Lainnya / Rokok Elektrik Cair
- **Penamaan Field & Tombol**:
  - *"Scan No PO Barcode"* (bukan sekadar "Barcode")
  - *"Jumlah Kemas / Produksi"* (istilah standar di Unit Pengemasan)
  - *"Target SAP"* (angka target cetak dari kartu mesin)
  - *"Simpan Item (Enter)"*
  - *"Selesaikan Laporan"*
- **Pencegahan Error**:
  - Input jumlah kemas wajib divalidasi berupa angka bulat positif (> 0).
  - Peringatan instan jika No PO yang sama discan dua kali dalam sesi laporan yang sama (opsi menambahkan kuantitas atau menolak duplikasi).

---

## 6. Implementation Boundaries & Verification Checklist

- [ ] Tidak ada modal dialog untuk alur pemindaian dan penambahan item PO harian.
- [ ] Tidak ada modal dialog untuk pengeditan kuantitas kemas pada tabel (wajib *inline input* di setiap baris).
- [ ] Format kode laporan wajib mengikuti pola `[SERIES]-[YYYY-MM-DD]` (contoh: `PCHT-2026-08-26`).
- [ ] Pengujian Pest wajib mencakup pengujian simulasi alur scan berurutan, transisi fokus, pengeditan inline, dan pembaruan agregasi total.
