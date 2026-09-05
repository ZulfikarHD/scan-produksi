# Panduan Pengguna (User Documentation)

Selamat datang di pusat dokumentasi dan panduan operasional **Sistem Monitoring & Pencatatan Produksi**.

---

## Daftar Panduan

1. **[Panduan Login & Navigasi Aplikasi](guides/panduan-login-dan-navigasi.md)**
   - Cara masuk ke dalam sistem menggunakan Nomor Pegawai (NP) / Username.
   - Penjelasan antarmuka dan struktur navigasi (sidebar, banner shift, header).
   - Panduan pergantian shift dan keluar dari sistem (*logout*).

2. **[Panduan Manajemen Data Petugas](guides/panduan-manajemen-data-petugas.md)**
   - Menambahkan akun petugas kemas atau supervisor baru.
   - Mengubah informasi petugas dan mereset kata sandi.
   - Mengatur status keaktifan akun petugas.

3. **[Panduan Update Database Master Data SAP & Log Audit](guides/panduan-update-database-sap.md)**
   - Mengunggah berkas ekspor data SAP (`.xlsx` atau `.csv`).
   - Memeriksa modal pratinjau data dan validasi baris.
   - Menyimpan atau melewati baris bermasalah.
   - Mengunduh template acuan berkas SAP.
   - Mengunduh berkas asli yang pernah diunggah serta berkas teks laporan rincian error.

4. **[Panduan Master Data Order Produksi & Input Manual](guides/panduan-master-data-order-produksi.md)**
   - Menjelajahi, memfilter, dan mencari master data order produksi harian.
   - Menambahkan order produksi darurat (*manual order*) tanpa SAP.
   - Mengubah rincian data order dan melihat spesifikasi teknis lengkap.
   - Menghapus order (termasuk aturan proteksi order yang sedang berjalan).
   - Mengekspor data order yang sedang difilter ke berkas Excel (`.xlsx`).

5. **[Panduan Inisiasi Sesi Laporan Produksi (Buat Laporan)](guides/panduan-inisiasi-sesi-laporan-produksi.md)**
   - Memilih tanggal laporan kerja (WIB), kategori seri produk pita cukai (PCHT, MMEA, HPTL/REL), dan shift kerja.
   - Memeriksa pratinjau kode laporan target (`PCHT-YYYY-MM-DD-S1`).
   - Melanjutkan sesi laporan aktif yang sudah pernah dibuat tanpa duplikasi data.
   - Membuka konsol pemindaian barcode berkecepatan tinggi.

6. **[Panduan Pemindaian Barcode & Input Hasil Produksi](guides/panduan-pemindaian-barcode-dan-input-produksi.md)**
   - Alur pemindaian berkelanjutan 100% keyboard dan scanner tanpa mouse (*Continuous Scan Loop*).
   - Pengalihan fokus otomatis kursor antara input barcode dan input kuantitas kemas.
   - Mengoreksi jumlah kemas langsung di dalam baris tabel (*Inline Table Edit*).
   - Menghapus baris item PO yang salah pindai.
   - Menyelesaikan laporan shift (*Finalisasi*) dan mencetak dokumen fisik standar A4.
   - Membuka kembali sesi draft oleh Supervisor.

7. **[Panduan Arsip Laporan, Koreksi, Verifikasi, dan Cetak Fisik](guides/panduan-arsip-laporan-verifikasi-dan-cetak.md)**
   - Menjelajah, memfilter (seri produk, shift, status, preset tanggal), dan mencari riwayat arsip laporan produksi.
   - Melihat rincian item order terscan dan jejak audit verifikasi.
   - Memperbaiki data laporan dan menambahkan order susulan dalam batas waktu 24 jam.
   - Memberikan persetujuan verifikasi resmi oleh Supervisor.
   - Mencetak dokumen fisik standar A4 dan mengekspor data ke format Excel (`.xlsx`).

8. **[Panduan Beranda & Analitik Produksi (Dashboard)](guides/panduan-beranda-dan-analitik-dashboard.md)**
   - Memantau 5 kartu indikator kinerja utama (KPI) harian secara *real-time*.
   - Membaca grafik analitik tren produksi 14 hari terakhir dengan fitur filter rentang waktu dan tipe grafik.
   - Memeriksa proporsi kontribusi antar shift kerja dan peringkat 5 mesin cetak terproduktif.
   - Memanfaatkan pintasan aksi cepat ke seluruh modul operasional sistem.
   - Memeriksa riwayat 5 berkas laporan produksi terbaru dan menyegarkan data seketika.

9. **[Panduan Verifikasi Struktur Data & Dokumen Laporan Produksi](guides/panduan-verifikasi-skema-dan-spesifikasi-basis-data.md)**
   - Memahami struktur master data pesanan produksi SAP dan atribut operasional kemas.
   - Memeriksa kode laporan, nomor PO barcode, dan akumulasi hasil kemas baik serta afval reject.
   - Meninjau riwayat impor berkas SAP dan penanganan kesalahan baris.
