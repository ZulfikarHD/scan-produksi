# Panduan Master Data Order Produksi

## Apa itu Master Data Order Produksi?

Master Data Order Produksi adalah basis data utama yang memuat identitas seluruh surat perintah kerja cetak (Nomor PO) yang diterbitkan oleh sistem SAP atau didaftarkan secara manual oleh Supervisor. Data ini menjadi acuan utama bagi petugas di Unit Pengemasan saat memindai barcode fisik pada Kartu Mesin Produksi sebelum proses verifikasi dan pengemasan produk sekuriti dimulai.

---

## Atribut & Informasi Utama Order

Setiap data order produksi di dalam sistem memuat rincian spesifikasi sebagai berikut:

1. **Nomor PO (*Purchase Order Number*)**  
   Nomor unik order produksi (contoh: `PO-2026-8891`). Nomor ini tercetak dalam bentuk barcode Code 128 pada Kartu Mesin Produksi yang dipindai petugas.
2. **Kode & Nama Produk**  
   Nama dan kode barang sekuriti yang diproduksi (contoh: *Sertifikat Sekuriti Hologram A4*, *Pita Cukai Tembakau*, *Ijazah Kelulusan*).
3. **Nama Pelanggan / Institusi Pemesan**  
   Kementerian, Bank, atau Lembaga pemesan dokumen sekuriti (contoh: *Kementerian Keuangan RI*, *Bank Indonesia*).
4. **Nomor Mesin Produksi**  
   Identitas mesin cetak yang memproses lembar produk (contoh: `MESIN-01`, `MESIN-02`).
5. **Spesifikasi & Jenis Kertas**  
   Rincian fitur sekuriti dan gramatur bahan kertas (contoh: *Security Paper 120 gsm dengan watermark dan strip hologram*).
6. **Target Kuantitas & Satuan**  
   Jumlah lembar/rim/buku target siap kemas yang wajib dipenuhi oleh lini pengemasan.
7. **Tanggal Order & Batas Waktu Kirim (*Delivery Deadline*)**  
   Jadwal masuk order dan batas waktu pengiriman akhir ke pelanggan.

---

## Tahapan Status Order Produksi

Setiap order memiliki 4 tingkatan status operasional:

| Status | Label Antarmuka | Warna Badge | Keterangan Operasional |
|--------|-----------------|-------------|------------------------|
| `ready` | **Siap Kemas** | Biru | Data order telah masuk ke basis data dan siap dipindai oleh Petugas Kemas di lini produksi. |
| `in_progress` | **Sedang Diproses** | Kuning / Amber | Proses pemindaian barcode dan pengemasan fisik sedang berlangsung pada shift kerja aktif. |
| `completed` | **Selesai** | Hijau | Akumulasi kuantitas kemas telah memenuhi target PO dan laporan shift telah ditutup. |
| `cancelled` | **Dibatalkan** | Merah | Order dibatalkan atau ditunda berdasarkan instruksi dari perencanaan produksi SAP. |

---

## FAQ (Pertanyaan Umum)

**Q: Mengapa barcode pada kartu mesin tidak terbaca saat discan?**  
A: Pastikan scanner barcode Code 128 terhubung dengan baik dan data Nomor PO pada kartu mesin sudah terdaftar di menu Master Data / Update Database.

**Q: Apa yang terjadi jika file ekspor SAP diunggah ulang untuk nomor PO yang sama?**  
A: Sistem secara otomatis memperbarui (*update*) data yang sudah ada tanpa membuat data ganda, sehingga data spesifikasi dan target kuantitas selalu menggunakan versi terbaru dari SAP.

---

## Troubleshooting

| Kendala | Penyebab | Solusi |
|---------|----------|--------|
| Nomor PO tidak ditemukan saat pemindaian | Data belum diimpor dari SAP | Hubungi Supervisor untuk melakukan impor berkas SAP terbaru atau menambahkan order manual darurat. |
| Target kuantitas berbeda dengan kartu fisik | Ada revisi jumlah lembar pada sistem SAP | Lakukan pembaruan berkas SAP di halaman Update Database untuk menyinkronkan data target terbaru. |
