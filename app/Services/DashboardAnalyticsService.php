<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Helpers\ShiftHelper;
use App\Models\DailyReport;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Cache duration in seconds (5 minutes).
     */
    public const CACHE_TTL = 300;

    /**
     * Ambil ringkasan indikator kinerja utama (KPI) produksi hari ini dalam zona waktu Asia/Jakarta (WIB).
     *
     * @return array<string, mixed>
     */
    public function getTodayMetrics(): array
    {
        $cacheKey = 'dashboard_today_metrics';

        return $this->remember($cacheKey, function () {
            $today = Carbon::today('Asia/Jakarta');
            $yesterday = $today->copy()->subDay();

            // Ambil semua laporan hari ini
            $todayReports = DailyReport::whereDate('report_date', $today)->get();
            $todayGood = (int) $todayReports->sum('total_good_quantity');
            $todayReject = (int) $todayReports->sum('total_reject_quantity');

            // Ambil semua laporan kemarin untuk pembanding persentase
            $yesterdayReports = DailyReport::whereDate('report_date', $yesterday)->get();
            $yesterdayGood = (int) $yesterdayReports->sum('total_good_quantity');

            // Kalkulasi perbandingan persentase terhadap kemarin
            $comparisonPercentage = 0.0;
            $trendDirection = 'neutral';

            if ($yesterdayGood > 0) {
                $comparisonPercentage = round((($todayGood - $yesterdayGood) / $yesterdayGood) * 100, 1);
                if ($comparisonPercentage > 0) {
                    $trendDirection = 'up';
                } elseif ($comparisonPercentage < 0) {
                    $trendDirection = 'down';
                }
            } elseif ($todayGood > 0) {
                $comparisonPercentage = 100.0;
                $trendDirection = 'up';
            }

            // Hitung persentase reject hari ini
            $totalProductionToday = $todayGood + $todayReject;
            $rejectRate = $totalProductionToday > 0
                ? round(($todayReject / $totalProductionToday) * 100, 2)
                : 0.0;

            // Hitung jumlah PO unik yang diproses hari ini
            $poCountToday = (int) DB::table('daily_reports')
                ->join('daily_report_items', 'daily_reports.id', '=', 'daily_report_items.daily_report_id')
                ->whereDate('daily_reports.report_date', $today)
                ->distinct('daily_report_items.po_number')
                ->count('daily_report_items.po_number');

            // Hitung status berkas laporan hari ini
            $submittedReportsCount = $todayReports->where('status', ReportStatus::SUBMITTED)->count();
            $verifiedReportsCount = $todayReports->where('status', ReportStatus::VERIFIED)->count();
            $draftReportsCount = $todayReports->where('status', ReportStatus::DRAFT)->count();
            $totalReportsCount = $todayReports->count();

            // Hitung petugas aktif yang bertugas pada shift saat ini
            $currentShiftData = ShiftHelper::getCurrentShift();
            $currentShiftEnum = WorkShift::tryFrom('shift_'.$currentShiftData['number']);

            $activeOperatorsCount = $currentShiftEnum
                ? $todayReports->where('shift', $currentShiftEnum)->pluck('user_id')->unique()->count()
                : 0;

            $totalActiveStaff = User::where('role', UserRole::PETUGAS)
                ->where('is_active', true)
                ->count();

            return [
                'date' => $today->toDateString(),
                'date_formatted' => ShiftHelper::getFormattedDateTime($today),
                'total_good' => $todayGood,
                'total_reject' => $todayReject,
                'reject_rate' => $rejectRate,
                'yesterday_good' => $yesterdayGood,
                'comparison_percentage' => $comparisonPercentage,
                'trend_direction' => $trendDirection,
                'po_count' => $poCountToday,
                'submitted_reports' => $submittedReportsCount,
                'verified_reports' => $verifiedReportsCount,
                'draft_reports' => $draftReportsCount,
                'total_reports' => $totalReportsCount,
                'active_operators' => $activeOperatorsCount,
                'total_active_staff' => $totalActiveStaff,
                'current_shift' => $currentShiftData,
            ];
        });
    }

    /**
     * Ambil data deret waktu analitik produksi untuk grafik tren harian (default 14 hari terakhir).
     * Menerapkan zero-fill agar tidak ada tanggal yang terputus.
     *
     * @return array<string, mixed>
     */
    public function getProductionTrend(int $days = 14): array
    {
        $days = in_array($days, [7, 14, 30], true) ? $days : 14;
        $cacheKey = "dashboard_{$days}_days_trend";

        return $this->remember($cacheKey, function () use ($days) {
            $endDate = Carbon::today('Asia/Jakarta');
            $startDate = $endDate->copy()->subDays($days - 1);

            // Kueri agregasi berbasis tanggal laporan
            $productionData = DB::table('daily_reports')
                ->join('daily_report_items', 'daily_reports.id', '=', 'daily_report_items.daily_report_id')
                ->whereDate('daily_reports.report_date', '>=', $startDate->toDateString())
                ->whereDate('daily_reports.report_date', '<=', $endDate->toDateString())
                ->select([
                    'daily_reports.report_date',
                    'daily_reports.id as report_id',
                    'daily_report_items.good_quantity',
                    'daily_report_items.reject_quantity',
                    'daily_report_items.po_number',
                ])
                ->get()
                ->groupBy(function ($item) {
                    return Carbon::parse($item->report_date)->toDateString();
                })
                ->map(function ($items) {
                    return (object) [
                        'total_good' => $items->sum('good_quantity'),
                        'total_reject' => $items->sum('reject_quantity'),
                        'total_po_count' => $items->pluck('po_number')->unique()->count(),
                        'total_reports' => $items->pluck('report_id')->unique()->count(),
                    ];
                });

            $labels = [];
            $fullDates = [];
            $goodSeries = [];
            $rejectSeries = [];
            $poCountSeries = [];
            $reportsCountSeries = [];

            $totalPeriodGood = 0;
            $totalPeriodReject = 0;
            $peakGood = 0;
            $peakDateFormatted = '-';

            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $shortLabel = self::formatShortIndonesianDate($date);
                $fullDateLabel = self::formatFullIndonesianDate($date);

                $labels[] = $shortLabel;
                $fullDates[] = $fullDateLabel;

                $record = $productionData->get($dateStr);
                $good = $record ? (int) $record->total_good : 0;
                $reject = $record ? (int) $record->total_reject : 0;
                $poCount = $record ? (int) $record->total_po_count : 0;
                $reportsCount = $record ? (int) $record->total_reports : 0;

                $goodSeries[] = $good;
                $rejectSeries[] = $reject;
                $poCountSeries[] = $poCount;
                $reportsCountSeries[] = $reportsCount;

                $totalPeriodGood += $good;
                $totalPeriodReject += $reject;

                if ($good > $peakGood) {
                    $peakGood = $good;
                    $peakDateFormatted = $fullDateLabel;
                }
            }

            $totalProductionPeriod = $totalPeriodGood + $totalPeriodReject;
            $overallRejectRate = $totalProductionPeriod > 0
                ? round(($totalPeriodReject / $totalProductionPeriod) * 100, 2)
                : 0.0;

            return [
                'days' => $days,
                'labels' => $labels,
                'full_dates' => $fullDates,
                'datasets' => [
                    'good' => $goodSeries,
                    'reject' => $rejectSeries,
                    'po_count' => $poCountSeries,
                    'reports_count' => $reportsCountSeries,
                ],
                'summary' => [
                    'total_good' => $totalPeriodGood,
                    'total_reject' => $totalPeriodReject,
                    'average_daily_good' => (int) round($totalPeriodGood / $days),
                    'peak_good' => $peakGood,
                    'peak_date' => $peakDateFormatted,
                    'reject_rate' => $overallRejectRate,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'start_date_formatted' => self::formatShortIndonesianDate($startDate),
                    'end_date_formatted' => self::formatShortIndonesianDate($endDate),
                ],
            ];
        });
    }

    /**
     * Alias spesifik untuk grafik 14 hari.
     *
     * @return array<string, mixed>
     */
    public function get14DaysProductionTrend(): array
    {
        return $this->getProductionTrend(14);
    }

    /**
     * Ambil distribusi proporsi hasil produksi berdasarkan shift kerja untuk periode hari tertentu.
     *
     * @return array<string, mixed>
     */
    public function getShiftDistribution(int $days = 14): array
    {
        $cacheKey = "dashboard_shift_distribution_{$days}";

        return $this->remember($cacheKey, function () use ($days) {
            $endDate = Carbon::today('Asia/Jakarta');
            $startDate = $endDate->copy()->subDays($days - 1);

            $shiftData = DB::table('daily_reports')
                ->join('daily_report_items', 'daily_reports.id', '=', 'daily_report_items.daily_report_id')
                ->whereDate('daily_reports.report_date', '>=', $startDate->toDateString())
                ->whereDate('daily_reports.report_date', '<=', $endDate->toDateString())
                ->select([
                    'daily_reports.shift',
                    'daily_report_items.good_quantity',
                    'daily_report_items.reject_quantity',
                ])
                ->get()
                ->groupBy('shift')
                ->map(function ($items) {
                    return (object) [
                        'total_good' => $items->sum('good_quantity'),
                        'total_reject' => $items->sum('reject_quantity'),
                    ];
                });

            $totalAllShiftsGood = (int) $shiftData->sum('total_good');
            $shiftsResult = [];

            foreach (WorkShift::cases() as $shiftEnum) {
                $record = $shiftData->get($shiftEnum->value);
                $good = $record ? (int) $record->total_good : 0;
                $reject = $record ? (int) $record->total_reject : 0;
                $percentage = $totalAllShiftsGood > 0 ? round(($good / $totalAllShiftsGood) * 100, 1) : 0.0;

                $shiftsResult[] = [
                    'shift' => $shiftEnum->value,
                    'name' => $shiftEnum->label(),
                    'time_range' => $shiftEnum->timeRange(),
                    'badge_color' => $shiftEnum->badgeColor(),
                    'good_quantity' => $good,
                    'reject_quantity' => $reject,
                    'total_quantity' => $good + $reject,
                    'percentage' => $percentage,
                ];
            }

            return [
                'total_good' => $totalAllShiftsGood,
                'shifts' => $shiftsResult,
            ];
        });
    }

    /**
     * Ambil peringkat 5 mesin cetak terbanyak dikemas dalam periode tertentu.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTopMachines(int $days = 14, int $limit = 5): array
    {
        $cacheKey = "dashboard_top_machines_{$days}";

        return $this->remember($cacheKey, function () use ($days, $limit) {
            $endDate = Carbon::today('Asia/Jakarta');
            $startDate = $endDate->copy()->subDays($days - 1);

            $machines = DB::table('daily_reports')
                ->join('daily_report_items', 'daily_reports.id', '=', 'daily_report_items.daily_report_id')
                ->whereDate('daily_reports.report_date', '>=', $startDate->toDateString())
                ->whereDate('daily_reports.report_date', '<=', $endDate->toDateString())
                ->whereNotNull('daily_report_items.machine_number')
                ->where('daily_report_items.machine_number', '!=', '')
                ->select([
                    'daily_report_items.machine_number',
                    'daily_report_items.good_quantity',
                    'daily_report_items.reject_quantity',
                    'daily_report_items.po_number',
                ])
                ->get()
                ->groupBy('machine_number')
                ->map(function ($items, $machineNumber) {
                    return (object) [
                        'machine_number' => $machineNumber,
                        'total_good' => $items->sum('good_quantity'),
                        'total_reject' => $items->sum('reject_quantity'),
                        'total_po_count' => $items->pluck('po_number')->unique()->count(),
                    ];
                })
                ->sortByDesc('total_good')
                ->take($limit);

            $totalAllMachinesGood = (int) DB::table('daily_reports')
                ->join('daily_report_items', 'daily_reports.id', '=', 'daily_report_items.daily_report_id')
                ->whereDate('daily_reports.report_date', '>=', $startDate->toDateString())
                ->whereDate('daily_reports.report_date', '<=', $endDate->toDateString())
                ->sum('daily_report_items.good_quantity');

            $result = [];
            $rank = 1;

            foreach ($machines as $machine) {
                $good = (int) $machine->total_good;
                $percentage = $totalAllMachinesGood > 0 ? round(($good / $totalAllMachinesGood) * 100, 1) : 0.0;

                $result[] = [
                    'rank' => $rank++,
                    'machine_number' => $machine->machine_number,
                    'good_quantity' => $good,
                    'reject_quantity' => (int) $machine->total_reject,
                    'po_count' => (int) $machine->total_po_count,
                    'percentage' => $percentage,
                ];
            }

            return $result;
        });
    }

    /**
     * Bersihkan seluruh cache analitik dashboard.
     */
    public function clearCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags(['production_stats'])->flush();
        } else {
            Cache::forget('dashboard_today_metrics');
            Cache::forget('dashboard_7_days_trend');
            Cache::forget('dashboard_14_days_trend');
            Cache::forget('dashboard_30_days_trend');
            Cache::forget('dashboard_shift_distribution_7');
            Cache::forget('dashboard_shift_distribution_14');
            Cache::forget('dashboard_shift_distribution_30');
            Cache::forget('dashboard_top_machines_7');
            Cache::forget('dashboard_top_machines_14');
            Cache::forget('dashboard_top_machines_30');
        }
    }

    /**
     * Eksekusi cache dengan dukungan tags jika tersedia.
     */
    protected function remember(string $key, \Closure $callback): mixed
    {
        if (Cache::supportsTags()) {
            return Cache::tags(['production_stats'])->remember($key, self::CACHE_TTL, $callback);
        }

        return Cache::remember($key, self::CACHE_TTL, $callback);
    }

    /**
     * Format tanggal ringkas Bahasa Indonesia (contoh: "13 Agu").
     */
    public static function formatShortIndonesianDate(Carbon $date): string
    {
        $shortMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $day = $date->format('d');
        $month = $shortMonths[(int) $date->format('m')] ?? $date->format('M');

        return "{$day} {$month}";
    }

    /**
     * Format tanggal lengkap Bahasa Indonesia (contoh: "Rabu, 26 Agustus 2026").
     */
    public static function formatFullIndonesianDate(Carbon $date): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dayName = $days[$date->format('l')] ?? $date->format('l');
        $day = $date->format('d');
        $month = $months[(int) $date->format('m')] ?? $date->format('F');
        $year = $date->format('Y');

        return "{$dayName}, {$day} {$month} {$year}";
    }
}
