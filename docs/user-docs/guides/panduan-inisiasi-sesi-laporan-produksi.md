# Panduan Inisiasi Sesi Laporan Produksi (Buat Laporan)

## Apa itu Inisiasi Sesi Laporan?
Fitur **Inisiasi Sesi Laporan** digunakan oleh Petugas Kemas dan Supervisor untuk memulai atau melanjutkan sesi pencatatan hasil produksi harian dokumen sekuriti (Pita Cukai). Setiap sesi laporan dikelompokkan berdasarkan:
1. **Tanggal Kerja** (WIB).
2. **Kategori Seri Produk Pita Cukai**:
   - **PCHT**: Pita Cukai Hasil Tembakau (rokok kretek, cerutu, sigaret).
   - **MMEA**: Minuman Mengandung Etil Alkohol.
   - **HPTL / REL**: Hasil Pengolahan Tembakau Lainnya dan Rokok Elektrik (vape).
3. **Shift Kerja**: Shift 1 (Pagi: 07.00 - 15.00), Shift 2 (Siang: 15.00 - 23.00), atau Shift 3 (Malam: 23.00 - 07.00).

Sistem secara otomatis memeriksa apakah laporan untuk tanggal, seri, dan shift tersebut sudah pernah dibuat sebelumnya. Jika sudah ada, sistem akan langsung membuka sesi aktif tersebut beserta seluruh riwayat pemindaian barcode yang tersimpan tanpa membuat laporan ganda.

---

## Cara Menggunakan

### 1. Membuka Menu Inisiasi Sesi Laporan
1. Masuk ke aplikasi menggunakan akun Petugas atau Supervisor.
2. Pada bilah navigasi (sidebar) sebelah kiri, klik menu **"Buat Laporan"**.
3. Sistem akan membuka halaman formulir ringkas inisiasi sesi laporan.

---

### 2. Memilih Parameter Laporan
Pada kartu formulir inisiasi:
1. **Tanggal Laporan**: Secara otomatis terisi dengan tanggal hari ini (WIB). Anda dapat mengubah tanggal jika ingin memeriksa atau melanjutkan laporan shift sebelumnya.
2. **Kategori Seri Produk**: Klik salah satu kartu kategori:
   - Pilih **PCHT** untuk Pita Cukai Hasil Tembakau.
   - Pilih **MMEA** untuk Minuman Mengandung Etil Alkohol.
   - Pilih **HPTL / REL** untuk Rokok Elektrik dan Tembakau Lainnya.
3. **Shift Kerja**: Sistem mendeteksi shift aktif saat ini secara otomatis berdasarkan jam kerja WIB. Anda dapat memilih shift lain secara manual jika diperlukan.
4. **Catatan Sesi (Opsional)**: Ketik catatan operasional khusus (misal: mesin cadangan atau kendala teknis).

---

### 3. Membuka Konsol Pemindaian Barcode
1. Perhatikan kotak pratinjau **Kode Laporan Sesi Target** di bagian atas formulir (contoh: `PCHT-2026-08-26-S1`).
2. Jika sesi belum pernah ada, akan muncul badge hijau **"Sesi Baru (Draft)"**.
3. Jika sesi sudah pernah dibuka sebelumnya, akan muncul badge kuning **"Sesi Sudah Ada (Lanjutkan)"** lengkap dengan rincian pembuat dan jumlah order yang sudah discan.
4. Klik tombol **"Buka Sesi Pemindaian PO"** (atau **"Lanjutkan Sesi Pemindaian PO"**).
5. Sistem akan langsung mengalihkan Anda ke halaman konsol scan barcode dan kursor langsung aktif di kolom scan No PO!

> 💡 **Tips Praktis:**
> Anda tidak perlu menggunakan mouse saat berada di konsol pemindaian. Arahkan scanner barcode ke Kartu Mesin Produksi, sistem akan otomatis membaca data SAP dan memindahkan kursor ke kolom jumlah kemas seketika.

---

## FAQ (Pertanyaan yang Sering Diajukan)

**Q: Apa yang terjadi jika saya membuka kategori dan shift yang sama dengan rekan kerja saya?**  
A: Sistem mendeteksi bahwa sesi laporan tersebut sudah aktif dan akan langsung membuka laporan yang sama. Data yang sudah dipindai oleh rekan Anda akan tetap tersimpan aman di tabel rekapitulasi.

**Q: Apakah petugas lain bisa melihat laporan yang saya buat pada shift hari ini?**  
A: Ya, pada panel sebelah kanan *"Sesi Hari Ini"*, seluruh sesi laporan yang terdaftar pada tanggal terpilih dapat dilihat dan dibuka langsung oleh sesama petugas atau supervisor.

---

## Troubleshooting

| Kendala | Penyebab | Solusi |
|---|---|---|
| Kode laporan menampilkan shift yang salah | Jam komputer atau pergantian shift baru saja lewat | Klik kartu shift yang sesuai pada pilihan Shift Kerja sebelum menekan tombol buka sesi. |
| Tidak bisa membuka sesi pemindaian | Field tanggal atau kategori belum dipilih | Pastikan tanggal dan radio button kategori seri produk terpilih dengan tanda centang/border hijau. |
| Muncul notifikasi "Sesi Laporan Ditemukan" | Kombinasi tanggal, seri, dan shift sudah pernah dibuat sebelumnya | Ini adalah fitur pengaman normal untuk mencegah laporan ganda. Sistem akan langsung memuat data yang sudah tersimpan. |
