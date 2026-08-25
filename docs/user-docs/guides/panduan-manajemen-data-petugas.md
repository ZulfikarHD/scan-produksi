# Panduan Manajemen Data Petugas — Panduan Pengguna

Modul **Data Petugas** adalah fitur khusus bagi Kepala Unit / Supervisor untuk mengelola akun staf operator kemas dan supervisor di Unit Pengemasan PT Percetakan Dokumen Sekuriti.

---

## Apa itu Menu Data Petugas?
Menu ini memungkinkan Supervisor mendaftarkan petugas baru, memperbarui informasi profil, mengatur hak akses peran, serta menonaktifkan atau menghapus akun tanpa perlu berpindah-pindah halaman kerja.

---

## Cara Menggunakan

### 1. Membuka Halaman Data Petugas
1. Masuk ke sistem menggunakan akun dengan peran **Supervisor**.
2. Pada menu navigasi sidebar sebelah kiri, klik menu **Data Petugas**.
3. Layar akan menampilkan tabel seluruh staf terdaftar lengkap dengan Nomor Pegawai (NP), username, email, peran, status keaktifan, dan waktu login terakhir.

---

### 2. Mencari dan Menyaring Data Petugas
Supervisor dapat menyaring data staf secara instan:
- **Kotak Pencarian**: Ketik nama lengkap, NP (contoh: `7426`), atau username pada kolom pencarian di bagian atas tabel. Tabel akan menyaring data secara otomatis dalam 300 milidetik.
- **Filter Peran**: Pilih *Supervisor* atau *Petugas Kemas* pada dropdown peran.
- **Filter Status**: Pilih *Aktif* atau *Nonaktif* pada dropdown status.
- **Tombol Reset**: Klik tombol *Reset* jika ingin membersihkan seluruh filter pencarian.

---

### 3. Menambah Petugas Baru
1. Klik tombol **+ Tambah Petugas** berwarna biru di pojok kanan atas.
2. Isi formulir yang muncul pada modal dialog:
   - **Nama Lengkap**: Masukkan nama lengkap petugas (maksimal 100 karakter).
   - **Nomor Pegawai (NP)**: Masukkan NP atau ID Karyawan (4 sampai 6 karakter, contoh: `7426` atau `T0087`).
   - **Username**: Masukkan username login (hanya huruf, angka, garis bawah, tanpa spasi).
   - **Alamat Email**: Masukkan email resmi petugas.
   - **Peran / Hak Akses**: Pilih **Petugas Kemas** (operator lini produksi) atau **Supervisor** (kepala unit).
   - **Status Akun**: Centang opsi *Status Akun Aktif* agar petugas dapat langsung login.
   - **Kata Sandi**: Masukkan kata sandi awal (minimal 8 karakter).
3. Klik tombol **Simpan Data**.
4. Notifikasi hijau *"Data petugas berhasil ditambahkan"* akan muncul di pojok kanan atas, dan nama petugas baru langsung tampil pada urutan teratas tabel.

---

### 4. Mengubah Data Petugas (Edit Profil / Reset Password)
1. Cari baris nama petugas yang ingin diubah.
2. Klik tombol menu aksi (ikon titik tiga `...`) di ujung kanan baris tabel, lalu pilih **Edit Data**.
3. Perbarui informasi yang diperlukan.
4. Jika ingin mereset kata sandi petugas, ketikkan kata sandi baru pada kolom Kata Sandi. Jika tidak ingin mengubah sandi, biarkan kolom tersebut kosong.
5. Klik tombol **Simpan Perubahan**.

---

### 5. Menonaktifkan / Mengaktifkan Akun Petugas
Gunakan opsi ini jika seorang petugas sedang cuti, rotasi shift, atau dinonaktifkan sementara tanpa menghapus riwayat laporan kerja:
1. Klik tombol menu aksi (ikon titik tiga `...`) pada baris petugas target.
2. Klik opsi **Nonaktifkan Akun** (atau **Aktifkan Akun** jika status sebelumnya nonaktif).
3. Status badge pada tabel akan berubah seketika menjadi abu-abu (*Nonaktif*), dan petugas tersebut tidak akan dapat login ke dalam sistem hingga diaktifkan kembali.

> 💡 **Catatan:** Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang digunakan untuk login.

---

### 6. Menghapus Akun Petugas
1. Klik tombol menu aksi (ikon titik tiga `...`) pada baris petugas target.
2. Klik opsi **Hapus Akun** (teks merah).
3. Dialog konfirmasi akan muncul. Baca nama petugas yang akan dihapus, lalu klik tombol **Ya, Hapus Akun**.
4. Data akun akan dihapus dari sistem.

> ⚠️ **Penting:** Akun petugas yang sudah tercatat pada dokumen Laporan Hasil Produksi dilindungi oleh sistem dan tidak dapat dihapus permanen demi menjaga keabsahan riwayat dokumen cetak. Untuk kondisi ini, gunakan fitur **Nonaktifkan Akun**.

---

## FAQ & Tanya Jawab

**Q: Mengapa muncul pesan kesalahan "Nomor Pegawai (NP) sudah terdaftar dalam sistem"?**  
A: Setiap petugas wajib memiliki Nomor Pegawai (NP), Username, dan Email yang unik. Pastikan tidak ada duplikasi data dengan petugas lain yang sudah terdaftar.

**Q: Apakah operator kemas (peran Petugas) dapat membuka menu Data Petugas?**  
A: Tidak. Menu Data Petugas dan seluruh fungsi pengelolaannya diproteksi khusus untuk akun dengan hak akses **Supervisor**. Petugas kemas yang mencoba mengakses halaman ini akan diblokir oleh sistem (*403 Akses Ditolak*).

---

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| Tombol Hapus Akun tidak muncul pada baris nama saya | Sistem memproteksi akun yang sedang aktif digunakan login | Anda tidak dapat menghapus akun Anda sendiri. Penghapusan akun supervisor lain harus dilakukan dari akun supervisor yang berbeda. |
| Petugas tidak bisa login setelah didaftarkan | Status akun dalam kondisi nonaktif | Buka menu aksi pada baris petugas tersebut, lalu pilih opsi *Aktifkan Akun*. |
| Kata sandi baru gagal disimpan saat tambah petugas | Panjang kata sandi kurang dari 8 karakter | Masukkan kata sandi minimal 8 karakter kombinasi huruf dan angka. |
