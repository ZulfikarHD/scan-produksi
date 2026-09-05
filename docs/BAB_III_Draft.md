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

Keempat, skala sistem yang terfokus pada digitalisasi pencatatan dokumen kemas dan pemantauan analitik 7, 14, atau 30 hari aktif memungkinkan seluruh spesifikasi dirancang secara komprehensif di awal. Pendekatan terstruktur ini meminimalkan risiko terjadinya pembengkakan ruang lingkup (*scope creep*) dan memastikan konsentrasi rekayasa tertuju pada stabilitas pembacaan scanner dan keakuratan kalkulasi data grafik.

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
   Tahap penerjemahan seluruh dokumen perancangan ke dalam pembuatan aplikasi fungsional. Pada tahap ini, basis data dikonfigurasikan, logika backend dibangun menggunakan Laravel dengan arsitektur MVC, antarmuka reaktif diwujudkan melalui Livewire dan Flux UI, integrasi pembacaan barcode Code 128 dikonfigurasikan, dan modul kalkulasi analitik grafik performa produksi (7, 14, atau 30 hari aktif) diaktifkan.

4. **Pengujian Sistem (*Testing*)**
   <br>
   Tahap verifikasi dan validasi aplikasi untuk memastikan setiap fungsi bekerja sesuai dengan rancangan spesifikasi kebutuhan. Pengujian dilakukan menggunakan metode *Black Box Testing* yang menguji keakuratan masukan dan keluaran sistem pada skenario operasional nyata.

5. **Pemeliharaan (*Maintenance*)**
   <br>
   Tahap pemeliharaan sistem setelah aplikasi diterapkan di lingkungan kerja. Kegiatan ini mencakup pencadangan basis data berkala, penanganan kendala teknis yang timbul, pembaruan keamanan, serta penyesuaian logika sistem terhadap perubahan format data ekspor SAP.

### 3.1.4 Kesesuaian Metode dengan Penelitian
Penerapan metode Waterfall memberikan kepastian alur kerja yang mendukung tujuan penelitian. Dengan menetapkan analisis dan desain arsitektur secara matang sebelum pembuatan aplikasi dimulai, pengembang dapat mengantisipasi kendala integrasi antara data hasil scanner dan basis data master SAP. Setiap fungsi yang dikembangkan, mulai dari pembacaan barcode cepat hingga penyajian grafik performa produksi (7/14/30 hari aktif), memiliki acuan pengujian yang jelas, sehingga aplikasi yang dihasilkan dapat berfungsi dengan stabil, akurat, dan mudah digunakan oleh petugas di Unit Pengemasan.

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

Kebutuhan fungsional mendefinisikan kapabilitas dan layanan yang wajib disediakan oleh sistem untuk memenuhi kebutuhan pengguna. Sistem informasi hasil produksi harian ini memiliki dua hak akses pengguna, yaitu Operator dan Kepala Unit (Supervisor), dengan rincian sebagai berikut:

**a. Kebutuhan Sistem Operator**
1) Operator dapat melakukan *login* ke dalam sistem.
2) Operator dapat mengakses halaman *Dashboard*.
3) Operator dapat membuka sesi laporan harian melalui menu **Buat Laporan** (PCHT, MMEA, atau HPTL).
4) Operator dapat memindai barcode Nomor PO dan menginputkan kuantitas hasil kemas pada konsol pemindaian.
5) Operator dapat melihat riwayat laporan melalui menu **Lihat Laporan**, mengoreksi laporan milik sendiri (dalam batas 24 jam dan belum diverifikasi), mengekspor rekapitulasi ke Excel, dan mencetak lembar fisik laporan.
6) Operator dapat melakukan *logout* dari sistem.

**b. Kebutuhan Sistem Kepala Unit (Supervisor)**
1) Kepala Unit dapat melakukan *login* ke dalam sistem.
2) Kepala Unit dapat mengakses halaman *Dashboard*.
3) Kepala Unit dapat mengimpor berkas master data pesanan SAP dan mengelola data order melalui menu **Update Database**.
4) Kepala Unit dapat membuka sesi laporan dan melakukan pemindaian barcode melalui menu **Buat Laporan**.
5) Kepala Unit dapat melihat, memverifikasi, mengoreksi, menghapus, membuka kembali sesi, mengekspor Excel, dan mencetak seluruh riwayat laporan melalui menu **Lihat Laporan**.
6) Kepala Unit dapat mengelola data akun petugas melalui menu **Data Petugas**.
7) Kepala Unit dapat melakukan *logout* dari sistem.

##### Kebutuhan Non-Fungsional (*Non-Functional Requirements*)
Kebutuhan non-fungsional menetapkan batasan kualitas teknis dan performa sistem:
1. **Performa dan Kecepatan Respon:** Sistem memberikan respon pencarian data produk pada saat barcode Nomor PO dipindai dalam waktu kurang dari 1 detik, serta merender kalkulasi grafik analitik 7, 14, atau 30 hari aktif dalam waktu di bawah 2 detik pada jaringan lokal.
2. **Keandalan dan Integritas Transaksi Data:** Sistem menerapkan validasi pencegahan duplikasi entri barcode Nomor PO dalam satu sesi laporan harian dan membungkus proses penyimpanan dalam mekanisme transaksi basis data (*database transaction*).
3. **Kompatibilitas Perangkat Keras dan Perangkat Lunak:** Sistem kompatibel secara langsung (*Plug-and-Play*) dengan perangkat *barcode scanner* USB yang membaca format Code 128, dapat diakses melalui peramban web modern (*Google Chrome, Mozilla Firefox, Microsoft Edge*), dan format cetak disesuaikan untuk kertas standar A4.
4. **Kemudahan Penggunaan (*Usability*):** Tata letak antarmuka dirancang bersih, intuitif, dan kontras tinggi untuk mempermudah operasional di meja kerja pengemasan.
5. **Keamanan Sistem dan Data:** Seluruh kata sandi pengguna dienkripsi dengan algoritma *Bcrypt*, dilengkapi proteksi terhadap serangan CSRF, SQL Injection, XSS, serta pembatasan hak akses berbasis peran pengguna (*role-based middleware*).

---

### B. Tahap Analisis Pemodelan

Tahap analisis pemodelan bertujuan untuk memvisualisasikan struktur fungsional sistem melalui Use Case Diagram terpisah per peran pengguna, aliran aktivitas proses (Activity Diagram), skema hubungan entitas berbasis notasi Chen (ERD), representasi struktur record logis (LRS), serta spesifikasi detail berkas tabel data sebelum pembuatan aplikasi dilaksanakan.

#### 1. Use Case Diagram
Use Case Diagram mendeskripsikan interaksi fungsional antara aktor (pengguna sistem) dengan fungsi-fungsi yang disediakan oleh sistem. Pemodelan use case disusun berdasarkan menu navigasi utama aplikasi web yang benar-benar tersedia di antarmuka sistem, yaitu **Dashboard**, **Buat Laporan**, **Lihat Laporan**, **Update Database** (khusus Supervisor), dan **Data Petugas** (khusus Supervisor). Diagram use case dipisahkan menjadi dua diagram utama berdasarkan hak akses masing-masing aktor. Relasi `<<include>>` digunakan untuk use case Login sebagai prasyarat akses menu utama pada kedua peran, sedangkan relasi `<<extend>>` digunakan untuk fitur opsional seperti pilihan seri laporan (PCHT, MMEA, HPTL), ekspor Excel, cetak lembar fisik, serta verifikasi laporan (khusus Supervisor).

##### a. Use Case Diagram Hak Akses Petugas (Operator)
Diagram use case untuk peran Petugas (Operator Pengemasan) dimodelkan pada Gambar 3.2:

```mermaid
flowchart LR
    subgraph SYSTEM ["Sistem Informasi Hasil Produksi Harian"]
        direction TB
        UC_LOGIN(["Login"])
        UC_DASH(["Mengakses Halaman Dashboard"])
        UC_BUAT(["Mengakses Menu Buat Laporan"])
        UC_LIHAT(["Melihat Laporan"])
        UC_LOGOUT(["Logout"])

        UC_PCHT(["Laporan PCHT"])
        UC_MMEA(["Laporan MMEA"])
        UC_HPTL(["Laporan HPTL"])
        UC_EXCEL(["Ekspor Excel"])
        UC_CETAK(["Cetak Lembar Fisik"])
        UC_BUAT_EXT(["Buat Laporan Baru"])
    end

    ACT_OP((Operator))

    ACT_OP --- UC_DASH
    ACT_OP --- UC_BUAT
    ACT_OP --- UC_LIHAT
    ACT_OP --- UC_LOGOUT

    UC_DASH -. "<<include>>" .-> UC_LOGIN
    UC_BUAT -. "<<include>>" .-> UC_LOGIN
    UC_LIHAT -. "<<include>>" .-> UC_LOGIN

    UC_PCHT -. "<<extend>>" .-> UC_BUAT
    UC_MMEA -. "<<extend>>" .-> UC_BUAT
    UC_HPTL -. "<<extend>>" .-> UC_BUAT

    UC_BUAT_EXT -. "<<extend>>" .-> UC_LIHAT
    UC_EXCEL -. "<<extend>>" .-> UC_LIHAT
    UC_CETAK -. "<<extend>>" .-> UC_LIHAT

    classDef act fill:#f1f5f9,stroke:#0f172a,stroke-width:1.5px,color:#0f172a;
    classDef sys fill:#ffffff,stroke:#2563eb,stroke-width:2px,color:#0f172a;
    classDef uc fill:#f8fafc,stroke:#475569,stroke-width:1px,color:#0f172a;
    classDef ext fill:#fefce8,stroke:#ca8a04,stroke-width:1px,color:#0f172a;

    class ACT_OP act;
    class SYSTEM sys;
    class UC_LOGIN,UC_DASH,UC_BUAT,UC_LIHAT,UC_LOGOUT uc;
    class UC_PCHT,UC_MMEA,UC_HPTL,UC_EXCEL,UC_CETAK,UC_BUAT_EXT ext;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.2: Use Case Diagram Hak Akses Petugas (Operator)*

##### b. Use Case Diagram Hak Akses Kepala Unit (Supervisor)
Diagram use case untuk peran Kepala Unit (Supervisor) dimodelkan pada Gambar 3.3:

```mermaid
flowchart LR
    subgraph SYSTEM ["Sistem Informasi Hasil Produksi Harian"]
        direction TB
        UC_LOGIN(["Login"])
        UC_DASH(["Mengakses Halaman Dashboard"])
        UC_UPDATE(["Update Database"])
        UC_BUAT(["Mengakses Menu Buat Laporan"])
        UC_LIHAT(["Melihat Laporan"])
        UC_PETUGAS(["Mengelola Data Petugas"])
        UC_LOGOUT(["Logout"])

        UC_PCHT(["Laporan PCHT"])
        UC_MMEA(["Laporan MMEA"])
        UC_HPTL(["Laporan HPTL"])
        UC_EXCEL(["Ekspor Excel"])
        UC_CETAK(["Cetak Lembar Fisik"])
        UC_VERIF(["Verifikasi Laporan"])
        UC_BUAT_EXT(["Buat Laporan Baru"])
    end

    ACT_SPV((Supervisor))

    ACT_SPV --- UC_DASH
    ACT_SPV --- UC_UPDATE
    ACT_SPV --- UC_BUAT
    ACT_SPV --- UC_LIHAT
    ACT_SPV --- UC_PETUGAS
    ACT_SPV --- UC_LOGOUT

    UC_DASH -. "<<include>>" .-> UC_LOGIN
    UC_UPDATE -. "<<include>>" .-> UC_LOGIN
    UC_BUAT -. "<<include>>" .-> UC_LOGIN
    UC_LIHAT -. "<<include>>" .-> UC_LOGIN
    UC_PETUGAS -. "<<include>>" .-> UC_LOGIN

    UC_PCHT -. "<<extend>>" .-> UC_BUAT
    UC_MMEA -. "<<extend>>" .-> UC_BUAT
    UC_HPTL -. "<<extend>>" .-> UC_BUAT
    UC_EXCEL -. "<<extend>>" .-> UC_LIHAT
    UC_CETAK -. "<<extend>>" .-> UC_LIHAT
    UC_VERIF -. "<<extend>>" .-> UC_LIHAT
    UC_BUAT_EXT -. "<<extend>>" .-> UC_LIHAT

    classDef act fill:#f1f5f9,stroke:#0f172a,stroke-width:1.5px,color:#0f172a;
    classDef sys fill:#ffffff,stroke:#2563eb,stroke-width:2px,color:#0f172a;
    classDef uc fill:#f8fafc,stroke:#475569,stroke-width:1px,color:#0f172a;
    classDef ext fill:#fefce8,stroke:#ca8a04,stroke-width:1px,color:#0f172a;

    class ACT_SPV act;
    class SYSTEM sys;
    class UC_LOGIN,UC_DASH,UC_UPDATE,UC_BUAT,UC_LIHAT,UC_PETUGAS,UC_LOGOUT uc;
    class UC_PCHT,UC_MMEA,UC_HPTL,UC_EXCEL,UC_CETAK,UC_VERIF,UC_BUAT_EXT ext;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.3: Use Case Diagram Hak Akses Kepala Unit (Supervisor)*

---

##### c. Tabel Deskripsi Skenario Use Case Sistem
Guna mendefinisikan secara terperinci mekanisme operasional setiap *use case*, berikut disajikan tabel deskripsi skenario yang selaras dengan antarmuka dan fungsionalitas sistem yang telah diimplementasikan (Tabel 3.1 s.d. Tabel 3.9):

*Tabel 3.1: Deskripsi Use Case Login*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Login |
| **Requirements** | Sistem harus dapat memvalidasi identitas pengguna berdasarkan kredensial Nomor Pegawai (NP), Username, atau Email beserta kata sandi untuk mengamankan hak akses fitur. |
| **Goal** | Pengguna (Operator atau Supervisor) berhasil masuk ke dalam sistem dengan hak akses menu yang sesuai perannya. |
| **Pre-Conditions** | Pengguna membuka peramban web dan berada pada halaman form login Sistem Informasi Hasil Produksi Harian. |
| **Post-Conditions** | Sistem mengalihkan pengguna ke halaman Dashboard utama sesuai dengan hak akses masing-masing aktor. |
| **Failed end Condition** | Sistem menolak akses pengguna, tetap berada di halaman login, dan menampilkan notifikasi kesalahan autentikasi. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. User memasukkan Nomor Pegawai (NP) / Username / Email dan kata sandi pada kolom yang disediakan.<br>2. User menekan tombol "Masuk ke Sistem".<br>3. Sistem memvalidasi kecocokan data kredensial dengan database `users`.<br>4. Sistem berhasil mengautentikasi dan mengarahkan user ke halaman Dashboard utama. |
| **Alternate Flow / Invariant A** | A1. User mencentang opsi "Ingat Saya" sebelum menekan tombol login.<br>A2. Sistem memperpanjang durasi sesi autentikasi pengguna pada perangkat browser. |
| **Invariant B** | B1. User memasukkan NP/Username/Email yang tidak terdaftar atau kata sandi yang salah.<br>B2. Sistem mendeteksi kegagalan autentikasi.<br>B3. Sistem menampilkan pesan error "Nomor Pegawai/Username atau Kata Sandi yang dimasukkan salah."<br>B4. User memasukkan kredensial milik akun yang dinonaktifkan.<br>B5. Sistem menampilkan pesan "Akun Anda sedang dinonaktifkan. Silakan hubungi Supervisor." |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.2: Deskripsi Use Case Logout*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Logout |
| **Requirements** | Sistem harus dapat mengakhiri sesi autentikasi pengguna secara aman dan membersihkan token sesi dari perangkat browser. |
| **Goal** | Pengguna mengakhiri sesi kerja operasional dan mencegah akses tidak sah ke sistem. |
| **Pre-Conditions** | Pengguna telah berhasil login dan berada di halaman manapun dalam sistem. |
| **Post-Conditions** | Sesi autentikasi pengguna dihapus dan sistem mengarahkan kembali ke halaman login. |
| **Failed end Condition** | Sesi tetap aktif akibat kegagalan penghapusan cookie sesi di perangkat browser. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna mengklik menu profil akun di pojok kanan atas antarmuka.<br>2. Pengguna memilih opsi "Keluar" atau "Logout".<br>3. Sistem menginvalidasi token sesi autentikasi.<br>4. Sistem mengalihkan pengguna kembali ke halaman login. |
| **Alternate Flow / Invariant A** | A1. Pengguna menutup tab browser tanpa melakukan logout eksplisit.<br>A2. Sesi autentikasi berakhir otomatis setelah durasi timeout sistem. |
| **Invariant B** | B1. Terjadi gangguan jaringan saat proses logout.<br>B2. Sistem menampilkan halaman error sementara, namun sesi tetap diakhiri di sisi server. |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.3: Deskripsi Use Case Mengakses Halaman Dashboard*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Mengakses Halaman Dashboard |
| **Requirements** | Sistem harus menyajikan ringkasan metrik produksi harian, grafik tren produksi dengan opsi rentang 7, 14, atau 30 hari, serta pintasan menu cepat setelah pengguna berhasil login. |
| **Goal** | Pengguna memperoleh gambaran umum capaian produksi harian dan dapat beralih ke menu operasional lainnya. |
| **Pre-Conditions** | Pengguna telah berhasil login (`<<include>>` Login). |
| **Post-Conditions** | Halaman Dashboard tampil lengkap dengan kartu KPI harian, grafik tren produksi, widget distribusi shift, top 5 mesin, dan daftar laporan terbaru. |
| **Failed end Condition** | Dashboard gagal dimuat akibat kegagalan query agregasi data produksi di server. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna login dan sistem mengalihkan ke halaman Dashboard.<br>2. Sistem menghitung metrik produksi hari berjalan: total kemas baik, reject, order diproses, dan jumlah berkas laporan.<br>3. Sistem menampilkan 5 kartu KPI harian: **Kemas Baik Hari Ini**, **Reject / Rusak**, **Order Diproses**, **Berkas Laporan**, dan **Petugas Shift**.<br>4. Sistem merender grafik tren produksi (Chart.js) untuk rentang waktu default (14 Hari) dengan opsi toggle **7 Hari**, **14 Hari**, atau **30 Hari**, serta pilihan jenis grafik **Area**, **Garis**, atau **Batang**.<br>5. Sistem menampilkan widget distribusi volume per shift, ranking top 5 mesin cetak, dan tabel 5 laporan produksi terkini beserta tombol **"Buat Laporan Baru"**. |
| **Alternate Flow / Invariant A** | A1. Pengguna mengubah rentang waktu grafik dengan menekan tombol 7 Hari / 14 Hari / 30 Hari, atau mengganti jenis grafik.<br>A2. Sistem memuat ulang data grafik sesuai rentang terpilih.<br>A3. (Khusus Supervisor) Supervisor mengklik pintasan **"Update Database SAP"** atau **"Kelola Data Petugas"** dari kartu aksi cepat di Dashboard.<br>A4. Sistem mengalihkan pengguna ke halaman menu terkait. |
| **Invariant B** | B1. Belum ada rekaman data transaksi pada rentang waktu yang dipilih.<br>B2. Sistem menampilkan grafik dengan nilai nol dan keterangan data kosong pada widget terkait. |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.4: Deskripsi Use Case Mengakses Menu Buat Laporan*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Mengakses Menu Buat Laporan |
| **Requirements** | Sistem harus menyediakan halaman inisiasi sesi laporan harian dengan pilihan tanggal, seri produk, dan shift kerja sebelum operator masuk ke halaman entri pemindaian barcode. |
| **Goal** | Operator dapat memulai atau melanjutkan sesi laporan produksi harian sesuai kategori seri produk dan giliran kerja. |
| **Pre-Conditions** | Operator telah berhasil login (`<<include>>` Login). |
| **Post-Conditions** | Sesi laporan harian terbuka dan sistem mengalihkan operator ke halaman entri pemindaian barcode. |
| **Failed end Condition** | Sesi laporan gagal dibuat akibat kegagalan penyimpanan ke basis data. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Operator mengklik menu "Buat Laporan" pada navigasi utama.<br>2. Sistem menampilkan halaman **"Inisiasi Sesi Laporan Produksi"** dengan form pilihan tanggal, kategori seri produk (PCHT / MMEA / HPTL), shift kerja, dan catatan sesi opsional. Sistem secara otomatis mendeteksi dan pra-mengisi shift kerja yang sedang aktif berdasarkan waktu saat ini (WIB).<br>3. Sistem menampilkan pratinjau kode laporan sesi secara otomatis (contoh: `PCHT-2026-08-26-S1`).<br>4. Operator menekan tombol **"Buka Sesi Pemindaian PO"** (atau **"Lanjutkan Sesi Pemindaian PO"** jika sesi sudah ada).<br>5. Sistem membuat record laporan baru berstatus `Draft` atau membuka sesi yang sudah ada, lalu mengalihkan ke halaman konsol pemindaian barcode. |
| **Alternate Flow / Invariant A** | A1. Sesi laporan untuk kombinasi tanggal, seri produk, dan shift yang sama sudah pernah dibuat.<br>A2. Sistem mendeteksi sesi aktif dan langsung membuka halaman konsol pemindaian tanpa membuat duplikat, dengan notifikasi "Sesi laporan {kode} ditemukan. Membuka sesi aktif...". |
| **Invariant B** | B1. Operator tidak memilih seri produk atau shift kerja.<br>B2. Sistem menolak inisiasi sesi.<br>B3. Sistem menampilkan pesan validasi "Kategori seri produk wajib dipilih" atau "Shift kerja wajib dipilih". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.5: Deskripsi Use Case Pemindaian Barcode & Entri Kemas (PCHT / MMEA / HPTL)*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Pemindaian Barcode & Entri Kemas (PCHT / MMEA / HPTL) |
| **Requirements** | Sistem harus mampu menerima data hasil scanner barcode Code 128, melakukan lookup instan ke master data SAP, menyimpan item langsung ke basis data, dan mengubah status laporan dari Draft menjadi Terkirim setelah selesai. |
| **Goal** | Operator menyelesaikan pencatatan hasil pengemasan harian untuk seri produk tertentu melalui konsol pemindaian barcode berkecepatan tinggi. |
| **Pre-Conditions** | Operator telah membuka sesi laporan melalui menu Buat Laporan (`<<extend>>` dari Mengakses Menu Buat Laporan) dan berada di halaman konsol pemindaian (`/reports/{kode}/entry`). |
| **Post-Conditions** | Item PO tersimpan di tabel `daily_report_items` dan laporan berstatus `submitted` (Terkirim / Selesai Scan) setelah operator menekan "Selesaikan Laporan". |
| **Failed end Condition** | Data pesanan gagal dimuat karena Nomor PO belum diimpor ke master data SAP. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Operator memindai barcode Code 128 pada Kartu Mesin Produksi ke kolom input **"1. Scan / Masukkan No PO Barcode"**.<br>2. Sistem mencari data pada tabel `production_orders` dan menampilkan Nama Produk, Spesifikasi, Nomor Mesin, dan Target Kuantitas SAP.<br>3. Operator menginputkan jumlah kemas pada kolom **"2. Jumlah Kemas (Lembar)"** dan menekan **"Simpan Item (Enter)"**.<br>4. Sistem menyimpan baris item ke basis data, menampilkan notifikasi sukses, dan mengembalikan fokus ke input barcode untuk PO berikutnya.<br>5. Operator dapat mengoreksi kuantitas secara langsung pada tabel (inline edit) atau menghapus baris item selama status laporan masih `Draft`.<br>6. Setelah seluruh Kartu Mesin shift tercatat, operator menekan **"Selesaikan Laporan"**.<br>7. Sistem mengubah status laporan dari `Draft` menjadi `Terkirim (Selesai Scan)` dan mengunci sesi dari penambahan item baru oleh operator. |
| **Alternate Flow / Invariant A** | A1. (Khusus Supervisor) Supervisor dapat menekan **"Buka Kembali Sesi"** pada laporan berstatus Terkirim untuk mengembalikan status ke `Draft`.<br>A2. Supervisor dapat menambah atau mengubah item kemas meskipun laporan sudah berstatus Terkirim. |
| **Invariant B** | B1. Operator memindai Nomor PO yang belum terdaftar di master SAP.<br>B2. Sistem membunyikan nada peringatan dan menampilkan notifikasi "No PO '{po_number}' tidak ditemukan pada Master Data SAP. Pastikan file SAP sudah diimpor."<br>B3. Operator mencoba menambah item saat laporan sudah bukan status `Draft`.<br>B4. Sistem menolak dan menampilkan "Laporan sudah diselesaikan dan terkunci dari penambahan item baru." |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.6: Deskripsi Use Case Melihat Laporan*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Melihat Laporan |
| **Requirements** | Sistem harus menyajikan arsip laporan produksi harian dengan filter multi-kriteria, rincian item per laporan, dan aksi verifikasi (khusus Supervisor). |
| **Goal** | Pengguna dapat meninjau riwayat laporan harian, melihat rincian item kemas, dan melakukan tindakan lanjutan seperti verifikasi atau cetak dokumen. |
| **Pre-Conditions** | Pengguna telah berhasil login ke dalam sistem. |
| **Post-Conditions** | Sistem menampilkan tabel arsip laporan atau rincian detail laporan yang dipilih. |
| **Failed end Condition** | Data laporan gagal dimuat akibat parameter filter yang tidak valid. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna mengklik menu "Lihat Laporan" pada navigasi utama.<br>2. Sistem menampilkan halaman arsip **"Laporan Produksi"** dengan 3 kartu statistik ringkasan (Total Berkas Laporan, Total Lembar Kemas Baik, Total Lembar Reject) dan tabel daftar laporan.<br>3. Pengguna memfilter berdasarkan kata kunci, tanggal, seri produk, shift, status (Draft / Terkirim / Terverifikasi), atau nama petugas.<br>4. Pengguna menekan tombol aksi **"Lihat Rincian"** pada baris laporan untuk membuka halaman detail.<br>5. Sistem menampilkan kartu metadata laporan, metrik realisasi, tabel rincian item PO, dan tombol aksi sesuai peran pengguna. |
| **Alternate Flow / Invariant A** | A1. Pengguna menekan **"Buat Laporan Baru"** dari halaman arsip untuk membuka sesi laporan baru.<br>A2. Pengguna menekan **"Ekspor Excel"** untuk mengunduh rekapitulasi data (lihat Tabel 3.7).<br>A3. Pengguna menekan **"Cetak Lembar Fisik"** untuk membuka tampilan cetak A4 (`/reports/{kode}/print`).<br>A4. (Khusus Operator) Operator menekan **"Koreksi Laporan"** untuk membuka halaman edit jika laporan milik sendiri, belum diverifikasi, dan masih dalam batas 24 jam.<br>A5. (Khusus Supervisor) Supervisor menekan **"Verifikasi Laporan"** pada halaman rincian untuk mengubah status laporan menjadi `Terverifikasi (Disetujui Ka. Unit)`.<br>A6. (Khusus Supervisor) Supervisor dapat menghapus laporan melalui modal konfirmasi **"Hapus Permanen"**.<br>A7. (Khusus Supervisor) Supervisor dapat menekan **"Buka Kembali Sesi"** pada konsol pemindaian laporan berstatus Terkirim untuk mengembalikan status ke `Draft`.<br>A8. (Khusus Supervisor) Supervisor dapat membuka halaman **"Koreksi Laporan"** tanpa batasan waktu dan tanpa syarat kepemilikan. |
| **Invariant B** | B1. Operator mencoba mengoreksi laporan milik operator lain atau laporan yang sudah lewat 24 jam.<br>B2. Sistem menolak akses edit dan tombol "Koreksi Laporan" tidak ditampilkan.<br>B3. Operator mencoba mengubah laporan berstatus `verified`.<br>B4. Sistem menampilkan peringatan bahwa laporan telah diverifikasi dan terkunci. |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.7: Deskripsi Use Case Ekspor Excel*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Ekspor Excel |
| **Requirements** | Sistem harus dapat mengekspor rekapitulasi data laporan produksi harian ke berkas spreadsheet Microsoft Excel (`.xlsx`) berdasarkan filter rentang tanggal, seri produk, shift, dan status. |
| **Goal** | Pengguna memperoleh salinan digital data laporan dalam format Excel untuk keperluan arsip, analisis lanjutan, atau pelaporan administratif. |
| **Pre-Conditions** | Pengguna berada di halaman "Lihat Laporan" (`<<extend>>` dari Melihat Laporan). |
| **Post-Conditions** | Berkas Excel (`.xlsx`) berhasil diunduh ke perangkat komputer lokal pengguna. |
| **Failed end Condition** | Ekspor gagal karena tidak ada data laporan yang sesuai dengan filter yang dipilih. |
| **Actors** | Operator, Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Pengguna menekan tombol "Ekspor Excel" pada halaman Lihat Laporan.<br>2. Sistem menampilkan modal "Ekspor Rekapitulasi Produksi (Excel)" dengan filter rentang tanggal, seri produk, shift, dan status.<br>3. Pengguna menentukan parameter filter dan menekan "Unduh File Excel (.xlsx)".<br>4. Sistem memproses query agregasi data laporan sesuai filter melalui endpoint `/reports/export`.<br>5. Sistem menghasilkan berkas `.xlsx` dan memicu unduhan otomatis ke perangkat lokal. |
| **Alternate Flow / Invariant A** | A1. Pengguna memfilter hanya seri produk PCHT dan shift Shift 1.<br>A2. Sistem mengekspor data terbatas sesuai kriteria filter yang dipilih. |
| **Invariant B** | B1. Rentang tanggal filter tidak valid (tanggal akhir lebih awal dari tanggal awal).<br>B2. Sistem menolak proses ekspor.<br>B3. Sistem menampilkan pesan validasi "Rentang tanggal tidak valid". |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.8: Deskripsi Use Case Update Database*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Update Database |
| **Requirements** | Sistem harus menyediakan antarmuka impor berkas SAP (`.xlsx`, `.xls`, `.csv`), penjelajah dan pengelolaan data order produksi (CRUD manual), riwayat impor, serta unduh template SAP. |
| **Goal** | Master data pesanan produksi SAP terbarui di basis data aplikasi sebagai referensi instan pemindaian barcode kartu mesin. |
| **Pre-Conditions** | Kepala Unit (Supervisor) telah login dan memiliki akses menu "Update Database". |
| **Post-Conditions** | Data pesanan tersimpan di tabel `production_orders` dan riwayat impor tercatat di tabel `sap_import_logs`. |
| **Failed end Condition** | Berkas ditolak karena format ekstensi tidak didukung atau struktur kolom wajib tidak sesuai standar. |
| **Actors** | Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Supervisor mengklik menu "Update Database" pada navigasi utama.<br>2. Sistem menampilkan halaman "Update Database SAP" dengan dua tab: **Impor SAP** dan **Data Order Produksi**.<br>3. (Tab Impor) Supervisor mengunggah berkas ekspor SAP, sistem menampilkan pratinjau validasi baris, lalu Supervisor menekan konfirmasi impor.<br>4. Sistem melakukan *bulk upsert* ke tabel `production_orders` dan mencatat riwayat impor.<br>5. (Tab Data Order) Supervisor dapat menambah order manual, mengubah spesifikasi order, menghapus order, memfilter data, dan mengekspor master data ke Excel. |
| **Alternate Flow / Invariant A** | A1. Berkas berisi Nomor PO yang sudah ada di basis data dengan revisi target kuantitas.<br>A2. Sistem secara cerdas memperbarui data yang ada (*update*) tanpa menduplikasi rekaman data. |
| **Invariant B** | B1. Supervisor mengunggah berkas dengan tipe file yang tidak valid (misal: `.pdf` atau `.docx`).<br>B2. Sistem membatalkan proses unggah.<br>B3. Sistem menampilkan pesan error "Format berkas tidak didukung. Harap unggah berkas spreadsheet dengan ekstensi .xlsx, .xls, atau .csv." |
*(Sumber: Hasil Penelitian, 2026)*

*Tabel 3.9: Deskripsi Use Case Mengelola Data Petugas*
| Elemen Use Case | Deskripsi Skenario |
|---|---|
| **Use Case Name** | Mengelola Data Petugas |
| **Requirements** | Sistem harus menyediakan fasilitas bagi Supervisor untuk menambah, melihat, mengubah profil, menonaktifkan, menghapus, dan mereset kata sandi akun operator pengemasan. |
| **Goal** | Data akun pengguna sistem terkelola secara terpusat, aman, dan tertib berdasarkan identitas Nomor Pegawai (NP/NIP). |
| **Pre-Conditions** | Kepala Unit (Supervisor) telah berhasil login ke dalam sistem. |
| **Post-Conditions** | Data akun petugas baru tersimpan, terbarui, dinonaktifkan, atau dihapus dari basis data `users`. |
| **Failed end Condition** | Pendaftaran atau pembaruan akun gagal akibat duplikasi Nomor Pegawai (NP/NIP) atau email di basis data. |
| **Actors** | Kepala Unit (Supervisor) |
| **Main Flow / Basic Path** | 1. Supervisor mengklik menu "Data Petugas" pada navigasi utama.<br>2. Sistem menampilkan tabel daftar akun petugas dengan filter pencarian nama/NIP, peran, dan status aktif.<br>3. Supervisor menekan "Tambah Petugas Baru" atau ikon edit pada baris akun petugas tertentu.<br>4. Sistem menampilkan modal form isian: Nama Lengkap, NIP/NP, Username, Email, Peran (Petugas / Supervisor), Kata Sandi, dan Status Aktif.<br>5. Supervisor menyimpan data dan sistem memvalidasi serta menyimpan ke tabel `users`. |
| **Alternate Flow / Invariant A** | A1. Supervisor menonaktifkan akun petugas melalui opsi toggle status aktif/nonaktif pada baris tabel.<br>A2. Supervisor menghapus akun petugas melalui modal konfirmasi hapus (ditolak jika akun sudah tercatat pada laporan produksi, atau jika akun adalah akun milik sendiri). |
| **Invariant B** | B1. Supervisor mendaftarkan Nomor Pegawai (NP) atau Email yang sudah terdaftar sebelumnya.<br>B2. Sistem menolak proses penyimpanan.<br>B3. Sistem menampilkan notifikasi validasi duplikasi data. |
*(Sumber: Hasil Penelitian, 2026)*

---

#### 2. Activity Diagram
Activity Diagram memodelkan aliran aktivitas operasional dan logika transisi sistem pada masing-masing fitur utama dengan membagi peran ke dalam partisi jalur aktivitas (*swimlanes*). Setiap diagram disusun berdasarkan alur halaman dan aksi yang benar-benar diimplementasikan pada aplikasi web (menu navigasi, tombol aksi, status laporan, dan validasi sistem). Untuk fitur yang memiliki beberapa operasi berbeda (seperti CRUD), setiap operasi dimodelkan dalam diagram terpisah guna meningkatkan keterbacaan dan ketepatan pemodelan alur.

##### a. Activity Diagram Login Sistem
Diagram ini menggambarkan alur kerja saat pengguna (Operator atau Supervisor) melakukan autentikasi ke dalam sistem melalui halaman login Fortify/Livewire:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Pengguna (Operator / Supervisor)"]
        P_Start(( ))
        P_Buka(Buka Halaman<br/>Login)
        P_Input(Input NP / Username /<br/>Email dan Password)
        P_Kirim(Klik Tombol<br/>Masuk ke Sistem)
        P_Dash(Tampil Halaman<br/>Dashboard)
        P_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Form(Tampilkan Form<br/>Login)
        S_Val{Kredensial<br/>Valid?}
        S_Aktif{Akun<br/>Aktif?}
        S_Err(Tampilkan Pesan<br/>Kredensial Salah)
        S_RateCheck{Percobaan<br/>Gagal >= 5?}
        S_Lockout(Tampilkan Pesan<br/>Terlalu Banyak Percobaan<br/>& Kunci Sementara)
        S_NonAktif(Tampilkan Pesan<br/>Akun Dinonaktifkan)
        S_Auth(Inisialisasi Sesi &<br/>Regenerasi Token)
    end

    P_Start --> P_Buka
    P_Buka --> S_Form
    S_Form --> P_Input
    P_Input --> P_Kirim
    P_Kirim --> S_Val
    S_Val -- Tidak Valid --> S_RateCheck
    S_RateCheck -- Ya --> S_Lockout
    S_Lockout --> S_Form
    S_RateCheck -- Belum --> S_Err
    S_Err --> S_Form
    S_Val -- Valid --> S_Aktif
    S_Aktif -- Tidak --> S_NonAktif
    S_NonAktif --> S_Form
    S_Aktif -- Ya --> S_Auth
    S_Auth --> P_Dash
    P_Dash --> P_End

    style P_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style P_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.4: Activity Diagram Login Sistem*

##### b. Activity Diagram Tambah Data Petugas
Diagram ini memodelkan alur penambahan akun petugas baru oleh Supervisor melalui form modal pada menu Data Petugas. Supervisor mengisi data akun lengkap (nama, NP, username, email, peran, dan kata sandi), kemudian sistem memvalidasi keunikan sebelum menyimpan ke tabel `users`:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu<br/>Data Petugas)
        SP_TambahBtn(Klik Tombol<br/>+ Tambah Petugas Baru)
        SP_IsiForm(Isi Form: Nama, NP,<br/>Username, Email, Peran, Password)
        SP_Simpan(Klik Simpan Data)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel<br/>Daftar Petugas)
        SS_Modal["Tampilkan Modal Form<br/>Tambah Petugas - Kosong"]
        SS_Val{Validasi: NP, Email,<br/>Username Unik &<br/>Password Min 8 Karakter?}
        SS_Err(Tampilkan Pesan<br/>Galat Validasi)
        SS_Simpan(Simpan Rekaman Akun<br/>Baru ke Tabel users)
        SS_Notif(Tutup Modal &<br/>Tampilkan Notifikasi Sukses)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_TambahBtn
    SP_TambahBtn --> SS_Modal
    SS_Modal --> SP_IsiForm
    SP_IsiForm --> SP_Simpan
    SP_Simpan --> SS_Val
    SS_Val -- Tidak Valid --> SS_Err
    SS_Err --> SS_Modal
    SS_Val -- Valid --> SS_Simpan
    SS_Simpan --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.5: Activity Diagram Tambah Data Petugas*

##### c. Activity Diagram Edit Data Petugas
Diagram ini memodelkan alur pengubahan data akun petugas yang sudah ada. Supervisor memilih baris petugas yang ingin diubah, sistem memuat data ke form modal (*pre-filled*), Supervisor memodifikasi data, lalu sistem memvalidasi sebelum memperbarui rekaman di tabel `users`. Kolom kata sandi bersifat opsional — jika dikosongkan, kata sandi lama tidak berubah:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu<br/>Data Petugas)
        SP_EditBtn(Klik Ikon Edit<br/>pada Baris Petugas)
        SP_UbahForm["Ubah Data Form:<br/>Nama, NP, Username, Email,<br/>Peran, Password - Opsional"]
        SP_Simpan(Klik Simpan Perubahan)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel<br/>Daftar Petugas)
        SS_Load(Muat Data Petugas &<br/>Tampilkan Modal Form Pre-Filled)
        SS_Val{"Validasi: NP, Email,<br/>Username Unik<br/>kecuali Milik Sendiri?"}
        SS_Err(Tampilkan Pesan<br/>Galat Validasi)
        SS_Update(Perbarui Rekaman Akun<br/>di Tabel users)
        SS_Notif(Tutup Modal &<br/>Tampilkan Notifikasi Sukses)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_EditBtn
    SP_EditBtn --> SS_Load
    SS_Load --> SP_UbahForm
    SP_UbahForm --> SP_Simpan
    SP_Simpan --> SS_Val
    SS_Val -- Tidak Valid --> SS_Err
    SS_Err --> SS_Load
    SS_Val -- Valid --> SS_Update
    SS_Update --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.6: Activity Diagram Edit Data Petugas*

##### d. Activity Diagram Nonaktifkan / Aktifkan Status Petugas
Diagram ini memodelkan alur pengubahan status aktif akun petugas melalui toggle langsung di tabel Data Petugas. Sistem membaca kondisi flag `is_active` saat ini dan membaliknya (aktif ↔ nonaktif) tanpa memerlukan konfirmasi modal:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu<br/>Data Petugas)
        SP_Toggle(Klik Toggle Status<br/>pada Baris Petugas)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel Daftar Petugas<br/>dengan Kolom Status)
        SS_Cek{Status is_active<br/>Saat Ini?}
        SS_Nonaktif(Set is_active = false<br/>Nonaktifkan Akun Petugas)
        SS_Aktif(Set is_active = true<br/>Aktifkan Kembali Akun)
        SS_Notif(Perbarui Tampilan Tabel &<br/>Tampilkan Notifikasi Sukses)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_Toggle
    SP_Toggle --> SS_Cek
    SS_Cek -- "Aktif - true" --> SS_Nonaktif
    SS_Cek -- "Nonaktif - false" --> SS_Aktif
    SS_Nonaktif --> SS_Notif
    SS_Aktif --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.7: Activity Diagram Nonaktifkan / Aktifkan Status Petugas*

##### e. Activity Diagram Hapus Data Petugas
Diagram ini memodelkan alur penghapusan akun petugas oleh Supervisor. Terdapat dua lapisan perlindungan: sistem menolak penghapusan akun milik Supervisor sendiri, dan menolak penghapusan akun yang sudah terkait dengan data laporan produksi (menyarankan nonaktifkan saja):

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu<br/>Data Petugas)
        SP_HapusBtn(Klik Ikon Hapus<br/>pada Baris Petugas)
        SP_Konfirm(Konfirmasi Hapus<br/>di Modal)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel<br/>Daftar Petugas)
        SS_ModalKonfirm(Tampilkan Modal<br/>Konfirmasi Hapus)
        SS_SelfCheck{Akun yang Dipilih<br/>Milik Sendiri?}
        SS_SelfErr(Tolak Hapus &<br/>Tampilkan Pesan Error)
        SS_LaporanCheck{Akun Terkait<br/>Data Laporan?}
        SS_LaporanErr(Tolak Hapus &<br/>Sarankan Nonaktifkan Saja)
        SS_Hapus(Hapus Rekaman Akun<br/>dari Tabel users)
        SS_Notif(Tampilkan Notifikasi<br/>Hapus Berhasil)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_HapusBtn
    SP_HapusBtn --> SS_ModalKonfirm
    SS_ModalKonfirm --> SP_Konfirm
    SP_Konfirm --> SS_SelfCheck
    SS_SelfCheck -- Ya --> SS_SelfErr
    SS_SelfErr --> SS_Tampil
    SS_SelfCheck -- Tidak --> SS_LaporanCheck
    SS_LaporanCheck -- Ya --> SS_LaporanErr
    SS_LaporanErr --> SS_Tampil
    SS_LaporanCheck -- Tidak --> SS_Hapus
    SS_Hapus --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.8: Activity Diagram Hapus Data Petugas*

##### f. Activity Diagram Impor Data Master SAP
Diagram ini menggambarkan alur kerja Supervisor mengunggah berkas ekspor SAP ke dalam sistem melalui tab Impor SAP pada menu Update Database. Sistem memvalidasi format berkas, melakukan *parsing* dan pratinjau baris data, lalu mengeksekusi operasi *bulk upsert* ke tabel `production_orders`:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu Update Database,<br/>Pilih Tab Impor SAP)
        SP_Pilih["Pilih & Unggah Berkas SAP<br/>.xlsx / .xls / .csv, Maks. 10 MB"]
        SP_Konfirm(Konfirmasi Impor Data<br/>di Modal Pratinjau)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Halaman<br/>Update Database)
        SS_ValFile{Validasi Format &<br/>Ukuran Berkas?}
        SS_FileErr(Tampilkan Pesan<br/>Format Berkas Tidak Sesuai)
        SS_Parse(Parsing Berkas & Validasi<br/>Baris Data per Kolom SAP)
        SS_Preview(Tampilkan Modal Pratinjau:<br/>Jumlah Baris Valid & Error)
        SS_Upsert["Bulk Upsert ke Tabel<br/>production_orders - status ready"]
        SS_Log(Catat ke sap_import_logs &<br/>Tampilkan Notifikasi Sukses)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_Pilih
    SP_Pilih --> SS_ValFile
    SS_ValFile -- Tidak Valid --> SS_FileErr
    SS_FileErr --> SS_Tampil
    SS_ValFile -- Valid --> SS_Parse
    SS_Parse --> SS_Preview
    SS_Preview --> SP_Konfirm
    SP_Konfirm --> SS_Upsert
    SS_Upsert --> SS_Log
    SS_Log --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.9: Activity Diagram Impor Data Master SAP*

##### g. Activity Diagram Tambah & Edit Order Produksi Manual
Diagram ini memodelkan alur penambahan dan pengeditan data order produksi secara manual oleh Supervisor melalui tab Master Data Orders. Kedua operasi menggunakan form modal yang sama — tambah membuka form kosong, sedangkan edit memuat data order yang dipilih (*pre-filled*):

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu Update Database,<br/>Pilih Tab Master Data Orders)
        SP_Pilih{Pilih Aksi?}
        SP_TambahBtn(Klik Tombol<br/>+ Tambah Order Baru)
        SP_EditBtn(Klik Ikon Edit<br/>pada Baris Order)
        SP_IsiForm(Isi / Ubah Data Form Order:<br/>No PO, Produk, Target Qty, dll.)
        SP_Simpan(Klik Simpan Data)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel Order Produksi<br/>dengan Filter)
        SS_ModalKosong["Tampilkan Modal Form<br/>Tambah Order - Kosong"]
        SS_ModalIsi(Muat Data Order &<br/>Tampilkan Modal Form Pre-Filled)
        SS_Val{Validasi: No PO Unik<br/>& Target Qty > 0?}
        SS_Err(Tampilkan Pesan<br/>Galat Validasi)
        SS_Save(Simpan / Perbarui Record<br/>di production_orders)
        SS_Notif(Tutup Modal &<br/>Tampilkan Notifikasi Sukses)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_Pilih
    SP_Pilih -- Tambah --> SP_TambahBtn
    SP_TambahBtn --> SS_ModalKosong
    SS_ModalKosong --> SP_IsiForm
    SP_Pilih -- Edit --> SP_EditBtn
    SP_EditBtn --> SS_ModalIsi
    SS_ModalIsi --> SP_IsiForm
    SP_IsiForm --> SP_Simpan
    SP_Simpan --> SS_Val
    SS_Val -- Tidak Valid --> SS_Err
    SS_Err --> SS_ModalKosong
    SS_Val -- Valid --> SS_Save
    SS_Save --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.10: Activity Diagram Tambah & Edit Order Produksi Manual*

##### h. Activity Diagram Hapus Order Produksi
Diagram ini memodelkan alur penghapusan rekaman order produksi dari master data. Sistem menerapkan perlindungan: order yang sedang berstatus `in_progress` tidak dapat dihapus untuk menjaga integritas data laporan yang sedang aktif:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Menu Update Database,<br/>Pilih Tab Master Data Orders)
        SP_HapusBtn(Klik Ikon Hapus<br/>pada Baris Order)
        SP_Konfirm(Konfirmasi Hapus<br/>di Modal)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Tabel<br/>Order Produksi)
        SS_ModalKonfirm(Tampilkan Modal<br/>Konfirmasi Hapus)
        SS_StatusCheck{Status Order<br/>= in_progress?}
        SS_StatusErr(Tolak Hapus & Tampilkan Pesan:<br/>Order Sedang Berjalan)
        SS_Hapus(Hapus Record Order<br/>dari production_orders)
        SS_Notif(Tampilkan Notifikasi<br/>Hapus Berhasil)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Tampil
    SS_Tampil --> SP_HapusBtn
    SP_HapusBtn --> SS_ModalKonfirm
    SS_ModalKonfirm --> SP_Konfirm
    SP_Konfirm --> SS_StatusCheck
    SS_StatusCheck -- Ya --> SS_StatusErr
    SS_StatusErr --> SS_Tampil
    SS_StatusCheck -- Tidak --> SS_Hapus
    SS_Hapus --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.11: Activity Diagram Hapus Order Produksi*

##### i. Activity Diagram Inisiasi Sesi Laporan Harian
Diagram ini memodelkan alur pembuatan sesi laporan harian melalui menu Buat Laporan. Sistem mendeteksi dan pra-mengisi shift aktif secara otomatis. Jika sesi dengan kombinasi seri produk, tanggal, dan shift yang sama sudah ada di basis data, sistem membuka sesi tersebut secara langsung tanpa membuat duplikat:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Operator ["Operator / Supervisor"]
        P_Start(( ))
        P_Buka(Buka Menu<br/>Buat Laporan)
        P_Pilih["Pilih Tanggal, Seri Produk<br/>PCHT / MMEA / HPTL, & Shift"]
        P_Launch(Klik Tombol<br/>Buka / Lanjutkan Sesi)
        P_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Form(Tampilkan Form<br/>Inisiasi Sesi)
        S_AutoShift["Deteksi & Pra-isi Shift<br/>Aktif Saat Ini - ShiftHelper WIB"]
        S_Val{Validasi<br/>Form Input?}
        S_ValErr(Tampilkan Pesan<br/>Galat Validasi)
        S_CekSesi{"Sesi Seri, Tanggal,<br/>Shift Sudah Ada<br/>di Basis Data?"}
        S_BukaSesi(Buka Sesi Existing &<br/>Redirect ke Konsol Scan)
        S_BuatKode(Generate Kode Laporan:<br/>format SERI-YYYY-MM-DD-SN)
        S_BuatSesi(Buat Record DailyReport Baru<br/>dengan Status: draft)
        S_Redirect(Redirect ke<br/>Konsol Pemindaian Barcode)
    end

    P_Start --> P_Buka
    P_Buka --> S_Form
    S_Form --> S_AutoShift
    S_AutoShift --> P_Pilih
    P_Pilih --> P_Launch
    P_Launch --> S_Val
    S_Val -- Tidak Valid --> S_ValErr
    S_ValErr --> S_Form
    S_Val -- Valid --> S_CekSesi
    S_CekSesi -- Sudah Ada --> S_BukaSesi
    S_BukaSesi --> P_End
    S_CekSesi -- Belum Ada --> S_BuatKode
    S_BuatKode --> S_BuatSesi
    S_BuatSesi --> S_Redirect
    S_Redirect --> P_End

    style P_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style P_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.12: Activity Diagram Inisiasi Sesi Laporan Harian*

##### j. Activity Diagram Scan Barcode, Kelola Item & Finalisasi Laporan
Diagram ini memodelkan alur utama pada konsol pemindaian barcode setelah sesi laporan dibuka. Alur mencakup pencarian Nomor PO, peringatan duplikat (tidak memblokir), penambahan item, pengeditan kuantitas *inline*, penghapusan item, dan finalisasi laporan:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Operator ["Operator / Supervisor"]
        P_Start(( ))
        P_Konsol(Berada di Konsol<br/>Pemindaian Barcode)
        P_Scan(Scan / Input<br/>No. PO Barcode)
        P_Qty["Input Jumlah Kemas<br/>Lembar"]
        P_Simpan(Klik Simpan Item<br/>atau Tekan Enter)
        P_AksiTabel{Pilih Aksi<br/>pada Item di Tabel?}
        P_EditQty(Edit Kuantitas<br/>Inline di Tabel)
        P_HapusItem(Klik Hapus Item<br/>di Tabel)
        P_CekLanjut{Masih Ada<br/>Kartu Mesin Lain?}
        P_Selesai(Klik Selesaikan<br/>Laporan)
        P_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Tampil(Tampilkan Konsol Scan &<br/>Tabel Item Draft)
        S_CariPO{Cari No. PO di<br/>production_orders?}
        S_POErr(Tampilkan Peringatan:<br/>PO Tidak Ditemukan)
        S_CekDuplikat{PO Sudah Discan<br/>di Sesi Ini?}
        S_DuplikatWarn["Tampilkan Peringatan<br/>Duplikat PO - Dapat Dilanjutkan"]
        S_InfoPO(Tampilkan Spesifikasi Produk<br/>Otomatis dari Master SAP)
        S_SimpanItem(Simpan Item ke<br/>daily_report_items)
        S_RefreshTotal(Refresh Total<br/>Laporan)
        S_UpdateQty(Perbarui Kuantitas Item<br/>di Basis Data)
        S_HapusItem(Hapus Baris Item<br/>dari Basis Data)
        S_CekItem{Minimal 1 Item<br/>Sudah Tersimpan?}
        S_ItemErr(Tampilkan Peringatan:<br/>Laporan Masih Kosong)
        S_Finalisasi(Ubah Status DailyReport:<br/>draft → submitted)
        S_Notif(Tampilkan Notifikasi<br/>Laporan Berhasil Diselesaikan)
    end

    P_Start --> P_Konsol
    P_Konsol --> S_Tampil
    S_Tampil --> P_Scan
    P_Scan --> S_CariPO
    S_CariPO -- Tidak Ditemukan --> S_POErr
    S_POErr --> S_Tampil
    S_CariPO -- Ditemukan --> S_CekDuplikat
    S_CekDuplikat -- Ya --> S_DuplikatWarn
    S_DuplikatWarn --> S_InfoPO
    S_CekDuplikat -- Tidak --> S_InfoPO
    S_InfoPO --> P_Qty
    P_Qty --> P_Simpan
    P_Simpan --> S_SimpanItem
    S_SimpanItem --> S_RefreshTotal
    S_RefreshTotal --> P_AksiTabel
    P_AksiTabel -- Edit Qty Inline --> P_EditQty
    P_EditQty --> S_UpdateQty
    S_UpdateQty --> S_RefreshTotal
    P_AksiTabel -- Hapus Item --> P_HapusItem
    P_HapusItem --> S_HapusItem
    S_HapusItem --> S_RefreshTotal
    P_AksiTabel -- Scan PO Berikutnya --> P_CekLanjut
    P_CekLanjut -- Ya --> P_Scan
    P_CekLanjut -- Tidak --> P_Selesai
    P_Selesai --> S_CekItem
    S_CekItem -- Belum Ada Item --> S_ItemErr
    S_ItemErr --> S_Tampil
    S_CekItem -- Ada Item --> S_Finalisasi
    S_Finalisasi --> S_Notif
    S_Notif --> P_End

    style P_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style P_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.13: Activity Diagram Scan Barcode, Kelola Item & Finalisasi Laporan*

##### k. Activity Diagram Melihat Arsip & Filter Laporan
Diagram ini memodelkan alur penelusuran riwayat laporan pada halaman Lihat Laporan, termasuk penerapan filter multi-kriteria (tanggal, seri produk, shift, status, petugas) dan navigasi menuju halaman detail laporan:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Operator / Supervisor"]
        U_Start(( ))
        U_Buka(Buka Menu<br/>Lihat Laporan)
        U_AksiArsip{Pilih Aksi<br/>di Halaman Arsip?}
        U_Filter(Set Filter: Tanggal,<br/>Seri, Shift, Status, Petugas)
        U_Reset(Klik Tombol<br/>Reset Filter)
        U_Detail(Klik Laporan untuk<br/>Membuka Halaman Detail)
        U_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Arsip(Tampilkan Arsip Laporan<br/>dengan Statistik Ringkasan<br/>& Pagination)
        S_Filter(Terapkan Filter<br/>Multi-Kriteria ke Query)
        S_Perbarui(Perbarui Tabel<br/>Arsip Laporan)
        S_ResetFilter(Reset Semua Filter<br/>ke Kondisi Awal)
        S_Detail(Load Halaman<br/>Detail Laporan)
    end

    U_Start --> U_Buka
    U_Buka --> S_Arsip
    S_Arsip --> U_AksiArsip
    U_AksiArsip -- Terapkan Filter --> U_Filter
    U_Filter --> S_Filter
    S_Filter --> S_Perbarui
    S_Perbarui --> S_Arsip
    U_AksiArsip -- Reset Filter --> U_Reset
    U_Reset --> S_ResetFilter
    S_ResetFilter --> S_Arsip
    U_AksiArsip -- Lihat Detail --> U_Detail
    U_Detail --> S_Detail
    S_Detail --> U_End

    style U_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style U_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.14: Activity Diagram Melihat Arsip & Filter Laporan*

##### l. Activity Diagram Ekspor & Cetak Laporan
Diagram ini menggambarkan dua jalur output laporan: ekspor rekapitulasi ke berkas Excel (.xlsx) menggunakan OpenSpout dari halaman arsip, dan cetak lembar fisik standar A4 / unduh PDF dari halaman detail laporan:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Operator / Supervisor"]
        U_Start(( ))
        U_Pilih{Pilih Metode<br/>Output Laporan?}
        U_FilterExcel(Set Filter Ekspor:<br/>Tanggal, Seri, Shift, Status)
        U_EksporBtn(Klik Tombol<br/>Ekspor di Modal)
        U_CetakBtn(Klik Tombol Cetak Lembar Fisik<br/>/ Unduh PDF di Halaman Detail)
        U_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_ModalEkspor(Tampilkan Modal<br/>Filter Ekspor Excel)
        S_GenerateXlsx(Generate Berkas .xlsx<br/>via ReportExportController)
        S_DownloadXlsx["Kirim Berkas .xlsx<br/>ke Browser - Download"]
        S_RenderPrint["Render Tampilan Cetak<br/>Standar A4 - Blade Print View"]
        S_PrintDialog(Buka Dialog Cetak Browser<br/>atau Unduh PDF)
    end

    U_Start --> U_Pilih
    U_Pilih -- "Ekspor Excel dari Arsip" --> S_ModalEkspor
    S_ModalEkspor --> U_FilterExcel
    U_FilterExcel --> U_EksporBtn
    U_EksporBtn --> S_GenerateXlsx
    S_GenerateXlsx --> S_DownloadXlsx
    S_DownloadXlsx --> U_End
    U_Pilih -- "Cetak / PDF dari Detail" --> U_CetakBtn
    U_CetakBtn --> S_RenderPrint
    S_RenderPrint --> S_PrintDialog
    S_PrintDialog --> U_End

    style U_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style U_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.15: Activity Diagram Ekspor & Cetak Laporan*

##### m. Activity Diagram Koreksi Laporan Produksi
Diagram ini memodelkan alur pengeditan laporan yang sudah ada melalui halaman Koreksi Laporan (`EditReport`). Akses diatur oleh kebijakan `DailyReportPolicy@update`: Supervisor selalu diizinkan; Petugas hanya untuk laporan milik sendiri yang belum diverifikasi dan dibuat dalam 24 jam terakhir. Operasi koreksi mencakup pengeditan kuantitas item, penambahan PO susulan, dan penghapusan item:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Operator / Supervisor"]
        U_Start(( ))
        U_Buka(Buka Halaman<br/>Detail Laporan)
        U_KoreksiBtn(Klik Tombol<br/>Koreksi Laporan)
        U_AksiEdit{Pilih Aksi<br/>Koreksi?}
        U_EditItem(Edit Kuantitas Baik /<br/>Reject pada Item)
        U_TambahPO(Input No. PO Susulan<br/>& Kuantitas Tambahan)
        U_HapusItem(Tandai Item<br/>untuk Dihapus)
        U_Simpan(Klik Simpan<br/>Perubahan)
        U_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_CekPolicy{"Cek Kebijakan Akses:<br/>Supervisor selalu atau<br/>Petugas milik sendiri, belum<br/>verified, dalam 24 jam?"}
        S_PolicyErr(Tolak Akses &<br/>Tampilkan Pesan Tidak Berhak)
        S_FormEdit(Tampilkan Halaman<br/>Form Koreksi Laporan)
        S_LookupPO{Cari No. PO Susulan<br/>di production_orders?}
        S_POErr(Tampilkan Pesan<br/>PO Tidak Ditemukan)
        S_TambahRow(Tambah Baris PO Susulan<br/>ke Form Sementara)
        S_Persist(Persist: Update / Create /<br/>Delete Item di Basis Data)
        S_UpdateTotal(Update Total Laporan<br/>& Simpan Catatan)
        S_Notif(Redirect ke Detail &<br/>Tampilkan Notifikasi Sukses)
    end

    U_Start --> U_Buka
    U_Buka --> S_CekPolicy
    S_CekPolicy -- Tidak Berhak --> S_PolicyErr
    S_PolicyErr --> U_End
    S_CekPolicy -- Berhak --> U_KoreksiBtn
    U_KoreksiBtn --> S_FormEdit
    S_FormEdit --> U_AksiEdit
    U_AksiEdit -- Edit Item --> U_EditItem
    U_EditItem --> U_Simpan
    U_AksiEdit -- Tambah PO Susulan --> U_TambahPO
    U_TambahPO --> S_LookupPO
    S_LookupPO -- Tidak Ditemukan --> S_POErr
    S_POErr --> S_FormEdit
    S_LookupPO -- Ditemukan --> S_TambahRow
    S_TambahRow --> U_AksiEdit
    U_AksiEdit -- Hapus Item --> U_HapusItem
    U_HapusItem --> U_Simpan
    U_Simpan --> S_Persist
    S_Persist --> S_UpdateTotal
    S_UpdateTotal --> S_Notif
    S_Notif --> U_End

    style U_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style U_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.16: Activity Diagram Koreksi Laporan Produksi*

##### n. Activity Diagram Verifikasi & Buka Kembali Laporan (Supervisor)
Diagram ini menggambarkan dua aksi khusus Supervisor pada halaman Detail Laporan: (1) memverifikasi laporan berstatus *submitted* (mengunci laporan dan mencatat identitas verifikator), dan (2) membuka kembali laporan berstatus *submitted* atau *verified* ke status *draft* untuk dilanjutkan pemindaian:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Buka(Buka Halaman<br/>Detail Laporan)
        SP_Pilih{Pilih Aksi<br/>Supervisor?}
        SP_VerifBtn(Klik Tombol<br/>Verifikasi Laporan)
        SP_VerifKonfirm(Konfirmasi di<br/>Modal Verifikasi)
        SP_ReopenBtn(Klik Tombol<br/>Buka Kembali Sesi)
        SP_ReopenKonfirm(Konfirmasi di<br/>Modal Buka Kembali)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Detail(Tampilkan Halaman<br/>Detail Laporan)
        SS_ModalVerif(Tampilkan Modal<br/>Konfirmasi Verifikasi)
        SS_SetVerif(Ubah Status → verified<br/>Catat supervisor_id & verified_at)
        SS_NotifVerif(Tampilkan Notifikasi<br/>Laporan Terverifikasi)
        SS_ModalReopen(Tampilkan Modal<br/>Konfirmasi Buka Kembali)
        SS_SetDraft(Ubah Status<br/>→ draft)
        SS_NotifReopen(Tampilkan Notifikasi<br/>Sesi Dibuka Kembali)
    end

    SP_Start --> SP_Buka
    SP_Buka --> SS_Detail
    SS_Detail --> SP_Pilih
    SP_Pilih -- "Verifikasi (Status: submitted)" --> SP_VerifBtn
    SP_VerifBtn --> SS_ModalVerif
    SS_ModalVerif --> SP_VerifKonfirm
    SP_VerifKonfirm --> SS_SetVerif
    SS_SetVerif --> SS_NotifVerif
    SS_NotifVerif --> SP_End
    SP_Pilih -- "Buka Kembali (Status: submitted / verified)" --> SP_ReopenBtn
    SP_ReopenBtn --> SS_ModalReopen
    SS_ModalReopen --> SP_ReopenKonfirm
    SP_ReopenKonfirm --> SS_SetDraft
    SS_SetDraft --> SS_NotifReopen
    SS_NotifReopen --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.17: Activity Diagram Verifikasi & Buka Kembali Laporan (Supervisor)*

##### o. Activity Diagram Hapus Laporan (Supervisor)
Diagram ini memodelkan alur penghapusan laporan produksi yang dapat dilakukan Supervisor dari dua titik: halaman Arsip Laporan atau halaman Detail Laporan. Setelah penghapusan, data laporan beserta seluruh item terkait dihapus dari basis data:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Supervisor ["Supervisor (Kepala Unit)"]
        SP_Start(( ))
        SP_Sumber{Akses Hapus<br/>dari Mana?}
        SP_Arsip(Buka Menu<br/>Lihat Laporan)
        SP_Detail(Buka Halaman<br/>Detail Laporan)
        SP_HapusBtn(Klik Ikon / Tombol<br/>Hapus Laporan)
        SP_Konfirm(Konfirmasi Hapus<br/>di Modal)
        SP_End((( )))
    end

    subgraph Sistem ["Sistem"]
        SS_Tampil(Tampilkan Arsip<br/>atau Detail Laporan)
        SS_ModalKonfirm(Tampilkan Modal<br/>Konfirmasi Hapus)
        SS_Hapus(Hapus Record DailyReport<br/>& Seluruh DailyReportItem Terkait)
        SS_Redirect(Redirect ke Halaman<br/>Arsip Laporan)
        SS_Notif(Tampilkan Notifikasi<br/>Laporan Berhasil Dihapus)
    end

    SP_Start --> SP_Sumber
    SP_Sumber -- dari Arsip --> SP_Arsip
    SP_Sumber -- dari Detail --> SP_Detail
    SP_Arsip --> SS_Tampil
    SP_Detail --> SS_Tampil
    SS_Tampil --> SP_HapusBtn
    SP_HapusBtn --> SS_ModalKonfirm
    SS_ModalKonfirm --> SP_Konfirm
    SP_Konfirm --> SS_Hapus
    SS_Hapus --> SS_Redirect
    SS_Redirect --> SS_Notif
    SS_Notif --> SP_End

    style SP_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style SP_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.18: Activity Diagram Hapus Laporan (Supervisor)*

##### p. Activity Diagram Dashboard dan Visualisasi Grafik Performa
Diagram ini menjelaskan alur pemrosesan data saat pengguna mengakses halaman Dashboard, termasuk metrik harian, grafik tren produksi, distribusi shift, ranking top 5 mesin cetak, dan daftar 5 laporan terbaru. Hasil agregasi di-*cache* selama 5 menit oleh `DashboardAnalyticsService` untuk menjaga responsivitas halaman:

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryBorderColor': '#000000', 'primaryTextColor': '#000000', 'lineColor': '#000000', 'textColor': '#000000', 'clusterBkg': '#ffffff', 'clusterBorder': '#000000', 'edgeLabelBackground':'#ffffff' }}}%%
flowchart TD
    subgraph Pengguna ["Operator / Supervisor"]
        U_Start(( ))
        U_Buka(Mengakses Menu<br/>Dashboard)
        U_Opsi{Ubah Rentang<br/>atau Tipe Grafik?}
        U_Pilih["Pilih 7 / 14 / 30 Hari<br/>atau Tipe Grafik Area/Garis/Batang"]
        U_Lihat(Meninjau KPI Harian,<br/>Grafik, & Laporan Terbaru)
        U_End((( )))
    end

    subgraph Sistem ["Sistem"]
        S_Metrik(Hitung Metrik<br/>Produksi Hari Ini)
        S_Rentang(Set Rentang Waktu<br/>Filter Grafik)
        S_Query["Query Agregasi Tren<br/>Kemas Baik & Reject - WIB"]
        S_Shift(Hitung Distribusi<br/>Output per Shift)
        S_Mesin(Ranking Top 5<br/>Mesin Cetak)
        S_Recent(Ambil 5 Laporan<br/>Terbaru)
        S_Render(Render Kartu KPI, Grafik Chart.js,<br/>Distribusi Shift & Tabel Laporan Terbaru)
    end

    U_Start --> U_Buka
    U_Buka --> S_Metrik
    S_Metrik --> S_Rentang
    S_Rentang --> S_Query
    S_Query --> S_Shift
    S_Shift --> S_Mesin
    S_Mesin --> S_Recent
    S_Recent --> S_Render
    S_Render --> U_Lihat
    U_Lihat --> U_Opsi
    U_Opsi -- Ya --> U_Pilih
    U_Pilih --> S_Rentang
    U_Opsi -- Tidak --> U_End

    style U_Start fill:#000000,stroke:#000000,stroke-width:2px;
    style U_End fill:#ffffff,stroke:#000000,stroke-width:2px;
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.19: Activity Diagram Dashboard dan Visualisasi Grafik Performa*

---

#### 3. Entity Relationship Diagram (ERD)
Entity Relationship Diagram (ERD) memodelkan keterhubungan konseptual antar-entitas data dalam sistem dokumentasi hasil produksi harian. Pemodelan ERD dirancang menggunakan **Notasi Chen** yang merepresentasikan entitas dalam bentuk persegi panjang (*rectangles*), relasi antar-entitas dalam bentuk belah ketupat (*diamonds*), serta atribut data dalam bentuk elips (*ovals*) dengan garis bawah penanda kunci utama (*Primary Key*).

Diagram ERD notasi Chen disajikan pada Gambar 3.20:

```mermaid
flowchart TD
    subgraph USR_GRP ["Entitas User"]
        E_USER["User"]
        A_U1(["<u>user_id (PK)</u>"])
        A_U2(["nip (UK)"])
        A_U2b(["username (UK)"])
        A_U3(["name"])
        A_U4(["email (UK)"])
        A_U5(["password"])
        A_U6(["role"])
        A_U7(["is_active"])
        A_U8(["last_login_at"])
        E_USER --- A_U1
        E_USER --- A_U2
        E_USER --- A_U2b
        E_USER --- A_U3
        E_USER --- A_U4
        E_USER --- A_U5
        E_USER --- A_U6
        E_USER --- A_U7
        E_USER --- A_U8
    end

    subgraph PO_GRP ["Entitas Production Order"]
        E_PO["Production_Order"]
        A_P1(["<u>production_order_id (PK)</u>"])
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
        A_D1(["<u>daily_report_id (PK)</u>"])
        A_D2(["report_code (UK)"])
        A_D2b(["product_series"])
        A_D3(["report_date"])
        A_D4(["shift"])
        A_D5(["total_items_count"])
        A_D6(["total_good_quantity"])
        A_D7(["total_reject_quantity"])
        A_D8(["notes"])
        A_D9(["status"])
        A_D10(["verified_at"])
        E_DR --- A_D1
        E_DR --- A_D2
        E_DR --- A_D2b
        E_DR --- A_D3
        E_DR --- A_D4
        E_DR --- A_D5
        E_DR --- A_D6
        E_DR --- A_D7
        E_DR --- A_D8
        E_DR --- A_D9
        E_DR --- A_D10
    end

    subgraph DRI_GRP ["Entitas Daily Report Item"]
        E_DRI["Daily_Report_Item"]
        A_I1(["<u>daily_report_item_id (PK)</u>"])
        A_I2(["po_number"])
        A_I3(["product_name"])
        A_I4(["specification"])
        A_I5(["machine_number"])
        A_I6(["target_quantity"])
        A_I7(["good_quantity"])
        A_I8(["reject_quantity"])
        A_I9(["unit"])
        A_I10(["notes"])
        A_I11(["scanned_at"])
        E_DRI --- A_I1
        E_DRI --- A_I2
        E_DRI --- A_I3
        E_DRI --- A_I4
        E_DRI --- A_I5
        E_DRI --- A_I6
        E_DRI --- A_I7
        E_DRI --- A_I8
        E_DRI --- A_I9
        E_DRI --- A_I10
        E_DRI --- A_I11
    end

    subgraph LOG_GRP ["Entitas SAP Import Log"]
        E_LOG["SAP_Import_Log"]
        A_L1(["<u>sap_import_log_id (PK)</u>"])
        A_L2(["file_name"])
        A_L3(["total_rows"])
        A_L4(["imported_count"])
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
*Gambar 3.20: Entity Relationship Diagram (ERD) Notasi Chen Sistem Dokumentasi Produksi Harian*

Kardinalitas dan hubungan relasional dalam ERD dijelaskan sebagai berikut:
1. **Relasi `User` ke `Production_Order` (mengimpor):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Supervisor dapat mengimpor banyak berkas rekaman pesanan produksi SAP.
2. **Relasi `User` ke `Daily_Report` (mencatat):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Petugas dapat mencatat banyak laporan hasil kemas harian.
3. **Relasi `User` ke `Daily_Report` (memverifikasi):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Supervisor dapat memverifikasi banyak dokumen laporan harian.
4. **Relasi `User` ke `SAP_Import_Log` (merekam log):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu pengguna Supervisor dapat memiliki banyak catatan log riwayat unggahan berkas SAP.
5. **Relasi `Daily_Report` ke `Daily_Report_Item` (memuat):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu dokumen laporan harian memuat satu atau banyak rincian item hasil kemas.
6. **Relasi `Production_Order` ke `Daily_Report_Item` (menjadi acuan):** Memiliki kardinalitas *One-to-Many* (1:M), di mana satu rekaman pesanan produksi SAP menjadi acuan data bagi satu atau banyak rincian item laporan harian.

---

#### 4. Logical Record Structure (LRS)
Logical Record Structure (LRS) dibentuk dari hasil transformasi diagram relasi entitas (ERD) ke dalam bentuk struktur rekaman tabel relasional yang telah melalui proses penentuan kunci utama (*Primary Key*) dan penempatan kunci tamu (*Foreign Key*).

Aturan transformasi ERD ke LRS yang diterapkan meliputi:
1. Hubungan kardinalitas *One-to-Many* (1:M) ditransformasikan dengan menempatkan atribut *Primary Key* dari entitas di sisi "1" (One) menjadi atribut *Foreign Key* pada entitas di sisi "M" (Many).
2. Seluruh atribut dari entitas diubah menjadi elemen data (*fields*) yang memiliki tipe data dan panjang presisi pada tingkat basis data fisik.

Struktur LRS Sistem Dokumentasi Hasil Produksi Harian disajikan pada Gambar 3.21:

```mermaid
classDiagram
    class USERS {
        *user_id (PK) : BIGINT
        nip : VARCHAR(30) [UK]
        username : VARCHAR(50) [UK]
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
        *production_order_id (PK) : BIGINT
        po_number : VARCHAR(50) [UK]
        no_obc : VARCHAR(50)
        perso_type : VARCHAR(50)
        seri : VARCHAR(50)
        product_code : VARCHAR(50)
        product_name : VARCHAR(255)
        customer_name : VARCHAR(255)
        specification : TEXT
        paper_type : VARCHAR(255)
        machine_number : VARCHAR(50)
        order_date : DATE
        delivery_deadline : DATE
        tgl_pakai_bb : DATE
        tgl_cetak : DATE
        tgl_verifikasi : DATE
        packaging_date : DATE
        target_quantity : INT
        rencet_quantity : INT
        jml_bb : INT
        jml_cd : INT
        total_bb : INT
        jml_cetak : INT
        baik_verifikasi : INT
        rusak_verifikasi : INT
        hcts_baik : INT
        total_hcts : INT
        kemas_quantity : INT
        kirim_quantity : INT
        unit : VARCHAR(30)
        status : ENUM('ready', 'in_progress', 'completed', 'cancelled')
        #created_by (FK) : BIGINT
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class DAILY_REPORTS {
        *daily_report_id (PK) : BIGINT
        report_code : VARCHAR(50) [UK]
        product_series : VARCHAR(30)
        report_date : DATE
        shift : ENUM('shift_1', 'shift_2', 'shift_3')
        #user_id (FK) : BIGINT
        #supervisor_id (FK) : BIGINT
        verified_at : TIMESTAMP
        total_items_count : INT
        total_good_quantity : INT
        total_reject_quantity : INT
        notes : TEXT
        status : ENUM('draft', 'submitted', 'verified')
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class DAILY_REPORT_ITEMS {
        *daily_report_item_id (PK) : BIGINT
        #daily_report_id (FK) : BIGINT
        #production_order_id (FK) : BIGINT
        po_number : VARCHAR(50)
        product_name : VARCHAR(255)
        specification : TEXT
        machine_number : VARCHAR(50)
        target_quantity : INT
        good_quantity : INT
        reject_quantity : INT
        unit : VARCHAR(30)
        notes : TEXT
        scanned_at : DATETIME
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    class SAP_IMPORT_LOGS {
        *sap_import_log_id (PK) : BIGINT
        file_name : VARCHAR(255)
        total_rows : INT
        imported_count : INT
        updated_count : INT
        failed_count : INT
        status : VARCHAR(30)
        #user_id (FK) : BIGINT
        created_at : TIMESTAMP
        updated_at : TIMESTAMP
    }

    USERS "1" --> "0..*" PRODUCTION_ORDERS : created_by
    USERS "1" --> "0..*" DAILY_REPORTS : user_id / supervisor_id
    USERS "1" --> "0..*" SAP_IMPORT_LOGS : user_id
    DAILY_REPORTS "1" --> "1..*" DAILY_REPORT_ITEMS : daily_report_id
    PRODUCTION_ORDERS "1" --> "0..*" DAILY_REPORT_ITEMS : production_order_id
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.21: Logical Record Structure (LRS) Sistem Dokumentasi Produksi Harian*

---

#### 5. Spesifikasi File
Spesifikasi file menjelaskan rincian teknis dari setiap tabel basis data yang digunakan dalam sistem, mencakup nama field, tipe data, panjang ukuran data, dan keterangan batasannya:

##### a. Spesifikasi File `users`
- **Nama File / Tabel:** `users`
- **Akronim File:** USR
- **Fungsi File:** Menyimpan data akun pengguna, peran hak akses, dan kredensial login
- **Tipe File:** File Master
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `user_id`
- **Kunci Unik (*Unique Key*):** `nip`, `username`, `email`
- **Struktur Tabel:** Disajikan pada Tabel 3.10

*Tabel 3.10: Spesifikasi File Tabel `users`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Pengguna | `user_id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nama Lengkap | `name` | VARCHAR | 100 | Not Null |
| 3 | Nomor Pegawai | `nip` | VARCHAR | 30 | Unique, Nullable (Kredensial Login) |
| 4 | Username | `username` | VARCHAR | 50 | Unique, Nullable (Kredensial Login) |
| 5 | Alamat Email | `email` | VARCHAR | 100 | Unique, Not Null |
| 6 | Kata Sandi | `password` | VARCHAR | 255 | Not Null (Terenkripsi Bcrypt) |
| 7 | Peran Akun | `role` | ENUM | - | Value: 'supervisor', 'petugas' |
| 8 | Status Keaktifan | `is_active` | BOOLEAN | 1 | Default: true (1) |
| 9 | Login Terakhir | `last_login_at` | TIMESTAMP | - | Nullable |
| 10 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 11 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### b. Spesifikasi File `production_orders`
- **Nama File / Tabel:** `production_orders`
- **Akronim File:** PO
- **Fungsi File:** Menyimpan data master pesanan produksi hasil ekspor dari sistem SAP
- **Tipe File:** File Master
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `production_order_id`
- **Kunci Unik (*Unique Key*):** `po_number`
- **Kunci Tamu (*Foreign Key*):** `created_by` (Relasi ke `users.user_id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.11

*Tabel 3.11: Spesifikasi File Tabel `production_orders`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Pesanan | `production_order_id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nomor PO / Barcode | `po_number` | VARCHAR | 50 | Unique, Not Null (Simbologi Code 128) |
| 3 | Nomor OBC | `no_obc` | VARCHAR | 50 | Index, Nullable |
| 4 | Tipe Perso | `perso_type` | VARCHAR | 50 | Nullable (Perso / Non Perso) |
| 5 | Kode Seri | `seri` | VARCHAR | 50 | Nullable |
| 6 | Kode Produk | `product_code` | VARCHAR | 50 | Index, Nullable |
| 7 | Nama Produk | `product_name` | VARCHAR | 255 | Index, Not Null |
| 8 | Nama Instansi Pemesan | `customer_name` | VARCHAR | 255 | Index, Nullable |
| 9 | Spesifikasi Teknis | `specification` | TEXT | - | Nullable (Bahan Kertas, Pengaman) |
| 10 | Jenis Kertas | `paper_type` | VARCHAR | 255 | Nullable |
| 11 | Nomor Mesin Asal | `machine_number` | VARCHAR | 50 | Index, Nullable (Identitas Mesin Cetak) |
| 12 | Tanggal Pesanan | `order_date` | DATE | - | Not Null (TGL OBC) |
| 13 | Tanggal Jatuh Tempo | `delivery_deadline` | DATE | - | Nullable (TGL JTempo) |
| 14 | Tanggal Pakai Bahan Baku | `tgl_pakai_bb` | DATE | - | Nullable |
| 15 | Tanggal Cetak | `tgl_cetak` | DATE | - | Nullable |
| 16 | Tanggal Verifikasi | `tgl_verifikasi` | DATE | - | Nullable |
| 17 | Tanggal Pengemasan | `packaging_date` | DATE | - | Nullable |
| 18 | Target Kuantitas Pesanan | `target_quantity` | INT UNSIGNED | 10 | Default: 0 (QTY Pesan SAP) |
| 19 | Kuantitas Rencana Cetak | `rencet_quantity` | INT UNSIGNED | 10 | Default: 0 (RENCET) |
| 20 | Jumlah Lembar Kerja Bahan Baku | `jml_bb` | INT UNSIGNED | 10 | Default: 0 |
| 21 | Jumlah Cetakan Dasar | `jml_cd` | INT UNSIGNED | 10 | Default: 0 |
| 22 | Total Bahan Baku | `total_bb` | INT UNSIGNED | 10 | Default: 0 (JML\_BB + JML\_CD) |
| 23 | Jumlah Cetak | `jml_cetak` | INT UNSIGNED | 10 | Default: 0 |
| 24 | Baik Verifikasi | `baik_verifikasi` | INT UNSIGNED | 10 | Default: 0 |
| 25 | Rusak / Afval Verifikasi | `rusak_verifikasi` | INT UNSIGNED | 10 | Default: 0 |
| 26 | Hasil HCTS Baik | `hcts_baik` | INT UNSIGNED | 10 | Default: 0 |
| 27 | Total HCTS | `total_hcts` | INT UNSIGNED | 10 | Default: 0 |
| 28 | Kuantitas Kemas | `kemas_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 29 | Kuantitas Kirim | `kirim_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 30 | Satuan Hitung | `unit` | VARCHAR | 30 | Default: 'lembar' |
| 31 | Status Pengerjaan | `status` | VARCHAR | 30 | Default: 'ready', Nilai: 'ready', 'in_progress', 'completed', 'cancelled' |
| 32 | ID Pembuat / Pengimpor | `created_by` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.user_id`, Nullable |
| 33 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 34 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### c. Spesifikasi File `daily_reports`
- **Nama File / Tabel:** `daily_reports`
- **Akronim File:** DREP
- **Fungsi File:** Menyimpan data header laporan hasil pengemasan produksi harian
- **Tipe File:** File Transaksi
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `daily_report_id`
- **Kunci Unik (*Unique Key*):** `report_code`, Komposit: (`product_series`, `report_date`, `shift`)
- **Kunci Tamu (*Foreign Key*):** `user_id`, `supervisor_id` (Relasi ke `users.user_id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.12

*Tabel 3.12: Spesifikasi File Tabel `daily_reports`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Laporan | `daily_report_id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Kode Laporan Sesi | `report_code` | VARCHAR | 50 | Unique, Not Null (Format: `{SERIES}-{YYYY-MM-DD}-S{N}`, contoh: `PCHT-2026-08-26-S1`) |
| 3 | Seri Produk | `product_series` | VARCHAR | 30 | Index, Not Null (Nilai: 'pcht', 'mmea', 'hptl') |
| 4 | Tanggal Pengemasan | `report_date` | DATE | - | Index, Not Null |
| 5 | Giliran Kerja | `shift` | VARCHAR | 30 | Default: 'shift_1' (Nilai: 'shift_1', 'shift_2', 'shift_3') |
| 6 | ID Petugas Pelaksana | `user_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.user_id` |
| 7 | ID Supervisor Pemeriksa | `supervisor_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.user_id`, Nullable |
| 8 | Waktu Verifikasi | `verified_at` | TIMESTAMP | - | Nullable (Terisi saat status `verified`) |
| 9 | Total Jenis Order | `total_items_count` | INT UNSIGNED | 5 | Default: 0 |
| 10 | Total Hasil Kemas Baik | `total_good_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 11 | Total Kuantitas Reject | `total_reject_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 12 | Catatan Operasional | `notes` | TEXT | - | Nullable |
| 13 | Status Verifikasi | `status` | VARCHAR | 30 | Default: 'draft' (Nilai: 'draft', 'submitted', 'verified') |
| 14 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 15 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### d. Spesifikasi File `daily_report_items`
- **Nama File / Tabel:** `daily_report_items`
- **Akronim File:** DRIT
- **Fungsi File:** Menyimpan baris rincian item hasil kemas per pesanan produksi
- **Tipe File:** File Transaksi (Detail)
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `daily_report_item_id`
- **Kunci Tamu (*Foreign Key*):** `daily_report_id` (ke `daily_reports.daily_report_id`), `production_order_id` (ke `production_orders.production_order_id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.13

*Tabel 3.13: Spesifikasi File Tabel `daily_report_items`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Rincian Item | `daily_report_item_id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | ID Header Laporan | `daily_report_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `daily_reports.daily_report_id` (Cascade) |
| 3 | ID Pesanan SAP | `production_order_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `production_orders.production_order_id`, Nullable |
| 4 | Nomor PO Snapshot | `po_number` | VARCHAR | 50 | Index, Not Null |
| 5 | Nama Produk Snapshot | `product_name` | VARCHAR | 255 | Not Null |
| 6 | Spesifikasi Snapshot | `specification` | TEXT | - | Nullable |
| 7 | Nomor Mesin Snapshot | `machine_number` | VARCHAR | 50 | Nullable |
| 8 | Target Kuantitas SAP | `target_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 9 | Jumlah Kemas Baik | `good_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 10 | Jumlah Kemas Reject | `reject_quantity` | INT UNSIGNED | 10 | Default: 0 |
| 11 | Satuan Hitung | `unit` | VARCHAR | 30 | Default: 'lembar' |
| 12 | Catatan Khusus Item | `notes` | TEXT | - | Nullable |
| 13 | Waktu Pemindaian | `scanned_at` | DATETIME | - | Nullable |
| 14 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 15 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
*(Sumber: Hasil Penelitian, 2026)*

##### e. Spesifikasi File `sap_import_logs`
- **Nama File / Tabel:** `sap_import_logs`
- **Akronim File:** IMPL
- **Fungsi File:** Menyimpan catatan riwayat aktivitas pengunggahan berkas ekspor master SAP
- **Tipe File:** File Log Riwayat
- **Media Penyimpanan:** Harddisk / SSD (MySQL Server)
- **Kunci Utama (*Primary Key*):** `sap_import_log_id`
- **Kunci Tamu (*Foreign Key*):** `user_id` (Relasi ke `users.user_id`)
- **Struktur Tabel:** Disajikan pada Tabel 3.14

*Tabel 3.14: Spesifikasi File Tabel `sap_import_logs`*
| No | Elemen Data | Nama Field | Tipe Data | Panjang / Ukuran | Keterangan / Constraint |
|---|---|---|---|---|---|
| 1 | ID Riwayat Impor | `sap_import_log_id` | BIGINT UNSIGNED | 20 | Primary Key, Auto Increment |
| 2 | Nama Berkas Unggahan | `file_name` | VARCHAR | 255 | Not Null |
| 3 | Jalur Penyimpanan Berkas | `file_path` | VARCHAR | 255 | Nullable (Path di server setelah unggah) |
| 4 | Total Baris Berkas | `total_rows` | INT UNSIGNED | 8 | Default: 0 |
| 5 | Jumlah Baris Diimpor | `imported_count` | INT UNSIGNED | 8 | Default: 0 |
| 6 | Jumlah Baris Diperbarui | `updated_count` | INT UNSIGNED | 8 | Default: 0 |
| 7 | Jumlah Baris Gagal | `failed_count` | INT UNSIGNED | 8 | Default: 0 |
| 8 | Status Impor | `status` | VARCHAR | 30 | Default: 'success', Nilai: 'success', 'partial', 'failed' |
| 9 | Log Kesalahan Baris | `error_log` | JSON | - | Nullable (Catatan baris yang gagal diproses) |
| 10 | ID Pengunggah | `user_id` | BIGINT UNSIGNED | 20 | Foreign Key ke `users.user_id`, Nullable |
| 11 | Waktu Dibuat | `created_at` | TIMESTAMP | - | Nullable |
| 12 | Waktu Diperbarui | `updated_at` | TIMESTAMP | - | Nullable |
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
| 11 | **Visualisasi Grafik Analitik** | Chart.js terintegrasi Alpine.js | Merender grafik analitik tren performa hasil kemas dengan opsi rentang 7, 14, atau 30 hari aktif pada halaman Dashboard |
| 12 | **Pengujian & Kualitas Kode** | Pest PHP (versi 5.x), Larastan (versi 3.x), Laravel Pint (versi 1.x) | Pengujian otomatis (*automated testing*), analisis statis tipe data, dan standarisasi format kode |
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
|                     |  NP / Username / Email:   |                     |
|                     |  [______________________] |                     |
|                     |                           |                     |
|                     |  Password:                |                     |
|                     |  [______________________] |                     |
|                     |                           |                     |
|                     |  [x] Ingat Saya           |                     |
|                     |                           |                     |
|                     |   [ MASUK KE SISTEM ]    |                     |
|                     |                           |                     |
|                     +---------------------------+                     |
|                                                                       |
+-----------------------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.22: Tampilan Antarmuka Halaman Login Sistem*

##### b. Tampilan Antarmuka Halaman Dashboard Utama dan Grafik Performa Produksi
Halaman dashboard menampilkan ringkasan kartu metrik capaian harian, navigasi cepat modul sistem, serta visualisasi grafik analitik performa produksi (7, 14, atau 30 hari aktif) yang menyajikan perbandingan output baik dan kuantitas reject:

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
|             | GRAFIK ANALITIK HASIL PRODUKSI [7 Hari|14 Hari|30 Hari] |
|             | +-----------------------------------------------------+ |
|             | | (Kuantitas)                                         | |
|             | |  60k |       _/\_        /\_                        | |
|             | |  40k |  _/\_/    \__/\__/   \__/\_   -- Kemas Baik  | |
|             | |  20k | /                          \  -- Reject      | |
|             | |    0 +---------------------------------             | |
|             | |       H-14 H-13 ... H-3  H-2  H-1 Hari Ini          | |
|             | +-----------------------------------------------------+ |
|             | * Rata-rata (14 Hari): 45.200 Lembar | Puncak: 56.000 lbr |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.23: Tampilan Antarmuka Halaman Dashboard dan Grafik Performa Produksi (7/14/30 Hari)*

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
*Gambar 3.24: Tampilan Antarmuka Halaman Manajemen Data Petugas*

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
*Gambar 3.25: Tampilan Antarmuka Halaman Update Database Master SAP*

##### e. Tampilan Antarmuka Halaman Buat Laporan (Scan Barcode & Entri Kemas)
Halaman ini merupakan antarmuka operasional utama Petugas di meja kerja pengemasan. Formulir dilengkapi area masukan scan barcode otomatis, penampil rincian spesifikasi barang seketika, kolom input jumlah kemas, dan tabel penampungan draf hasil kemas multi-item:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Petugas: Umar I.Z.           [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | BUAT LAPORAN PRODUKSI HARIAN (SCAN BARCODE)             |
| - Dashboard | Tanggal: 25/08/2026 | Shift: [Shift 1 v] | Petugas: Umar|
| - Up Database+-------------------------------------------------------+ |
| - Data Petugs| [||||] 1. Scan / Masukkan No PO Barcode                 | |
| - Buat Lapor | [ PO-2026-8891                    ]  [Simpan Item(Enter)]| |
| - Lihat Lapor|                                                       | |
|             | Nama Produk: Sertifikat Sekuriti Hologram A4            | |
|             | Spesifikasi: Kertas Watermark 120gsm, 2 Spot UV         | |
|             | Mesin Asal : MESIN-02  | Target: 5.000 Lembar           | |
|             |                                                         | |
|             | 2. Jumlah Kemas (Lembar): [ 5.000 ]                     | |
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
|             |            [ SELESAIKAN LAPORAN ]                       | |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.26: Tampilan Antarmuka Halaman Buat Laporan dengan Scan Barcode*

##### f. Tampilan Antarmuka Halaman Lihat Laporan
Halaman ini menyajikan tabel riwayat seluruh dokumen laporan hasil kemas yang tersimpan, dilengkapi fitur pencarian, filter rentang tanggal, status verifikasi, dan tombol aksi:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Supervisor / Petugas         [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | DAFTAR LAPORAN HASIL PRODUKSI HARIAN                    |
| - Dashboard | Filter Tanggal: [ 2026-08-01 ] s/d [ 2026-08-25 ]       |
| - Up Database+----+-------------------+------------+-------+---------+| |
| - Data Petugs| No | Kode Laporan        | Tanggal    | Shift |   Aksi       || |
| - Buat Lapor +----+---------------------+------------+-------+--------------+| |
| - Lihat Lapor| 1  | PCHT-2026-08-25-S1  | 25/08/2026 | 1     |[Lihat Rincian]| |
|             | 2  | PCHT-2026-08-24-S2  | 24/08/2026 | 2     |[Lihat Rincian]| |
|             | +----+-------------------+------------+-------+---------+| |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.27: Tampilan Antarmuka Halaman Lihat Laporan Produksi Harian*

##### g. Tampilan Antarmuka Halaman Detail dan Verifikasi Laporan
Halaman ini menampilkan rincian lengkap item kemas pada suatu dokumen laporan, memfasilitasi pengeditan korektif oleh Petugas (jika masih draf), serta menyediakan tombol aksi persetujuan (*verifikasi*) bagi Supervisor:

```
+-----------------------------------------------------------------------+
| [PT PDS LOGO]  Unit Pengemasan  | Supervisor: Agus S.          [User] v |
+-------------+---------------------------------------------------------+
| NAVIGATION  | RINCIAN LAPORAN: PCHT-2026-08-25-S1                     |
| - Dashboard | Tanggal: 25/08/2026 | Shift: Shift 1 | Petugas: Umar    |
| - Up Database| Status : [ TERKIRIM (SELESAI SCAN) ]                   |
| - Data Petugs+----+--------------+-------------------+------+-------+ |
| - Buat Lapor | No | No PO        | Nama Produk       | Baik | Reject| |
| - Lihat Lapor+----+--------------+-------------------+------+-------+ |
|             | 1  | PO-2026-8891 | Sertifikat A4     | 5.000| 25    | |
|             | 2  | PO-2026-8892 | Ijazah B5         | 2.500| 10    | |
|             | +----+--------------+-------------------+------+-------+ |
|             | Total Kemas Baik: 7.500 Lembar | Total Reject: 35 Lembar| |
|             |                                                         |
|             | [ Cetak Lembar Fisik ]      [ Verifikasi Laporan ]       |
+-------------+---------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.28: Tampilan Antarmuka Detail dan Verifikasi Laporan Produksi Harian*

##### h. Tampilan Format Lembar Cetak Laporan Standar Kertas A4
Format cetak lembar dokumen resmi standar A4 yang dihasilkan sistem lengkap dengan kop perusahaan, rincian item, total hasil kemas, dan kolom tanda tangan pengesahan:

```
+-----------------------------------------------------------------------+
|                     PT PERCETAKAN DOKUMEN SEKURITI                    |
|                LAPORAN HASIL PRODUKSI HARIAN PENGEMASAN               |
|                                                                       |
| No. Laporan : PCHT-2026-08-25-S1          Tanggal : 25 Agustus 2026   |
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
| Dibuat oleh (Petugas Kemas),               Mengetahui,               |
|                                            Kepala Unit / Supervisor   |
|                                                                       |
| ( Umar Ibnu Zainal M. )                    ( Agus Soelistyo )        |
+-----------------------------------------------------------------------+
```
*(Sumber: Hasil Penelitian, 2026)*  
*Gambar 3.29: Tampilan Format Lembar Cetak Laporan Produksi Harian (Standar A4)*

---

#### 3. Implementasi Modul Program
Implementasi aplikasi dikelompokkan ke dalam enam modul fungsional utama yang saling terintegrasi sebagaimana dirangkum pada Tabel 3.17:

*Tabel 3.17: Pemetaan dan Implementasi Modul Program*
| No | Modul Program | Komponen / Berkas Utama | Penjelasan Implementasi Fungsional |
|---|---|---|---|
| 1 | **Skema Basis Data & Model Eloquent** | `database/migrations/`, Model: `User`, `ProductionOrder`, `DailyReport`, `DailyReportItem`, `SapImportLog` | Membangun struktur tabel terelasi, foreign key constraints, tipe data kolom, seeder inisialisasi, dan pemodelan relasi Eloquent ORM untuk menjamin integritas transaksi data. |
| 2 | **Autentikasi & Kontrol Akses Peran** | `app/Livewire/Auth/Login.php`, `FortifyServiceProvider`, Middleware `EnsureUserRole` | Menerapkan login menggunakan identitas Nomor Pegawai (NP) dan password terenkripsi, manajemen sesi, rate limiting 5 percobaan per menit, serta proteksi halaman berbasis peran Petugas dan Supervisor. |
| 3 | **Update Database (Master Data SAP)** | `app/Livewire/MasterData/UpdateDatabase.php`, `app/Services/SapImportService.php` | Mengakomodasi pengunggahan berkas ekspor SAP (.xlsx/.xls/.csv), validasi struktur kolom, parsing baris pesanan, dan pembaruan massal (*bulk upsert*) data Nomor PO, spesifikasi bahan, dan target kemas. |
| 4 | **Buat Laporan (Scan Barcode & Kemas)** | `app/Livewire/Reports/ReportSessionLauncher.php`, `app/Livewire/Reports/ProductionEntry.php`, Alpine.js Scanner Listener | Menangkap pembacaan scanner barcode Code 128 pada Nomor PO, melakukan pencarian instan (*instant lookup*) data produk, menyediakan input kuantitas kemas, dan menyimpan item langsung ke basis data. |
| 5 | **Manajemen & Cetak Laporan** | `app/Livewire/Reports/ReportIndex.php`, `ReportDetail.php`, `EditReport.php`, `app/Services/ReportCodeGenerator.php`, `@media print` | Menampilkan riwayat laporan harian, membangkitkan kode laporan unik otomatis dengan format `{SERIES}-{YYYY-MM-DD}-S{N}` (contoh: `PCHT-2026-08-26-S1`), memfasilitasi koreksi laporan oleh Petugas dan Supervisor, verifikasi persetujuan oleh Supervisor, serta mencetak dokumen fisik rapi standar kertas A4. |
| 6 | **Dashboard & Grafik Tren Produksi** | `app/Livewire/Dashboard/DashboardIndex.php`, `DashboardAnalyticsService.php`, Komponen Chart.js | Mengeksekusi query agregasi SQL untuk menghitung akumulasi kemas baik dan reject pada rentang 7, 14, atau 30 hari aktif terakhir (WIB), merender 5 kartu metrik KPI harian, grafik interaktif tipe Area/Garis/Batang, widget distribusi shift, dan ranking top 5 mesin cetak. |
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
| 1. | Pengguna mengosongkan kolom NP/Username/Email dan password, kemudian menekan tombol "Masuk ke Sistem". | Login: (kosong)<br>Password: (kosong) | Login gagal dan sistem menampilkan pesan validasi "Nomor Pegawai, Username, atau Email wajib diisi." | Sistem menolak proses login dan memunculkan notifikasi validasi form. | Valid |
| 2. | Pengguna memasukkan Nomor Pegawai (NP) yang tidak terdaftar pada basis data. | NP: 999999<br>Password: rahasia123 | Login gagal dan sistem menampilkan pesan "Nomor Pegawai/Username atau Kata Sandi yang dimasukkan salah." | Sistem menolak akses dan memunculkan notifikasi akun tidak terdaftar. | Valid |
| 3. | Pengguna memasukkan Nomor Pegawai (NP) terdaftar namun kata sandi yang dimasukkan salah. | NP: 202401<br>Password: salahpass | Login gagal dan sistem menampilkan pesan "Nomor Pegawai/Username atau Kata Sandi yang dimasukkan salah." | Sistem menolak akses dan memunculkan notifikasi kata sandi salah. | Valid |
| 4. | Pengguna memasukkan Nomor Pegawai (NP) dan kata sandi yang benar dan terdaftar. | NP: 202401<br>Password: Password123! | Login berhasil dan sistem menginisialisasi sesi kerja serta mengarahkan ke halaman Dashboard. | Sistem membuka sesi dan mengarahkan pengguna ke halaman Dashboard. | Valid |
| 5. | Supervisor menambahkan akun petugas baru dengan data yang valid. | NP: 202405<br>Nama: Budi Santoso<br>Email: budi@pds.co.id<br>Role: Petugas | Data akun baru tersimpan ke tabel `users` dan muncul di tabel daftar petugas. | Data berhasil tersimpan dan daftar tabel petugas memuat akun baru. | Valid |
| 6. | Supervisor mengunggah berkas ekspor SAP format spreadsheet valid (.xlsx). | File: `sap_po_august.xlsx`<br>(Kolom No PO, Nama, Qty lengkap) | Sistem mengekstrak seluruh baris pesanan dan memperbarui tabel master `production_orders`. | Sistem melakukan *bulk upsert* dan menampilkan jumlah data terproses. | Valid |
| 7. | Supervisor mengunggah berkas dengan ekstensi yang tidak diizinkan (.pdf). | File: `dokumen_sap.pdf` | Sistem menolak berkas dan memunculkan pesan "Format berkas tidak didukung. Harap unggah berkas spreadsheet dengan ekstensi .xlsx, .xls, atau .csv." | Berkas ditolak sistem dan menampilkan peringatan tipe berkas tidak didukung. | Valid |
| 8. | Petugas memindai barcode Nomor PO yang terdaftar pada master SAP menggunakan scanner. | Barcode Scanner input:<br>`PO-2026-8891` | Kolom Nama Produk, Spesifikasi Kertas, dan Nomor Mesin terisi otomatis secara seketika (< 1 detik). | Seluruh kolom spesifikasi produk terisi otomatis dari basis data. | Valid |
| 9. | Petugas memindai barcode Nomor PO yang belum diimpor ke dalam sistem. | Barcode Scanner input:<br>`PO-9999-0000` | Sistem membunyikan peringatan dan menampilkan notifikasi "Nomor PO belum terdaftar". | Sistem membunyikan alarm dan memunculkan alert PO tidak ditemukan. | Valid |
| 10. | Petugas memasukkan jumlah hasil kemas baik lalu menekan tombol "Selesaikan Laporan" untuk menyelesaikan sesi. | Kemas Baik: 5.000<br>Klik "Selesaikan Laporan" | Sistem mengubah status laporan menjadi `Terkirim (Selesai Scan)` dan membangkitkan kode laporan unik otomatis (contoh: `PCHT-2026-08-26-S1`). | Laporan tersimpan di basis data dengan kode unik dan status Terkirim. | Valid |
| 11. | Petugas mengedit angka kemas pada laporan yang masih berstatus draf. | Kemas Baik diubah dari 5.000 menjadi 5.200 | Sistem memperbarui rekaman jumlah kemas pada tabel rincian laporan. | Angka kuantitas kemas berhasil diperbarui di database. | Valid |
| 12. | Supervisor meninjau dan menyetujui laporan harian yang diserahkan petugas. | Klik tombol "Verifikasi Laporan" → konfirmasi "Ya, Verifikasi Laporan" | Status laporan berubah menjadi `Terverifikasi (Disetujui Ka. Unit)` dan data terkunci dari perubahan. | Status laporan terverifikasi dan identitas Supervisor tercatat di basis data. | Valid |
| 13. | Pengguna menekan tombol Cetak Lembar Fisik pada dokumen laporan. | Klik tombol "Cetak Lembar Fisik" | Sistem merender tampilan cetak standar kertas A4 rapi lengkap dengan kolom tanda tangan. | Lembar cetak berformat standar A4 muncul pada jendela pratinjau cetak. | Valid |
| 14. | Pengguna mengakses halaman Dashboard saat terdapat transaksi pada rentang waktu aktif. | Buka halaman Dashboard | Sistem menampilkan kartu KPI harian dan visualisasi grafik tren performa (default 14 hari, dengan opsi toggle 7 / 14 / 30 hari). | Kartu metrik KPI dan grafik Chart.js ter-render interaktif dengan opsi rentang waktu. | Valid |
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
   Kegiatan pemeliharaan basis data dilaksanakan melalui penjadwalan pencadangan (*backup*) data secara berkala guna mencegah risiko kehilangan arsip riwayat laporan produksi harian akibat kegagalan perangkat keras komputer klien. Selain itu, dilakukan optimasi indeks tabel secara periodik pada entitas `daily_reports`, `daily_report_items`, dan `production_orders` untuk menjaga kecepatan respon proses pemindaian barcode dan query analitik grafik performa produksi seiring bertambahnya volume data transaksi tahunan.
