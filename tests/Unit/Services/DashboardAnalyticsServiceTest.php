<?php

use App\Enums\ProductSeries;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Carbon\Carbon;

test('shift distribution aggregates all 3 shifts and calculates correct percentages', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $today = Carbon::today('Asia/Jakarta');
    $user = User::factory()->create();

    // Shift 1: 50.000 lembar (50%)
    $reportS1 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
        'shift' => WorkShift::SHIFT_1,
        'total_good_quantity' => 50000,
        'product_series' => ProductSeries::PCHT,
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $reportS1->id,
        'good_quantity' => 50000,
        'reject_quantity' => 200,
    ]);

    // Shift 2: 30.000 lembar (30%)
    $reportS2 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
        'shift' => WorkShift::SHIFT_2,
        'total_good_quantity' => 30000,
        'product_series' => ProductSeries::MMEA,
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $reportS2->id,
        'good_quantity' => 30000,
        'reject_quantity' => 100,
    ]);

    // Shift 3: 20.000 lembar (20%)
    $reportS3 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
        'shift' => WorkShift::SHIFT_3,
        'total_good_quantity' => 20000,
        'product_series' => ProductSeries::HPTL,
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $reportS3->id,
        'good_quantity' => 20000,
        'reject_quantity' => 50,
    ]);

    $distribution = $service->getShiftDistribution(14);

    expect($distribution['total_good'])->toBe(100000)
        ->and(count($distribution['shifts']))->toBe(3);

    $s1 = collect($distribution['shifts'])->firstWhere('shift', 'shift_1');
    $s2 = collect($distribution['shifts'])->firstWhere('shift', 'shift_2');
    $s3 = collect($distribution['shifts'])->firstWhere('shift', 'shift_3');

    expect($s1['good_quantity'])->toBe(50000)
        ->and($s1['percentage'])->toBe(50.0)
        ->and($s2['good_quantity'])->toBe(30000)
        ->and($s2['percentage'])->toBe(30.0)
        ->and($s3['good_quantity'])->toBe(20000)
        ->and($s3['percentage'])->toBe(20.0);
});

test('top machines returns ranked list limited to 5 machines descending by good quantity', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $today = Carbon::today('Asia/Jakarta');
    $user = User::factory()->create();

    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'machine_number' => 'MESIN-01',
        'good_quantity' => 40000,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'machine_number' => 'MESIN-02',
        'good_quantity' => 25000,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'machine_number' => 'MESIN-03',
        'good_quantity' => 15000,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'machine_number' => 'MESIN-04',
        'good_quantity' => 10000,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'machine_number' => 'MESIN-05',
        'good_quantity' => 8000,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'machine_number' => 'MESIN-06',
        'good_quantity' => 2000,
    ]);

    $topMachines = $service->getTopMachines(14, 5);

    expect(count($topMachines))->toBe(5)
        ->and($topMachines[0]['machine_number'])->toBe('MESIN-01')
        ->and($topMachines[0]['rank'])->toBe(1)
        ->and($topMachines[0]['good_quantity'])->toBe(40000)
        ->and($topMachines[1]['machine_number'])->toBe('MESIN-02')
        ->and($topMachines[4]['machine_number'])->toBe('MESIN-05');
});

test('indonesian date format helpers produce correct localized strings', function () {
    $date = Carbon::create(2026, 8, 26, 14, 30, 0, 'Asia/Jakarta');

    $short = DashboardAnalyticsService::formatShortIndonesianDate($date);
    $full = DashboardAnalyticsService::formatFullIndonesianDate($date);

    expect($short)->toBe('26 Agu')
        ->and($full)->toBe('Rabu, 26 Agustus 2026');
});
