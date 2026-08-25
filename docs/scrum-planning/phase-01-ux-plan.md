# UX Plan — Phase 01: Foundation, Authentication & User Management (Data Petugas)

> Created before implementation. This document is a hard constraint for all work in Phase 01.
> Epic file: `docs/scrum-planning/phase-01-foundation-auth-user-management.md`

---

## 1. Navigation Footprint

Perancangan navigasi pada Phase 01 membatasi jumlah halaman dan elemen antarmuka seminimal mungkin agar operator kemas dan supervisor di lini produksi dapat bekerja secara fokus tanpa disorientasi menu.

### Navigation Hierarchy & Routes Summary

- **Total Sidebar Menu Items untuk Phase 01**: 1 item aktif khusus Supervisor (**Data Petugas**). Struktur navigasi global aplikasi secara keseluruhan mencakup 6 menu utama (Dashboard, Update Database, Buat Laporan, Lihat Laporan, Data Petugas, Pengaturan) yang visibilitasnya dikontrol berdasarkan role akun.
- **Total Dedicated Routes/Pages**: **2 rute**.
  1. `/login` — Halaman autentikasi mandiri (*guest layout*, tanpa sidebar dan header).
  2. `/petugas` — Halaman manajemen data petugas (*app layout*, hak akses khusus Supervisor).
- **Single Page Consolidation**:
  - Daftar petugas, pencarian real-time, filter status/role, form tambah petugas, form edit petugas, serta konfirmasi aktivasi/deaktivasi disatukan sepenuhnya dalam **1 halaman** (`/petugas`) menggunakan modal dialog reaktif Flux UI (`<flux:modal>`). Tidak ada navigasi ke rute terpisah seperti `/petugas/create` atau `/petugas/{id}/edit`.
- **Drawer / Sheet vs. Modal**:
  - Form input data petugas dan dialog konfirmasi menggunakan **Modal Dialog di tengah layar** (*Center Modal*) karena input data bersifat ringkas (6 field) dan membutuhkan fokus penuh sebelum kembali ke data table.
- **Backend-Only (No Dedicated UI Surface)**:
  - US-1.3 (Sistem Role & Permission Access Control): Logika middleware `EnsureUserRole`, verifikasi akun aktif/nonaktif saat login, pembatasan session Fortify, dan proteksi integritas relasi foreign key database.

---

## 2. Screen Inventory

Inventaris seluruh layar, panel, dan komponen modal interaktif pada Phase 01:

| Screen / Panel | Location | What's visible on first open | Further triggers | Click depth |
|---|---|---|---|---|
| **Layar Login** | `/login` | Form login terpusat (*card layout*): Logo PT PDS, judul sistem, input Nomor Pegawai (NP) / Username / Email, input Password (dengan ikon toggle intip password), checkbox "Ingat Saya", dan tombol "Masuk ke Sistem". | Pesan error validasi inline jika data tidak valid; indikator loading pada tombol submit saat proses verifikasi kredensial. | 0 (Entry point pengguna belum login) |
| **Shell Layout Aplikasi (Header & Sidebar)** | `layouts.app` (Membungkus semua halaman internal) | **Sidebar**: Logo & nama sistem, daftar menu sesuai role (Petugas vs Supervisor), badge versi.<br>**Header**: Banner shift aktif (Shift 1 / Shift 2 / Shift 3), jam real-time WIB (`Asia/Jakarta`), toggle tema gelap/terang, dan menu profil pengguna (avatar, nama, badge peran). | Klik avatar membuka dropdown profil dan tombol "Keluar" (Logout); toggle tema beralih antara dark dan light mode secara instan. | 0 (Selalu terlihat pada semua halaman setelah login) |
| **Halaman Data Petugas** | `/petugas` (Khusus Supervisor) | Header halaman ("Data Petugas"), tombol aksi utama "+ Tambah Petugas", filter bar (pencarian teks dengan debounce 300ms, dropdown filter Role, dropdown filter Status), data table Flux berisi daftar staf (Nama, NP/NIP, Username, Email, Role badge, Status badge, Login Terakhir, Menu Aksi), serta kontrol paginasi. | Mengetik di kotak cari atau mengubah dropdown filter langsung menyaring tabel secara reaktif; klik baris aksi memicu modal edit/deaktivasi. | 1 klik dari Sidebar (menu "Data Petugas") |
| **Modal Form Petugas (Tambah / Edit)** | Modal `<flux:modal name="petugas-form">` di dalam `/petugas` | Header modal ("Tambah Petugas Baru" atau "Ubah Data Petugas"), input field: Nama Lengkap, NP / ID Karyawan, Username, Email, Pilihan Role (Supervisor / Petugas Kemas), input Password (wajib saat tambah baru, opsional dengan teks bantuan saat edit), tombol "Batal", dan tombol "Simpan Data". | Menampilkan pesan error validasi real-time jika ada duplikasi NP/email/username; tombol simpan menampilkan state loading saat proses penulisan data. | 1 klik dari tombol "+ Tambah Petugas" atau 2 klik dari menu baris tabel ("Edit") |
| **Modal Konfirmasi Status / Hapus** | Modal `<flux:modal name="delete-confirm">` di dalam `/petugas` | Dialog peringatan dengan teks konsekuensi yang jelas: konfirmasi nonaktifkan akun atau hapus akun beserta nama petugas terkait, tombol "Batal", dan tombol konfirmasi aksi (merah/amber). | Klik konfirmasi menjalankan mutasi status atau penghapusan data, menutup modal, dan memicu notifikasi toast. | 2 klik dari menu aksi baris tabel |
| **Global Toast Feedback** | Komponen `<flux:toast />` (Pojok kanan atas layar) | Tersembunyi saat awal; muncul otomatis selama 4 detik saat ada aksi berhasil (sukses simpan/update status) atau gagal (gagal login/akses ditolak). | Dapat ditutup manual oleh pengguna dengan klik tombol silang (x). | 0 (Otomatis muncul setelah aksi) |

---

## 3. User Journey Maps

Pemetaan langkah kerja pengguna untuk seluruh tugas utama pada Phase 01:

### Journey 1: Login Akun Petugas / Supervisor
- **Goal**: Masuk ke dalam sistem kerja harian menggunakan kredensial NP atau Username dan Password.
- **Starts at**: Halaman Login (`/login`).
- **Steps**:
  1. Pengguna membuka URL sistem dan langsung melihat kursor aktif pada input NP/Username.
  2. Pengguna mengetik NP/Username dan Password (dapat klik ikon mata untuk memastikan ketikan benar).
  3. Pengguna menekan tombol "Masuk ke Sistem" (atau menekan tombol `Enter`).
- **Done**: Sistem memvalidasi akun, mengarahkan pengguna ke halaman Dashboard, dan menampilkan pesan sambutan selamat bertugas.
- **Step count**: 3 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 2: Supervisor Menambah Akun Petugas Baru
- **Goal**: Mendaftarkan operator kemas baru ke dalam sistem agar dapat mulai melakukan pencatatan hasil produksi.
- **Starts at**: Dashboard Supervisor atau Halaman Data Petugas (`/petugas`).
- **Steps**:
  1. Supervisor mengklik menu "Data Petugas" pada sidebar navigasi.
  2. Supervisor mengklik tombol "+ Tambah Petugas" di pojok kanan atas toolbar.
  3. Supervisor mengisi form data (Nama, NP, Username, Email, Peran, Password awal).
  4. Supervisor mengklik tombol "Simpan Data".
- **Done**: Modal tertutup secara otomatis, baris petugas baru muncul di urutan teratas tabel, dan notifikasi toast sukses "Data petugas berhasil ditambahkan" muncul di pojok kanan atas.
- **Step count**: 4 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 3: Supervisor Mengubah Data Petugas
- **Goal**: Memperbarui informasi profil atau peran operator kemas yang sudah terdaftar.
- **Starts at**: Halaman Data Petugas (`/petugas`).
- **Steps**:
  1. Supervisor mencari nama/NP petugas pada input pencarian (hasil tabel menyaring seketika).
  2. Supervisor mengklik tombol menu aksi pada baris petugas, lalu memilih "Edit Data".
  3. Supervisor mengubah data yang diperlukan pada modal form (misal: penyesuaian email atau kenaikan peran).
  4. Supervisor mengklik tombol "Simpan Perubahan".
- **Done**: Modal tertutup, data pada baris tabel terperbarui secara instan, dan notifikasi toast sukses ditampilkan.
- **Step count**: 4 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 4: Supervisor Menonaktifkan Akun Petugas (Deaktivasi)
- **Goal**: Memblokir akses login petugas yang sedang cuti atau rotasi tugas tanpa menghapus riwayat laporan yang pernah dibuat.
- **Starts at**: Halaman Data Petugas (`/petugas`).
- **Steps**:
  1. Supervisor mengklik toggle/menu aksi "Nonaktifkan Akun" pada baris petugas target.
  2. Supervisor membaca dialog konfirmasi singkat dan mengklik "Ya, Nonaktifkan".
- **Done**: Modal konfirmasi tertutup, status badge berubah menjadi abu-abu "Nonaktif", dan toast "Status akun petugas berhasil diperbarui" ditampilkan.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

### Journey 5: Logout Akun Petugas / Supervisor (Ganti Shift)
- **Goal**: Mengakhiri sesi kerja pada komputer workstation sebelum pergantian shift operasional.
- **Starts at**: Halaman mana saja saat sedang login.
- **Steps**:
  1. Pengguna mengklik foto profil / nama akun di pojok kanan atas header.
  2. Pengguna mengklik opsi "Keluar / Logout".
- **Done**: Sesi kerja dibersihkan, CSRF token diperbarui, dan pengguna dialihkan kembali ke layar `/login`.
- **Step count**: 2 langkah.
- **Status**: ✅ OK (≤4 langkah).

---

## 4. Indonesian UX Guardrails

Panduan antarmuka khusus untuk menyesuaikan karakteristik operator dan staf lapangan di Unit Pengemasan:

### Patience Thresholds (Batas Toleransi Langkah Kerja)
- **Aktivitas Harian (Login, Logout, Cek Shift)**: Maksimal **2–3 langkah**. Input fokus otomatis (*autofocus*) saat membuka layar login agar pengguna dapat langsung mengetik tanpa klik tambahan.
- **Aktivitas Berkala (Tambah Petugas, Edit Profil, Ubah Status)**: Maksimal **3–4 langkah**. Seluruh proses diselesaikan dalam satu dialog modal tanpa reload halaman.
- **Aktivitas Darurat (Reset Password Petugas Lupa Kata Sandi)**: Maksimal **3 langkah** langsung melalui modal edit profil petugas oleh Supervisor.

### Cognitive Load Budget (Beban Kognitif Ringan)
- **Maksimal 3–4 aksi utama** yang terlihat langsung pada layar kerja (Tambah, Cari, Filter Role, Filter Status).
- **Formulir Ringkas**: Form petugas dibatasi hanya 6 field esensial yang dikelompokkan rapi (Identitas Staf: Nama & NP; Akses Sistem: Username, Email, Peran, Password).
- **Progressive Disclosure**:
  - Pada mode Edit Petugas, field password bersifat opsional dengan teks bantuan yang jelas (*"Kosongkan password jika tidak ingin mengubah"*).
  - Filter tabel bersifat inline dan terintegrasi dengan URL query string sehingga status pencarian tetap tersimpan jika halaman di-refresh.
- **Maksimal 1 Tingkat Modal**: Dilarang keras menampilkan modal di atas modal (*no nested modals*). Konfirmasi aksi dilakukan dengan transisi state modal yang jelas atau inline alert.

### Trust Signals & Umpan Balik Operasional
- **Konfirmasi Bahasa Indonesia yang Lugas untuk Aksi Destruktif**:
  - Dialog nonaktifkan akun: *"Apakah Anda yakin ingin menonaktifkan akun [Nama Petugas]? Petugas ini tidak akan dapat masuk ke sistem hingga diaktifkan kembali."*
  - Dialog proteksi data: *"Akun petugas yang sudah tercatat pada Laporan Hasil Produksi tidak dapat dihapus permanen untuk menjaga keabsahan riwayat dokumen. Silakan gunakan opsi Nonaktifkan Akun."*
- **Umpan Balik Instan (Toast Feedback)**: Setiap aksi simpan, ubah, aktivasi, dan hapus wajib memunculkan Flux Toast notifikasi berwarna hijau (sukses) atau merah (gagal) berdurasi 4 detik.
- **Pesan Kesalahan Bahasa Indonesia yang Manusiawi**:
  - ❌ *"The username field is required"* $\rightarrow$ ✅ *"Nomor Pegawai atau Username wajib diisi."*
  - ❌ *"Invalid credentials"* $\rightarrow$ ✅ *"Nomor Pegawai/Username atau Kata Sandi yang dimasukkan salah."*
  - ❌ *"Too many login attempts. Please try again in 60 seconds."* $\rightarrow$ ✅ *"Terlalu banyak percobaan login gagal. Demi keamanan, silakan coba lagi setelah 1 menit."*

### Risks Specific to Phase 01 & Mitigations

| Sub-epic | Potensi Risiko UX / Operasional | Solusi & Mitigasi |
|---|---|---|
| **US-1.1 Base Layout** | Banner shift tidak sinkron dengan jam kerja fisik di lini produksi | Jam header menggunakan waktu server WIB (`Asia/Jakarta`) dan mengelompokkan shift otomatis: Shift 1 (07:00–15:00), Shift 2 (15:00–23:00), Shift 3 (23:00–07:00). |
| **US-1.2 Login Page** | Operator salah ketik password berulang kali karena tidak bisa melihat teks | Pasang tombol toggle ikon mata (*show/hide password*) pada komponen `<flux:input>` password. |
| **US-1.3 Role Access** | Petugas kemas bingung saat melihat menu administrasi yang tidak bisa diakses | Sidebar secara tegas menyembunyikan menu "Data Petugas" dan "Update Database" bagi akun dengan peran Petugas (menu hanya dirender untuk peran Supervisor). |
| **US-1.4 Data Petugas Table** | Pencarian nama terasa lambat atau lagging saat mengetik | Gunakan Livewire debouncing 300ms (`wire:model.live.debounce.300ms`) dan skeleton/loading shimmer pada baris tabel saat proses query berjalan. |
| **US-1.5 Form Petugas Modal** | Supervisor tidak sengaja menutup modal saat form sudah terisi separuh jalan | Gunakan modal Flux dengan opsi `flyout` atau tombol batal eksplisit agar data tidak hilang akibat ketidaksengajaan klik di luar modal. |

---

## 5. Implementation Boundaries (Anti-Splitting Rules)

Batasan arsitektur dan antarmuka yang wajib dipatuhi oleh tim pengembang/AI saat mengimplementasikan Phase 01:

### Do Not Split — Combine Into One Surface:
- **Manajemen Petugas Keseluruhan**: Tabel data staf, pencarian, filter peran, tombol tambah, modal form create/edit, serta konfirmasi status **wajib disatukan di dalam satu halaman `/petugas`**.
- Dilarang membuat file controller/view terpisah untuk aksi-aksi kecil petugas.

### Make a Modal, Not a New Route:
- Form input Petugas Baru $\rightarrow$ Gunakan `<flux:modal name="petugas-form">`, **BUKAN** rute `/petugas/create`.
- Form edit data Petugas $\rightarrow$ Gunakan modal yang sama `<flux:modal name="petugas-form">` dengan data model terikat, **BUKAN** rute `/petugas/{id}/edit`.
- Dialog konfirmasi Hapus / Deaktivasi $\rightarrow$ Gunakan `<flux:modal name="delete-confirm">`, **BUKAN** halaman konfirmasi rute terpisah.

### Make a Header Banner, Not a Dedicated Widget Page:
- Informasi shift operasional dan jam real-time diletakkan di komponen `<flux:header>` utama, bukan di halaman dashboard terpisah atau widget yang memakan area kerja utama.

### Backend-Only — No Dedicated UI:
- Logika otorisasi role `EnsureUserRole` middleware $\rightarrow$ Cukup mengarahkan kembali (*redirect*) dengan pesan kilat atau menampilkan halaman error 403 standar jika ada bypass URL.
- Algoritma hashing password dan Fortify rate-limiting $\rightarrow$ Berjalan otomatis pada level server tanpa konfigurasi UI khusus.
- Proteksi integritas foreign key (relasi user dengan laporan produksi) $\rightarrow$ Divalidasi langsung saat pemanggilan method `delete()` di Livewire component.

### Strictly Forbidden:
- **Dilarang** membuat item navigasi sidebar terpisah untuk "Tambah Petugas" atau "Hak Akses/Role Management". Seluruh pengelolaan akun berpusat pada satu menu **"Data Petugas"**.
- **Dilarang** menggunakan bahasa teknis internal (seperti *SQLSTATE*, *Eloquent Model*, *Undefined Variable*, *403 Forbidden*) pada toast feedback atau pesan error formulir.
- **Dilarang** membuat sistem multi-step form (*wizard*) yang berbelit-belit untuk pendaftaran staf petugas kemas. Form pendaftaran harus selesai dalam 1 modal ringkas.
- **Dilarang** menggunakan package/library JavaScript eksternal yang berat untuk data table jika sudah dapat ditangani secara native dan reaktif oleh Livewire 4 dan Flux UI.
