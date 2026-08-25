# Examples of Industrial Paper Writing Style

This document contrasts dry, bullet-heavy writing with the desired narrative-driven, industrial storytelling style.

---

## 1. Operational Background & Friction

### ❌ Weak Style (Dry, Disjointed Bullet Points)
```markdown
### 1.1 Kondisi Operasional
- Unit Cetak mencetak pita cukai untuk Bea Cukai.
- Memiliki 9 mesin cetak (Komori, Ryobi, GTO) dan 42 operator.
- Bekerja 3 shift 24 jam.
- Pencatatan masih manual di buku folio.
- Data kerusakan diambil dari SAP secara global.
- Akibatnya teknisi bingung saat memperbaiki mesin dan rekapitulasi data lambat.
```

### ✅ Desired Style (Flowing Narrative & Shop-Floor Context)
```markdown
### 1.1.1 Profil Operasional & Karakteristik Produk Sekuriti Negara
Berdasarkan Peraturan Pemerintah Nomor 06 Tahun 2019, Perum Percetakan Uang Republik Indonesia (Peruri) merupakan Badan Usaha Milik Negara (BUMN) yang mengemban amanah strategis dari Pemerintah Republik Indonesia untuk menyelenggarakan pencetakan Uang Rupiah serta dokumen sekuriti negara bernilai tinggi. Salah satu portofolio produk sekuriti non-uang yang memiliki kontribusi penerimaan negara sangat masif dan diproduksi secara berkelanjutan adalah Pita Cukai, yang mencakup Pita Cukai Hasil Tembakau (PCHT) dan Minuman Mengandung Etil Alkohol (MMEA). Dokumen sekuriti ini berfungsi sebagai instrumen pengawasan fiskal sekaligus bukti fisik pelunasan penerimaan cukai negara di bawah kewenangan Direktorat Jenderal Bea dan Cukai (DJBC) Kementerian Keuangan Republik Indonesia.

Dalam menjalankan mandat produksi tersebut, lini produksi Unit Cetak Pita Cukai beroperasi dengan intensitas tinggi selama 24 jam sehari secara non-stop dengan menerapkan pola 3 *shift* kerja bergilir (*Shift* Pagi pukul 07.00–15.00 WIB, *Shift* Sore pukul 15.00–23.00 WIB, dan *Shift* Malam pukul 23.00–07.00 WIB). Operasional harian ini didukung oleh 9 unit mesin cetak *sheet-fed offset* yang terdiri dari 4 unit mesin Komori (KMR1, KMR2, KMR3, KMR4), 2 unit mesin Ryobi (RYB1, RYB2), serta 3 unit mesin GTO (GTO-1, GTO-2, GTO-3), dengan melibatkan sekitar 42 personel operator cetak dan kepala kelompok. Volume pesanan pita cukai yang dikelola unit ini mencapai rata-rata 160.000.000 lembar cetak per tahun, dengan volume aktual pada tahun anggaran 2025 menembus 177.636.930 lembar cetak.

### 1.1.2 Keterbatasan Sistem Pencatatan Eksisting (*Data Silo* & Rekapitulasi Manual)
Meskipun lini produksi Unit Cetak Pita Cukai memproses jutaan lembar dokumen sekuriti negara setiap hari dengan mesin-mesin modern, sistem pencatatan operasional di lapangan (*shop floor*) hingga akhir tahun 2025 masih terperangkap dalam kondisi pemisahan data (*data silo*). Dari sisi kuantitas di lapangan, hasil cetak harian, nomor mesin, dan nomor PO masih dicatat manual pada buku folio fisik yang menumpuk di meja kontrol mesin dan baru direkapitulasi saat momen Penilaian Kinerja Pegawai Kuartalan maupun Evaluasi Akhir Masa Kontrak. Sebaliknya, data kualitas dari sistem SAP (T-Code: `ZPPRSIPPC0012`) hanya menyajikan ringkasan global di level unit tanpa atribusi nomor mesin pencetak maupun *shift* kerja.
```

---

## 2. Executive Takeaway Format

### Template & Example
```markdown
> ***Executive Takeaway:***  
> Unit Cetak Pita Cukai mengelola pesanan strategis negara sebesar **160.000.000 Lembar Cetak / tahun** (aktual 2025: **177.636.930 Lembar Cetak**). Sepanjang tahun 2025, rata-rata *inschiet* berfluktuasi pada level **4,61%** (puncak Q4: **5,11%**), merepresentasikan potensi pemborosan biaya cetak sebesar **Rp 22,13 Miliar s.d. Rp 24,56 Miliar / tahun**. Ketiadaan data granular per mesin dan *shift* memicu *downtime maintenance trial-and-error* hingga **> 1 shift per mesin** serta proses rekapitulasi data manual yang menumpuk. Implementasi DSS SIRINE 4.0 berhasil memangkas *inschiet* menjadi **4,34% di Q1** dan **3,33% di Q2 2026**, mengamankan penghematan riil sebesar **Rp 2,23 Miliar dalam 6 bulan** (proyeksi tahunan **Rp 6,82 Miliar / tahun**).
```

---

## 3. Visuals and Paired Business Insights

### Visual Layout Example
```markdown
![Grafik Baseline Inschiet Cetak per Kuartal 2025](../extracted_images/image1.png)
*Gambar 1.1: Grafik Distribusi Inschiet Cetak per Kuartal 2025 vs Garis Rata-rata Baseline 4,61% (Sumber: Rekap SIRINE & SAP ZPPRSIPPC0012)*

> ***Business Insight Gambar 1.1:***  
> Garis putus-putus oranye menunjukkan rata-rata baseline tahunan sebesar **4,61%**. Lonjakan batang Q4 ke level **5,11%** menegaskan bahwa ketiadaan sistem diagnostik berbasis kondisi mesin dan *shift* di lapangan mengakibatkan lonjakan volume pesanan selalu berbanding lurus dengan pembengkakan angka lembar rusak.
```

---

## 4. Open Mathematical Modeling (LaTeX Block)

### Calculation Template
```markdown
#### Skenario B: Berdasarkan Volume Order Aktual Tahun 2025 (177.636.930 Lembar)
$$\begin{aligned}
\text{Total Volume Order Aktual 2025} &= 177.636.930 \text{ Lembar Cetak} \\
\text{Jumlah Lembar Rusak Baseline (4,61\%)} &= 177.636.930 \times 4,61\% = \mathbf{8.189.062 \text{ Lembar Rusak / Tahun}} \\
\text{Nilai Kerugian Finansial Aktual 2025} &= 8.189.062 \text{ lembar} \times \text{Rp } 3.000 = \mathbf{\text{Rp } 24.567.186.000 \text{ / Tahun}} \\
&\approx \mathbf{\text{Rp } 24,56 \text{ Miliar / Tahun (atau Rp 2,05 Miliar / Bulan)}}
\end{aligned}$$
```

---

## 5. Structured Data Table with Verified Sources

### Table Template
```markdown
Tabel 1.2 Data Baseline Volume Produksi dan Inschiet Cetak Tahun 2025
| Parameter Data | Nilai / Angka | Satuan | Periode | Sumber Data Terverifikasi |
| :--- | :---: | :---: | :---: | :--- |
| **Total Order Produksi 2025** | **177.636.930** | Lembar Cetak | Tahun 2025 | Modul *SAP Production Order* (`ZPPRSIPPC0012`) |
| **Inschiet Kuartal 1 (Q1)** | **4,72%** | Persentase (%) | Jan – Mar 2025 | Rekap Verifikasi Mutu & SAP (`ZPPRSIPPC0012`) |
| **Inschiet Kuartal 2 (Q2)** | **3,97%** | Persentase (%) | Apr – Jun 2025 | Rekap Verifikasi Mutu & SAP (`ZPPRSIPPC0012`) |
| **Inschiet Kuartal 3 (Q3)** | **4,64%** | Persentase (%) | Jul – Sep 2025 | Rekap Verifikasi Mutu & SAP (`ZPPRSIPPC0012`) |
| **Inschiet Kuartal 4 (Q4)** | **5,11%** | Persentase (%) | Okt – Des 2025 | Rekap Verifikasi Mutu & SAP (`ZPPRSIPPC0012`) |
| **RATA-RATA BASELINE 2025** | **4,61%** | Persentase (%) | Tahun 2025 | Konsolidasi Tahunan SIRINE & SAP (`ZPPRSIPPC0012`) |
*(Sumber: Rekapitulasi Data Mutu Verifikasi & SAP ZPPRSIPPC0012)*
```
