# Panduan Pemindaian Barcode & Input Hasil Produksi

Panduan praktis bagi Petugas Kemas dan Supervisor untuk mencatat hasil pengemasan Kartu Mesin Produksi menggunakan alat pemindai barcode (*Code 128 Scanner*), mengoreksi data secara langsung di tabel, menyelesaikan laporan shift, dan mencetak dokumen fisik laporan.

---

## 1. Alur Pemindaian Berkelanjutan (*Continuous Scan Loop*)

Alur kerja dirancang 100% menggunakan scanner fisik dan keyboard tanpa perlu menyentuh mouse komputer sama sekali:

```
[ Arahkan Scanner ke Kartu Mesin ] ──> [ Bunyi Nada Sukses & Kursor Otomatis Pindah ke Qty ]
                                                              │
                                                              ▼
[ Kursor Otomatis Kembali ke Scan Barcode ] <── [ Ketik Jumlah Kemas lalu Tekan ENTER ]
```

### Langkah-Langkah Pemindaian:
1. Buka sesi laporan yang ingin dikerjakan melalui menu **"Buat Laporan"** (atau langsung masuk ke halaman konsol `/reports/{kode_laporan}/entry`).
2. Kursor secara otomatis sudah berada di dalam kotak **"1. Scan / Masukkan No PO Barcode"**.
3. Arahkan *barcode scanner* genggam ke barcode pada **Kartu Mesin Produksi**:
   - Scanner akan membaca Nomor Order (contoh: `PO-2026-0001`) dan mengirim tombol **Enter**.
4. Sistem seketika menarik rincian data master SAP (Nama Produk, Nomor Mesin, Spesifikasi Kertas Sekuriti, Target Lembar Cetak) dan membunyikan nada melodi sukses.
5. Kursor kursor **secara otomatis berpindah dan menyorot kotak "2. Jumlah Kemas (Lembar)"**.
6. Ketik angka hasil kemas aktual (atau biarkan angka target bawaan) lalu tekan tombol **Enter** pada keyboard:
   - Baris item PO langsung tersimpan ke database dan muncul di baris paling atas tabel rekapitulasi.
   - Total lembar dan jumlah order pada ringkasan tabel otomatis bertambah.
   - Kursor kursor **secara otomatis melompat kembali ke kotak Scan Barcode No PO**.
7. Lanjutkan memindai Kartu Mesin berikutnya tanpa jeda.

---

## 2. Mengoreksi Kesalahan Input Jumlah Kemas (*Inline Table Edit*)

Jika operator salah mengetik angka hasil kemas (misalnya kelebihan angka nol atau salah hitung):

1. Klik langsung pada kotak angka di kolom **"Jumlah Kemas (Edit Langsung)"** pada baris PO yang ingin diperbaiki di tabel bawah.
2. Ketik angka yang benar (contoh: ubah dari `20000` menjadi `25000`).
3. Tekan tombol **Enter** atau tekan tombol **Tab** untuk berpindah:
   - Angka baru langsung tersimpan ke database.
   - Total akumulasi hasil kemas pada baris bawah (*footer*) tabel langsung terhitung ulang secara instan.
   - Tidak ada modal atau dialog popup yang menutupi layar.

---

## 3. Menghapus Baris PO yang Salah Pindai

Jika kartu mesin tidak sengaja terpindai dua kali atau salah ambil:

1. Pada baris PO yang bersangkutan di tabel, klik ikon **Tempat Sampah** berwarna merah di kolom **Aksi**.
2. Konfirmasi dialog konfirmasi peramban (*browser confirmation*).
3. Baris item terhapus dan total kuantitas laporan terhitung ulang secara otomatis.

---

## 4. Menyelesaikan Laporan Shift (*Finalisasi*)

Setelah seluruh Kartu Mesin pada shift tersebut selesai dipindai dan dikemas:

1. Periksa baris ringkasan di bagian bawah tabel:
   - Pastikan **Total Order**, **Target SAP**, dan **Total Hasil Kemas** sudah sesuai dengan fisik tumpukan kemasan.
2. Klik tombol **"Selesaikan Laporan"** pada bagian kanan atas halaman.
3. Konfirmasi pesan bahwa seluruh Kartu Mesin telah selesai dipindai.
4. Status laporan berubah menjadi **"Terkirim (Selesai Scan)"**:
   - Konsol pemindaian dan pengeditan tabel dikunci untuk mencegah perubahan tidak disengaja oleh operator.
   - Muncul tombol **"Cetak Dokumen Fisik"**.

---

## 5. Mencetak Dokumen Fisik Laporan (Standar A4)

1. Klik tombol **"Cetak Dokumen Fisik"** pada header laporan.
2. Halaman cetak standar A4 akan terbuka di tab baru, menampilkan:
   - Kop resmi **PT Percetakan Dokumen Sekuriti — Unit Pengemasan**.
   - Metadata lengkap (Kode Laporan, Seri Produk, Tanggal, Shift, Nama Petugas Kemas, Status Dokumen).
   - Tabel rincian seluruh PO yang diproses.
   - Total akumulasi lembar baik.
   - Kolom tanda tangan resmi untuk Petugas Kemas (Pembuat) dan Kepala Unit / Supervisor (Mengetahui).
3. Klik tombol **"Cetak Dokumen Sekarang (Print)"** untuk langsung mengirim dokumen ke mesin printer fisik.

---

## 6. Tindakan Khusus Supervisor: Membuka Kembali Sesi Draft

Jika terdapat revisi darurat pada laporan yang sudah berstatus **Terkirim**:
- Supervisor yang login dapat mengklik tombol **"Buka Kembali Sesi"** pada header laporan.
- Status laporan akan kembali menjadi **Draft** sehingga operator dapat menambah atau memperbaiki data kembali.
