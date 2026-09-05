# Panduan Konfigurasi & Portabilitas Basis Data - Panduan Pengguna

## Apa itu Portabilitas Basis Data?

Portabilitas basis data adalah kemampuan aplikasi **scan-produksi** untuk dijalankan secara mulus dan konsisten pada berbagai jenis mesin basis data relasional (**SQLite**, **MySQL 8.0+**, dan **PostgreSQL 16+**) tanpa memerlukan perubahan kode program. 

Sistem dirancang sedemikian rupa agar:
1. **Pengembangan & Uji Otomatis:** Pengembang dapat menggunakan **SQLite** secara lokal atau *in-memory* dengan proses pengujian instan (< 10 detik).
2. **Server Produksi Pabrik:** Server operasional di lingkungan pabrik pengemasan menggunakan **MySQL 8.0+** dengan keandalan transaksi ACID penuh dan penyimpanan terpusat.
3. **Infrastruktur Korporat Masa Depan:** Jika perusahaan bertransisi ke sistem klaster **PostgreSQL 16+**, seluruh skema migrasi dan data laporan dapat langsung digunakan tanpa kendala ketidakcocokan sintaks.

---

## Cara Menggunakan

### 1. Menyiapkan Lingkungan Basis Data Lokal (SQLite)

Cocok untuk pengujian cepat di laptop pengembang atau demonstrasi sidang skripsi.

1. Pastikan ekstensi `pdo_sqlite` dan `sqlite3` aktif di PHP.
2. Buka berkas konfigurasi `.env` di direktori utama proyek.
3. Atur parameter koneksi sebagai berikut:
   ```ini
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database/database.sqlite
   DB_FOREIGN_KEYS=true
   ```
4. Buat berkas database kosong jika belum tersedia:
   ```bash
   touch database/database.sqlite
   ```
5. Jalankan migrasi dan data awal (*seeder*):
   ```bash
   php artisan migrate:fresh --seed
   ```

> 💡 **Tips:** Pengaturan `DB_FOREIGN_KEYS=true` wajib diaktifkan agar SQLite memberlakukan aturan kunci tamu (*foreign keys*) secara ketat sama seperti MySQL.

---

### 2. Menyiapkan Lingkungan Server Produksi (MySQL 8.0+)

Konfigurasi standar untuk server operasional *on-premise* unit pengemasan PT Percetakan Dokumen Sekuriti.

1. Masuk ke terminal MySQL Server sebagai administrator:
   ```sql
   CREATE DATABASE scan_produksi_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'pds_app_user'@'localhost' IDENTIFIED BY 'PasswordKuatProduksi2026!';
   GRANT ALL PRIVILEGES ON scan_produksi_prod.* TO 'pds_app_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
2. Buka berkas `.env` pada server produksi dan sesuaikan:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=scan_produksi_prod
   DB_USERNAME=pds_app_user
   DB_PASSWORD=PasswordKuatProduksi2026!
   DB_CHARSET=utf8mb4
   DB_COLLATION=utf8mb4_unicode_ci
   ```
3. Jalankan migrasi basis data:
   ```bash
   php artisan migrate
   ```
4. Verifikasi status migrasi seluruh tabel:
   ```bash
   php artisan migrate:status
   ```

---

### 3. Memeriksa Integritas dan Menjalankan Uji Otomatis

Sebelum menyerahkan server kepada operator kemas atau demonstrasi sidang, jalankan uji verifikasi portabilitas:

1. Buka terminal di server atau workstation.
2. Jalankan perintah uji:
   ```bash
   php artisan test --compact --filter=DatabasePortabilityTest
   ```
3. Pastikan keenam skenario uji (koneksi, indeks, JSON, referensial, kueri ANSI) berstatus hijau (`PASSED`).

---

## FAQ

**Q: Mengapa kolom yang ditambahkan dengan `->after()` tidak berada di posisi yang sama pada SQLite atau PostgreSQL?**  
A: Klausa `->after()` adalah fitur spesifik MySQL untuk merapikan urutan kolom secara visual. Pada SQLite dan PostgreSQL, kolom baru otomatis diletakkan di akhir tabel. Aplikasi scan-produksi membaca data berdasarkan nama kolom, bukan nomor urutan kolom fisik, sehingga sistem dijamin 100% aman dan bekerja normal.

**Q: Apakah data rincian hasil scan PO akan hilang jika master order SAP terhapus?**  
A: Tidak. Sistem menggunakan aturan `nullOnDelete()` pada relasi order. Nomor PO, nama produk, spesifikasi, dan jumlah hasil kemas telah tersimpan sebagai snapshot permanen di tabel rincian laporan, sehingga data historis dan bukti audit fisik tetap utuh.

**Q: Apa yang terjadi jika file ekspor SAP memuat catatan error yang sangat panjang?**  
A: Kolom `error_log` pada riwayat impor bertipe JSON dan secara otomatis dikonversi menjadi array oleh sistem. Berkas log mampu menampung ribuan baris rincian error validasi tanpa terpotong.

---

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| `General error: 1 no such table` pada SQLite | Berkas `database.sqlite` belum dibuat atau path salah | Pastikan berkas `database/database.sqlite` telah dibuat dengan `touch` dan path pada `.env` sudah sesuai. |
| `Access denied for user` pada MySQL | Username atau password basis data pada berkas `.env` tidak cocok | Periksa kembali kredensial pengguna MySQL dan pastikan hak akses database telah diberikan (`GRANT ALL PRIVILEGES`). |
| `Foreign key constraint fails` saat menghapus data master | Terdapat dependensi referensial yang mengunci | Gunakan alur penghapusan resmi dari menu Master Data Explorer aplikasi agar sistem menjalankan validasi status transaksi. |
| Pengujian otomatis berjalan lambat pada MySQL lokal | I/O disk sinkron per transaksi | Untuk pengujian unit harian, disarankan tetap menggunakan konfigurasi bawaan `DB_CONNECTION=sqlite` dengan basis data memori (`:memory:`). |
