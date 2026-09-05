# Panduan Verifikasi Struktur Data & Dokumen Laporan Produksi

## Apa itu Struktur Data dan Spesifikasi File Sistem?

Sistem Pencatatan Hasil Produksi Harian (`scan-produksi`) mengelola data operasional pengemasan pita cukai melalui basis data yang terstruktur rapi dan transparan. Panduan ini disusun untuk membantu **Kepala Unit / Supervisor** dan **Petugas Kemas** memahami bagaimana data pesanan produksi, sesi laporan harian, dan berkas impor SAP disimpan, dihubungkan, serta diverifikasi keabsahannya.

Dengan struktur yang terstandarisasi, setiap lembar hasil kemas yang dipindai menggunakan *barcode scanner* terhubung secara langsung dengan pesanan aslinya di SAP tanpa risiko data ganda atau nilai rekapitulasi yang tidak sinkron.

---

## Cara Memeriksa dan Memverifikasi Data

### 1. Memeriksa Struktur Pesanan Produksi (Master Order)

Setiap pesanan produksi yang masuk ke sistem memiliki identitas unik berupa **Nomor PO** yang tercetak pada Kartu Mesin Produksi dalam bentuk barcode Code 128.

1. Buka menu **Data Master SAP** (khusus akun Supervisor).
2. Perhatikan kolom-kolom utama pada tabel:
   - **Nomor PO (`po_number`)**: Nomor acuan utama untuk barcode scanner.
   - **Nomor OBC (`no_obc`)**: Nomor bukti cetak pesanan.
   - **Nama Produk (`product_name`)**: Deskripsi jenis pita cukai.
   - **Nomor Mesin (`machine_number`)**: Identitas mesin cetak asal lembar kerja.
   - **Target Pesanan (`target_quantity`)**: Jumlah lembar yang wajib diselesaikan.
   - **Status**: Status pengerjaan pesanan (`ready`, `in_progress`, atau `completed`).

> 💡 **Tips:** Kolom teks seperti Nomor PO dan Mesin dibatasi maksimal 50 karakter agar pencarian data melalui scanner berjalan instan tanpa jeda pemrosesan.

### 2. Memeriksa Sesi Laporan dan Akumulasi Hasil Kemas

Laporan harian dikelompokkan berdasarkan seri produk, tanggal pengerjaan, dan giliran kerja (shift).

1. Buka menu **Laporan Harian**.
2. Pilih dokumen laporan yang ingin diperiksa. Kode laporan tersusun dengan format resmi:
   - Contoh: `PCHT-2026-08-26-S1` (Seri PCHT, Tanggal 26 Agustus 2026, Shift 1).
3. Di dalam rincian laporan, periksa tiga angka akumulasi utama:
   - **Total Order**: Jumlah variasi Nomor PO yang dipindai.
   - **Hasil Baik (`total_good_quantity`)**: Total lembar yang berhasil dikemas dalam kondisi baik.
   - **Hasil Rusak / Afval (`total_reject_quantity`)**: Total lembar yang rusak atau afval selama proses pengemasan.
4. Setiap pemindaian baru atau perubahan kuantitas lembar secara otomatis memperbarui ketiga angka akumulasi tersebut dalam satu transaksi database terpadu.

### 3. Memverifikasi Riwayat Impor Berkas SAP

Supervisor dapat meninjau rekaman setiap berkas Excel atau CSV yang telah diunggah ke sistem.

1. Masuk ke halaman **Riwayat Impor SAP**.
2. Periksa ringkasan berkas:
   - **Nama Berkas**: Nama file dokumen mentah yang diunggah.
   - **Total Baris**: Jumlah baris data yang terbaca.
   - **Berhasil / Diperbarui / Gagal**: Rincian jumlah baris baru, baris yang diperbarui, atau baris yang ditolak validasi.
3. Jika terdapat baris yang gagal, klik ikon rincian untuk membaca pesan kesalahan spesifik per baris.

---

## FAQ

**Q: Mengapa sistem menolak pembuatan sesi laporan baru jika tanggal, seri, dan shift sudah pernah dibuat?**  
A: Sistem memiliki aturan keunikan komposit (*Unique Constraint*) untuk memastikan tidak terjadi duplikasi lembar laporan pada giliran kerja yang sama. Jika sesi sudah ada, petugas akan langsung diarahkan ke sesi yang telah berjalan.

**Q: Apakah data rincian laporan akan berubah jika master data SAP di kemudian hari diperbarui?**  
A: Tidak. Saat barcode dipindai, sistem menyimpan *snapshot* (salinan permanen) atas nama produk, spesifikasi kertas, dan nomor mesin pada saat pemindaian dilakukan, sehingga laporan fisik yang telah dicetak tetap valid dan otentik.

**Q: Apa yang dimaksud dengan field primary key berformat `tablename_id`?**  
A: Setiap tabel di basis data memiliki kolom identitas khusus bernama nama tabel diikuti `_id` (misalnya `user_id`, `production_order_id`, `daily_report_id`). Hal ini memudahkan pelacakan relasi antar-tabel secara transparan dan memenuhi standar dokumentasi akademis skripsi.

---

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---|---|---|
| Pemindaian barcode PO tidak memunculkan data pesanan | Nomor PO belum terdaftar pada master SAP atau format barcode rusak | Pastikan berkas ekspor SAP telah diimpor oleh Supervisor. Coba ketik Nomor PO secara manual pada kolom input. |
| Muncul pemberitahuan bahwa sesi shift sudah dibuka operator lain | Dua operator membuka sesi laporan dengan seri produk, tanggal, dan shift yang sama secara bersamaan | Sistem secara otomatis mengarahkan operator kedua ke sesi yang telah dibuka tanpa menimbulkan galat. |
| Catatan kendala mesin terpotong saat disimpan | Karakter melebihi batas kolom lama | Kolom catatan kini menggunakan tipe teks panjang (`TEXT`) sehingga dapat memuat keterangan kondisi fisik kertas secara lengkap tanpa terpotong. |
