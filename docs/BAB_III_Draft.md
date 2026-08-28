# BAB III
# PENGEMBANGAN PERANGKAT LUNAK

Bab ini menguraikan secara komprehensif metodologi, alur kerja, serta tahapan rekayasa perangkat lunak yang diterapkan dalam membangun Sistem Dokumentasi Hasil Produksi Harian Berbasis Web dengan Scan Barcode dan Grafik Performa 14 Hari Kerja. Objek penelitian ini berpusat pada Unit Pengemasan di PT Percetakan Dokumen Sekuriti, Karawang, yang bertanggung jawab atas proses akhir verifikasi kuantitas, pengemasan dokumen berpengaman tinggi, dan pelaporan hasil kerja harian kepada Kepala Unit / Supervisor. Seluruh tahapan pengembangan disusun secara terstruktur untuk menjawab permasalahan inefisiensi pencatatan manual, mengurangi potensi kesalahan manusia (*human error*), serta menghadirkan sistem pemantauan produktivitas harian yang transparan dan terukur.

---

## 3.1. Metode Pengembangan Perangkat Lunak

### 3.1.1 Nama dan Konsep Dasar Metode Pengembangan
Model pengembangan perangkat lunak yang diterapkan dalam penelitian ini adalah **Metode Waterfall** (*Classic Life Cycle*). Metode Waterfall merupakan pendekatan rekayasa perangkat lunak yang bersifat sekuensial dan sistematis, di mana kemajuan proses pengembangan diposisikan mengalir secara linier ke arah bawah melalui tahapan-tahapan yang terdefinisi secara baku. Dalam model ini, suatu fase pengembangan harus diselesaikan secara tuntas dan menghasilkan dokumen spesifikasi serta hasil kerja yang valid sebelum fase berikutnya dapat dimulai.

Prinsip dasar metode Waterfall menekankan pentingnya investigasi kebutuhan yang mendalam dan perancangan arsitektur yang matang sebelum tahap pembuatan aplikasi (coding) dilaksanakan. Pendekatan ini membantu menjaga kualitas aplikasi, memperjelas batasan ruang lingkup pekerjaan, serta memudahkan pemantauan kemajuan pengembangan sistem.

### 3.1.2 Alasan Pemilihan Metode Waterfall
Pemilihan metode Waterfall dalam pengembangan sistem dokumentasi hasil produksi harian ini didasarkan pada karakteristik operasional pabrik dan spesifikasi teknis sistem yang dikembangkan. Beberapa pertimbangan mendasar yang melandasi pemilihan metode ini antara lain:

Pertama, karakteristik kebutuhan fungsional sistem telah teridentifikasi secara jelas dan terperinci sejak tahap awal observasi lapangan di Unit Pengemasan PT Percetakan Dokumen Sekuriti. Format data pesanan dari sistem SAP, struktur Nomor PO yang tertuang dalam bentuk barcode tipe Code 128 pada Kartu Mesin Produksi, serta kebutuhan formulir pelaporan hasil kemas memiliki format dan aturan bisnis yang sudah baku. Kondisi kebutuhan yang pasti ini sangat selaras dengan sifat metode Waterfall yang tidak membutuhkan perubahan arsitektur secara berulang-ulang di tengah proses konstruksi.

Kedua, prosedur operasional standar (SOP) di area kerja pengemasan memiliki alur kerja yang stabil dan terikat pada hierarki pertanggungjawaban produksi. Hubungan kerja antara Petugas Pengemasan sebagai pelaksana pencatatan dan Kepala Unit / Supervisor sebagai pemeriksa dan penanggung jawab laporan harian menuntut kejelasan peran serta keabsahan data yang valid dan akurat. Dengan metode Waterfall, pembagian hak akses pengguna dan alur verifikasi laporan dapat dirancang dengan jelas sejak awal.

Ketiga, keterbatasan waktu pengerjaan dan sumber daya penelitian memerlukan manajemen proyek yang teratur dengan batas pencapaian (*milestones*) yang terukur. Dengan membagi pengembangan ke dalam tahapan sekuensial yang berurutan, evaluasi terhadap keberhasilan setiap fase, mulai dari analisis alur data, pemodelan basis data, hingga pengujian scanner, dapat dilakukan secara terstruktur tanpa menimbulkan tumpang tindih aktivitas.

Keempat, skala sistem yang terfokus pada digitalisasi pencatatan dokumen kemas dan pemantauan analitik 14 hari kerja memungkinkan seluruh spesifikasi dirancang secara komprehensif di awal. Pendekatan terstruktur ini meminimalkan risiko terjadinya pembengkakan ruang lingkup (*scope creep*) dan memastikan konsentrasi rekayasa tertuju pada stabilitas pembacaan scanner dan keakuratan kalkulasi data grafik.

### 3.1.3 Deskripsi Singkat Tahapan Metode Waterfall
Secara umum, metode Waterfall yang diterapkan dalam penelitian ini terdiri atas lima tahapan utama yang dilaksanakan secara berurutan:

1. **Analisis Kebutuhan (*Requirement Analysis*)**
   <br>
   Tahap pengumpulan data komprehensif melalui wawancara, observasi langsung di area kerja pengemasan, serta studi pustaka. Tahap ini menghasilkan dokumen spesifikasi kebutuhan sistem pengguna yang mencakup kebutuhan fungsional dan non-fungsional sebagai acuan utama perancangan.

2. **Perancangan Sistem (*System Design*)**
   <br>
   Tahap perancangan arsitektur perangkat lunak yang menerjemahkan kebutuhan sistem ke dalam representasi logis dan fisik. Aktivitas ini mencakup pemodelan use case diagram terpisah per aktor beserta tabel deskripsi skenarionya, activity diagram seluruh modul, perancangan Entity Relationship Diagram (ERD) notasi Chen, transformasi Logical Record Structure (LRS), spesifikasi tabel basis data, dan perancangan antarmuka pengguna.

3. **Implementasi (*Implementation*)**
   <br>
   Tahap penerjemahan seluruh dokumen perancangan ke dalam pembuatan aplikasi fungsional. Pada tahap ini, basis data dikonfigurasikan, logika backend dibangun menggunakan Laravel dengan arsitektur MVC, antarmuka reaktif diwujudkan melalui Livewire dan Flux UI, integrasi pembacaan barcode Code 128 dikonfigurasikan, dan modul kalkulasi analitik grafik 14 hari kerja diaktifkan.

4. **Pengujian Sistem (*Testing*)**
   <br>
   Tahap verifikasi dan validasi aplikasi untuk memastikan setiap fungsi bekerja sesuai dengan rancangan spesifikasi kebutuhan. Pengujian dilakukan menggunakan metode *Black Box Testing* yang menguji keakuratan masukan dan keluaran sistem pada skenario operasional nyata.

5. **Pemeliharaan (*Maintenance*)**
   <br>
   Tahap pemeliharaan sistem setelah aplikasi diterapkan di lingkungan kerja. Kegiatan ini mencakup pencadangan basis data berkala, penanganan kendala teknis yang timbul, pembaruan keamanan, serta penyesuaian logika sistem terhadap perubahan format data ekspor SAP.

### 3.1.4 Kesesuaian Metode dengan Penelitian
Penerapan metode Waterfall memberikan kepastian alur kerja yang mendukung tujuan penelitian. Dengan menetapkan analisis dan desain arsitektur secara matang sebelum pembuatan aplikasi dimulai, pengembang dapat mengantisipasi kendala integrasi antara data hasil scanner dan basis data master SAP. Setiap fungsi yang dikembangkan, mulai dari pembacaan barcode cepat hingga penyajian grafik performa 14 hari kerja, memiliki acuan pengujian yang jelas, sehingga aplikasi yang dihasilkan dapat berfungsi dengan stabil, akurat, dan mudah digunakan oleh petugas di Unit Pengemasan.

### 3.1.5 Ilustrasi Alur Metode Waterfall
Alur tahapan pengembangan perangkat lunak menggunakan model Waterfall diilustrasikan secara runtut pada Gambar 3.1 berikut:

```mermaid
flowchart TD
    A["1. Analisis Kebutuhan (Requirements Analysis)<br/>- Pengumpulan Data: Wawancara, Observasi, Studi Pustaka<br/>- Identifikasi Kebutuhan Fungsional & Non-Fungsional"] --> B["2. Perancangan Sistem (System Design)<br/>- Pemodelan UML: Use Case Diagram & Activity Diagram<br/>- Perancangan Data: ERD Chen, LRS, & Spesifikasi File Basis Data<br/>- Perancangan Antarmuka Pengguna (UI/UX Mockups)"]
    B --> C["3. Implementasi (Implementation)<br/>- Konfigurasi Environment: Windows 11, Laragon, MySQL<br/>- Pembuatan Aplikasi: Laravel, Livewire, Flux UI, Tailwind CSS<br/>- Integrasi Scanner Barcode Code 128 & Grafik Chart.js"]
    C --> D["4. Pengujian Sistem (Testing)<br/>- Pengujian Fungsionalitas Menggunakan Black Box Testing<br/>- Verifikasi Validitas Input Barcode, Peran Akun, & Agregasi"]
    D --> E["5. Pemeliharaan (Maintenance)<br/>- Pemeliharaan Korektif, Adaptif SAP, & Perfektif<br/>- Prosedur Pencadangan Basis Data & Optimasi Indeks"]

    classDef stage fill:#f8fafc,stroke:#334155,stroke-width:1.5px,color:#0f172a;
    class A,B,C,D,E stage;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.1: Alur Tahapan Metode Waterfall pada Pengembangan Sistem Dokumentasi Produksi Harian*

---

## 3.2 Tahap Pengembangan Perangkat Lunak

### A. Tahapan Perencanaan Kebutuhan

Tahap perencanaan kebutuhan merupakan langkah fondasional dalam siklus rekayasa perangkat lunak. Pada tahap ini, pengembang melakukan investigasi menyeluruh mengenai mekanisme operasional yang sedang berjalan pada Unit Pengemasan PT Percetakan Dokumen Sekuriti untuk mengidentifikasi akar permasalahan, memetakan kendala operasional, dan merumuskan batasan solusi teknologi yang tepat sasaran.

#### 1. Tahap Pengumpulan Data
Untuk memperoleh data yang akurat, komprehensif, dan relevan dengan kebutuhan sistem, proses pengumpulan data dilakukan melalui tiga teknik utama:

##### a. Wawancara
Wawancara mendalam (*in-depth interview*) dilakukan secara langsung dengan para pemangku kepentingan (*stakeholders*) di lingkungan Unit Pengemasan PT Percetakan Dokumen Sekuriti, antara lain:
1. **Kepala Unit / Supervisor Pengemasan (Bpk. Agus Soelistyo)**
   <br>
   Wawancara difokuskan pada prosedur operasional standar (SOP) pemeriksaan hasil produksi, mekanisme verifikasi laporan harian, kendala lambatnya penerimaan rekapitulasi kerja dari giliran kerja (*shift*) sebelumnya, serta kebutuhan terhadap instrumen pemantauan visual berupa grafik performa 14 hari kerja terakhir untuk mengevaluasi stabilitas produktivitas dan fluktuasi angka *reject*.
2. **Petugas Operator Pengemasan (Bpk. Umar Ibnu Zainal M. dan Operator Shift)**
   <br>
   Wawancara difokuskan pada alur teknis pencatatan fisik yang selama ini dilakukan di meja kerja pengemasan, kendala penyalinan manual dari Kartu Mesin Produksi ke lembar kertas folio, durasi waktu yang dihabiskan untuk mencatat data satu kartu mesin (mencapai 2 hingga 3 menit per pesanan), serta kesulitan dalam menelusuri riwayat pesanan yang terselip.

Dari hasil wawancara, disimpulkan bahwa pengguna sangat membutuhkan sistem berbasis web yang dapat mengisi data spesifikasi pesanan secara otomatis hanya dengan memindai barcode Nomor PO, sehingga petugas dapat berfokus pada aktivitas fisik pengemasan tanpa terbebani administrasi tulis tangan.

##### b. Observasi
Observasi lapangan dilakukan dengan mengamati secara langsung alur kerja fisik di ruang Unit Pengemasan PT Percetakan Dokumen Sekuriti di Karawang selama jam kerja aktif (meliputi Shift 1 Pagi, Shift 2 Siang, dan Shift 3 Malam).

Fokus pengamatan meliputi:
1. **Pemeriksaan Fisik Kartu Mesin Produksi:** Mengamati struktur atribut data yang tertera pada lembaran kartu mesin, termasuk keberadaan kode barcode linier tipe **Code 128** yang merepresentasikan Nomor PO (*Production Order*), nomor mesin cetak asal, jenis bahan kertas berpengaman, dan target kuantitas.
2. **Peralatan Kerja Tersedia:** Memeriksa ketersediaan fasilitas komputer PC dan perangkat *barcode scanner* genggam berbasis USB yang berada di meja kontrol pengemasan, yang selama ini belum terintegrasi ke dalam sistem pencatatan terpusat.
3. **Penanganan Dokumen Fisik:** Mengamati bagaimana lembaran formulir laporan harian ditumpuk, disalin ulang pada akhir shift, ditandatangani secara manual, dan disimpan dalam ordner arsip fisik yang rentan mengalami kerusakan fisik atau kehilangan data historis.

##### c. Studi Pustaka
Studi pustaka dilakukan dengan mengkaji berbagai referensi ilmiah, jurnal rekayasa perangkat lunak, buku teks, dan dokumentasi teknis yang relevan dengan topik penelitian.

Kajian pustaka mencakup:
1. Teori rekayasa perangkat lunak model Waterfall dan pemodelan visual menggunakan Unified Modeling Language (UML) dari literatur standar akademik.
2. Prinsip kerja simbologi barcode linier **Code 128** serta teknik penangkapan data masukan scanner melalui antarmuka *Human Interface Device* (HID) pada aplikasi web.
3. Arsitektur pengembangan web modern menggunakan kerangka kerja Laravel, Livewire untuk reaktivitas antarmuka, dan integrasi pustaka Chart.js untuk perenderan grafik analitik deret waktu (*time-series*).
4. Dokumentasi teknis mengenai standar keamanan dokumen sekuriti dan regulasi pengelolaan produksi dokumen sekuriti negara.

---

#### 2. Analisa Kebutuhan Sistem Pengguna

Kebutuhan fungsional mendefinisikan kapabilitas, proses kerja, dan layanan interaktif yang wajib disediakan oleh sistem aplikasi untuk memenuhi kebutuhan pengguna, adapun dalam sistem informasi hasil produksi harian unit pengemasan berbasis web yang penulis rancang terbagi menjadi dua hak akses user yaitu operator dan kepala unit yang memiliki masing-masing hak akses menu yang berbeda untuk menunjang operasional sistem informasi, berikut dibawah ini rincian dari hak akses user sistem informasi yang penulis buat:

**Kebutuhan Fungsional Hak Akses Operator**
1) Operator dapat melakukan login ke dalam sistem menggunakan Nomor Pegawai (NP) dan password yang terdaftar.
2) Operator dapat melakukan pemindaian barcode Nomor PO pada Kartu Mesin Produksi menggunakan barcode scanner berbasis USB (tipe Code 128).
3) Operator dapat melihat informasi detail spesifikasi produk (Nama Produk, Spesifikasi Kertas/Pengaman, Nomor Mesin Asal, dan Target Kuantitas) yang tampil secara otomatis dari master data SAP.
4) Operator dapat menginputkan kuantitas hasil kemas baik (siap didistribusikan) dan kuantitas reject (cacat/rusak).
5) Operator dapat menambahkan item ke dalam daftar penampungan draf laporan sementara (*staging table*) multi-item.
6) Operator dapat memfinalisasi dan menyimpan laporan hasil produksi harian ke basis data.
7) Operator dapat melihat riwayat laporan harian yang telah dibuat dan melakukan pengeditan korektif terhadap data item selama laporan masih berstatus draf.
8) Operator dapat mencetak lembaran fisik dokumen laporan produksi harian format standar kertas A4 lengkap dengan kolom tanda tangan.
9) Operator dapat memantau ringkasan metrik dashboard harian dan visualisasi grafik performa hasil produksi 14 hari kerja terakhir.
10) Operator dapat melakukan logout untuk mengakhiri sesi kerja operasional.

**Kebutuhan Fungsional Hak Akses Kepala Unit / Supervisor**
1) Kepala Unit / Supervisor dapat melakukan login ke dalam sistem menggunakan Nomor Pegawai (NP) dan password yang terdaftar.
2) Kepala Unit / Supervisor dapat mengelola data akun pengguna operator pengemasan (menambah petugas baru, melihat daftar seluruh petugas, mengubah data profil, menonaktifkan akun, serta mereset kata sandi).
3) Kepala Unit / Supervisor dapat mengunggah dan mengimpor berkas master data pesanan produksi hasil ekspor dari sistem SAP dalam format spreadsheet (`.xlsx` atau `.csv`).
4) Kepala Unit / Supervisor dapat melihat riwayat berkas impor master data SAP beserta jumlah baris data yang berhasil diproses.
5) Kepala Unit / Supervisor dapat melihat, mencari, dan meninjau seluruh riwayat laporan produksi harian dari seluruh shift kerja pengemasan.
6) Kepala Unit / Supervisor dapat memverifikasi dan menyetujui (*verify*) dokumen laporan produksi harian yang telah diserahkan oleh operator.
7) Kepala Unit / Supervisor dapat mencetak dokumen fisik laporan hasil produksi harian yang telah terverifikasi dalam format standar kertas A4.
8) Kepala Unit / Supervisor dapat menganalisis kartu metrik KPI harian serta mengevaluasi grafik performa 14 hari kerja terakhir guna memantau stabilitas pencapaian output dan fluktuasi reject.
9) Kepala Unit / Supervisor dapat melakukan logout untuk mengakhiri sesi kerja manajerial.

##### Kebutuhan Non-Fungsional (*Non-Functional Requirements*)
Kebutuhan non-fungsional menetapkan batasan kualitas teknis dan performa sistem:
1. **Performa dan Kecepatan Respon:** Sistem memberikan respon pencarian data produk pada saat barcode Nomor PO dipindai dalam waktu kurang dari 1 detik, serta merender kalkulasi grafik analitik 14 hari kerja dalam waktu di bawah 2 detik pada jaringan lokal.
2. **Keandalan dan Integritas Transaksi Data:** Sistem menerapkan validasi pencegahan duplikasi entri barcode Nomor PO dalam satu sesi laporan harian dan membungkus proses penyimpanan dalam mekanisme transaksi basis data (*database transaction*).
3. **Kompatibilitas Perangkat Keras dan Perangkat Lunak:** Sistem kompatibel secara langsung (*Plug-and-Play*) dengan perangkat *barcode scanner* USB yang membaca format Code 128, dapat diakses melalui peramban web modern (*Google Chrome, Mozilla Firefox, Microsoft Edge*), dan format cetak disesuaikan untuk kertas standar A4.
4. **Kemudahan Penggunaan (*Usability*):** Tata letak antarmuka dirancang bersih, intuitif, dan kontras tinggi untuk mempermudah operasional di meja kerja pengemasan.
5. **Keamanan Sistem dan Data:** Seluruh kata sandi pengguna dienkripsi dengan algoritma *Bcrypt*, dilengkapi proteksi terhadap serangan CSRF, SQL Injection, XSS, serta pembatasan hak akses berbasis peran pengguna (*role-based middleware*).

---

### B. Tahap Analisis Pemodelan

Tahap analisis pemodelan bertujuan untuk memvisualisasikan struktur fungsional sistem melalui Use Case Diagram terpisah per peran pengguna, aliran aktivitas proses (Activity Diagram), skema hubungan entitas berbasis notasi Chen (ERD), representasi struktur record logis (LRS), serta spesifikasi detail berkas tabel data sebelum pembuatan aplikasi dilaksanakan.

#### 1. Use Case Diagram
Use Case Diagram mendeskripsikan interaksi fungsional antara aktor (pengguna sistem) dengan fungsi-fungsi yang disediakan oleh sistem. Pada perancangan sistem ini, diagram use case dipisahkan menjadi dua diagram utama berdasarkan hak akses masing-masing aktor, yaitu **Use Case Petugas (Operator)** dan **Use Case Kepala Unit (Supervisor)**, di mana setiap fungsionalitas operasional memiliki relasi ketergantungan `<<include>>` terhadap proses Login Sistem.

##### a. Use Case Diagram Hak Akses Petugas (Operator)
Diagram use case untuk peran Petugas (Operator Pengemasan) dimodelkan pada Gambar 3.2:

```mermaid
flowchart LR
    subgraph SYSTEM ["Sistem Dokumentasi Hasil Produksi Harian"]
        direction TB
        UC_SCAN(["Scan Barcode & Entri Kemas"])
        UC_SAVE(["Simpan Laporan Harian"])
        UC_VIEW(["Lihat & Edit Laporan"])
        UC_PRINT(["Cetak Dokumen Laporan"])
        UC_DASH(["Pantau Dashboard & Grafik 14 Hari"])
        UC_LOGIN(["Login Sistem"])
    end

    ACT_OP((Petugas / Operator))

    ACT_OP --- UC_SCAN
    ACT_OP --- UC_SAVE
    ACT_OP --- UC_VIEW
    ACT_OP --- UC_PRINT
    ACT_OP --- UC_DASH

    UC_SCAN -. "<<include>>" .-> UC_LOGIN
    UC_SAVE -. "<<include>>" .-> UC_LOGIN
    UC_VIEW -. "<<include>>" .-> UC_LOGIN
    UC_PRINT -. "<<include>>" .-> UC_LOGIN
    UC_DASH -. "<<include>>" .-> UC_LOGIN

    classDef act fill:#f1f5f9,stroke:#0f172a,stroke-width:1.5px,color:#0f172a;
    classDef sys fill:#ffffff,stroke:#2563eb,stroke-width:2px,color:#0f172a;
    classDef uc fill:#f8fafc,stroke:#475569,stroke-width:1px,color:#0f172a;

    class ACT_OP act;
    class SYSTEM sys;
    class UC_SCAN,UC_SAVE,UC_VIEW,UC_PRINT,UC_DASH,UC_LOGIN uc;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.2: Use Case Diagram Hak Akses Petugas (Operator)*

##### b. Use Case Diagram Hak Akses Kepala Unit (Supervisor)
Diagram use case untuk peran Kepala Unit (Supervisor) dimodelkan pada Gambar 3.3:

```mermaid
flowchart LR
    subgraph SYSTEM ["Sistem Dokumentasi Hasil Produksi Harian"]
        direction TB
        UC_USER(["Manajemen Data Petugas"])
        UC_SAP(["Update Database (Import SAP)"])
        UC_VERIF(["Meninjau & Verifikasi Laporan"])
        UC_PRINT(["Cetak Dokumen Laporan"])
        UC_DASH(["Pantau Dashboard & Grafik 14 Hari"])
        UC_LOGIN(["Login Sistem"])
    end

    ACT_SPV((Kepala Unit / Supervisor))

    ACT_SPV --- UC_USER
    ACT_SPV --- UC_SAP
    ACT_SPV --- UC_VERIF
    ACT_SPV --- UC_PRINT
    ACT_SPV --- UC_DASH

    UC_USER -. "<<include>>" .-> UC_LOGIN
    UC_SAP -. "<<include>>" .-> UC_LOGIN
    UC_VERIF -. "<<include>>" .-> UC_LOGIN
    UC_PRINT -. "<<include>>" .-> UC_LOGIN
    UC_DASH -. "<<include>>" .-> UC_LOGIN

    classDef act fill:#f1f5f9,stroke:#0f172a,stroke-width:1.5px,color:#0f172a;
    classDef sys fill:#ffffff,stroke:#2563eb,stroke-width:2px,color:#0f172a;
    classDef uc fill:#f8fafc,stroke:#475569,stroke-width:1px,color:#0f172a;

    class ACT_SPV act;
    class SYSTEM sys;
    class UC_USER,UC_SAP,UC_VERIF,UC_PRINT,UC_DASH,UC_LOGIN uc;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.3: Use Case Diagram Hak Akses Kepala Unit (Supervisor)*

---

##### c. Tabel Deskripsi Skenario Use Case Sistem
Guna mendefinisikan secara terperinci mekanisme operasional setiap *use case*, berikut disajikan tabel deskripsi skenario use case lengkap (Tabel 3.1 s.d. Tabel 3.9):

*Tabel 3.1: Deskripsi Use Case Login Sistem*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Login Sistem |
| **Requirements** | Sistem harus dapat memvalidasi identitas pengguna berdasarkan kredensial Nomor Pegawai (NP) dan password yang dimasukkan untuk mengamankan hak akses fitur. |
| **Goal** | Pengguna (Petugas atau Supervisor) berhasil masuk ke dalam sistem dengan hak akses menu yang sesuai. |
| **Pre-Conditions** | Pengguna membuka peramban web dan berada pada halaman form login Sistem Dokumentasi Hasil Produksi Harian. |
| **Post-Conditions** | Sistem mengalihkan pengguna ke halaman Dashboard utama sesuai dengan hak akses masing-masing aktor. |
| **Failed end Condition** | Sistem menolak akses pengguna, tetap berada di halaman login, dan menampilkan notifikasi kesalahan autentikasi. |
| **Actors** | Petugas (Operator), Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. User memasukkan Nomor Pegawai (NP) dan password pada kolom yang disediakan.<br>2. User mengklik tombol "Masuk Sistem".<br>3. Sistem memvalidasi kecocokan data kredensial dengan database `users`.<br>4. Sistem berhasil mengautentikasi dan mengarahkan user ke halaman Dashboard utama. |
| **Alternate Flow / Invariant A** | A1. User mencentang opsi "Ingat Saya" sebelum menekan tombol login.<br>A2. Sistem memperpanjang durasi sesi autentikasi pengguna pada perangkat browser. |
| **Invariant B** | B1. User memasukkan NP yang tidak terdaftar atau password yang salah.<br>B2. Sistem mendeteksi kegagalan autentikasi.<br>B3. Sistem menampilkan pesan error "Kredensial Nomor Pegawai atau password tidak sesuai!". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.2: Deskripsi Use Case Scan Barcode & Entri Hasil Kemas*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Scan Barcode & Entri Kemas |
| **Requirements** | Sistem harus mampu menerima masukan hasil pemindaian scanner barcode Code 128 dari Kartu Mesin Produksi dan mengambil data spesifikasi pesanan dari master SAP secara instan. |
| **Goal** | Petugas dapat menginputkan data hasil kemas baik dan reject per pesanan produksi tanpa perlu mengetik manual spesifikasi barang. |
| **Pre-Conditions** | Petugas telah berhasil login dan berada pada halaman menu "Buat Laporan". |
| **Post-Conditions** | Rincian data kemas pesanan berhasil ditambahkan ke dalam tabel penampungan sementara (*staging table*). |
| **Failed end Condition** | Data pesanan gagal dimuat karena barcode Nomor PO belum diimpor ke dalam master data SAP. |
| **Actors** | Petugas (Operator) |
| **Main Flow / Basic Path** | 1. Petugas memilih giliran kerja operasional (Shift 1, Shift 2, atau Shift 3).<br>2. Petugas mengarahkan scanner ke barcode Code 128 pada Kartu Mesin Produksi.<br>3. Scanner membaca Nomor PO dan sistem mencari data pada tabel `production_orders`.<br>4. Sistem menampilkan Nama Produk, Spesifikasi Kertas/Pengaman, Nomor Mesin, dan Target Kuantitas.<br>5. Petugas menginputkan kuantitas kemas baik dan kuantitas reject.<br>6. Petugas menekan tombol "Tambah ke Daftar Laporan".<br>7. Sistem memvalidasi input dan memasukkan item ke tabel draf laporan sementara. |
| **Alternate Flow / Invariant A** | A1. Petugas memindai kartu mesin berikutnya untuk pesanan lain.<br>A2. Sistem menambahkan baris baru ke dalam tabel penampungan sementara tanpa menghapus item sebelumnya. |
| **Invariant B** | B1. Petugas memindai barcode yang belum terdaftar pada master SAP.<br>B2. Sistem membunyikan nada peringatan.<br>B3. Sistem menampilkan notifikasi "Nomor PO tidak terdaftar pada Master Data SAP! Silakan hubungi Supervisor". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.3: Deskripsi Use Case Simpan Laporan Harian*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Simpan Laporan Harian |
| **Requirements** | Sistem harus membangkitkan Nomor Laporan unik otomatis dan menyimpan seluruh transaksi hasil kemas ke dalam basis data. |
| **Goal** | Seluruh rekaman hasil pengemasan selama satu shift tersimpan secara permanen ke dalam basis data dan siap diverifikasi. |
| **Pre-Conditions** | Terdapat minimal satu baris item hasil kemas pada tabel penampungan sementara. |
| **Post-Conditions** | Dokumen laporan tersimpan pada tabel `daily_reports` dan `daily_report_items` dengan status awal "submitted/draft". |
| **Failed end Condition** | Proses penyimpanan gagal dan sistem melakukan *rollback* transaksi basis data. |
| **Actors** | Petugas (Operator) |
| **Main Flow / Basic Path** | 1. Petugas memeriksa rekapitulasi total item, total baik, dan total reject pada halaman form.<br>2. Petugas menekan tombol "Finalisasi & Simpan Laporan".<br>3. Sistem membangkitkan Nomor Laporan unik otomatis (`LAP-YYYYMMDD-XXXX`).<br>4. Sistem mengeksekusi transaksi penyimpanan header laporan dan rincian item ke database.<br>5. Sistem mengosongkan tabel sementara dan menampilkan notifikasi sukses disertai tombol cetak. |
| **Alternate Flow / Invariant A** | A1. Petugas memilih opsi "Simpan Sebagai Draft" untuk melanjutkan entri data nanti.<br>A2. Sistem menyimpan draf laporan dengan status `draft`. |
| **Invariant B** | B1. Petugas menekan tombol simpan saat tabel rincian item masih kosong.<br>B2. Sistem membatalkan proses penyimpanan.<br>B3. Sistem menampilkan peringatan "Daftar rincian item kemas tidak boleh kosong!". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.4: Deskripsi Use Case Lihat & Edit Laporan Harian*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Lihat & Edit Laporan |
| **Requirements** | Sistem menyajikan daftar riwayat laporan hasil produksi harian, menampilkan rincian item, dan memfasilitasi pengeditan korektif selama belum diverifikasi. |
| **Goal** | Pengguna dapat meninjau arsip laporan harian dan petugas dapat mengoreksi kesalahan entri angka kemas sebelum disahkan. |
| **Pre-Conditions** | Pengguna telah login dan mengakses menu "Lihat Laporan". |
| **Post-Conditions** | Sistem menyajikan tabel riwayat laporan atau data kuantitas item berhasil diperbarui di basis data. |
| **Failed end Condition** | Perubahan ditolak karena dokumen laporan telah dikunci oleh status verifikasi Supervisor. |
| **Actors** | Petugas (Operator), Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna membuka menu "Lihat Laporan".<br>2. Sistem menampilkan tabel daftar laporan lengkap dengan filter tanggal dan status.<br>3. Pengguna menekan tombol "Lihat" pada salah satu nomor laporan.<br>4. Sistem menampilkan rincian detail seluruh item pesanan.<br>5. (Khusus Petugas) Petugas mengoreksi angka kemas pada laporan berstatus draf dan menekan "Update Data".<br>6. Sistem memperbarui rekaman data dan memuat ulang rincian laporan. |
| **Alternate Flow / Invariant A** | A1. Pengguna memfilter daftar laporan berdasarkan rentang tanggal tertentu.<br>A2. Sistem menampilkan daftar laporan yang sesuai dengan filter secara dinamis. |
| **Invariant B** | B1. Petugas mencoba mengubah angka kemas pada laporan yang telah berstatus `verified`.<br>B2. Sistem mengunci seluruh field formulir.<br>B3. Sistem menampilkan peringatan "Laporan telah diverifikasi oleh Supervisor dan tidak dapat diubah!". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.5: Deskripsi Use Case Cetak Dokumen Laporan Produksi Harian (A4)*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Cetak Dokumen Laporan |
| **Requirements** | Sistem menyediakan fungsi perenderan format cetak fisik standar kertas A4 lengkap dengan kop perusahaan dan kolom pengesahan tanda tangan. |
| **Goal** | Lembar fisik laporan hasil produksi harian dapat dicetak rapi melalui printer untuk dokumentasi arsip kantor. |
| **Pre-Conditions** | Pengguna membuka detail dokumen laporan yang ingin dicetak. |
| **Post-Conditions** | Dokumen tercetak rapi pada media kertas A4 melalui perangkat printer. |
| **Failed end Condition** | Tampilan cetak berantakan atau terpotong akibat ketidaksesuaian pengaturan margin printer. |
| **Actors** | Petugas (Operator), Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna menekan tombol "Cetak Laporan" pada tampilan rincian laporan.<br>2. Sistem memicu stylesheet cetak khusus (`@media print`) yang menyembunyikan elemen navigasi web.<br>3. Sistem merender layout lembar cetak standar A4 lengkap dengan nomor laporan, tabel item, dan kolom tanda tangan Petugas serta Supervisor.<br>4. Pengguna memilih printer fisik dan mengeksekusi pencetakan dokumen. |
| **Alternate Flow / Invariant A** | A1. Pengguna memilih opsi "Save as PDF" pada dialog peramban web.<br>A2. Sistem menyimpan salinan berkas laporan digital ke komputer lokal. |
| **Invariant B** | B1. Terjadi kendala koneksi perangkat printer fisik.<br>B2. Dialog peramban web menampilkan status printer *offline*. |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.6: Deskripsi Use Case Monitoring Dashboard & Grafik Performa 14 Hari Kerja*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Pantau Dashboard & Grafik 14 Hari |
| **Requirements** | Sistem menyajikan visualisasi data grafik agregasi volume hasil kemas baik dan kuantitas reject selama 14 hari kerja aktif terakhir (WIB) secara interaktif. |
| **Goal** | Pengguna dapat memantau kartu metrik KPI harian dan mengevaluasi tren performa produktivitas pengemasan secara visual. |
| **Pre-Conditions** | Pengguna telah berhasil login ke dalam sistem. |
| **Post-Conditions** | Sistem memuat kartu metrik harian dan visualisasi grafik performa 14 hari kerja terakhir. |
| **Failed end Condition** | Grafik gagal dimuat karena kegagalan pemrosesan query agregasi deret waktu di server basis data. |
| **Actors** | Petugas (Operator), Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna mengakses menu Dashboard (Beranda).<br>2. Sistem melakukan agregasi data produksi hari berjalan untuk menghitung total kemas baik, reject, dan order terscan.<br>3. Sistem mengeksekusi query agregasi deret waktu untuk rentang 14 hari kerja aktif terakhir.<br>4. Sistem menampilkan kartu ringkasan metrik KPI harian.<br>5. Pustaka Chart.js merender visualisasi grafik tren performa perbandingan output baik terhadap reject.<br>6. Pengguna dapat mengarahkan kursor (*hover*) pada grafik untuk melihat rincian capaian tanggal tertentu. |
| **Alternate Flow / Invariant A** | A1. Pengguna mengarahkan kursor pada titik data tertentu di grafik.<br>A2. Sistem memunculkan *tooltip* interaktif yang memuat tanggal, volume kemas baik, dan jumlah reject. |
| **Invariant B** | B1. Belum ada rekaman data transaksi pada rentang 14 hari kerja.<br>B2. Sistem menampilkan visualisasi grafik garis awal nol (*zero-baseline*) dengan keterangan data kosong. |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.7: Deskripsi Use Case Manajemen Data Petugas Operator*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Manajemen Data Petugas |
| **Requirements** | Sistem menyediakan fasilitas bagi Supervisor untuk menambah, melihat, mengubah profil, menonaktifkan, dan mereset kata sandi akun operator pengemasan. |
| **Goal** | Data akun pengguna sistem terkelola secara terpusat, aman, dan tertib berdasarkan identitas Nomor Pegawai (NP). |
| **Pre-Conditions** | Kepala Unit (Supervisor) telah berhasil login ke dalam sistem. |
| **Post-Conditions** | Data akun petugas baru tersimpan, terbarui, atau dinonaktifkan dari basis data `users`. |
| **Failed end Condition** | Pendaftaran atau pembaruan akun gagal akibat duplikasi Nomor Pegawai (NP) atau email di basis data. |
| **Actors** | Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Supervisor mengklik menu "Data Petugas".<br>2. Sistem menampilkan tabel daftar seluruh akun pengguna terdaftar.<br>3. Supervisor menekan tombol "Tambah Petugas Baru" atau tombol aksi "Edit".<br>4. Sistem menampilkan modal dialog form isian data petugas.<br>5. Supervisor melengkapi isian NP, Nama Lengkap, Email, Peran, dan Kata Sandi.<br>6. Supervisor menekan tombol "Simpan Data".<br>7. Sistem memvalidasi data dan menyimpan perubahan ke tabel `users`.<br>8. Sistem memperbarui daftar tabel petugas dan menampilkan notifikasi sukses. |
| **Alternate Flow / Invariant A** | A1. Supervisor melakukan reset password akun petugas yang lupa kata sandi.<br>A2. Sistem memperbarui hash password baru pada akun petugas terkait. |
| **Invariant B** | B1. Supervisor mendaftarkan Nomor Pegawai (NP) yang sudah terdaftar sebelumnya.<br>B2. Sistem menolak proses penyimpanan.<br>B3. Sistem menampilkan pesan error "Nomor Pegawai (NP) sudah terdaftar dalam sistem!". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.8: Deskripsi Use Case Update Database (Import Master SAP)*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Update Database (Import SAP) |
| **Requirements** | Sistem menyediakan antarmuka pengunggahan berkas spreadsheet ekspor SAP (`.xlsx`/`.csv`) dan melakukan pembaruan massal (*bulk upsert*) ke basis data master. |
| **Goal** | Master data pesanan produksi SAP terbarui di basis data aplikasi sebagai referensi instan pemindaian barcode kartu mesin. |
| **Pre-Conditions** | Supervisor telah login dan memiliki berkas ekspor SAP yang valid di komputer lokal. |
| **Post-Conditions** | Data pesanan tersimpan di tabel `production_orders` dan riwayat tercatat di tabel `import_logs`. |
| **Failed end Condition** | Berkas ditolak karena format ekstensi tidak didukung atau struktur kolom wajib tidak sesuai standar. |
| **Actors** | Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Supervisor memilih menu "Update Database".<br>2. Sistem menampilkan formulir unggah berkas dan tabel riwayat impor berkas sebelumnya.<br>3. Supervisor memilih berkas ekspor SAP (`.xlsx` atau `.csv`) dari komputer lokal.<br>4. Supervisor menekan tombol "Proses & Impor ke Master Data".<br>5. Sistem memvalidasi format berkas dan memetakan struktur kolom pesanan.<br>6. Sistem melakukan *bulk upsert* data pesanan ke tabel `production_orders`.<br>7. Sistem mencatat rekam riwayat ke tabel `import_logs` dan menampilkan notifikasi sukses. |
| **Alternate Flow / Invariant A** | A1. Berkas berisi Nomor PO yang sudah ada di basis data dengan revisi target kuantitas.<br>A2. Sistem secara cerdas memperbarui data yang ada (*update*) tanpa menduplikasi rekaman data. |
| **Invariant B** | B1. Supervisor mengunggah berkas dengan tipe file yang tidak valid (misal: `.pdf` atau `.docx`).<br>B2. Sistem membatalkan proses unggah.<br>B3. Sistem menampilkan pesan error "Format berkas tidak valid! Hanya berkas .xlsx dan .csv yang diizinkan". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.9: Deskripsi Use Case Meninjau & Verifikasi Laporan Produksi*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Verifikasi Laporan |
| **Requirements** | Sistem menyediakan sarana bagi Supervisor untuk memeriksa kesesuaian fisik data kemas dan memberikan status pengesahan resmi (*verified*). |
| **Goal** | Dokumen laporan produksi harian resmi disahkan keabsahannya dan terkunci dari perubahan data lebih lanjut. |
| **Pre-Conditions** | Supervisor membuka rincian laporan harian yang berstatus draf/submitted. |
| **Post-Conditions** | Kolom `status` pada laporan berubah menjadi `verified` dan identitas supervisor tercatat sebagai pemeriksa. |
| **Failed end Condition** | Verifikasi gagal akibat gangguan konektivitas server basis data. |
| **Actors** | Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Supervisor membuka daftar laporan dan memilih dokumen yang berstatus belum diverifikasi.<br>2. Sistem menampilkan rincian detail seluruh item kemas baik dan reject.<br>3. Supervisor memeriksa kesesuaian data dengan kondisi fisik di lapangan.<br>4. Supervisor menekan tombol "Verifikasi & Setujui Laporan".<br>5. Sistem memperbarui status dokumen menjadi `verified` dan mencatat `supervisor_id`.<br>6. Sistem menampilkan notifikasi sukses bahwa laporan telah resmi disahkan. |
| **Alternate Flow / Invariant A** | A1. Supervisor menambahkan catatan koreksi operasional pada kolom `notes` sebelum menyetujui laporan.<br>A2. Catatan tersebut tersimpan permanen melekat pada dokumen laporan. |
| **Invariant B** | B1. Supervisor menemukan ketidaksesuaian angka kemas yang signifikan.<br>B2. Supervisor menunda verifikasi dan menginstruksikan operator untuk melakukan pengecekan ulang fisik. |
*(Sumber: Hasil Penelitian, 2026)*

---

#### 2. Activity Diagram
Activity Diagram memodelkan aliran aktivitas operasional dan logika transisi sistem pada masing-masing fitur utama dengan membagi peran ke dalam partisi jalur aktivitas (*swimlanes*):

##### a. Activity Diagram Login Sistem
Diagram ini menggambarkan alur kerja saat pengguna (Petugas atau Supervisor) melakukan autentikasi ke dalam sistem, dimulai dari pengisian form hingga inisialisasi sesi kerja yang valid:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Pengguna (Petugas / Supervisor)"]
        P_Start(( ))
        P_Buka(Buka Halaman<br/>Login)
        P_Input(Input Nomor Pegawai<br/>dan Password)
        P_Kirim(Klik Tombol<br/>Masuk Sistem)
        P_Dash(Tampil Halaman<br/>Dashboard)
        P_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Form(Tampilkan Form<br/>Login)
        S_Val{Validasi<br/>Kredensial?}
        S_Err(Tampilkan Pesan<br/>Kredensial Salah)
        S_Auth(Inisialisasi Sesi<br/>Pengguna)
    end

    P_Start --> P_Buka
    P_Buka --> S_Form
    S_Form --> P_Input
    P_Input --> P_Kirim
    P_Kirim --> S_Val
    S_Val -- Tidak Valid --> S_Err
    S_Err --> S_Form
    S_Val -- Valid --> S_Auth
    S_Auth --> P_Dash
    P_Dash --> P_End

    style P_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style P_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.4: Activity Diagram Login Sistem*

##### b. Activity Diagram Manajemen Data Petugas
Diagram ini memodelkan aktivitas pengelolaan data akun operator pengemasan oleh Supervisor, mencakup peninjauan daftar petugas, penambahan atau pembaruan profil melalui modal dialog terpusat, validasi keunikan Nomor Pegawai (NP), hingga penyimpanan rekaman data akun:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu<br/>Data Petugas)
        SP_Pilih(Pilih Aksi:<br/>Tambah / Edit / Status)
        SP_Input(Input Data Akun<br/>dan Password)
        SP_Simpan(Klik Tombol<br/>Simpan Data)
        SP_Lihat(Terima Pembaruan<br/>Daftar Petugas)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel<br/>Daftar Petugas)
        SS_Modal(Tampilkan Modal<br/>Form Petugas)
        SS_Val{Validasi<br/>Data & NP?}
        SS_Err(Tampilkan Notifikasi<br/>Galat Validasi)
        SS_Save(Simpan Rekaman<br/>ke Database)
        SS_Notif(Tutup Modal & Kirim<br/>Notifikasi Sukses)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_Pilih
    SP_Pilih --> SS_Modal
    SS_Modal --> SP_Input
    SP_Input --> SP_Simpan
    SP_Simpan --> SS_Val
    SS_Val -- Tidak Valid --> SS_Err
    SS_Err --> SS_Modal
    SS_Val -- Valid --> SS_Save
    SS_Save --> SS_Notif
    SS_Notif --> SP_Lihat
    SP_Lihat --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.5: Activity Diagram Manajemen Data Petugas*

##### c. Activity Diagram Update Database (Import Master SAP)
Diagram ini menggambarkan alur kerja pengunggahan berkas data pesanan produksi SAP oleh Supervisor, validasi format dan skema kolom berkas, proses parsing atribut pesanan, hingga pembaruan massal (*bulk upsert*) ke dalam tabel master sistem:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        S_Start(( ))
        S_Buka(Buka Menu<br/>Update Database)
        S_Pilih(Pilih Berkas<br/>Excel / CSV SAP)
        S_Upload(Klik Tombol<br/>Proses & Impor)
        S_Lihat(Terima Konfirmasi<br/>Data Terimpor)
        S_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SIS_Form(Tampilkan Form<br/>Upload & Riwayat)
        SIS_Val{Validasi Format<br/>& Skema Kolom?}
        SIS_Err(Tampilkan Pesan<br/>Format Tidak Sesuai)
        SIS_Parse(Parsing Baris Data<br/>Pesanan SAP)
        SIS_Upsert(Simpan Data ke<br/>Tabel Master Order)
        SIS_Notif(Catat Log Riwayat &<br/>Kirim Notifikasi)
    end

    S_Start --> S_Buka
    S_Buka --> SIS_Form
    SIS_Form --> S_Pilih
    S_Pilih --> S_Upload
    S_Upload --> SIS_Val
    SIS_Val -- Tidak Valid --> SIS_Err
    SIS_Err --> SIS_Form
    SIS_Val -- Valid --> SIS_Parse
    SIS_Parse --> SIS_Upsert
    SIS_Upsert --> SIS_Notif
    SIS_Notif --> S_Lihat
    S_Lihat --> S_End

    style S_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style S_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.6: Activity Diagram Update Database (Import SAP)*

##### d. Activity Diagram Buat Laporan (Scan Barcode & Entri Kemas)
Diagram ini memodelkan aktivitas operasional di area pengemasan saat Petugas memindai barcode Nomor PO pada Kartu Mesin Produksi menggunakan scanner, verifikasi instan data spesifikasi produk, pengisian kuantitas hasil kemas baik dan reject, hingga finalisasi penyimpanan laporan harian:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Petugas ["Petugas (Operator Kemas)"]
        P_Start(( ))
        P_Buka(Buka Menu Buat<br/>Laporan & Pilih Shift)
        P_Scan(Scan Barcode No PO<br/>pada Kartu Mesin)
        P_Input(Input Kuantitas Kemas<br/>Baik & Reject)
        P_Tambah(Klik Tambah ke<br/>Daftar Laporan)
        P_Cek{Ada Kartu Mesin<br/>Lain?}
        P_Simpan(Klik Finalisasi &<br/>Simpan Laporan)
        P_Lihat(Terima Konfirmasi<br/>Laporan Tersimpan)
        P_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Form(Tampilkan Form &<br/>Fokus Kolom Barcode)
        S_Cari{Cari No PO di<br/>Master SAP?}
        S_Err(Peringatan Nomor PO<br/>Tidak Terdaftar)
        S_Info(Tampilkan Spesifikasi<br/>Produk & Mesin)
        S_Stage(Tambahkan Item ke<br/>Tabel Sementara)
        S_Save(Generate No Laporan<br/>& Simpan ke Database)
        S_Notif(Tampilkan Notifikasi<br/>Sukses & Dialog Cetak)
    end

    P_Start --> P_Buka
    P_Buka --> S_Form
    S_Form --> P_Scan
    P_Scan --> S_Cari
    S_Cari -- Tidak Ditemukan --> S_Err
    S_Err --> S_Form
    S_Cari -- Ditemukan --> S_Info
    S_Info --> P_Input
    P_Input --> P_Tambah
    P_Tambah --> S_Stage
    S_Stage --> P_Cek
    P_Cek -- Ya --> P_Scan
    P_Cek -- Tidak --> P_Simpan
    P_Simpan --> S_Save
    S_Save --> S_Notif
    S_Notif --> P_Lihat
    P_Lihat --> P_End

    style P_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style P_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.7: Activity Diagram Buat Laporan dengan Scan Barcode*

##### e. Activity Diagram Manajemen dan Cetak Laporan
Diagram ini menggambarkan alur kerja peninjauan daftar riwayat laporan hasil produksi harian, proses pengeditan korektif data kemas oleh Petugas, proses persetujuan verifikasi oleh Supervisor, serta pencetakan lembar laporan fisik berformat standar A4 lengkap dengan kolom pengesahan tanda tangan:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Petugas ["Petugas (Operator Kemas)"]
        P_Start(( ))
        P_Buka(Buka Menu<br/>Lihat Laporan)
        P_Pilih(Pilih Nomor<br/>Laporan)
        P_Aksi{Pilih Aksi<br/>Pengelolaan?}
        P_Edit(Edit Kuantitas Kemas<br/>& Simpan)
        P_Cetak(Klik Tombol<br/>Cetak Laporan)
        P_End((( )))
    end

    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu<br/>Lihat Laporan)
        SP_Pilih(Pilih Nomor<br/>Laporan)
        SP_Aksi{Pilih Aksi<br/>Pengelolaan?}
        SP_Verif(Verifikasi & Setujui<br/>Laporan Harian)
        SP_Cetak(Klik Tombol<br/>Cetak Laporan)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Tampil(Tampilkan Tabel<br/>Daftar Laporan)
        S_Detail(Tampilkan Rincian<br/>Detail Item Kemas)
        S_Update(Update Perubahan Data<br/>ke Database)
        S_SetVerif(Ubah Status Laporan<br/>Menjadi Verified)
        S_Print(Render Format Cetak<br/>Standar Kertas A4)
    end

    P_Start --> P_Buka
    P_Buka --> S_Tampil
    SP_Start --> SP_Buka
    SP_Buka --> S_Tampil

    S_Tampil --> P_Pilih
    S_Tampil --> SP_Pilih
    P_Pilih --> S_Detail
    SP_Pilih --> S_Detail

    S_Detail --> P_Aksi
    P_Aksi -- Edit Data --> P_Edit
    P_Edit --> S_Update
    S_Update --> S_Tampil
    P_Aksi -- Cetak --> P_Cetak
    P_Cetak --> S_Print

    S_Detail --> SP_Aksi
    SP_Aksi -- Verifikasi --> SP_Verif
    SP_Verif --> S_SetVerif
    S_SetVerif --> S_Tampil
    SP_Aksi -- Cetak --> SP_Cetak
    SP_Cetak --> S_Print

    S_Print --> P_End
    S_Print --> SP_End

    style P_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style P_End fill:#ffffff,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.8: Activity Diagram Manajemen dan Cetak Laporan*

##### f. Activity Diagram Dashboard dan Visualisasi Grafik Performa 14 Hari Kerja
Diagram ini menjelaskan alur pemrosesan data riwayat produksi untuk menghasilkan kartu metrik dan grafik analitik pada halaman beranda sistem, mulai dari inisialisasi rentang 14 hari kerja aktif terakhir (WIB), eksekusi query agregasi basis data, kalkulasi metrik capaian produksi, hingga penampilan visualisasi grafik garis dan batang yang interaktif:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Pengguna (Petugas / Supervisor)"]
        U_Start(( ))
        U_Buka(Mengakses Halaman<br/>Dashboard / Beranda)
        U_Lihat(Meninjau Metrik KPI &<br/>Grafik 14 Hari Kerja)
        U_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Filter(Filter Rentang 14 Hari<br/>Kerja Aktif Terakhir)
        S_Query(Eksekusi Query Agregasi<br/>Kemas Baik & Reject)
        S_Hitung(Kalkulasi Rata-Rata &<br/>Capaian Puncak)
        S_KPI(Render Kartu Metrik<br/>KPI Harian)
        S_Grafik(Render Grafik Analitik<br/>Performa 14 Hari)
    end

    U_Start --> U_Buka
    U_Buka --> S_Filter
    S_Filter --> S_Query
    S_Query --> S_Hitung
    S_Hitung --> S_KPI
    S_KPI --> S_Grafik
    S_Grafik --> U_Lihat
    U_Lihat --> U_End

    style U_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style U_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.9: Activity Diagram Dashboard dan Visualisasi Grafik Performa 14 Hari Kerja*

---

#### 3. Entity Relationship Diagram (ERD)
Entity Relationship Diagram (ERD) memodelkan keterhubungan konseptual antar-entitas data dalam sistem dokumentasi hasil produksi harian. Pemodelan ERD dirancang menggunakan **Notasi Chen** yang merepresentasikan entitas dalam bentuk persegi panjang (*rectangles*), relasi antar-entitas dalam bentuk belah ketupat (*diamonds*), serta atribut data dalam bentuk elips (*ovals*) dengan garis bawah penanda kunci utama (*Primary Key*).

Diagram ERD notasi Chen disajikan pada Gambar 3.10:

```mermaid
flowchart TD
    subgraph USR_GRP ["Entitas User"]
        E_USER["User"]
        A_U1(["<u>id (PK)</u>"])
        A_U2(["np (UK)"])
        A_U3(["name"])
        A_U4(["email (UK)"])
        A_U5(["password"])
        A_U6(["role"])
        A_U7(["is_active"])
        A_U8(["last_login_at"])
        E_USER --- A_U1
        E_USER --- A_U2
        E_USER --- A_U3
        E_USER --- A_U4
        E_USER --- A_U5
        E_USER --- A_U6
        E_USER --- A_U7
        E_USER --- A_U8
    end

    subgraph PO_GRP ["Entitas Production Order"]
        E_PO["Production_Order"]
        A_P1(["<u>id (PK)</u>"])
        A_P2(["po_number (UK)"])
        A_P3(["product_code"])
        A_P4(["product_name"])
        A_P5(["customer_name"])
        A_P6(["specification"])
        A_P7(["paper_type"])
        A_P8(["machine_number"])
        A_P9(["target_quantity"])
        A_P10(["unit"])
        A_P11(["order_date"])
        A_P12(["status"])
        E_PO --- A_P1
        E_PO --- A_P2
        E_PO --- A_P3
        E_PO --- A_P4
        E_PO --- A_P5
        E_PO --- A_P6
        E_PO --- A_P7
        E_PO --- A_P8
        E_PO --- A_P9
        E_PO --- A_P10
        E_PO --- A_P11
        E_PO --- A_P12
    end

    subgraph DR_GRP ["Entitas Daily Report"]
        E_DR["Daily_Report"]
        A_D1(["<u>id (PK)</u>"])
        A_D2(["report_number (UK)"])
        A_D3(["report_date"])
        A_D4(["shift"])
        A_D5(["total_items_count"])
        A_D6(["total_good_quantity"])
        A_D7(["total_reject_quantity"])
        A_D8(["notes"])
        A_D9(["status"])
        E_DR --- A_D1
        E_DR --- A_D2
        E_DR --- A_D3
        E_DR --- A_D4
        E_DR --- A_D5
        E_DR --- A_D6
        E_DR --- A_D7
        E_DR --- A_D8
        E_DR --- A_D9
    end

    subgraph DRI_GRP ["Entitas Daily Report Item"]
        E_DRI["Daily_Report_Item"]
        A_I1(["<u>id (PK)</u>"])
        A_I2(["po_number"])
        A_I3(["product_name"])
        A_I4(["specification"])
        A_I5(["machine_number"])
        A_I6(["good_quantity"])
        A_I7(["reject_quantity"])
        A_I8(["unit"])
        A_I9(["item_notes"])
        E_DRI --- A_I1
        E_DRI --- A_I2
        E_DRI --- A_I3
        E_DRI --- A_I4
        E_DRI --- A_I5
        E_DRI --- A_I6
        E_DRI --- A_I7
        E_DRI --- A_I8
        E_DRI --- A_I9
    end

    subgraph LOG_GRP ["Entitas Import Log"]
        E_LOG["Import_Log"]
        A_L1(["<u>id (PK)</u>"])
        A_L2(["file_name"])
        A_L3(["total_rows"])
        A_L4(["imported_rows"])
        E_LOG --- A_L1
        E_LOG --- A_L2
        E_LOG --- A_L3
        E_LOG --- A_L4
    end

    R_IMP{"mengimpor"}
    R_CATAT{"mencatat"}
    R_VERIF{"memverifikasi"}
    R_MUAT{"memuat"}
    R_ACUAN{"menjadi acuan"}
    R_LOG{"merekam log"}

    E_USER ---|1| R_IMP ---|M| E_PO
    E_USER ---|1| R_CATAT ---|M| E_DR
    E_USER ---|1| R_VERIF ---|M| E_DR
    E_USER ---|1| R_LOG ---|M| E_LOG
    E_DR ---|1| R_MUAT ---|M| E_DRI
    E_PO ---|1| R_ACUAN ---|M| E_DRI
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.10: Entity Relationship Diagram (ERD) Notasi Chen Sistem Dokumentasi Produksi Harian*

Kardinalitas dan hubungan relasional dalam ERD dijelaskan sebagai berikut:
1. **Relasi `User` ke `Production_Order` (mengimpor):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Supervisor dapat mengimpor banyak berkas rekaman pesanan produksi SAP.
2. **Relasi `User` ke `Daily_Report` (mencatat):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Petugas dapat mencatat banyak laporan hasil kemas harian.
3. **Relasi `User` ke `Daily_Report` (memverifikasi):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Supervisor dapat memverifikasi banyak dokumen laporan harian.
4. **Relasi `User` ke `Import_Log` (merekam log):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Supervisor dapat memiliki banyak catatan log riwayat unggahan berkas SAP.
5. **Relasi `Daily_Report` ke `Daily_Report_Item` (memuat):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu dokumen laporan harian memuat satu atau banyak rincian item hasil kemas.
6. **Relasi `Production_Order` ke `Daily_Report_Item` (menjadi acuan):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu rekaman pesanan produksi SAP menjadi acuan data bagi satu atau banyak rincian item laporan harian.

---

#### 4. Logical Record Structure (LRS)
Logical Record Structure (LRS) dibentuk dari hasil transformasi diagram relasi entitas (ERD) ke dalam bentuk struktur rekaman tabel relasional yang telah melalui proses penentuan kunci utama (*Primary Key*) dan penempatan kunci tamu (*Foreign Key*).

Aturan transformasi ERD ke LRS yang diterapkan meliputi:
1. Hubungan kardinalitas *One-to-Many* (1:M) ditransformasikan dengan menempatkan atribut *Primary Key* dari entitas di sisi "1" (One) menjadi atribut *Foreign Key* pada entitas di sisi "M" (Many).
2. Seluruh atribut dari entitas diubah menjadi elemen data (*fields*) yang memiliki tipe data dan panjang presisi pada tingkat basis data fisik.

Struktur LRS Sistem Dokumentasi Hasil Produksi Harian disajikan pada Gambar 3.11:

```mermaid
classDiagram
    class USERS {
        *id (PK) : BIGINT
        np : VARCHAR(50) [UK]
        name : VARCHAR(100)
        email : VARCHAR(100) [UK]
        password : VARCHAR(255)
        role : ENUM('supervisor', 'petugas')
        is_active : BOOLEAN
        last_login_at : TIMESTAMP
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class PRODUCTION_ORDERS {
        *id (PK) : BIGINT
        po_number : VARCHAR(50) [UK]
        product_code : VARCHAR(50)
        product_name : VARCHAR(150)
        customer_name : VARCHAR(150)
        specification : TEXT
        paper_type : VARCHAR(100)
        machine_number : VARCHAR(50)
        target_quantity : INT
        unit : VARCHAR(20)
        order_date : DATE
        status : ENUM('ready', 'in_progress', 'completed')
        #imported_by (FK) : BIGINT
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class DAILY_REPORTS {
        *id (PK) : BIGINT
        report_number : VARCHAR(30) [UK]
        report_date : DATE
        shift : ENUM('shift_1', 'shift_2', 'shift_3')
        #user_id (FK) : BIGINT
        #supervisor_id (FK) : BIGINT
        total_items_count : INT
        total_good_quantity : INT
        total_reject_quantity : INT
        notes : TEXT
        status : ENUM('draft', 'submitted', 'verified')
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class DAILY_REPORT_ITEMS {
        *id (PK) : BIGINT
        #daily_report_id (FK) : BIGINT
        #production_order_id (FK) : BIGINT
        po_number : VARCHAR(50)
        product_name : VARCHAR(150)
        specification : TEXT
        machine_number : VARCHAR(50)
        good_quantity : INT
        reject_quantity : INT
        unit : VARCHAR(20)
        item_notes : VARCHAR(255)
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class IMPORT_LOGS {
        *id (PK) : BIGINT
        file_name : VARCHAR(255)
        total_rows : INT
        imported_rows : INT
        #user_id (FK) : BIGINT
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    USERS "1" --> "0..*" PRODUCTION_ORDERS : imported_by
    USERS "1" --> "0..*" DAILY_REPORTS : user_id / supervisor_id
    USERS "1" --> "0..*" IMPORT_LOGS : user_id
    DAILY_REPORTS "1" --> "1..*" DAILY_REPORT_ITEMS : daily_report_id
    PRODUCTION_ORDERS "1" --> "0..*" DAILY_REPORT_ITEMS : production_order_id
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.11: Logical Record Structure (LRS) Sistem Dokumentasi Produksi Harian*

---

#### 5. Spesifikasi File
Spesifikasi file menjelaskan rincian teknis dari setiap tabel basis data yang digunakan dalam sistem, mencakup nama field, tipe data, panjang ukuran data, dan keterangan batasannya:

##### a. Spesifikasi File `users`
- **Nama File / Tabel:** `users`
- **Akronim File:** USR
- **Fungsi File:** Menyimpan data akun pengguna, peran hak akses, dan kredensial login
- **Tipe File:** File Master
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `id`
- **Kunci Unik (*Unique Key*):** `np`, `email`
- **Struktur Tabel:** Disajikan pada Tabel 3.10

*Tabel 3.10: Spesifikasi File Tabel `users`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Pengguna | `id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nama Lengkap | `name` | VARCHAR | 100 | Not Null |
| 3 | Nomor Pegawai | `np` | VARCHAR | 50 | Unique, Not Null (Kredensial Login) |
| 4 | Alamat Email | `email` | VARCHAR | 100 | Unique, Not Null |
| 5 | Kata Sandi | `password` | VARCHAR | 255 | Not Null (Terenkripsi Bcrypt) |
| 6 | Peran Akun | `role` | ENUM | - | Value: 'supervisor', 'petugas' |
| 7 | Status Keaktifan | `is_active` | BOOLEAN | 1 | Default: true (1) |
| 8 | Login Terakhir | `last_login_at` | TIMESTAMP | - | Nullable |
| 9 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 10 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### b. Spesifikasi File `production_orders`
- **Nama File / Tabel:** `production_orders`
- **Akronim File:** PO
- **Fungsi File:** Menyimpan data master pesanan produksi hasil ekspor dari sistem SAP
- **Tipe File:** File Master
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `id`
- **Kunci Unik (*Unique Key*):** `po_number`
- **Kunci Tamu (*Foreign Key*):** `imported_by` (Relasi ke `users.id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.11

*Tabel 3.11: Spesifikasi File Tabel `production_orders`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Pesanan | `id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nomor PO / Barcode | `po_number` | VARCHAR | 50 | Unique, Not Null (Simbologi Code 128) |
| 3 | Kode Produk | `product_code` | VARCHAR | 50 | Index, Not Null |
| 4 | Nama Produk | `product_name` | VARCHAR | 150 | Not Null |
| 5 | Nama Instansi Pemesan | `customer_name` | VARCHAR | 150 | Not Null |
| 6 | Spesifikasi Teknis | `specification` | TEXT | - | Not Null (Bahan Kertas, Pengaman) |
| 7 | Jenis Kertas | `paper_type` | VARCHAR | 100 | Nullable |
| 8 | Nomor Mesin Asal | `machine_number` | VARCHAR | 50 | Nullable (Identitas Mesin Cetak) |
| 9 | Target Kuantitas | `target_quantity` | INT UNSIGNED | 10 | Not Null |
| 10 | Satuan Hitung | `unit` | VARCHAR | 20 | Default: 'lembar' |
| 11 | Tanggal Pesanan | `order_date` | DATE | - | Not Null |
| 12 | Status Pengerjaan | `status` | ENUM | - | Value: 'ready', 'in_progress', 'completed' |
| 13 | ID Pengimpor | `imported_by` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.id` |
| 14 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 15 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### c. Spesifikasi File `daily_reports`
- **Nama File / Tabel:** `daily_reports`
- **Akronim File:** DREP
- **Fungsi File:** Menyimpan data header laporan hasil pengemasan produksi harian
- **Tipe File:** File Transaksi
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `id`
- **Kunci Unik (*Unique Key*):** `report_number`
- **Kunci Tamu (*Foreign Key*):** `user_id`, `supervisor_id` (Relasi ke `users.id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.12

*Tabel 3.12: Spesifikasi File Tabel `daily_reports`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Laporan | `id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nomor Dokumen Laporan | `report_number` | VARCHAR | 30 | Unique, Not Null (`LAP-YYYYMMDD-XXXX`) |
| 3 | Tanggal Pengemasan | `report_date` | DATE | - | Index, Not Null |
| 4 | Giliran Kerja | `shift` | ENUM | - | Value: 'shift_1', 'shift_2', 'shift_3' |
| 5 | ID Petugas Pelaksana | `user_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.id` |
| 6 | ID Supervisor Pemeriksa | `supervisor_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.id`, Nullable |
| 7 | Total Jenis Order | `total_items_count` | INT UNSIGNED | 5 | Default: 0 |
| 8 | Total Hasil Kemas Baik | `total_good_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 9 | Total Kuantitas Reject | `total_reject_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 10 | Catatan Operasional | `notes` | TEXT | - | Nullable |
| 11 | Status Verifikasi | `status` | ENUM | - | Value: 'draft', 'submitted', 'verified' |
| 12 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 13 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### d. Spesifikasi File `daily_report_items`
- **Nama File / Tabel:** `daily_report_items`
- **Akronim File:** DRIT
- **Fungsi File:** Menyimpan baris rincian item hasil kemas per pesanan produksi
- **Tipe File:** File Transaksi (Detail)
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `id`
- **Kunci Tamu (*Foreign Key*):** `daily_report_id` (ke `daily_reports.id`), `production_order_id` (ke `production_orders.id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.13

*Tabel 3.13: Spesifikasi File Tabel `daily_report_items`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Rincian Item | `id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | ID Header Laporan | `daily_report_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `daily_reports.id` (Cascade) |
| 3 | ID Pesanan SAP | `production_order_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `production_orders.id` |
| 4 | Nomor PO Snapshot | `po_number` | VARCHAR | 50 | Index, Not Null |
| 5 | Nama Produk Snapshot | `product_name` | VARCHAR | 150 | Not Null |
| 6 | Spesifikasi Snapshot | `specification` | TEXT | - | Not Null |
| 7 | Nomor Mesin Snapshot | `machine_number` | VARCHAR | 50 | Nullable |
| 8 | Jumlah Kemas Baik | `good_quantity` | INT UNSIGNED | 10 | Not Null |
| 9 | Jumlah Kemas Reject | `reject_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 10 | Satuan Hitung | `unit` | VARCHAR | 20 | Default: 'lembar' |
| 11 | Catatan Khusus Item | `item_notes` | VARCHAR | 255 | Nullable |
| 12 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 13 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### e. Spesifikasi File `import_logs`
- **Nama File / Tabel:** `import_logs`
- **Akronim File:** IMPL
- **Fungsi File:** Menyimpan catatan riwayat aktivitas pengunggahan berkas ekspor master SAP
- **Tipe File:** File Log Riwayat
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `id`
- **Kunci Tamu (*Foreign Key*):** `user_id` (Relasi ke `users.id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.14

*Tabel 3.14: Spesifikasi File Tabel `import_logs`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Riwayat Impor | `id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nama Berkas Unggahan | `file_name` | VARCHAR | 255 | Not Null |
| 3 | Total Baris Berkas | `total_rows` | INT UNSIGNED | 8 | Not Null |
| 4 | Jumlah Baris Berhasil | `imported_rows` | INT UNSIGNED | 8 | Not Null |
| 5 | ID Pengunggah | `user_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.id` |
| 6 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 7 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

---

### C. Implementasi

Tahap implementasi merupakan fase eksekusi rekayasa di mana seluruh spesifikasi kebutuhan, diagram pemodelan UML, skema basis data, dan rancangan antarmuka diterjemahkan ke dalam pembuatan aplikasi fungsional yang dapat dijalankan secara nyata pada lingkungan kerja operasional.

#### 1. Lingkungan Pengembangan Perangkat Lunak
Pelaksanaan implementasi didukung oleh konfigurasi lingkungan perangkat keras dan perangkat lunak yang disajikan pada Tabel 3.15 dan Tabel 3.16:

*Tabel 3.15: Spesifikasi Lingkungan Perangkat Keras (Hardware Environment)*
| No | Komponen Perangkat Keras | Spesifikasi Teknis Lingkungan Kerja | Peruntukan / Fungsi |
|---|---|---|---|
| 1 | **Komputer Pengembang** | Prosesor AMD Ryzen 5 / Intel Core i5, RAM 16 GB DDR4, Penyimpanan SSD 512 GB NVMe | Pembuatan program, pengujian unit, kompilasi aset frontend, dan simulasi server |
| 2 | **Komputer Klien (Unit Pengemasan)** | PC Desktop dengan prosesor Intel Celeron / Core i3, RAM 4 GB DDR3/DDR4, Monitor Display resolusi minimal 1366x768 piksel, antarmuka jaringan LAN / Wi-Fi | Perangkat operasional utama bagi Petugas dan Supervisor di area kerja pengemasan |
| 3 | **Barcode Scanner** | Scanner genggam (*Handheld Barcode Reader*) koneksi USB Plug-and-Play (antarmuka HID), mendukung simbologi linear **Code 128** | Membaca barcode Nomor PO pada Kartu Mesin Produksi secara cepat dan akurat |
| 4 | **Printer Dokumen** | Printer Inkjet / Laser standar kantor dengan ukuran media kertas A4 | Mencetak lembaran fisik laporan hasil produksi harian untuk pengesahan tanda tangan |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.16: Spesifikasi Lingkungan Perangkat Lunak (Software Environment)*
| No | Komponen Perangkat Lunak | Nama Paket / Versi Teknologi | Keterangan dan Peranan dalam Aplikasi |
|---|---|---|---|
| 1 | **Sistem Operasi** | Windows 11 (64-bit) | Sistem operasi utama pada komputer pengembang dan klien operasional |
| 2 | **Server Lingkungan Lokal** | Laragon (Local Development Environment) | Mengelola stack server lokal yang terintegrasi (Web Server, PHP runtime, dan database) |
| 3 | **Basis Data (DBMS)** | MySQL Server | Menyimpan master data SAP, data transaksi laporan harian, akun pengguna, dan riwayat audit |
| 4 | **Bahasa Pemrograman Backend** | PHP (versi 8.3+) | Bahasa pemrograman server-side utama untuk mengeksekusi logika bisnis aplikasi |
| 5 | **Backend Framework** | Laravel Framework (versi 13.x) | Kerangka kerja web utama dengan arsitektur MVC dan *Service Layer Pattern* |
| 6 | **Autentikasi & Keamanan** | Laravel Fortify (versi 1.37+) | Menyediakan backend autentikasi aman berbasis Nomor Pegawai (NP), password hashing, dan rate limiting |
| 7 | **Arsitektur Frontend Reaktif** | Livewire (versi 4.x) | Membangun komponen antarmuka web dinamis dan reaktif tanpa perpindahan halaman |
| 8 | **Komponen UI & Optimasi** | Livewire Flux UI (versi 2.13+) & Livewire Blaze (versi 1.0) | Pustaka komponen antarmuka modern dan mesin optimasi perenderan Blade View |
| 9 | **CSS Framework & Bundler** | Tailwind CSS (versi 4.x) dengan `@tailwindcss/vite` & Vite (versi 8.x) | Desain antarmuka responsif dan alat kompilasi aset frontend cepat |
| 10 | **Pengelola Paket Dependensi** | Composer (PHP) & pnpm (Node.js) | Manajemen instalasi dan pembaruan dependensi backend PHP dan frontend JavaScript |
| 11 | **Visualisasi Grafik Analitik** | Chart.js terintegrasi Alpine.js | Merender grafik analitik tren performa hasil kemas 14 hari kerja pada halaman Dashboard |
| 12 | **Pengujian & Kualitas Kode** | Pest PHP (versi 5.x), Larastan (versi 3.x), Laravel Pint (versi 1.27) | Pengujian otomatis (*automated testing*), analisis statis tipe data, dan standarisasi format kode |
*(Sumber: Hasil Penelitian, 2026)*

---

#### 2. Implementasi Antarmuka Sistem
Berikut disajikan rancangan dan implementasi antarmuka visual keseluruhan halaman sistem dari proses awal login hingga penerbitan cetak laporan:

##### a. Tampilan Antarmuka Halaman Login
Halaman login dirancang dengan tata letak minimalis dan terpusat yang memfasilitasi proses autentikasi cepat bagi pengguna menggunakan Nomor Pegawai (NP) dan password:

```
+-----------------------------------------------------------------------+
|                                                                       |
|                     PT PERCETAKAN DOKUMEN SEKURITI                    |
|             Sistem Dokumentasi Hasil Produksi Harian (Unit Kemas)     |
|                                                                       |
|                     +---------------------------+                     |
|                     |       FORM LOGIN USER     |                     |
|                     |                           |                     |
|                     |  Nomor Pegawai (NP):      |                     |
|                     |  [______________________] |                     |
|                     |                           |                     |
|                     |  Password:                |                     |
|                     |  [______________________] |                     |
|                     |                           |                     |
|                     |  [x] Ingat Saya           |                     |
|                     |                           |                     |
|                     |     [   MASUK SISTEM   ]  |                     |
|                     |                           |                     |
|                     +---------------------------+                     |
|                                                                       |
+-----------------------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.12: Tampilan Antarmuka Halaman Login Sistem*

##### b. Tampilan Antarmuka Halaman Dashboard Utama dan Grafik Performa 14 Hari Kerja
Halaman dashboard menampilkan ringkasan kartu metrik capaian harian, navigasi cepat modul sistem, serta visualisasi grafik analitik performa produksi 14 hari kerja terakhir yang menyajikan perbandingan output baik dan kuantitas reject:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Shift: Shift 1 | WIB: 08:30 | [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | DASHBOARD UTAMA & ANALITIK PRODUKSI                     |
| - Dashboard |                                                         |
| - Up Database| +-------------+ +-------------+ +-------------+ +-------+ |
| - Data Petugs| | TOTAL KEMAS | | TOTAL REJECT| | PO TERSCAN  | |STATUS | |
| - Buat Lapor | | 48.500 lbr  | | 320 lbr     | | 12 Order    | |Draft:2| |
| - Lihat Lapor| +-------------+ +-------------+ +-------------+ +-------+ |
|             |                                                         |
|             | GRAFIK ANALITIK HASIL PRODUKSI 14 HARI KERJA TERAKHIR   |
|             | +-----------------------------------------------------+ |
|             | | (Kuantitas)                                         | |
|             | |  60k |       _/\_        /\_                        | |
|             | |  40k |  _/\_/    \__/\__/   \__/\_   -- Kemas Baik  | |
|             | |  20k | /                          \  -- Reject      | |
|             | |    0 +---------------------------------             | |
|             | |       H-14 H-13 ... H-3  H-2  H-1 Hari Ini          | |
|             | +-----------------------------------------------------+ |
|             | * Rata-rata 14 Hari: 45.200 Lembar | Puncak: 56.000 lbr |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.13: Tampilan Antarmuka Halaman Dashboard dan Grafik Performa 14 Hari Kerja*

##### c. Tampilan Antarmuka Halaman Manajemen Data Petugas
Halaman ini memfasilitasi Supervisor untuk mengelola akun operator pengemasan melalui tabel data terstruktur dan form modal penambahan/pengubahan akun:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Supervisor Admin             [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | MANAJEMEN DATA PETUGAS OPERATOR                         |
| - Dashboard |                                                         |
| - Up Database| [ + TAMBAH PETUGAS BARU ]       Cari Petugas: [_______] |
| - Data Petugs+----+---------+---------------------+-----------+-------+ |
| - Buat Lapor | No | NP      | Nama Petugas        | Peran     | Status| |
| - Lihat Lapor+----+---------+---------------------+-----------+-------+ |
|             | 1  | 202401  | Umar Ibnu Zainal M. | Petugas   | Aktif | |
|             | 2  | 202402  | Hendra Setiawan     | Petugas   | Aktif | |
|             | 3  | 202001  | Agus Soelistyo      | Supervisor| Aktif | |
|             | +----+---------+---------------------+-----------+-------+ |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.14: Tampilan Antarmuka Halaman Manajemen Data Petugas*

##### d. Tampilan Antarmuka Halaman Update Database (Import SAP)
Halaman ini dirancang khusus bagi Supervisor untuk mengunggah berkas data master pesanan produksi yang diekspor dari sistem SAP, lengkap dengan tabel riwayat berkas impor terakhir:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Supervisor Admin             [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | UPDATE DATABASE MASTER PRODUKSI DARI SAP                |
| - Dashboard |                                                         |
| - Up Database| +-----------------------------------------------------+ |
| - Data Petugs| |  PILIH BERKAS EKSPOR SAP (.XLSX / .CSV)             | |
| - Buat Lapor | |  [ [Pilih File] sap_production_order_august.xlsx ]  | |
| - Lihat Lapor| |                                                     | |
|             | |  [   PROSES & IMPOR KE MASTER DATA   ]               | |
|             | +-----------------------------------------------------+ |
|             |                                                         |
|             | RIWAYAT UNGGAHAN MASTER SAP TERAKHIR                    |
|             | +----+-------------------------+----------+-----------+ |
|             | | No | Nama Berkas             | Jml Data | Tanggal   | |
|             | +----+-------------------------+----------+-----------+ |
|             | | 1  | sap_po_2026_08_25.xlsx  | 145 PO   | 25/08/2026| |
|             | | 2  | sap_po_2026_08_18.xlsx  | 120 PO   | 18/08/2026| |
|             | +----+-------------------------+----------+-----------+ |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.15: Tampilan Antarmuka Halaman Update Database Master SAP*

##### e. Tampilan Antarmuka Halaman Buat Laporan (Scan Barcode & Entri Kemas)
Halaman ini merupakan antarmuka operasional utama Petugas di meja kerja pengemasan. Formulir dilengkapi area masukan scan barcode otomatis, penampil rincian spesifikasi barang seketika, kolom input jumlah kemas, dan tabel penampungan draf hasil kemas multi-item:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Petugas: Umar I.Z.           [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | BUAT LAPORAN PRODUKSI HARIAN (SCAN BARCODE)             |
| - Dashboard | Tanggal: 25/08/2026 | Shift: [Shift 1 v] | Petugas: Umar|
| - Up Database+-------------------------------------------------------+ |
| - Data Petugs| [|||||||||||||||||] SCAN BARCODE NO PO (KARTU MESIN)   | |
| - Buat Lapor | [ PO-2026-8891                                    ]   | |
| - Lihat Lapor|                                                       | |
|             | Nama Produk: Sertifikat Sekuriti Hologram A4            | |
|             | Spesifikasi: Kertas Watermark 120gsm, 2 Spot UV         | |
|             | Mesin Asal : MESIN-02  | Target: 5.000 Lembar           | |
|             |                                                         | |
|             | Jml Kemas Baik: [ 5.000 ]  Jml Reject: [ 25 ]           | |
|             | [ + TAMBAH KE DAFTAR LAPORAN ]                          | |
|             +---------------------------------------------------------+ |
|             | TABEL RINCIAN HASIL KEMAS HARIAN (DRAFT)                |
|             | +----+--------------+-------------------+------+-------+| |
|             | | No | No PO        | Nama Produk       | Baik | Reject|| |
|             | +----+--------------+-------------------+------+-------+| |
|             | | 1  | PO-2026-8891 | Sertifikat A4     | 5.000| 25    || |
|             | | 2  | PO-2026-8892 | Ijazah Sekuriti   | 2.500| 10    || |
|             | +----+--------------+-------------------+------+-------+| |
|             | Total: 2 PO | Total Kemas: 7.500 | Total Reject: 35     | |
|             |                                                         | |
|             | [ SIMPAN SEBAGAI DRAFT ]     [ FINALISASI & SIMPAN ]    | |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.16: Tampilan Antarmuka Halaman Buat Laporan dengan Scan Barcode*

##### f. Tampilan Antarmuka Halaman Lihat Laporan
Halaman ini menyajikan tabel riwayat seluruh dokumen laporan hasil kemas yang tersimpan, dilengkapi fitur pencarian, filter rentang tanggal, status verifikasi, dan tombol aksi:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Supervisor / Petugas         [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | DAFTAR LAPORAN HASIL PRODUKSI HARIAN                    |
| - Dashboard | Filter Tanggal: [ 2026-08-01 ] s/d [ 2026-08-25 ]       |
| - Up Database+----+-------------------+------------+-------+---------+| |
| - Data Petugs| No | Nomor Laporan     | Tanggal    | Shift | Aksi    || |
| - Buat Lapor +----+-------------------+------------+-------+---------+| |
| - Lihat Lapor| 1  | LAP-20260825-0001 | 25/08/2026 | 1     |[Lihat]  || |
|             | 2  | LAP-20260824-0002 | 24/08/2026 | 2     |[Lihat]  || |
|             | +----+-------------------+------------+-------+---------+| |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.17: Tampilan Antarmuka Halaman Lihat Laporan Produksi Harian*

##### g. Tampilan Antarmuka Halaman Detail dan Verifikasi Laporan
Halaman ini menampilkan rincian lengkap item kemas pada suatu dokumen laporan, memfasilitasi pengeditan korektif oleh Petugas (jika masih draf), serta menyediakan tombol aksi persetujuan (*verifikasi*) bagi Supervisor:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Supervisor: Agus S.          [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | RINCIAN LAPORAN: LAP-20260825-0001                      |
| - Dashboard | Tanggal: 25/08/2026 | Shift: Shift 1 | Petugas: Umar    |
| - Up Database| Status : [ BELUM VERIFIKASI / SUBMITTED ]              |
| - Data Petugs+----+--------------+-------------------+------+-------+ |
| - Buat Lapor | No | No PO        | Nama Produk       | Baik | Reject| |
| - Lihat Lapor+----+--------------+-------------------+------+-------+ |
|             | 1  | PO-2026-8891 | Sertifikat A4     | 5.000| 25    | |
|             | 2  | PO-2026-8892 | Ijazah B5         | 2.500| 10    | |
|             | +----+--------------+-------------------+------+-------+ |
|             | Total Kemas Baik: 7.500 Lembar | Total Reject: 35 Lembar| |
|             |                                                         |
|             | [ CETAK LAPORAN ]         [ VERIFIKASI & SETUJUI LAPORAN]|
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.18: Tampilan Antarmuka Detail dan Verifikasi Laporan Produksi Harian*

##### h. Tampilan Format Lembar Cetak Laporan Standar Kertas A4
Format cetak lembar dokumen resmi standar A4 yang dihasilkan sistem lengkap dengan kop perusahaan, rincian item, total hasil kemas, dan kolom tanda tangan pengesahan:

```
+-----------------------------------------------------------------------+
|                     PT PERCETAKAN DOKUMEN SEKURITI                    |
|                LAPORAN HASIL PRODUKSI HARIAN PENGEMASAN               |
|                                                                       |
| No. Laporan : LAP-20260825-0001           Tanggal : 25 Agustus 2026   |
| Petugas     : Umar Ibnu Zainal M.         Shift   : Shift 1 (Pagi)    |
| Status      : TERVERIFIKASI                                           |
|-----------------------------------------------------------------------|
| No | Nomor PO      | Nama Produk / Spesifikasi | Mesin | Baik | Reject|
|----+---------------+---------------------------+-------+------+-------|
| 1  | PO-2026-8891  | Sertifikat Hologram A4    | M-02  | 5.000| 25    |
| 2  | PO-2026-8892  | Ijazah Sekuriti B5        | M-01  | 2.500| 10    |
|----+---------------+---------------------------+-------+------+-------|
| TOTAL HASIL PENGEMASAN                         |       | 7.500| 35    |
|-----------------------------------------------------------------------|
| Mengetahui,                                 Petugas Pelaksana,        |
| Kepala Unit / Supervisor                                              |
|                                                                       |
| ( Agus Soelistyo )                          ( Umar Ibnu Zainal M. )   |
+-----------------------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.19: Tampilan Format Lembar Cetak Laporan Produksi Harian (Standar A4)*

---

#### 3. Implementasi Modul Program
Implementasi aplikasi dikelompokkan ke dalam enam modul fungsional utama yang saling terintegrasi sebagaimana dirangkum pada Tabel 3.17:

*Tabel 3.17: Pemetaan dan Implementasi Modul Program*
| No | Modul Program | Komponen / Berkas Utama | Penjelasan Implementasi Fungsional |
|---|---|---|---|
| 1 | **Skema Basis Data & Model Eloquent** | `database/migrations/`, Model: `User`, `ProductionOrder`, `DailyReport`, `DailyReportItem`, `ImportLog` | Membangun struktur tabel terelasi, foreign key constraints, tipe data kolom, seeder inisialisasi, dan pemodelan relasi Eloquent ORM untuk menjamin integritas transaksi data. |
| 2 | **Autentikasi & Kontrol Akses Peran** | `app/Livewire/Auth/Login.php`, `FortifyServiceProvider`, Middleware `EnsureUserRole` | Menerapkan login menggunakan identitas Nomor Pegawai (NP) dan password terenkripsi, manajemen sesi, rate limiting 5 percobaan per menit, serta proteksi halaman berbasis peran Petugas dan Supervisor. |
| 3 | **Update Database (Master Data SAP)** | `app/Livewire/MasterData/UpdateDatabase.php`, `app/Services/SapImportService.php` | Mengakomodasi pengunggahan berkas ekspor SAP (.xlsx/.csv), validasi struktur kolom, parsing baris pesanan, dan pembaruan massal (*bulk upsert*) data Nomor PO, spesifikasi bahan, dan target kemas. |
| 4 | **Buat Laporan (Scan Barcode & Kemas)** | `app/Livewire/Reports/CreateDailyReport.php`, Alpine.js Scanner Listener | Menangkap pembacaan scanner barcode Code 128 pada Nomor PO, melakukan pencarian instan (*instant lookup*) data produk, menyediakan input kuantitas baik/reject, dan menampung item pada tabel sementara (*staging table*). |
| 5 | **Manajemen & Cetak Laporan** | `app/Livewire/Reports/ReportIndex.php`, `ReportDetail.php`, `ReportNumberGenerator`, `@media print` | Menampilkan riwayat laporan harian, membangkitkan Nomor Laporan unik otomatis (`LAP-YYYYMMDD-XXXX`), memfasilitasi verifikasi persetujuan oleh Supervisor, serta mencetak dokumen fisik rapi standar kertas A4. |
| 6 | **Dashboard & Grafik Performa 14 Hari** | `app/Livewire/Dashboard/DashboardIndex.php`, `DashboardAnalyticsService.php`, Komponen Chart.js | Mengeksekusi query agregasi SQL untuk menghitung akumulasi kemas baik dan reject 14 hari kerja aktif terakhir (WIB), merender kartu metrik KPI harian, dan menampilkan visualisasi grafik interaktif. |
*(Sumber: Hasil Penelitian, 2026)*

---

### D. Pengujian Sistem

Tahap pengujian sistem bertujuan untuk menguji, memverifikasi, dan memvalidasi bahwa seluruh fungsi pada perangkat lunak telah beroperasi secara presisi sesuai dengan rancangan spesifikasi kebutuhan serta bebas dari galat yang dapat mengganggu operasional di lapangan.

#### 1. Metode Pengujian
Metode pengujian yang diterapkan dalam penelitian ini adalah **Black Box Testing** (pengujian kotak hitam). Metode ini menitikberatkan pengujian pada aspek fungsionalitas eksternal perangkat lunak, yaitu dengan memberikan kombinasi masukan (*input*) data tertentu pada antarmuka pengguna dan memverifikasi apakah keluaran (*output*) serta perilaku sistem yang dihasilkan telah sesuai dengan hasil yang diharapkan, tanpa perlu memeriksa struktur internal kode program.

#### 2. Rencana dan Skenario Pengujian
Pengujian fungsionalitas dijalankan secara menyeluruh terhadap seluruh skenario operasional utama sistem sebagaimana dirangkum pada Tabel 3.18:

*Tabel 3.18: Rekapitulasi Hasil Pengujian Fungsionalitas Sistem (Black Box Testing)*
| No. | Skenario Pengujian | Test Case / Masukan Data | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|---|---|---|---|---|---|
| 1. | Pengguna mengosongkan kolom Nomor Pegawai (NP) dan password, kemudian menekan tombol Masuk Sistem. | NP: (kosong)<br>Password: (kosong) | Login gagal dan sistem menampilkan pesan validasi "Field Nomor Pegawai wajib diisi". | Sistem menolak proses login dan memunculkan notifikasi validasi form. | Valid |
| 2. | Pengguna memasukkan Nomor Pegawai (NP) yang tidak terdaftar pada basis data. | NP: 999999<br>Password: rahasia123 | Login gagal dan sistem menampilkan pesan peringatan "Kredensial tidak cocok". | Sistem menolak akses dan memunculkan notifikasi akun tidak terdaftar. | Valid |
| 3. | Pengguna memasukkan Nomor Pegawai (NP) terdaftar namun password yang dimasukkan salah. | NP: 202401<br>Password: salahpass | Login gagal dan sistem menampilkan pesan peringatan "Kredensial tidak cocok". | Sistem menolak akses dan memunculkan notifikasi password salah. | Valid |
| 4. | Pengguna memasukkan Nomor Pegawai (NP) dan password yang benar dan terdaftar. | NP: 202401<br>Password: Password123! | Login berhasil dan sistem menginisialisasi sesi kerja serta mengarahkan ke Dashboard. | Sistem membuka sesi dan mengarahkan pengguna ke halaman Dashboard. | Valid |
| 5. | Supervisor menambahkan akun petugas baru dengan data yang valid. | NP: 202405<br>Nama: Budi Santoso<br>Email: budi@pds.co.id<br>Role: Petugas | Data akun baru tersimpan ke tabel `users` dan muncul di tabel daftar petugas. | Data berhasil tersimpan dan daftar tabel petugas memuat akun baru. | Valid |
| 6. | Supervisor mengunggah berkas ekspor SAP format spreadsheet valid (.xlsx). | File: `sap_po_august.xlsx`<br>(Kolom No PO, Nama, Qty lengkap) | Sistem mengekstrak seluruh baris pesanan dan memperbarui tabel master `production_orders`. | Sistem melakukan *bulk upsert* dan menampilkan jumlah data terproses. | Valid |
| 7. | Supervisor mengunggah berkas dengan ekstensi yang tidak diizinkan (.pdf). | File: `dokumen_sap.pdf` | Sistem menolak berkas dan memunculkan pesan "Format berkas tidak valid". | Berkas ditolak sistem dan menampilkan peringatan tipe berkas. | Valid |
| 8. | Petugas memindai barcode Nomor PO yang terdaftar pada master SAP menggunakan scanner. | Barcode Scanner input:<br>`PO-2026-8891` | Kolom Nama Produk, Spesifikasi Kertas, dan Nomor Mesin terisi otomatis secara seketika (< 1 detik). | Seluruh kolom spesifikasi produk terisi otomatis dari basis data. | Valid |
| 9. | Petugas memindai barcode Nomor PO yang belum diimpor ke dalam sistem. | Barcode Scanner input:<br>`PO-9999-0000` | Sistem membunyikan peringatan dan menampilkan notifikasi "Nomor PO belum terdaftar". | Sistem membunyikan alarm dan memunculkan alert PO tidak ditemukan. | Valid |
| 10. | Petugas memasukkan jumlah hasil kemas baik dan reject lalu menekan tombol simpan laporan. | Kemas Baik: 5.000<br>Reject: 25<br>Klik "Finalisasi & Simpan" | Sistem membangkitkan Nomor Laporan unik otomatis (`LAP-YYYYMMDD-XXXX`) dan menyimpan item. | Laporan tersimpan di basis data dengan nomor dokumen unik baru. | Valid |
| 11. | Petugas mengedit angka kemas pada laporan yang masih berstatus draf. | Kemas Baik diubah dari 5.000 menjadi 5.200 | Sistem memperbarui rekaman jumlah kemas pada tabel rincian laporan. | Angka kuantitas kemas berhasil diperbarui di database. | Valid |
| 12. | Supervisor meninjau dan menyetujui laporan harian yang diserahkan petugas. | Klik tombol "Verifikasi & Setujui Laporan" | Status laporan berubah menjadi `verified` dan formulir dikunci dari perubahan. | Status laporan terverifikasi dan identitas supervisor tercatat. | Valid |
| 13. | Pengguna menekan tombol Cetak Laporan pada dokumen yang telah disetujui. | Klik tombol "Cetak Laporan" | Sistem merender tampilan cetak standar kertas A4 rapi lengkap dengan kolom tanda tangan. | Lembar cetak berformat standar A4 muncul pada jendela pratinjau cetak. | Valid |
| 14. | Pengguna mengakses halaman Dashboard saat terdapat transaksi 14 hari terakhir. | Buka halaman Dashboard | Sistem menampilkan kartu KPI harian dan visualisasi grafik tren performa 14 hari kerja. | Kartu metrik KPI dan grafik Chart.js 14 hari kerja ter-render interaktif. | Valid |
*(Sumber: Hasil Penelitian, 2026)*

#### 3. Kesimpulan Hasil Pengujian
Berdasarkan hasil eksekusi seluruh butir pengujian *Black Box Testing*, seluruh fungsi sistem terbukti beroperasi secara presisi sesuai dengan rancangan spesifikasi kebutuhan dan siap diimplementasikan untuk mendukung operasional pencatatan harian di Unit Pengemasan PT Percetakan Dokumen Sekuriti.

---

### E. Pemeliharaan

Tahap pemeliharaan (*maintenance*) merupakan tahapan pamungkas dalam daur hidup pengembangan perangkat lunak metode Waterfall. Tahap ini bertujuan untuk memastikan sistem perangkat lunak tetap beroperasi secara optimal, andal, aman, dan adaptif terhadap dinamika kebutuhan operasional setelah diterapkan secara penuh di lingkungan kerja Unit Pengemasan PT Percetakan Dokumen Sekuriti.

Aktivitas pemeliharaan perangkat lunak mencakup empat kategori penanganan utama:

1. **Pemeliharaan Korektif (*Corrective Maintenance*)**
   <br>
   Kegiatan pemeliharaan korektif mencakup penanganan cepat terhadap kendala teknis, galat masukan data, atau anomali pembacaan scanner yang berpotensi ditemukan selama penggunaan rutin di area kerja pengemasan. Prosedur penanganan dilakukan dengan meninjau log error aplikasi dan melakukan perbaikan logika program secara terukur tanpa mengganggu integritas data yang telah tersimpan.

2. **Pemeliharaan Adaptif Format Master SAP (*Adaptive Maintenance*)**
   <br>
   Kegiatan pemeliharaan adaptif difokuskan pada penyesuaian logika pembacaan kolom pada modul *Update Database* apabila di masa mendatang pihak IT PT Percetakan Dokumen Sekuriti melakukan modifikasi atau pembaruan struktur kolom pada berkas ekspor sistem SAP.

3. **Pemeliharaan Perfektif dan Penjaminan Keamanan (*Perfective & Security Maintenance*)**
   <br>
   Kegiatan pemeliharaan perfektif dilakukan melalui pembaruan versi dependensi aplikasi secara berkala guna memastikan sistem terlindung dari celah keamanan web terbaru, serta melakukan optimalisasi perenderan antarmuka untuk meningkatkan kenyamanan kerja operator.

4. **Pemeliharaan Basis Data dan Pencadangan (*Database Backup & Routine Maintenance*)**
   <br>
   Kegiatan pemeliharaan basis data dilaksanakan melalui penjadwalan pencadangan (*backup*) data secara berkala guna mencegah risiko kehilangan arsip riwayat laporan produksi harian akibat kegagalan perangkat keras komputer klien. Selain itu, dilakukan optimasi indeks tabel secara periodik pada entitas `daily_reports`, `daily_report_items`, dan `production_orders` untuk menjaga kecepatan respon proses pemindaian barcode dan query analitik grafik performa 14 hari kerja seiring bertambahnya volume data transaksi tahunan.
