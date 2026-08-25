# BAB III: PENGEMBANGAN PERANGKAT LUNAK

Bab ini menjelaskan metode dan tahapan yang digunakan dalam proses pengembangan perangkat lunak Sistem Dokumentasi Hasil Produksi Harian Berbasis Web dengan Scan Barcode dan Grafik Performa 14 Hari Kerja. Uraian pada bab ini bertujuan untuk memberikan gambaran sistematis mengenai pendekatan, alur kerja, serta aktivitas pengembangan yang dilakukan hingga menghasilkan perangkat lunak sesuai dengan kebutuhan pengguna.

---

## 3.1 Metode Pengembangan Perangkat Lunak

Metode pengembangan perangkat lunak yang digunakan dalam penelitian ini adalah **Metode Waterfall**. Metode Waterfall merupakan model pengembangan perangkat lunak yang dilakukan secara bertahap dan berurutan (*sequential linear*), di mana setiap tahap harus diselesaikan secara tuntas sebelum melanjutkan ke tahap berikutnya.

### Alasan Pemilihan Metode
1. **Karakteristik Sistem**: Kebutuhan fungsional sistem sudah terdefinisi secara jelas sejak awal berdasarkan proses observasi pada Unit Pengemasan di PT Percetakan Dokumen Sekuriti (digitalisasi pencatatan dokumen kemas manual, integrasi data master SAP, scan barcode Code 128 pada Kartu Mesin Produksi, serta analitik performa 14 hari kerja terakhir).
2. **Kebutuhan Pengguna**: Alur kerja pencatatan dan pelaporan hasil produksi memiliki prosedur standar operasional (SOP) yang pasti dan tidak sering berubah.
3. **Keterbatasan Waktu & Sumber Daya**: Metode Waterfall memberikan struktur dokumentasi dan batasan tahapan yang teratur, sehingga mempermudah penjadwalan dan evaluasi penelitian.
4. **Kompleksitas Sistem**: Sistem berskala terfokus pada unit kerja tertentu dengan integrasi barcode scanner dan visualisasi data performa harian.

### Deskripsi Tahapan Waterfall
Tahapan pengembangan menggunakan metode Waterfall meliputi:
1. **Analisis Kebutuhan (*Requirement Analysis*)**: Mengidentifikasi permasalahan di lantai produksi dan merumuskan kebutuhan fungsional serta non-fungsional.
2. **Perancangan Sistem (*System Design*)**: Merancang arsitektur sistem, pemodelan UML (Use Case & Activity Diagram), basis data (ERD), dan antarmuka pengguna (UI/UX).
3. **Implementasi (*Implementation*)**: Menerjemahkan rancangan sistem ke dalam kode program (backend Laravel, frontend Inertia Vue, dan library grafik).
4. **Pengujian Sistem (*Testing*)**: Melakukan pengujian fungsionalitas menggunakan metode *Black Box Testing*.
5. **Pemeliharaan (*Maintenance*)**: Menjaga keandalan sistem, penanganan pencadangan basis data, dan penyesuaian jika terjadi pembaruan format data master SAP.

---

## 3.2 Tahap Pengembangan Perangkat Lunak

### A. Analisis Kebutuhan

Tahap analisis kebutuhan dilakukan untuk memahami alur kerja dokumentasi hasil produksi di Unit Pengemasan serta memetakan fitur-fitur yang dibutuhkan pengguna.

#### 1. Analisis Permasalahan dan Kebutuhan Pengguna
- **Masalah Sistem Manual**: Pencatatan data kemas harian dengan tulis tangan memakan banyak waktu, rawan terjadi kesalahan input (*human error*), risiko kartu mesin terselip atau hilang, dan menghambat pelaporan ke Kepala Unit/Supervisor.
- **Kebutuhan Komputerisasi & Barcode**: Pemanfaatan barcode (tipe Code 128) yang sudah tercetak pada Kartu Mesin Produksi dan data SAP yang sudah ada untuk pengisian data spesifikasi produk secara otomatis melalui scanner barcode.
- **Kebutuhan Monitoring Kinerja**: Visualisasi performa hasil produksi berupa grafik selama 14 hari kerja terakhir pada dashboard sistem untuk memudahkan monitoring dan evaluasi tren produktivitas.

#### 2. Kebutuhan Fungsional (*Functional Requirements*)
- **Modul Autentikasi**: Petugas harus melakukan login menggunakan *username* dan *password* untuk mengakses sistem.
- **Modul Master Data SAP (Update Database)**: Fitur untuk mengunggah (*import*) file data master produk/order yang diekspor dari SAP.
- **Modul Kelola Petugas**: Melihat dan mengelola data pengguna/petugas yang berhak mengoperasikan aplikasi.
- **Modul Buat Laporan (Scan Barcode)**:
  - Input Nomor PO melalui scan barcode (Code 128) dari Kartu Mesin Produksi.
  - Sistem otomatis menampilkan spesifikasi produk (Nama Produk, No Order, Ukuran, dll.) berdasarkan data master.
  - Petugas mengisi dan memverifikasi jumlah hasil kemas siap kirim.
  - Simpan laporan ke basis data dan opsi cetak (*print*) dokumen laporan.
- **Modul Lihat Laporan**: Menampilkan daftar riwayat laporan harian tersimpan dengan ID Laporan unik, fitur lihat/edit detail laporan, dan fitur hapus laporan.
- **Modul Grafik Performa 14 Hari Kerja**: Menghitung dan menampilkan visualisasi grafik tren output hasil produksi selama 14 hari kerja aktif terakhir pada dashboard.

#### 3. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)
- **Hardware/Perangkat Keras**: Mendukung perangkat scanner barcode genggam tipe Code 128 (koneksi USB/Plug-and-Play), PC/Laptop, dan printer.
- **Performa & Responsivitas**: Waktu respon pencarian data produk saat barcode discan kurang dari 1 detik.
- **Keandalan & Validasi Data**: Mencegah duplikasi data barcode pada satu sesi laporan harian.
- **Usability**: Antarmuka web yang bersih, intuitif, dan mudah dioperasikan oleh operator di lantai produksi.

---

### B. Perancangan Sistem

Tahap perancangan sistem menerjemahkan kebutuhan ke dalam model visual dan struktur teknis sebelum pengkodean dimulai.

#### 1. Pemodelan UML (*Unified Modeling Language*)
- **Use Case Diagram**:
  - **Aktor**: Petugas / Operator Kemas (dan Supervisor/Admin).
  - **Use Case**: Login, Update/Import Database SAP, Kelola Petugas, Buat Laporan (Scan Barcode & Input Jumlah Kemas), Simpan Laporan, Lihat/Edit Laporan, Cetak Laporan, serta Pantau Grafik Performa 14 Hari Kerja.
- **Activity Diagram**:
  - *Activity Diagram Login*
  - *Activity Diagram Update Database (Import SAP)*
  - *Activity Diagram Data Petugas*
  - *Activity Diagram Buat Laporan (Scan Barcode & Simpan)*
  - *Activity Diagram Lihat & Edit Laporan*
  - *Activity Diagram Dashboard & Grafik Performa 14 Hari Kerja*

#### 2. Perancangan Basis Data
- **Entity Relationship Diagram (ERD) / Class Diagram**:
  - **Tabel Users / Petugas**: Menyimpan kredensial dan identitas operator.
  - **Tabel Master Data SAP**: Menyimpan data order, nomor PO/barcode, nama produk, spesifikasi cetak, dan target.
  - **Tabel Laporan Harian (Header)**: Menyimpan ID Laporan, tanggal kerja, shift, ID petugas pembuat, dan ringkasan total.
  - **Tabel Detail Laporan (Items)**: Menyimpan rincian barcode No PO, spesifikasi, dan jumlah hasil kemas per item.
  - **Agregasi Performa Harian**: Struktur query data untuk menghitung total produksi per hari selama rentang 14 hari kerja.

#### 3. Perancangan Antarmuka Pengguna (*UI/UX Mockups*)
- **Halaman Login**: Form input username dan password.
- **Halaman Dashboard / Beranda**: Ringkasan metrik dan tampilan grafik garis/batang performa 14 hari kerja terakhir.
- **Halaman Update Database**: Form upload file data master SAP.
- **Halaman Data Petugas**: Tabel daftar akun petugas sistem.
- **Halaman Buat Laporan**: Kolom input scan barcode otomatis, tabel rincian hasil scan dan jumlah kemas, serta tombol Simpan & Cetak.
- **Halaman Lihat Laporan**: Tabel daftar riwayat laporan harian dengan aksi Lihat/Edit dan Hapus.
- **Template Cetak Dokumen**: Format siap cetak laporan hasil kemas harian.

---

### C. Implementasi

Tahap implementasi merupakan tahap penerjemahan rancangan ke dalam baris kode program dan konfigurasi lingkungan sistem.

#### 1. Lingkungan Pengembangan Perangkat Lunak
- **Sistem Operasi**: Linux / Windows
- **Bahasa Pemrograman & Framework Backend**: PHP (Laravel Framework)
- **Framework Frontend & UI**: Livewire 4, Flux UI, Blade Components, Blaze Optimizer, dan Tailwind CSS v4
- **Library Grafik**: Chart.js (terintegrasi melalui Alpine.js untuk visualisasi performa 14 hari kerja)
- **Database Management System**: MySQL / SQLite
- **Web Server & Tools**: Nginx / Apache, Node.js & pnpm, Git

#### 2. Implementasi Modul Program
- Pembuatan migrasi database, model Eloquent, dan *Service Layer*.
- Pembuatan komponen *Livewire* untuk otentikasi (Fortify), import master data, manajemen laporan, dan kalkulasi data grafik.
- Implementasi *event listener* pada komponen Alpine.js/Livewire untuk menangkap *keystroke* dari scanner barcode (Code 128) secara instan dan akurat.
- Implementasi komponen visualisasi grafik performa 14 hari kerja pada dashboard.

---

### D. Pengujian Sistem

Tahap pengujian dilakukan untuk memastikan setiap fungsionalitas sistem bekerja sesuai dengan spesifikasi yang dirancang dan bebas dari kesalahan fatal.

- **Metode Pengujian**: **Black Box Testing** (berfokus pada fungsionalitas input dan output tanpa memeriksa struktur internal kode program).
- **Rencana Skenario Pengujian**:
  1. *Pengujian Login*: Validasi masukan username dan password yang benar maupun salah.
  2. *Pengujian Import Master Data*: Validasi file SAP yang diunggah dan penyimpanan ke database master.
  3. *Pengujian Scan Barcode*: Memverifikasi pembacaan barcode dari scanner fisik dan pemunculan spesifikasi produk secara otomatis.
  4. *Pengujian Simpan & Edit Laporan*: Pengujian validasi input jumlah kemas dan integritas penyimpanan data.
  5. *Pengujian Cetak Laporan*: Memastikan format cetak laporan tersusun rapi sesuai template.
  6. *Pengujian Grafik Performa 14 Hari Kerja*: Memverifikasi keakuratan kalkulasi data hasil kemas pada 14 hari kerja terakhir yang dirender pada grafik.

---

### E. Pemeliharaan

Tahap pemeliharaan dilakukan setelah sistem selesai dibangun dan siap digunakan, mencakup:
1. **Pemeliharaan Basis Data**: Melakukan pencadangan (*backup*) data laporan dan riwayat produksi secara berkala.
2. **Penyesuaian Format Data**: Mengakomodasi penyesuaian *field* atau kolom jika terjadi perubahan struktur ekspor data dari SAP.
3. **Perbaikan Galat (*Bug Fixing*)**: Memperbaiki kendala teknis atau galat yang baru ditemukan selama operasional harian.
