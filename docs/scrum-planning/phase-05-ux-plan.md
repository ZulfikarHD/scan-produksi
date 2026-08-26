# UX Plan — Phase 05: Dashboard Analytics & 14-Day Production Graph (Beranda & Analitik)

> Created before implementation. This document is a hard constraint for all work in Phase 05.  
> Epic file: `docs/scrum-planning/phase-05-dashboard-analytics-14-day-chart.md`

---

## 1. Navigation Footprint

Perancangan antarmuka pada Phase 05 berfokus pada penyediaan pusat komando operasional (*Operational Command Center*) yang menyajikan gambaran menyeluruh hasil kerja pengemasan di PT Percetakan Dokumen Sekuriti (PT PDS). Halaman Beranda (Dashboard) dirancang sebagai pintu gerbang utama sistem setelah pengguna masuk (*login*), menggabungkan ringkasan indikator kinerja utama (*Key Performance Indicators* / KPI) harian, visualisasi analitik tren hasil kemas **14 hari terakhir**, distribusi performa shift dan mesin cetak, pintasan aksi cepat ke modul kerja inti, serta daftar aktivitas berkas laporan terbaru. Seluruh informasi disatukan dalam satu tata letak responsif yang padat informasi, mudah dipahami dalam sekali pandang (*single-glance clarity*), dan bebas dari kerumitan navigasi.

### Navigation Hierarchy & Routes Summary

- **Total Sidebar Menu Items untuk Phase 05**: **1 item** pada posisi teratas sidebar navigasi utama, yaitu menu **"Dashboard"** (dengan ikon *home* / *chart*, dapat diakses oleh Petugas Pengemasan dan Kepala Unit / Supervisor). Menu ini merupakan beranda utama dari 5 modul navigasi sistem (Dashboard, Update Database, Buat Laporan, Lihat Laporan, Data Petugas).
- **Total Dedicated Routes/Pages**: **1 rute utama**:
  - `GET /` (atau `GET /dashboard`): **Halaman Utama Beranda & Analitik Produksi** — Menampilkan seluruh komponen ringkasan KPI, grafik analitik 14 hari, widget distribusi shift dan mesin, tombol aksi cepat, serta tabel 5 laporan terbaru dalam satu tampilan terpadu (*single cohesive dashboard*).
- **Single Page Consolidation (Konsolidasi Antarmuka Tunggal)**:
  - Seluruh sub-fitur pada Phase 05 (US-5.1 KPI Cards, US-5.2 Grafik 14 Hari, US-5.3 Widget Shift & Mesin, US-5.4 Quick Action Hub & Recent Activity Feed) disatukan sepenuhnya di dalam **satu halaman Beranda** (`/dashboard`). Tidak ada pemecahan rute analitik ke halaman terpisah seperti `/dashboard/chart`, `/dashboard/machines`, atau `/analytics`.
- **Drawer / Sheet vs. Modal (Bebas Modal pada Dashboard Utama)**:
  - Halaman Dashboard tidak memerlukan modal dialog pembuatan data baru. Seluruh tombol pada *Quick Action Hub* bertindak sebagai tautan navigasi langsung (*direct action links*) menuju modul terkait (misalnya menuju alur pemindaian barcode pada `/reports/session`, impor data SAP pada `/update-database`, atau penelusuran arsip pada `/reports`).
  - Penyesuaian rentang waktu grafik (pilihan 7 Hari, 14 Hari [default], dan 30 Hari) serta pengubahan tipe grafik (Area, Garis, Batang) dikendalikan secara reaktif pada komponen kartu grafik melalui interaktivitas Alpine.js dan Livewire tanpa memicu pemuatan ulang halaman (*zero full-page reload*).
- **Backend-Only (No Dedicated UI Surface)**:
  - Layanan agregasi data time-series `DashboardAnalyticsService` yang menghitung total kuantitas kemas baik, total reject, jumlah PO unik, serta rasio kerusakan per hari.
  - Logika pembentukan deret waktu 14 hari berturut-turut (*CarbonPeriod sequence*) dengan pengisian nilai nol (*zero-fill*) untuk tanggal yang tidak memiliki aktivitas produksi (hari libur operasional atau jeda shift) agar visualisasi grafik tidak terputus atau menampilkan data kosong.
  - Strategi *caching* metrik dan grafik menggunakan penandaan tag `Cache::tags(['production_stats'])` dengan masa berlaku 5 menit, serta pembersihan cache otomatis saat ada laporan harian baru yang diserahkan atau diverifikasi.
  - Standardisasi perhitungan seluruh batas pergantian hari dan filter tanggal berbasis zona waktu resmi `Asia/Jakarta` (WIB).

---

## 2. Screen Inventory

Inventaris seluruh komponen layar, kartu informasi, panel grafik, dan tabel aktivitas pada Halaman Utama Beranda (Phase 05):

| Screen / Panel | Location | What's visible on first open | Further triggers | Click depth |
|---|---|---|---|---|
| **Layar 1: Halaman Utama Beranda & Analitik** | `/dashboard` (Menu "Dashboard") | **Header Beranda**: Teks sambutan dinamis berdasarkan waktu dan nama pengguna ("Selamat Pagi/Siang/Malam, [Nama]"), tanggal hari ini dalam format Bahasa Indonesia baku (`Asia/Jakarta`), jam digital real-time, serta badge penanda shift operasional yang sedang aktif (Shift 1 / Shift 2 / Shift 3).<br>**Baris 1 — Kartu KPI Produksi Hari Ini**: 5 kartu metrik responsif Flux UI (`<flux:card>`).<br>**Baris 2 — Hub Pintasan Aksi Cepat**: Baris kartu tombol akses cepat dengan ikon deskriptif sesuai hak akses peran pengguna.<br>**Baris 3 — Kartu Grafik Analitik Tren 14 Hari**: Panel visualisasi utama dengan ringkasan statistik (Rata-rata Harian, Hari Puncak, Total Akumulasi 14 Hari), tombol toggle rentang waktu, tombol toggle tipe visualisasi, dan grafik interaktif Chart.js.<br>**Baris 4 — Grid Distribusi Shift & Top Mesin**: 2 kartu bersisian berisi widget persentase kontribusi shift kerja dan tabel peringkat 5 mesin cetak terbanyak dikemas.<br>**Baris 5 — Tabel Aktivitas Laporan Terbaru**: Tabel 5 berkas laporan produksi terakhir yang diserahkan petugas. | Mengarahkan kursor pada grafik menampilkan kotak informasi (*tooltip*) rincian tanggal, kuantitas baik, reject, dan total PO; mengklik tombol toggle rentang waktu memperbarui dataset grafik secara reaktif; mengklik tombol aksi cepat mengarahkan ke modul yang dituju; mengklik baris laporan terbaru membuka detail laporan. | 0 (Landing page otomatis setelah login) atau 1 klik dari Sidebar menu "Dashboard" |
| **Komponen Kartu KPI Produksi Hari Ini (US-5.1)** | Bagian atas `/dashboard` | 5 kartu metrik ringkas:<br>1. **Total Kemas Hari Ini**: Angka total lembar hasil kemas baik (aksen hijau/emerald) dengan indikator persentase perbandingan terhadap hari kemarin.<br>2. **Total Reject Hari Ini**: Angka total lembar afkir/rusak (aksen merah/rose) beserta persentase tingkat kerusakan (*reject rate*).<br>3. **PO / Item Terscan**: Jumlah Nomor Order produksi unik yang telah diproses hari ini (aksen indigo).<br>4. **Laporan Masuk & Terverifikasi**: Jumlah laporan yang telah diserahkan dan status verifikasinya (aksen amber).<br>5. **Petugas Bertugas**: Jumlah operator kemas yang aktif pada shift berjalan (aksen sky/biru). | Pembaruan data otomatis saat terjadi pergantian laporan atau pemuatan ulang berkala. | 0 (Langsung terlihat pada viewport atas) |
| **Komponen Hub Pintasan Aksi Cepat (US-5.4)** | Baris kedua `/dashboard` | 4 tombol ubin (*action tiles*) akses cepat:<br>1. **Buat Laporan Baru (Scan Barcode)**: Tautan utama menuju `/reports/session` (aksen primer hijau/emerald).<br>2. **Update Database (Impor SAP)**: Tautan menuju `/update-database` (khusus Supervisor).<br>3. **Lihat Semua Laporan**: Tautan menuju penjelajah arsip laporan `/reports`.<br>4. **Kelola Data Petugas**: Tautan menuju `/petugas` (khusus Supervisor). | Mengklik tombol langsung membuka halaman modul yang bersangkutan tanpa perantara modal. | 1 klik menuju alur kerja utama |
| **Komponen Grafik Analitik 14 Hari Terakhir (US-5.2)** | Baris ketiga `/dashboard` | **Header Grafik**: Judul "Grafik Tren Produksi & Hasil Kemas", indikator periode tanggal aktif (contoh: "13 Agu 2026 - 26 Agu 2026").<br>**Badge Ringkasan Atas**: Rata-rata Produksi Harian, Hari Produksi Tertinggi (*Peak Day*), dan Total Akumulasi 14 Hari.<br>**Kontrol Grafik**: Pilihan rentang waktu (*Pills*: `7 Hari`, `14 Hari` [aktif], `30 Hari`), pilihan tipe grafik (`Area`, `Garis`, `Batang`).<br>**Kanvas Visualisasi**: Sumbu X (Tanggal dalam format `d M`), Sumbu Y (Kuantitas lembar), Seri 1 (Garis/Area hijau teal untuk Kemas Baik), Seri 2 (Garis/Batang merah rose untuk Reject). | Hover atau sentuhan pada titik data memunculkan tooltip interaktif berisi tanggal lengkap, jumlah lembar baik, jumlah reject, dan jumlah PO; klik toggle rentang waktu memuat data baru secara reaktif. | 0 klik untuk melihat; 1 klik untuk mengganti filter rentang waktu |
| **Komponen Widget Distribusi Shift (US-5.3)** | Kolom kiri baris keempat `/dashboard` | Kartu "Distribusi Volume per Shift (14 Hari)": Visualisasi diagram donat (*donut chart*) atau bilah kemajuan (*progress bars*) yang menampilkan persentase pembagian hasil kemas antara Shift 1 (Pagi - Biru langit), Shift 2 (Siang - Kuning amber), dan Shift 3 (Malam - Ungu), dilengkapi total lembar per shift. | Hover pada segmen diagram menampilkan detail lembar dan persentase kontribusi terhadap total volume pabrik. | 0 (Langsung terlihat saat menggulir halaman) |
| **Komponen Widget Top 5 Mesin Cetak (US-5.3)** | Kolom kanan baris keempat `/dashboard` | Kartu "Top 5 Mesin Cetak Terbanyak Dikemas (14 Hari)": Tabel peringkat ringkas (Peringkat 1 s/d 5, Kode Nomor Mesin, Total Lembar Kemas Baik, Persentase terhadap total produksi, dan status ketercapaian target). | Mengarahkan kursor pada baris mesin menampilkan rata-rata lembar per hari untuk mesin tersebut. | 0 (Langsung terlihat saat menggulir halaman) |
| **Komponen Tabel Laporan Terbaru (US-5.4)** | Baris kelima (bawah) `/dashboard` | Header "Laporan Produksi Terkini", tautan "Lihat Semua Laporan $\rightarrow$".<br>Tabel ringkas 5 laporan terakhir: Kolom Kode Laporan, Seri Produk badge (PCHT / MMEA / HPTL), Tanggal Kerja, Shift badge, Petugas Kemas (Nama & NP), Total Lembar, Status badge (Terkirim [amber] / Terverifikasi [hijau]), Tombol Aksi Cepat ("Lihat Detail", "Cetak"). | Mengklik tombol "Lihat Detail" membuka halaman rincian `/reports/{report_code}`; mengklik "Cetak" membuka lembar cetak standar A4 di tab baru. | 1 klik menuju berkas laporan spesifik |

---

## 3. User Journey Maps

Pemetaan alur kerja dan interaksi pengguna untuk skenario pemantauan kinerja, evaluasi hasil kemas, dan eksekusi tindakan cepat dari Beranda:

### Journey 1: Supervisor Memantau Kinerja dan Tren Produksi 14 Hari Terakhir
- **Goal**: Mengetahui kestabilan volume hasil kemas, mengevaluasi konsistensi output harian, dan mendeteksi adanya lonjakan atau penurunan produksi yang signifikan dalam 2 pekan terakhir.
- **Starts at**: Halaman Utama Beranda (`/dashboard`).
- **Steps**:
  1. Supervisor masuk ke sistem dan langsung melihat deretan kartu KPI pada bagian atas layar untuk mengetahui pencapaian hari ini.
  2. Supervisor menelaah grafik utama "Grafik Tren Produksi & Hasil Kemas (14 Hari Terakhir)".
  3. Supervisor mengarahkan kursor (*hover*) pada titik puncak grafik (hari dengan volume tertinggi) untuk melihat rincian tanggal, total lembar baik, dan jumlah PO yang terselesaikan pada hari tersebut.
  4. Supervisor mengklik opsi toggle "30 Hari" jika ingin membandingkan pola tren produksi dengan siklus bulanan sebelumnya.
- **Done**: Grafik memperbarui garis tren secara instan, supervisor memperoleh gambaran akurat mengenai kapasitas kemas aktual dan hari-hari sibuk tanpa perlu membuka laporan manual satu per satu.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 2: Supervisor Memeriksa Beban Kerja Antar Shift dan Utilisasi Mesin
- **Goal**: Menganalisis proporsi kontribusi output antara Shift 1, Shift 2, dan Shift 3 serta mengidentifikasi 5 mesin cetak dengan volume kemas tertinggi guna evaluasi alokasi tenaga kerja.
- **Starts at**: Halaman Utama Beranda (`/dashboard`).
- **Steps**:
  1. Supervisor menggulirkan layar ke area widget distribusi kinerja di bawah grafik utama.
  2. Supervisor membaca kartu "Distribusi Volume per Shift" untuk memastikan pembagian beban kerja antar shift berjalan seimbang (misalnya Shift 1: 42%, Shift 2: 38%, Shift 3: 20%).
  3. Supervisor memeriksa kartu "Top 5 Mesin Cetak Terbanyak Dikemas" untuk mengetahui mesin mana yang mendominasi antrean pengemasan pada periode 14 hari berjalan.
- **Done**: Supervisor mendapatkan data konkret untuk rapat evaluasi operasional harian dan dasar penentuan penambahan personil pada shift atau mesin tertentu.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 3: Petugas Kemas Memeriksa Shift Aktif dan Memulai Sesi Pemindaian Baru
- **Goal**: Memastikan identitas shift kerja yang berlaku dan langsung membuka sesi pemindaian barcode Kartu Mesin Produksi tanpa hambatan navigasi.
- **Starts at**: Halaman Utama Beranda (`/dashboard`).
- **Steps**:
  1. Petugas kemas masuk ke akun sistem dan melihat banner header yang menunjukkan shift operasional aktif saat ini (contoh: "Shift 1 - Pagi").
  2. Petugas mengklik tombol utama "Buat Laporan Baru (Scan Barcode)" pada area *Quick Action Hub*.
- **Done**: Sistem langsung mengarahkan petugas ke halaman inisiasi sesi laporan (`/reports/session`) dengan tanggal hari ini dan shift yang telah terpilih secara otomatis, siap untuk melanjutkan ke konsol pemindaian barcode.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 4: Supervisor Memeriksa dan Memverifikasi Laporan Terbaru Langsung dari Beranda
- **Goal**: Menemukan berkas laporan yang baru saja diserahkan oleh petugas shift sebelumnya dan langsung membuka lembar verifikasi resmi tanpa harus mencari di menu arsip.
- **Starts at**: Halaman Utama Beranda (`/dashboard`).
- **Steps**:
  1. Supervisor melihat tabel "Laporan Produksi Terkini" di bagian bawah beranda dan menemukan laporan berstatus "Terkirim" (*badge amber*).
  2. Supervisor mengklik tombol "Lihat Detail" pada baris laporan tersebut.
- **Done**: Sistem langsung membuka halaman rincian laporan (`/reports/{report_code}`), di mana supervisor dapat memeriksa daftar PO dan menekan tombol verifikasi.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 5: Supervisor Meninjau Rasio Reject dan Tingkat Kerusakan Kemas
- **Goal**: Memantau apakah persentase kerusakan lembar cetak (*reject rate*) berada dalam batas toleransi wajar (standar < 1.5%) atau memerlukan inspeksi fisik lanjutan di meja kontrol.
- **Starts at**: Halaman Utama Beranda (`/dashboard`).
- **Steps**:
  1. Supervisor memeriksa kartu KPI kedua "Total Reject Hari Ini" yang menampilkan angka absolut lembar reject dan persentase tingkat reject hari ini.
  2. Supervisor memeriksa garis merah (*reject series*) pada grafik 14 hari untuk melihat apakah terdapat lonjakan tingkat reject pada hari-hari tertentu.
- **Done**: Supervisor segera mengetahui jika terjadi anomali kualitas cetak atau kertas sekuriti dan dapat segera mengambil tindakan pencegahan pada lini produksi terkait.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

## 4. Indonesian UX Guardrails

Panduan perancangan antarmuka dan batasan pengalaman pengguna yang disesuaikan secara ketat dengan karakteristik operasional staf pabrik dan supervisor di lingkungan Unit Pengemasan PT PDS:

### Patience Thresholds (Batas Toleransi Langkah Kerja)

1. **Pemantauan Kinerja Rutin (Melihat KPI & Tren Grafik)**
   <br>
   Maksimal **0–1 klik**. Begitu pengguna berhasil masuk ke dalam sistem, seluruh metrik penting, grafik 14 hari, dan ringkasan shift harus langsung tersaji di layar tanpa mengharuskan pengguna menekan tombol filter atau membuka menu tambahan terlebih dahulu.

2. **Memulai Aktivitas Harian (Scan Barcode / Buat Laporan Baru)**
   <br>
   Maksimal **1 klik** dari Beranda melalui tombol aksi cepat berukuran besar dan kontras tinggi di area *Quick Action Hub*.

3. **Interaksi dan Filter Grafik (Ganti Periode 7 / 14 / 30 Hari atau Tipe Visualisasi)**
   <br>
   Maksimal **1 klik** dengan respon tampilan seketika (<100 ms) melalui pembaruan data Alpine.js tanpa ada jeda kedipan (*flicker*) atau pemuatan ulang halaman web (*full-page reload*).

4. **Menjangkau Laporan Tertentu untuk Pemeriksaan / Cetak Fisik**
   <br>
   Maksimal **1–2 langkah** melalui baris aksi cepat pada tabel laporan terkini di bagian bawah beranda.

---

### Cognitive Load Budget (Beban Kognitif Ringan & Terstruktur)

1. **Hierarki Tata Letak Vertikal yang Logis (*Top-Down Information Flow*)**
   <br>
   Informasi disusun berurutan dari yang paling mendesak dan spesifik hari ini hingga analitik makro:
   - *Level 1 (Paling Atas)*: Konteks Waktu, Banner Shift Aktif, dan Kartu Metrik KPI Hari Ini.
   - *Level 2*: Hub Pintasan Aksi Cepat (*Quick Actions*) untuk navigasi instan tugas kerja utama.
   - *Level 3 (Pusat)*: Grafik Tren Produksi & Hasil Kemas 14 Hari Terakhir sebagai pusat analisis visual.
   - *Level 4*: Widget Distribusi Performa (Proporsi Shift Kerja dan Peringkat 5 Mesin Terbanyak).
   - *Level 5 (Bawah)*: Tabel Riwayat 5 Laporan Terkini beserta tombol pintasan lihat dan cetak.

2. **Standarisasi Warna dan Semantik Status (*Consistent Color Coding*)**
   <br>
   - **Hijau (Emerald)**: Output Hasil Kemas Baik (*Good Output*), status laporan `Terverifikasi`, indikator peningkatan performa positif.
   - **Merah (Rose / Red)**: Lembar Reject / Afkir / Rusak, indikator penurunan volume, peringatan anomali.
   - **Biru / Indigo**: Shift 1 (Pagi), jumlah PO / item aktif, tautan informasi umum.
   - **Kuning / Amber**: Shift 2 (Siang), status laporan `Terkirim` (*Pending Verification*), perhatian operasional.
   - **Ungu (Purple)**: Shift 3 (Malam), badge seri produk sekuriti khusus.

3. **Format Angka, Tanggal, dan Bahasa Standar Indonesia**
   <br>
   - Format penulisan angka kuantitas menggunakan pemisah ribuan titik (contoh: `125.000 lembar`, bukan `125,000` atau `125000`).
   - Format tanggal pada judul dan kartu menggunakan penanggalan Bahasa Indonesia baku (contoh: `Rabu, 26 Agustus 2026`).
   - Label sumbu X pada grafik menggunakan singkatan bulan Indonesia yang ringkas (contoh: `13 Agu`, `14 Agu`, ..., `26 Agu`).
   - Waktu dan pergantian hari mengacu secara konsisten pada zona waktu resmi Indonesia Barat (`Asia/Jakarta` / WIB).

4. **Penanganan Kondisi Data Kosong (*Zero-State Graceful Handling*)**
   <br>
   Jika sistem baru pertama kali diinstal atau belum ada data laporan produksi dalam rentang 14 hari terakhir, sistem tidak boleh menampilkan grafik rusak atau area putih kosong. Sistem wajib menampilkan ilustrasi grafik datar dengan nilai `0 lembar`, teks pembimbing ramah ("Belum ada aktivitas produksi tercatat pada periode ini. Klik 'Buat Laporan Baru' untuk memulai pemindaian."), serta kartu KPI bernilai `0` tanpa pesan galat teknis.

---

### Trust Signals (Kejelasan dan Kepastian Data)

1. **Stempel Waktu Pembaruan Data (*Live Data Timestamp*)**
   <br>
   Menampilkan teks keterangan waktu sinkronisasi data yang jelas pada pojok kartu (contoh: *"Data diperbarui otomatis per 14:35 WIB"*), disertai tombol pembaruan halus (*soft refresh button*) agar pengguna yakin bahwa data yang mereka amati adalah kondisi terkini di lini pengemasan.

2. **Kotak Informasi Interaktif yang Jelas pada Grafik (*Context-Rich Tooltips*)**
   <br>
   Saat pengguna mengarahkan kursor pada titik grafik harian, kotak *tooltip* harus menampilkan rincian komprehensif tanpa singkatan teknis yang membingungkan:
   - Baris 1: Tanggal lengkap (contoh: `Selasa, 25 Agustus 2026`).
   - Baris 2: Hasil Kemas Baik: `18.500 lembar` (badge hijau).
   - Baris 3: Reject / Rusak: `120 lembar (0.64%)` (badge merah).
   - Baris 4: Total Order Diproses: `8 PO`.
   - Baris 5: Jumlah Laporan Shift: `3 Berkas`.

---

### Risks Specific to This Epic & Mitigations

| Sub-epic / Fitur | Risiko UX / Operasional | Mitigasi Desain & Teknis |
|---|---|---|
| **US-5.2 Grafik 14 Hari (Data Time-Series)** | Tanggal libur operasional atau hari tanpa produksi menyebabkan patahan garis grafik (*broken chart*) atau error komputasi. | Layanan `DashboardAnalyticsService` mengiterasi seluruh deret waktu 14 hari secara berurutan (*CarbonPeriod*) dan menerapkan mekanisme *zero-fill* (mengisi angka 0 untuk kuantitas baik dan reject jika tidak ada rekaman data pada tanggal tersebut). |
| **US-5.1 & US-5.2 Beban Query Agregasi** | Kueri agregasi SQL yang menghitung ribuan baris item produksi dapat memperlambat waktu pemuatan (*rendering*) halaman beranda. | Terapkan strategi penyimpanan singgah (*caching*) selama 5 menit menggunakan `Cache::tags(['production_stats'])` dengan invalidasi instan saat terjadi penambahan atau verifikasi laporan, serta optimasi indeks basis data pada kolom `report_date`. |
| **US-5.2 Keterbacaan Grafik pada Layar Kecil** | Label sumbu X dan grafik garis menjadi bertumpuk dan tidak terbaca saat diakses melalui perangkat tablet atau layar beresolusi rendah di meja kerja operator. | Konfigurasikan Chart.js agar secara otomatis melompati label (*auto-skip X-axis labels*), sediakan ruang sentuh interaktif yang responsif (*touch-friendly*), dan tampilkan 3 kartu ringkasan angka utama (Rata-rata, Hari Puncak, Total 14 Hari) di atas kanvas grafik. |
| **US-5.1 Pergantian Shift Malam (Shift 3)** | Produksi Shift 3 yang melewati batas tengah malam (pukul 00:00 - 07:00 WIB) berisiko terpotong atau tercatat di hari kalender yang berbeda. | Seluruh logika query dan pelaporan mengacu pada tanggal operasional laporan (`report_date`) dan zona waktu `Asia/Jakarta`, bukan tanggal timestamp pembuatan record acak di database. |
| **US-5.4 Akses Cepat Berdasarkan Peran Akun** | Petugas kemas mengklik tombol yang memerlukan hak akses supervisor (misalnya Impor SAP atau Kelola Petugas) dan mendapatkan pesan error 403. | Terapkan proteksi antarmuka kondisional (`@can` atau pemeriksaan role) pada *Quick Action Hub* sehingga tombol khusus Supervisor hanya muncul untuk akun Supervisor, sedangkan Petugas hanya melihat tombol yang relevan dengan tugasnya. |

---

## 5. Implementation Boundaries (Anti-Splitting Rules)

Bagian ini merupakan batasan keras bagi pengembang aplikasi dan sistem AI pelaksana mengenai hal-hal yang **TIDAK BOLEH** dilakukan saat mengimplementasikan Phase 05:

### Do Not Split — Combine Into One Surface:
- Seluruh komponen Beranda (Kartu KPI Hari Ini, Hub Pintasan Aksi Cepat, Grafik Analitik 14 Hari, Widget Distribusi Shift & Mesin, serta Tabel Laporan Terkini) **wajib disatukan di dalam satu file view dan komponen Livewire tunggal** (`app/Livewire/Dashboard/DashboardIndex.php` dan `resources/views/livewire/dashboard/index.blade.php`).
- **Dilarang keras** memecah Beranda menjadi beberapa rute terpisah seperti `/dashboard/analytics`, `/dashboard/kpi`, atau `/dashboard/overview`.

### Make a Tab/Toggle, Not a New Route:
- Pengubahan rentang waktu visualisasi grafik (`7 Hari`, `14 Hari`, `30 Hari`) serta pengubahan tipe grafik (`Area`, `Garis`, `Batang`) **wajib berupa kontrol toggle interaktif di dalam kartu grafik** yang dikelola via Alpine.js atau *Livewire wire:click properties*, **bukan berupa rute baru** seperti `/dashboard/7-days` atau `/dashboard/monthly`.

### Make a Shortcut Link, Not an Embedded Duplicate Module:
- Tombol-tombol pada *Quick Action Hub* berfungsi murni sebagai **tautan navigasi langsung** (`href="{{ route(...) }}"`) menuju rute modul yang sudah ada (`/reports/session`, `/update-database`, `/reports`, `/petugas`).
- **Dilarang keras** menyematkan formulir unggah berkas SAP, formulir scan barcode, atau modal input data petugas di dalam halaman Dashboard. Dashboard berfungsi murni sebagai penampil informasi dan penyedia navigasi cepat.

### Backend-Only — No Dedicated UI:
- **`DashboardAnalyticsService`**: Menangani kueri agregasi SQL mentah, kalkulasi perbandingan persentase terhadap hari kemarin, pembentukan array deret 14 hari dengan *zero-fill*, dan pengelompokan performa shift/mesin.
- **Strategi Caching**: Penggunaan penanda tag cache `production_stats` dan invalidasi otomatis saat event `DailyReportCreated` atau `DailyReportVerified` terjadi.
- **Standardisasi Zona Waktu**: Penanganan waktu `Asia/Jakarta` pada query builder dan format penanggalan Carbon.

### Strictly Forbidden:
- ❌ **Dilarang membuat sub-menu sidebar baru** untuk analitik (misal: "Grafik Produksi", "Statistik Mesin", "Analisis Shift"). Seluruh wawasan analitik harus langsung terlihat pada menu utama **"Dashboard"**.
- ❌ **Dilarang memuat library grafik eksternal yang berat via CDN** di luar bundel aset Vite/pnpm. Gunakan library Chart.js ringan yang terintegrasi rapi dengan komponen Alpine.js (`x-data="productionChart(...)"`).
- ❌ **Dilarang membuat rute dashboard terpisah untuk role Petugas vs Supervisor** (misal: `/dashboard/petugas` dan `/dashboard/supervisor`). Gunakan **satu rute `/dashboard` tunggal** yang secara dinamis dan elegan menyesuaikan elemen kartu aksi cepat serta visibilitas metrik berdasarkan peran pengguna yang sedang aktif.
- ❌ **Dilarang menggunakan istilah asing yang kaku** pada label antarmuka. Gunakan istilah operasional yang komunikatif dan lazim di Unit Pengemasan PT PDS (seperti *Hasil Kemas Baik*, *Lembar Reject*, *Nomor Order / No PO*, *Kartu Mesin*, *Shift 1 / 2 / 3*, dan *Kepala Unit / Supervisor*).
