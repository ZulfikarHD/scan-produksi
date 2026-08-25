# Panduan Master Data Order Produksi & Input Manual

Panduan ini ditujukan bagi **Supervisor / Kepala Unit** dan **Petugas Kemas** untuk mencari spesifikasi order, menambahkan order manual darurat, menyaring data produksi, serta mengekspor berkas order ke Excel.

---

## 1. Apa itu Master Data Order Produksi?

Master Data Order Produksi adalah basis data utama yang memuat identitas seluruh surat perintah kerja cetak (Nomor PO) yang diterbitkan oleh sistem SAP atau didaftarkan secara manual oleh Supervisor. Data ini menjadi acuan utama bagi petugas di Unit Pengemasan saat memindai barcode fisik pada Kartu Mesin Produksi sebelum proses verifikasi dan pengemasan produk sekuriti dimulai.

---

## 2. Cara Menjelajah & Menyaring Master Data Order

Seluruh data order dapat diakses melalui menu **Update Database** pada tab **"Master Data Order Produksi"**:

1. Buka menu **Update Database** dari sidebar sebelah kiri, lalu klik tab **"Master Data Order Produksi"**.
2. **Pencarian Instan:** Ketik Nomor PO, Nomor OBC, nama produk, atau nama pelanggan pada kolom pencarian. Tabel akan menyaring data secara langsung (*live search*).
3. **Filter Tambahan:**
   - **Filter Status:** Pilih status order (*Siap Kemas*, *Sedang Diproses*, *Selesai*, atau *Dibatalkan*).
   - **Filter Mesin:** Pilih nomor mesin cetak / work center tertentu.
   - **Filter Pelanggan:** Ketik nama institusi/pelanggan pemesan.
   - **Filter Rentang Tanggal:** Tentukan tanggal mulai dan tanggal akhir order.
4. **Bersihkan Filter:** Klik tombol **"Bersihkan Filter"** untuk mengembalikan tabel ke tampilan awal.

---

## 3. Menambahkan Order Produksi Manual (Order Darurat / Ad-Hoc)

Jika terdapat order cetak mendesak yang belum sempat diekspor dari sistem SAP kantor pusat:

1. Pada tab **Master Data Order Produksi**, klik tombol **"+ Tambah Order Manual"** di bagian kanan atas.
2. Isi formulir yang muncul:
   - **Nomor PO (Barcode Key):** Masukkan nomor order unik (contoh: `PO-2026-8891`).
   - **Nomor OBC & Kode Barang:** Masukkan nomor OBC dan kode material jika tersedia.
   - **Nama Produk / Material:** Masukkan nama produk sekuriti (wajib diisi).
   - **Nama Pelanggan:** Masukkan instansi atau kementerian pemesan.
   - **Nomor Mesin (Work Center):** Masukkan kode mesin cetak (contoh: `HDR-1001` atau `MESIN-02`).
   - **Tanggal Order & Batas Kirim:** Tentukan tanggal masuk order dan batas waktu pengiriman.
   - **Target Kemas (Qty Pesan):** Masukkan target lembar/satuan siap kemas (minimal 1).
   - **Rencana Cetak (Rencet):** Masukkan estimasi lembar cetak termasuk inschiet.
   - **Satuan:** Pilih satuan kemas (*lembar*, *rim*, *buku*, *set*, *box*).
   - **Spesifikasi Teknis:** Tuliskan detail fitur sekuriti, guilloche, hologram, atau nomorator.
3. Klik tombol **"Simpan Order Produksi"**.
4. Order baru akan langsung tercatat dengan status **"Siap Kemas"** dan siap dipindai oleh operator di lini produksi.

---

## 4. Mengubah Data Order & Melihat Detail Spesifikasi

1. **Melihat Detail Spesifikasi Lengkap:**
   - Pada baris order yang dipilih, klik tombol menu aksi (ikon titik tiga) lalu pilih **"Lihat Detail"**.
   - Jendela spesifikasi teknis akan terbuka menampilkan informasi produk, parameter mesin, rincian kuantitas target vs realisasi, persentase pemenuhan kemas, serta timeline siklus produksi.
2. **Mengubah Data Order:**
   - Klik tombol menu aksi pada baris tabel lalu pilih **"Ubah Data"** (atau klik tombol *Ubah Data* di dalam jendela detail).
   - Sesuaikan data yang ingin diperbarui, lalu klik **"Simpan Perubahan Order"**.

---

## 5. Menghapus Order Produksi

1. Klik tombol menu aksi pada baris order yang ingin dihapus, lalu pilih **"Hapus Order"**.
2. Jendela konfirmasi akan muncul. Klik tombol merah **"Ya, Hapus Order"**.
3. **Catatan Keamanan Operasional:** Order yang sedang berstatus **"Sedang Diproses"** di lini pengemasan tidak dapat dihapus untuk mencegah terjadinya kesalahan data sesi pemindaian barcode operator.

---

## 6. Mengekspor Master Data ke Excel (.xlsx)

1. Terapkan filter pencarian yang Anda butuhkan pada tabel master data.
2. Klik tombol **"Ekspor Data (.xlsx)"** di toolbar bagian atas.
3. Berkas spreadsheet Excel berisi seluruh data order yang sesuai dengan filter akan otomatis terunduh lengkap dengan 24 kolom standar SAP dan status terkini.

---

## 7. Status Operasional Order Produksi

| Status | Label Antarmuka | Warna Badge | Keterangan Operasional |
|--------|-----------------|-------------|------------------------|
| `ready` | **Siap Kemas** | Biru / Zinc | Data order telah masuk ke basis data dan siap dipindai oleh Petugas Kemas di lini produksi. |
| `in_progress` | **Sedang Diproses** | Kuning / Amber | Proses pemindaian barcode dan pengemasan fisik sedang berlangsung pada shift kerja aktif. |
| `completed` | **Selesai** | Hijau | Akumulasi kuantitas kemas telah memenuhi target PO dan laporan shift telah ditutup. |
| `cancelled` | **Dibatalkan** | Merah | Order dibatalkan atau ditunda berdasarkan instruksi dari perencanaan produksi SAP. |
