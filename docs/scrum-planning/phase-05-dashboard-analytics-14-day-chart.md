# Phase 05 - Dashboard Analytics & 14-Day Production Graph

**Sprint Goal**: Membangun Beranda / Dashboard terpadu berbasis Livewire 4 & Flux UI yang menampilkan ringkasan KPI produksi harian, pintasan navigasi cepat ke seluruh modul sistem, serta **Grafik Analitik Hasil Produksi 14 Hari Terakhir** (visualisasi volume hasil kemas baik, reject, dan perbandingan performa shift/mesin menggunakan Alpine.js + Chart.js) untuk monitoring Kepala Unit/Supervisor dan Petugas.

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
- 4-5 responsive Flux cards (`<flux:card>`) with distinct icon accents (Emerald for good output, Rose for reject, Indigo for active POs, Amber for pending reports) and trend indicators.

**Tasks:**
1. Create `app/Services/DashboardAnalyticsService.php` to compute daily aggregation queries.
2. Implement caching strategy using `Cache::tags(['production_stats'])`.
3. Create Livewire Component `app/Livewire/Dashboard/DashboardIndex.php` and Blade view `resources/views/livewire/dashboard/index.blade.php`.
4. Write Pest feature tests for dashboard statistics aggregation in `tests/Feature/Dashboard/DashboardStatsTest.php`.

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
- [ ] Chart renders smoothly using Chart.js via Alpine.js component (`x-data="productionChart(...)"`).

**UI Specs:**
- Large centerpiece card on the dashboard with title "Grafik Tren Produksi & Hasil Kemas (14 Hari Terakhir)".
- Interactive hover tooltips and responsive canvas container.
- Clean axis labels in Indonesian format.

**Tasks:**
1. Add 14-day timeseries aggregation query in `DashboardAnalyticsService.php` with zero-fill for days without production.
2. Setup lightweight Chart.js bundle in `resources/js/app.js`.
3. Create Alpine.js chart component in Blade view.
4. Implement reactive date-range switcher.
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
- [ ] Responsive side-by-side Flux cards below the 14-day chart.

**Tasks:**
1. Implement shift and machine grouping queries in `DashboardAnalyticsService.php`.
2. Create Blade partials for shift distribution and machine output tables.
3. Write unit tests for shift and machine aggregation in `tests/Unit/Services/DashboardAnalyticsServiceTest.php`.

---

### US-5.4: Quick Action Hub & Recent Activity Feed

**As a** petugas or supervisor  
**I want** quick-access action buttons and a live list of recent production reports on the dashboard  
**So that** I can immediately start scanning, update master SAP data, or review the latest submitted shift reports

**Acceptance Criteria:**
- [ ] Quick Action Grid:
  - Button 1: "Buat Laporan Baru (Scan Barcode)" (`<flux:button variant="primary" href="{{ route('reports.create') }}">`).
  - Button 2: "Update Database (Import SAP)" (`<flux:button href="{{ route('master-data.update-database') }}">`).
  - Button 3: "Lihat Semua Laporan" (`<flux:button href="{{ route('reports.index') }}">`).
  - Button 4: "Kelola Data Petugas" (Supervisor only, `<flux:button href="{{ route('petugas.index') }}">`).
- [ ] Recent Reports Table:
  - Displays the 5 most recent daily reports submitted using `<flux:table>`.
  - Columns: ID Laporan, Tanggal, Shift, Petugas, Total Qty, Status, Aksi Cepat (Lihat / Cetak).
- [ ] Live timestamp display in `Asia/Jakarta` (WIB) with active shift banner.

**UI Specs:**
- Quick actions presented as accessible tile cards with descriptive Flux icons.
- Recent activity table with direct view links.

**Tasks:**
1. Wire Livewire `DashboardIndex` component with today's metrics and recent reports.
2. Create Blade views with Flux components.
3. Optimize rendering with Blaze (`@blaze`).
4. Write Pest feature test for complete dashboard payload in `tests/Feature/Dashboard/DashboardIndexTest.php`.

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

### Livewire DashboardIndex Component

```php
// app/Livewire/Dashboard/DashboardIndex.php
namespace App\Livewire\Dashboard;

use App\Models\DailyReport;
use App\Services\DashboardAnalyticsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DashboardIndex extends Component
{
    public function render(DashboardAnalyticsService $analyticsService)
    {
        return view('livewire.dashboard.index', [
            'metrics' => $analyticsService->getTodayMetrics(),
            'chart14Days' => $analyticsService->get14DaysProductionTrend(),
            'shiftDistribution' => $analyticsService->getShiftDistribution(14),
            'topMachines' => $analyticsService->getTopMachines(14),
            'recentReports' => DailyReport::with(['user', 'supervisor'])->latest()->take(5)->get(),
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
| Charting library (Chart.js via Alpine) | Frontend package |
| Authenticated user role & Flux layout | Phase 01 |

---

## Risks

| Risk | Mitigation |
|---|---|
| Heavy database aggregation queries slowing down dashboard render | Cache aggregated query results for 5 minutes and utilize indexed `report_date` columns |
| Timezone inconsistencies causing day boundary mismatch in 14-day series | Standardize all date grouping queries explicitly to `Asia/Jakarta` (WIB) |
