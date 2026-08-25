# Scrum Planning Overview - Sistem Scan Produksi Harian PT PDS

**Project Name**: Sistem Dokumentasi Hasil Produksi Harian Berbasis Web dengan Scan Barcode  
**Client / Case Study**: PT Percetakan Dokumen Sekuriti (Unit Pengemasan / Packaging Unit)  
**Developer**: Zulfikar Hidayatullah  
**Tech Stack**: Laravel, Inertia.js (Vue 3), Wayfinder Route Helper, Tailwind CSS, Pest PHP, pnpm  
**Standards**: Timezone `Asia/Jakarta` (WIB), Currency `IDR` (`Rp`), Laravel Service Pattern  

---

## 1. Project Background & Objective

Berdasarkan analisis dokumen Laporan KKP (Umar Ibnu Zainal Muttaqin - NIM 311810909), Unit Pengemasan pada PT Percetakan Dokumen Sekuriti menghadapi kendala inefisiensi pencatatan manual (tulis tangan) hasil produksi kemas harian. Penumpukan Kartu Mesin Produksi, risiko data terselip/hilang, dan keterlambatan pelaporan ke Kepala Unit/Supervisor menjadi masalah operasional harian.

Sistem baru ini memanfaatkan **Barcode Scanner (Code 128)** yang membaca Nomor Order (No PO) dari Kartu Mesin Produksi, terintegrasi dengan Master Data SAP (via fitur Update Database/Import), mengotomatisasi pengisian spesifikasi barang, menghasilkan ID Laporan unik, menyediakan pencetakan dokumen hasil kemas, serta visualisasi data performa produksi **14 hari terakhir** pada Dashboard.

---

## 2. Architecture & Role Matrix

### Actors & Roles
1. **Petugas (Operator Pengemasan)**:
   - Login & autentikasi sistem.
   - Scan barcode Kartu Mesin Produksi (Code 128) dan input data hasil kemas harian.
   - Menyimpan draft dan finalisasi Laporan Produksi Harian.
   - Mencetak laporan harian.
2. **Kepala Unit / Supervisor (Admin / Reviewer)**:
   - Mengelola master data petugas (CRUD User Petugas).
   - Melakukan import/update database dari file SAP (Excel/CSV).
   - Memantau rekapitulasi, detail audit, dan grafik performa produksi 14 hari terakhir di Dashboard.
   - Mengedit/koreksi data laporan serta verifikasi status laporan.

---

## 3. Sprint Roadmap Breakdown

| Phase | Phase Title | Focus Area | Est. Duration | Total Points |
|---|---|---|---|---|
| **Phase 01** | [Foundation, Auth & User Management](phase-01-foundation-auth-user-management.md) | Base Architecture, Fortify Auth, Role Middleware, Petugas CRUD | 3-4 days | 26 pts |
| **Phase 02** | [SAP Master Data Import & Management](phase-02-sap-master-data-import.md) | Master Data Schema, SAP Excel/CSV Parser, Validation, Import History | 4-5 days | 29 pts |
| **Phase 03** | [Barcode Scan & Daily Production Entry](phase-03-barcode-scan-daily-production.md) | Code 128 Scanner Handler, Real-time Spec Lookup, Packaging Form, Multi-Item Draft | 5-6 days | 34 pts |
| **Phase 04** | [Report Management, Verification & Print](phase-04-report-management-and-print.md) | Report Index, Unique Report ID Generator, Edit/Audit Trail, Print Template & Export | 4-5 days | 28 pts |
| **Phase 05** | [Dashboard Analytics & 14-Day Production Graph](phase-05-dashboard-analytics-14-day-chart.md) | 14-Day Trend Chart, KPI Metric Cards, Shift Breakdown, Quick Actions | 3-4 days | 24 pts |

**Total Estimated Sprint Duration**: 19 - 24 Days  
**Total Story Points**: 141 Story Points

---

## 4. Phase Navigation Links

- [Phase 01: Foundation, Auth & User Management](phase-01-foundation-auth-user-management.md)
- [Phase 02: SAP Master Data Import & Management](phase-02-sap-master-data-import.md)
- [Phase 03: Barcode Scan & Daily Production Entry](phase-03-barcode-scan-daily-production.md)
- [Phase 04: Report Management, Verification & Print](phase-04-report-management-and-print.md)
- [Phase 05: Dashboard Analytics & 14-Day Production Graph](phase-05-dashboard-analytics-14-day-chart.md)
