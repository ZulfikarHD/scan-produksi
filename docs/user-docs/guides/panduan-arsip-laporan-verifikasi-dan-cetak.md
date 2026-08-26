# Panduan Arsip Laporan, Koreksi, Verifikasi, dan Cetak Fisik

## Apa itu Modul Arsip Laporan Produksi?
Modul Arsip Laporan Produksi adalah pusat kendali dan penyimpanan seluruh riwayat laporan hasil pengemasan dokumen sekuriti harian. Modul ini memungkinkan Petugas dan Supervisor untuk mencari laporan lama, melihat rincian order yang terscan, melakukan perbaikan data jika terdapat selisih hitung, memberikan verifikasi resmi oleh Supervisor, serta mencetak dokumen fisik A4 maupun mengekspor data ke format Excel.

---

## Cara Menggunakan

### 1. Menjelajah dan Memfilter Laporan
1. Buka menu **Lihat Laporan** pada bilah navigasi utama sebelah kiri.
2. Gunakan kolom pencarian cepat untuk mencari berdasarkan **Kode Laporan** (contoh: `PCHT-2026-08-26-S1`) atau **Nomor PO**.
3. Pilih filter kriteria yang tersedia:
   - **Seri Produk**: PCHT, MMEA, Dokumen Sekuriti Umum, dll.
   - **Shift Kerja**: Shift 1 (Pagi), Shift 2 (Siang), Shift 3 (Malam).
   - **Status Laporan**: Draft, Selesai / Terkirim, atau Terverifikasi.
   - **Preset Tanggal**: Hari Ini, Kemarin, 7 Hari Terakhir, atau Bulan Ini.
4. Klik **Reset** untuk mengembalikan semua filter ke kondisi awal.

---

### 2. Melihat Rincian Laporan & Jejak Audit
1. Pada tabel daftar laporan, klik tombol **Rincian** (ikon mata) pada baris laporan yang ingin dilihat.
2. Halaman rincian akan menampilkan:
   - **Banner Status**: Menunjukkan apakah laporan masih dalam status *Draft*, *Terkirim (Menunggu Verifikasi)*, atau *Terverifikasi*.
   - **Informasi Petugas & Shift**: Nama operator pengemas, tanggal produksi, dan catatan operasional.
   - **Metrik Total**: Total PO, Akumulasi Lembar Baik, dan Lembar Reject.
   - **Tabel Rincian Order**: Daftar seluruh nomor PO, spesifikasi bahan, target lembar, dan hasil cetak.

---

### 3. Mengoreksi Laporan Produksi (Batas Waktu 24 Jam)
Petugas dapat memperbaiki data laporan yang sudah dikirim selama masih dalam jangka waktu **24 jam** sejak dibuat dan **belum diverifikasi** oleh Supervisor.

1. Buka halaman rincian laporan atau klik tombol **Edit / Koreksi** pada tabel laporan.
2. Ubah angka pada kolom **Hasil Kemas Baik** atau **Reject** sesuai fisik aktual.
3. Untuk menambahkan order susulan yang belum terscan:
   - Masukkan Nomor PO pada bagian formulir bawah.
   - Tekan tombol **Cari PO**.
   - Masukkan kuantitas kemas baik dan reject, lalu klik **Tambahkan ke Laporan**.
4. Untuk menghapus baris item yang salah input, klik tombol **Hapus** (ikon tempat sampah) pada baris terkait.
5. Klik tombol **Simpan Perubahan Laporan**.

> 💡 **Catatan:** Laporan yang sudah diverifikasi oleh Supervisor tidak dapat diedit kembali oleh Petugas.

---

### 4. Verifikasi Laporan (Khusus Supervisor)
Supervisor bertindak sebagai pemeriksa akhir atas keabsahan data produksi sebelum laporan ditutup secara permanen.

1. Buka rincian laporan yang berstatus **Terkirim**.
2. Periksa kesesuaian akumulasi lembar baik dengan catatan fisik.
3. Klik tombol **Verifikasi Laporan** di pojok kanan atas.
4. Pada kotak dialog konfirmasi, klik **Ya, Verifikasi Laporan**.
5. Status laporan akan berubah menjadi **Terverifikasi** dengan stempel tanda tangan digital Supervisor dan waktu verifikasi tercatat rapi.

---

### 5. Cetak Dokumen Fisik A4 & Ekspor Excel
1. **Cetak Fisik Standar A4**:
   - Pada halaman rincian laporan, klik tombol **Cetak Laporan** (ikon printer).
   - Halaman cetak dengan tata letak resmi perusahaan akan terbuka.
   - Tekan tombol **Cetak Sekarang** atau gunakan shortcut keyboard `Ctrl + P` untuk mencetak ke printer.
2. **Ekspor File Excel (.xlsx)**:
   - Pada halaman daftar laporan, klik tombol **Ekspor Excel**.
   - Pilih rentang tanggal atau kriteria seri yang diinginkan pada modal popup.
   - Klik **Unduh Spreadsheet Excel** untuk mengunduh file secara instan.

---

## Pertanyaan Umum (FAQ)

**Q: Mengapa tombol Koreksi/Edit tidak muncul pada akun saya?**  
A: Tombol edit hanya aktif jika:
1. Laporan tersebut dibuat oleh akun Anda sendiri dan umurnya kurang dari 24 jam.
2. Laporan belum diverifikasi oleh Supervisor.
3. Anda login sebagai akun Supervisor (yang memiliki hak koreksi tanpa batas waktu).

**Q: Apa yang terjadi jika laporan dihapus oleh Supervisor?**  
A: Menghapus laporan akan menghapus seluruh data item laporan tersebut dan mengembalikan status Order Produksi terkait ke daftar antrean sehingga dapat dipindai ulang pada sesi baru.

---

## Panduan Penanganan Masalah (Troubleshooting)

| Masalah | Penyebab | Solusi |
|---|---|---|
| Tidak bisa mengunduh file Excel | Pop-up browser diblokir | Izinkan pop-up download pada pengaturan browser Anda |
| Angka total di cetakan tidak sesuai | Perubahan belum disimpan | Pastikan Anda telah menekan tombol "Simpan Perubahan Laporan" sebelum mencetak |
| Tombol Verifikasi tidak muncul | Akun berstatus Petugas | Fitur verifikasi hanya dapat diakses oleh akun dengan peran Supervisor |
