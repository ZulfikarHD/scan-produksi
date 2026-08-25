# Panduan Pengguna — Login & Navigasi Aplikasi

Aplikasi Sistem Monitoring & Pencatatan Produksi dirancang khusus untuk mempermudah petugas kemas dan supervisor di Unit Pengemasan PT Percetakan Dokumen Sekuriti dalam mencatat serta memantau hasil produksi harian.

---

## 1. Cara Masuk ke Sistem (Login)

1. Buka browser pada komputer workstation kerja Anda dan akses alamat sistem.
2. Pada layar login **Masuk ke Sistem**:
   - **Nomor Pegawai (NP) / Username / Email**: Masukkan Nomor Pegawai Anda (4 sampai 6 karakter kombinasi huruf dan angka, contoh: `7426` atau `T0087`), atau username, atau alamat email terdaftar.
   - **Kata Sandi**: Masukkan kata sandi akun Anda. Anda dapat mengklik ikon mata di sebelah kanan input untuk memeriksa ketikan sandi.
   - **Ingat Saya**: Centang opsi ini jika Anda ingin sistem mengingat sesi login pada workstation pribadi.
3. Klik tombol **Masuk ke Sistem** (atau tekan tombol `Enter` pada keyboard).
4. Jika data valid, Anda akan langsung diarahkan ke halaman **Dashboard**.

> 💡 **Tips Keamanan:** Jangan membagikan kata sandi akun Anda kepada siapapun. Jika akun Anda dinonaktifkan atau Anda lupa kata sandi, segera hubungi Supervisor Unit Pengemasan untuk bantuan reset.

---

## 2. Mengenal Tampilan Aplikasi (Layout Shell)

Setelah berhasil masuk, Anda akan melihat antarmuka utama aplikasi yang terdiri dari:

### A. Header Bagian Atas
- **Banner Shift Operasional**: Menampilkan shift kerja aktif saat ini secara otomatis berdasarkan waktu WIB:
  - **Shift 1 (Pagi)**: Pukul 07:00 – 15:00 WIB
  - **Shift 2 (Siang)**: Pukul 15:00 – 23:00 WIB
  - **Shift 3 (Malam)**: Pukul 23:00 – 07:00 WIB
- **Jam Real-time WIB**: Menampilkan hari, tanggal, bulan, tahun, dan jam server Indonesia bagian barat (`Asia/Jakarta`).
- **Peralihan Mode Gelap/Terang**: Klik ikon matahari / bulan di pojok kanan atas untuk mengubah tampilan layar ke mode gelap (*Dark Mode*) atau mode terang (*Light Mode*).

### B. Menu Navigasi Sidebar (Sisi Kiri)
- **Dashboard**: Halaman ringkasan monitoring produksi.
- **Update Database** *(Khusus Supervisor)*: Menu sinkronisasi data order produksi dari SAP.
- **Buat Laporan**: Formulir scan barcode dan pencatatan hasil produksi kemas.
- **Lihat Laporan**: Tabel riwayat laporan produksi harian.
- **Data Petugas** *(Khusus Supervisor)*: Menu pengelolaan akun operator dan supervisor.
- **Pengaturan**: Halaman konfigurasi profil dan keamanan akun pribadi.

---

## 3. Cara Keluar dari Sistem (Logout / Ganti Shift)

Saat jam kerja shift Anda telah berakhir dan akan digantikan oleh rekan shift berikutnya:

1. Klik nama profil akun Anda di bagian kiri bawah sidebar (atau di pojok kanan atas pada layar mobile/tablet).
2. Klik opsi tombol **Keluar / Logout** berwarna merah.
3. Sesi kerja Anda akan dibersihkan dengan aman dan layar akan kembali ke halaman login.

---

## FAQ & Tanya Jawab

**Q: Saya memasukkan NP saya tetapi muncul tulisan "Nomor Pegawai/Username atau Kata Sandi yang dimasukkan salah", apa yang harus saya lakukan?**  
A: Pastikan format NP dan huruf besar/kecil pada kata sandi sudah benar. Jika Anda salah memasukkan kata sandi sebanyak 5 kali berturut-turut, sistem akan mengunci percobaan login selama 1 menit demi keamanan data.

**Q: Mengapa menu "Data Petugas" atau "Update Database" tidak muncul pada akun saya?**  
A: Menu administrasi tersebut khusus diberikan kepada akun dengan peran **Supervisor**. Akun operator kemas memiliki akses fokus pada menu operasional seperti Buat Laporan dan Lihat Laporan.
