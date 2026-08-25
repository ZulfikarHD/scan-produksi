# Phase 05 - Dashboard Analytics & 14-Day Production Graph

**Sprint Goal**: Membangun Beranda / Dashboard terpadu yang menampilkan ringkasan KPI produksi harian, pintasan navigasi cepat ke seluruh modul sistem, serta **Grafik Analitik Hasil Produksi 14 Hari Terakhir** (visualisasi volume hasil kemas baik, reject, dan perbandingan performa shift/mesin) untuk monitoring Kepala Unit/Supervisor dan Petugas.

**Estimated Duration**: 3-4 days

**Depends on**: Phase 03 (Daily Production Data) & Phase 04 (Report Management)

---

## User Stories

### US-5.1: Production Metrics & KPI Summary Cards

**As a** supervisor or petugas  
**I want** to see real-time production KPI summary cards at the top of the dashboard  
**So that** I immediately know today's packaging output, active orders, and shift achievements at a single glance

**Acceptance Criteria:**
- [ ] Stat Card 1: **Total Kemas Hari Ini** (Total good quantity packed today in WIB timezone, with % comparison to yesterday).
- [ ] Stat Card 2: **Total Reject Hari Ini** (Total reject quantity today and reject rate percentage).
- [ ] Stat Card 3: **Total PO / Item Terscan** (Count of unique production orders processed today).
- [ ] Stat Card 4: **Laporan Terkirim & Terverifikasi** (Count of submitted vs verified reports today).
- [ ] Stat Card 5: **Petugas Bertugas** (Count of active packaging operators on duty for current shift).
- [ ] Real-time caching: Service caches KPI metrics for 5 minutes with instant cache tag invalidation on new report submission.

**UI Specs:**
- 4-5 responsive metric cards with distinct icons, color accents (Emerald for good output, Rose for reject, Indigo for active POs, Amber for pending reports), and trend indicators (+X% vs kemarin).

**Tasks:**
1. Create `app/Services/DashboardAnalyticsService.php` to compute daily aggregation queries.
2. Implement caching strategy using `Cache::tags(['production_stats'])`.
3. Create Inertia Page `resources/js/pages/Dashboard/Index.vue`.
4. Create reusable KPI card component `resources/js/components/Dashboard/MetricCard.vue`.
5. Write Pest feature tests for dashboard statistics aggregation in `tests/Feature/Dashboard/DashboardStatsTest.php`.

---

### US-5.2: 14-Day Production Output Interactive Graph (Core Feature)

**As a** supervisor / kepala unit  
**I want** an interactive chart displaying daily production volume over the last 14 days  
**So that** I can analyze packaging trends, identify volume peaks/dips, and evaluate packaging consistency

**Acceptance Criteria:**
- [ ] Dynamic Line / Bar Chart showing daily production totals for the **last 14 consecutive days** (Day 1 to Day 14).
- [ ] Dual dataset visualization:
  - Series 1 (Green/Teal): **Hasil Kemas Baik (Good Output Qty)**
  - Series 2 (Red/Rose): **Jumlah Reject (Defect Qty)**
- [ ] Tooltip detailing for each day:
  - Exact Date (Format: `DD MMMM YYYY`, e.g. `25 Agustus 2026`)
  - Qty Kemas Baik
  - Qty Reject
  - Total PO Diproses
  - Total Laporan Shift
- [ ] Toggle view options:
  - Switch chart type: Area / Line / Bar
  - Filter toggle: `14 Hari Terakhir` (Default), `7 Hari Terakhir`, `30 Hari Terakhir`
- [ ] Summary stat badges above the chart:
  - Rata-rata Produksi Harian (14 hari)
  - Hari Produksi Tertinggi (Peak Day)
  - Total Akumulasi 14 Hari
- [ ] Chart renders smoothly using lightweight frontend library (Chart.js / ApexCharts / Vue-Chartjs).

**UI Specs:**
- Large centerpiece card on the dashboard with title "Grafik Tren Produksi & Hasil Kemas (14 Hari Terakhir)".
- Interactive hover tooltips and responsive canvas container.
- Clean axis labels in Indonesian format.

**Tasks:**
1. Add 14-day timeseries aggregation query in `DashboardAnalyticsService.php` with zero-fill for days without production.
2. Install client-side chart library (e.g. `chart.js` + `vue-chartjs`).
3. Create Vue component `resources/js/components/Dashboard/Production14DaysChart.vue`.
4. Implement reactive date-range switcher and chart type switcher.
5. Write Pest feature tests for 14-day date-range calculation and zero-filled timeseries data in `tests/Feature/Dashboard/ProductionChartTest.php`.

---

### US-5.3: Shift & Machine Performance Distribution Widgets

**As a** supervisor  
**I want** to see the distribution of packaging volume by Shift (Shift 1, 2, 3) and Machine over the last 14 days  
**So that** I can allocate manpower and identify any bottlenecks in specific printing/packaging lines

**Acceptance Criteria:**
- [ ] Widget 1: **Performa Berdasarkan Shift (14 Hari)**:
  - Donut / Pie chart or progress bars showing % contribution of Shift 1 (Pagi), Shift 2 (Siang), and Shift 3 (Malam).
- [ ] Widget 2: **Top 5 Mesin Cetak Terbanyak Dikemas**:
  - Breakdown of output volume per machine number (e.g. `MESIN-01`, `MESIN-02`, etc.).
- [ ] Responsive side-by-side card layout below the 14-day chart.

**Tasks:**
1. Implement shift and machine grouping queries in `DashboardAnalyticsService.php`.
2. Create Vue components `resources/js/components/Dashboard/ShiftDistributionCard.vue` and `MachineOutputCard.vue`.
3. Write unit tests for shift and machine aggregation in `tests/Unit/Services/DashboardAnalyticsServiceTest.php`.

---

### US-5.4: Quick Action Hub & Recent Activity Feed

**As a** petugas or supervisor  
**I want** quick-access action buttons and a live list of recent production reports on the dashboard  
**So that** I can immediately start scanning, update master SAP data, or review the latest submitted shift reports

**Acceptance Criteria:**
- [ ] Quick Action Grid:
  - Button 1: "Buat Laporan Baru (Scan Barcode)" (Primary call to action, navigates to `/reports/create` via Wayfinder).
  - Button 2: "Update Database (Import SAP)" (Navigates to `/update-database`).
  - Button 3: "Lihat Semua Laporan" (Navigates to `/reports`).
  - Button 4: "Kelola Data Petugas" (Supervisor only, navigates to `/petugas`).
- [ ] Recent Reports Table:
  - Displays the 5 most recent daily reports submitted.
  - Columns: ID Laporan, Tanggal, Shift, Petugas, Total Qty, Status, Aksi Cepat (Lihat / Cetak).
- [ ] Live timestamp display in `Asia/Jakarta` (WIB) with active shift banner.

**UI Specs:**
- Quick actions presented as accessible tile cards with descriptive icons.
- Recent activity table with direct view links.

**Tasks:**
1. Implement `DashboardController.php` assembling all dashboard datasets for Inertia payload.
2. Create component `resources/js/components/Dashboard/QuickActionHub.vue`.
3. Create component `resources/js/components/Dashboard/RecentReportsTable.vue`.
4. Wire Wayfinder named routes (`productionReports.create`, `masterData.updateDatabase`, `productionReports.index`, `petugas.index`).
5. Write Pest feature test for complete dashboard payload in `tests/Feature/Dashboard/DashboardIndexTest.php`.

---

## Technical Notes

### 14-Day Production Timeseries Query Pattern

```php
// app/Services/DashboardAnalyticsService.php
namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Get aggregated daily production data for the last 14 days.
     */
    public function get14DaysProductionTrend(): array
    {
        $endDate = Carbon::today('Asia/Jakarta');
        $startDate = $endDate->copy()->subDays(13); // 14 days inclusive

        // Query raw aggregations grouped by date
        $productionData = DB::table('daily_reports')
            ->join('daily_report_items', 'daily_reports.id', '=', 'daily_report_items.daily_report_id')
            ->whereBetween('daily_reports.report_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('daily_reports.report_date')
            ->select([
                'daily_reports.report_date',
                DB::raw('SUM(daily_report_items.good_quantity) as total_good'),
                DB::raw('SUM(daily_report_items.reject_quantity) as total_reject'),
                DB::raw('COUNT(DISTINCT daily_report_items.po_number) as total_po_count'),
                DB::raw('COUNT(DISTINCT daily_reports.id) as total_reports'),
            ])
            ->get()
            ->keyBy('report_date');

        // Zero-fill all 14 days in sequence
        $labels = [];
        $goodSeries = [];
        $rejectSeries = [];
        $poCountSeries = [];
        $total14DaysGood = 0;
        $total14DaysReject = 0;

        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $label = $date->translatedFormat('d M'); // e.g. "12 Agu"
            $labels[] = $label;

            $record = $productionData->get($dateStr);
            $good = $record ? (int) $record->total_good : 0;
            $reject = $record ? (int) $record->total_reject : 0;
            $poCount = $record ? (int) $record->total_po_count : 0;

            $goodSeries[] = $good;
            $rejectSeries[] = $reject;
            $poCountSeries[] = $poCount;

            $total14DaysGood += $good;
            $total14DaysReject += $reject;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'good' => $goodSeries,
                'reject' => $rejectSeries,
                'po_count' => $poCountSeries,
            ],
            'summary' => [
                'total_good_14_days' => $total14DaysGood,
                'total_reject_14_days' => $total14DaysReject,
                'average_daily_good' => round($total14DaysGood / 14),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ];
    }
}
```

### Dashboard Controller Pattern

```php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Services\DashboardAnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardAnalyticsService $analyticsService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'metrics' => $this->analyticsService->getTodayMetrics(),
            'chart14Days' => $this->analyticsService->get14DaysProductionTrend(),
            'shiftDistribution' => $this->analyticsService->getShiftDistribution(14),
            'topMachines' => $this->analyticsService->getTopMachines(14),
            'recentReports' => $this->analyticsService->getRecentReports(5),
        ]);
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-5.1 Production Metrics & KPI Summary Cards | 5 | Must |
| 2 | US-5.2 14-Day Production Output Interactive Graph | 8 | Must |
| 3 | US-5.3 Shift & Machine Performance Distribution Widgets | 5 | Should |
| 4 | US-5.4 Quick Action Hub & Recent Activity Feed | 6 | Must |

**Total Story Points: 24**

---

## Dependencies

| Dependency | From |
|---|---|
| DailyReport & DailyReportItem data models | Phase 03 & 04 |
| Charting library (`chart.js` / `vue-chartjs`) | Frontend package |
| Authenticated user role & navigation | Phase 01 |

---

## Risks

| Risk | Mitigation |
|---|---|
| Heavy database aggregation queries slowing down dashboard render | Cache aggregated query results for 5 minutes and utilize indexed `report_date` columns |
| Timezone inconsistencies causing day boundary mismatch in 14-day series | Standardize all date grouping queries explicitly to `Asia/Jakarta` (WIB) |
