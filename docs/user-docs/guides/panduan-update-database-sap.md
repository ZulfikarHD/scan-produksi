# Panduan Pengguna — Update Database Master Data SAP

Panduan ini ditujukan bagi **Kepala Unit / Supervisor** untuk melakukan sinkronisasi dan pembaruan data order produksi harian dari berkas ekspor SAP ke dalam sistem aplikasi Unit Pengemasan, mengunduh template, serta mengaudit riwayat impor.

---

## 1. Apa itu Menu Update Database?

Menu **Update Database** adalah fasilitas khusus bagi Supervisor untuk mengunggah berkas ekspor data dari sistem SAP (format `.xlsx` atau `.csv`) ke dalam basis data aplikasi secara otomatis. 

Dengan fitur ini:
- Supervisor tidak perlu mengetik ulang nomor PO, spesifikasi produk, target kemas, atau nomor mesin satu per satu.
- Data yang sudah ada di database akan diperbarui secara otomatis (*upsert*) jika ada perubahan kuantitas atau spesifikasi dari SAP tanpa menduplikasi data.
- Sistem menyediakan pratinjau (*preview*) dan pemeriksaan validasi sebelum data benar-benar disimpan ke database.
- Riwayat seluruh berkas yang pernah diunggah tersimpan rapi dan dapat diunduh kembali sewaktu-waktu sebagai arsip audit.

---

## 2. Cara Mengunggah Berkas SAP

### Langkah 1: Membuka Menu Update Database
1. Pastikan Anda telah login menggunakan akun dengan peran **Supervisor**.
2. Klik menu **"Update Database"** pada sidebar navigasi sebelah kiri.
3. Anda akan diarahkan ke halaman utama Update Database pada tab **"Impor Berkas SAP"**.

### Langkah 2: Mengunggah Berkas
1. Pada kotak **"Unggah Berkas Ekspor SAP"**, klik tombol **"Pilih Berkas SAP"** atau seret (*drag-and-drop*) berkas spreadsheet dari komputer Anda ke dalam area bergaris putus-putus.
2. Format berkas yang didukung adalah:
   - Excel Spreadsheet (`.xlsx`)
   - Berkas CSV (`.csv`)
   - Ukuran berkas maksimal: **10 MB**.
3. Sistem akan memproses berkas dan menampilkan animasi *"Sedang mengunggah dan membaca baris spreadsheet..."*.

### Langkah 3: Memeriksa Pratinjau Data (Modal Pratinjau)
Setelah berkas berhasil dibaca, jendela **Pratinjau Impor Berkas SAP** akan terbuka secara otomatis di layar:
1. Periksa kartu ringkasan status di bagian atas:
   - **Total Baris**: Jumlah baris data yang terdeteksi di dalam berkas.
   - **Valid** (Badge Hijau): Jumlah baris data yang siap disimpan ke basis data.
   - **Bermasalah** (Badge Merah): Jumlah baris data yang formatnya tidak lengkap (misal: Nomor PO kosong atau Target Qty bukan angka).
2. Tinjau tabel sampel 20 baris pertama untuk memastikan kolom Nomor PO, Nama Produk, dan Target Kuantitas sudah sesuai.

### Langkah 4: Menyimpan ke Database
- **Jika semua baris valid:** Klik tombol **"Simpan Semua ke Database"**.
- **Jika terdapat baris bermasalah:** Anda dapat memilih tombol **"Lewati Baris Error & Simpan Baris Valid"** untuk tetap menyimpan baris data yang sehat, atau klik **"Batal Impor"** jika ingin memperbaiki berkas Excel terlebih dahulu.
- Setelah proses selesai, notifikasi pemberitahuan (toast hijau) akan muncul di pojok layar, dan baris riwayat baru akan otomatis tercatat pada tabel **Riwayat Pembaruan Database**.

---

## 3. Mengunduh Contoh Template SAP

Jika Anda membutuhkan format acuan kolom berkas Excel yang sesuai dengan standar sistem:
1. Buka halaman **Update Database**.
2. Klik tombol **"Unduh Template SAP"** di pojok kanan atas halaman.
3. Berkas `template_sap_master_data.xlsx` akan otomatis terunduh ke komputer Anda lengkap dengan contoh susunan 24 kolom SAP.

---

## 4. Memeriksa Riwayat, Mengunduh Berkas Asli & Laporan Error (Audit Trail)

Tabel **Riwayat Pembaruan Database** di bagian bawah tab impor mencatat seluruh aktivitas unggahan:
- **Waktu Impor:** Tercatat dalam format Waktu Indonesia Barat (WIB).
- **Nama Berkas & Pengunggah:** Menampilkan nama berkas asli dan nama akun Supervisor yang memproses.
- **Statistik Baris:** Jumlah baris total, baris baru (+), baris yang diperbarui, dan baris bermasalah.
- **Melihat Detail Error:** Klik tombol **"Lihat Error"** pada baris yang berstatus *Sebagian Berhasil* untuk meninjau rincian nomor baris yang bermasalah.
- **Mengunduh Berkas Asli:** Klik menu aksi (ikon titik tiga) $\rightarrow$ pilih **"Unduh Berkas Asli"** untuk mengunduh salinan berkas spreadsheet yang diunggah pada shift tersebut.
- **Mengunduh Laporan Error (.txt):** Klik menu aksi $\rightarrow$ pilih **"Unduh Laporan Error (TXT)"** untuk mengunduh berkas laporan teks rincian kesalahan baris untuk dikirimkan ke tim input SAP.

---

## 5. Tanya Jawab (FAQ)

**Q: Apa yang terjadi jika saya mengunggah berkas yang berisi Nomor PO yang sudah pernah diimpor kemarin?**  
**A:** Sistem secara otomatis memperbarui data order yang sudah ada dengan data terbaru dari SAP (*upsert*). Sistem tidak akan membuat data ganda (*duplicate*).

**Q: Mengapa berkas Excel saya ditolak saat diunggah?**  
**A:** Pastikan berkas memiliki ekstensi `.xlsx` atau `.csv`, ukuran berkas tidak melebihi 10 MB, dan berkas tidak dalam keadaan rusak (*corrupted*).

**Q: Apakah Petugas Kemas biasa dapat mengakses menu ini?**  
**A:** Tidak. Menu Update Database dirancang khusus dengan hak akses terbatas untuk Kepala Unit / Supervisor.

---

## 6. Panduan Pemecahan Masalah (Troubleshooting)

| Gejala / Pesan Kendala | Penyebab Umum | Solusi yang Disarankan |
|------------------------|---------------|------------------------|
| *"Format berkas tidak didukung"* | Berkas menggunakan format lama `.xls` binary atau format lain | Buka berkas di Microsoft Excel, lalu pilih *Save As* dengan format *Excel Workbook (.xlsx)* atau *CSV (Comma delimited)*. |
| *"Ukuran berkas melebihi batas maksimum 10 MB"* | Berkas terlalu besar karena berisi gambar atau format berlebih | Hapus lembar kerja (*sheet*) yang tidak digunakan atau simpan sebagai format `.csv`. |
| *"Baris X: Nomor PO tidak boleh kosong"* | Kolom No PO pada baris tersebut tidak terisi | Lengkapi nomor PO pada berkas Excel Anda, atau gunakan tombol *Lewati Baris Error* saat menyimpan. |
| Modal pratinjau tidak muncul setelah memilih berkas | Koneksi internet terputus saat proses upload | Muat ulang (*refresh*) halaman peramban Anda dan pilih kembali berkas yang ingin diunggah. |
