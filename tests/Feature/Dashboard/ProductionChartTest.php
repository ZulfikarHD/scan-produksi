<?php

use App\Enums\ProductSeries;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Carbon\Carbon;

test('14-day timeseries returns exactly 14 sequential data points with zero-fill', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $trend = $service->get14DaysProductionTrend();

    expect($trend['days'])->toBe(14)
        ->and(count($trend['labels']))->toBe(14)
        ->and(count($trend['full_dates']))->toBe(14)
        ->and(count($trend['datasets']['good']))->toBe(14)
        ->and(count($trend['datasets']['reject']))->toBe(14)
        ->and(count($trend['datasets']['po_count']))->toBe(14)
        ->and(count($trend['datasets']['reports_count']))->toBe(14);

    // Semua nilai bernilai 0 jika belum ada data
    expect($trend['summary']['total_good'])->toBe(0)
        ->and($trend['summary']['total_reject'])->toBe(0)
        ->and($trend['summary']['average_daily_good'])->toBe(0)
        ->and($trend['summary']['peak_good'])->toBe(0);
});

test('14-day trend accurately aggregates multiple reports on same day and detects peak day', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $today = Carbon::today('Asia/Jakarta');
    $peakDay = $today->copy()->subDays(3);
    $otherDay = $today->copy()->subDays(5);
    $user = User::factory()->create();

    // Hari Puncak: 2 Laporan total 25.000 lembar baik, 500 reject
    $peakReport1 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $peakDay->toDateString(),
        'shift' => WorkShift::SHIFT_1,
        'product_series' => ProductSeries::PCHT,
        'total_good_quantity' => 15000,
        'total_reject_quantity' => 300,
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $peakReport1->id,
        'po_number' => 'PO-PEAK-1',
        'good_quantity' => 15000,
        'reject_quantity' => 300,
    ]);

    $peakReport2 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $peakDay->toDateString(),
        'shift' => WorkShift::SHIFT_2,
        'product_series' => ProductSeries::MMEA,
        'total_good_quantity' => 10000,
        'total_reject_quantity' => 200,
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $peakReport2->id,
        'po_number' => 'PO-PEAK-2',
        'good_quantity' => 10000,
        'reject_quantity' => 200,
    ]);

    // Hari Lain: 1 Laporan 8.000 lembar
    $otherReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $otherDay->toDateString(),
        'shift' => WorkShift::SHIFT_1,
        'product_series' => ProductSeries::PCHT,
        'total_good_quantity' => 8000,
        'total_reject_quantity' => 100,
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $otherReport->id,
        'po_number' => 'PO-OTHER-1',
        'good_quantity' => 8000,
        'reject_quantity' => 100,
    ]);

    $trend = $service->get14DaysProductionTrend();

    expect($trend['summary']['total_good'])->toBe(33000)
        ->and($trend['summary']['total_reject'])->toBe(600)
        ->and($trend['summary']['average_daily_good'])->toBe((int) round(33000 / 14))
        ->and($trend['summary']['peak_good'])->toBe(25000);

    expect($trend['summary']['peak_date'])->toContain($peakDay->format('d'));
});

test('timeseries supports 7-day and 30-day dynamic ranges', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $trend7 = $service->getProductionTrend(7);
    $trend30 = $service->getProductionTrend(30);

    expect(count($trend7['labels']))->toBe(7)
        ->and($trend7['days'])->toBe(7)
        ->and(count($trend30['labels']))->toBe(30)
        ->and($trend30['days'])->toBe(30);
});
