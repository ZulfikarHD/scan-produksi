# Panduan Beranda & Analitik Produksi (Dashboard)

## Apa itu Beranda & Analitik Produksi?
Halaman **Beranda (Dashboard)** adalah pusat kendali dan pemantauan harian utama yang dapat diakses oleh **Petugas Pengemasan** maupun **Kepala Unit / Supervisor** setelah berhasil masuk ke sistem. Halaman ini menyajikan indikator performa harian (KPI), grafik tren volume kemas baik dan reject 14 hari terakhir, proporsi hasil kerja antar shift, peringkat mesin cetak terproduktif, tombol aksi cepat, serta riwayat berkas laporan terkini dalam satu tampilan terpadu.

---

## Fitur Utama pada Dashboard

### 1. Kartu Indikator Kinerja Utama (KPI Hari Ini)
Di bagian atas layar, terdapat 5 kartu ringkasan status produksi hari ini:
- **Kemas Baik Hari Ini**: Total lembar produk yang selesai dikemas dalam kondisi baik, lengkap dengan persentase perbandingan terhadap hari kemarin.
- **Reject / Rusak**: Jumlah lembar rusak/cacat beserta indikator persentase tingkat reject (dengan acuan standar toleransi < 1.5%).
- **Order Diproses**: Jumlah Purchase Order (PO) unik yang telah terscan di lini pengemasan hari ini.
- **Berkas Laporan**: Total berkas laporan shift yang masuk hari ini, dipisahkan antara status *Terverifikasi* dan *Menunggu Verifikasi*.
- **Petugas Shift**: Jumlah operator kemas yang aktif berkontribusi pada laporan hari ini.

### 2. Hub Pintasan Aksi Cepat (Quick Action Hub)
Pintasan satu kali klik untuk mempercepat alur kerja operasional:
- **Buat Laporan (Scan Barcode)**: Membuka sesi pemindaian barcode Kartu Mesin baru.
- **Lihat Semua Laporan**: Membuka arsip dan riwayat seluruh laporan shift.
- **Update Database SAP** *(Khusus Supervisor)*: Pintasan langsung untuk mengunggah berkas Excel PO baru.
- **Kelola Data Petugas** *(Khusus Supervisor)*: Pintasan cepat ke modul manajemen akun dan NP operator.

### 3. Grafik Analitik Tren Produksi (14 Hari Terakhir)
Grafik interaktif visualisasi hasil kemas harian:
- **Rentang Waktu**: Dapat diubah secara instan antara pilihan **7 Hari**, **14 Hari (Standar)**, atau **30 Hari**.
- **Pilihan Tipe Visual**: Pilihan tampilan model grafik **Area**, **Garis**, atau **Batang**.
- **Ringkasan Cepat**: Menampilkan rata-rata lembar per hari, hari puncak produksi (*Peak Day*), serta akumulasi total lembar periode.
- **Interaktivitas Tooltip**: Arahkan kursor (*hover*) pada titik grafik untuk melihat tanggal lengkap, jumlah lembar baik, reject, jumlah PO, dan jumlah laporan shift.

### 4. Widget Distribusi Shift & Top 5 Mesin Cetak
- **Distribusi Shift**: Menampilkan persentase perbandingan hasil kerja Shift 1 (Pagi), Shift 2 (Siang), dan Shift 3 (Malam) menggunakan bilah indikator warna.
- **Top 5 Mesin Cetak**: Tabel peringkat mesin cetak yang menghasilkan kuantitas lembar kemas terbanyak dalam rentang waktu yang dipilih.

### 5. Riwayat Laporan Produksi Terkini
Tabel 5 berkas laporan produksi terbaru yang menampilkan kode laporan, tanggal & shift, nama petugas pembuat, total lembar, status verifikasi, serta tombol pintasan untuk melihat detail lengkap laporan.

---

## Cara Menggunakan

### Mengubah Filter Waktu dan Tipe Grafik
1. Pada kartu **Grafik Tren Produksi**, temukan tombol kontrol di sudut kanan atas header grafik.
2. Klik tombol rentang hari yang diinginkan (**7 Hari**, **14 Hari**, atau **30 Hari**).
3. Klik tombol jenis grafik yang diinginkan (**Area**, **Garis**, atau **Batang**).
4. Grafik akan langsung diperbarui seketika tanpa memuat ulang seluruh halaman web.

### Menyegarkan Data Terbaru (Refresh)
Jika ingin memastikan data KPI dan grafik menampilkan angka paling mutakhir dari laporan yang baru saja diserahkan:
1. Klik tombol **"Segarkan Data"** di bagian pojok kanan atas halaman Beranda.
2. Sistem akan membersihkan memori singgah (*cache*) dan memperbarui seluruh angka secara instan dengan notifikasi konfirmasi di sudut layar.

---

## FAQ

**Q: Mengapa angka pada grafik tetap muncul meskipun ada hari libur di mana tidak ada produksi?**  
A: Sistem secara otomatis menampilkan nilai 0 pada tanggal-tanggal tanpa aktivitas produksi agar garis grafik tetap akurat dan tidak melompat tanggal secara tidak teratur.

**Q: Siapa yang dapat melihat tombol "Update Database SAP" dan "Kelola Data Petugas" di Beranda?**  
A: Tombol tersebut hanya akan muncul pada akun dengan peran **Supervisor / Kepala Unit**. Untuk akun Petugas, antarmuka dirancang bersih dengan 2 tombol utama yang relevan dengan tugas pengemasan.

**Q: Berapa lama data KPI diperbarui secara otomatis?**  
A: Data analitik disimpan dalam memori cerdas selama 5 menit untuk menjaga kecepatan halaman. Anda dapat menekan tombol **"Segarkan Data"** kapan saja untuk mendapatkan data real-time seketika.
