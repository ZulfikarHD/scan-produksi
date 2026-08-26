<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Helpers\ShiftHelper;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Carbon\Carbon;

test('today metrics return zero-state gracefully when no reports exist', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $metrics = $service->getTodayMetrics();

    expect($metrics['total_good'])->toBe(0)
        ->and($metrics['total_reject'])->toBe(0)
        ->and($metrics['reject_rate'])->toBe(0.0)
        ->and($metrics['yesterday_good'])->toBe(0)
        ->and($metrics['comparison_percentage'])->toBe(0.0)
        ->and($metrics['trend_direction'])->toBe('neutral')
        ->and($metrics['po_count'])->toBe(0)
        ->and($metrics['total_reports'])->toBe(0)
        ->and($metrics['submitted_reports'])->toBe(0)
        ->and($metrics['verified_reports'])->toBe(0);
});

test('today metrics accurately aggregate good output, reject rate and comparison to yesterday', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $today = Carbon::today('Asia/Jakarta');
    $yesterday = $today->copy()->subDay();

    $user = User::factory()->create(['role' => UserRole::PETUGAS]);

    // Laporan Kemarin: 10.000 lembar baik
    $yesterdayReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $yesterday->toDateString(),
        'shift' => WorkShift::SHIFT_1,
        'total_good_quantity' => 10000,
        'total_reject_quantity' => 100,
        'status' => ReportStatus::VERIFIED,
    ]);

    // Laporan Hari Ini: 15.000 lembar baik, 300 reject (+50% vs kemarin)
    $todayReport1 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
        'shift' => WorkShift::SHIFT_1,
        'total_good_quantity' => 10000,
        'total_reject_quantity' => 200,
        'status' => ReportStatus::VERIFIED,
    ]);

    $todayReport2 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
        'shift' => WorkShift::SHIFT_2,
        'total_good_quantity' => 5000,
        'total_reject_quantity' => 100,
        'status' => ReportStatus::SUBMITTED,
    ]);

    // Items untuk hitung PO unik
    DailyReportItem::factory()->create([
        'daily_report_id' => $todayReport1->id,
        'po_number' => 'PO-2026-0001',
        'good_quantity' => 10000,
        'reject_quantity' => 200,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $todayReport2->id,
        'po_number' => 'PO-2026-0002',
        'good_quantity' => 5000,
        'reject_quantity' => 100,
    ]);

    // Item dengan PO yang sama tidak boleh dihitung ganda
    DailyReportItem::factory()->create([
        'daily_report_id' => $todayReport2->id,
        'po_number' => 'PO-2026-0001',
        'good_quantity' => 0,
        'reject_quantity' => 0,
    ]);

    $metrics = $service->getTodayMetrics();

    expect($metrics['total_good'])->toBe(15000)
        ->and($metrics['total_reject'])->toBe(300)
        ->and($metrics['yesterday_good'])->toBe(10000)
        ->and($metrics['comparison_percentage'])->toBe(50.0)
        ->and($metrics['trend_direction'])->toBe('up')
        ->and($metrics['po_count'])->toBe(2)
        ->and($metrics['total_reports'])->toBe(2)
        ->and($metrics['verified_reports'])->toBe(1)
        ->and($metrics['submitted_reports'])->toBe(1);

    // Reject rate: 300 / (15000 + 300) = 300 / 15300 = 1.96%
    expect($metrics['reject_rate'])->toBe(1.96);
});

test('comparison percentage handles negative trend correctly', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $today = Carbon::today('Asia/Jakarta');
    $yesterday = $today->copy()->subDay();
    $user = User::factory()->create();

    DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $yesterday->toDateString(),
        'total_good_quantity' => 20000,
    ]);

    DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_date' => $today->toDateString(),
        'total_good_quantity' => 10000,
    ]);

    $metrics = $service->getTodayMetrics();

    expect($metrics['total_good'])->toBe(10000)
        ->and($metrics['yesterday_good'])->toBe(20000)
        ->and($metrics['comparison_percentage'])->toBe(-50.0)
        ->and($metrics['trend_direction'])->toBe('down');
});

test('today metrics correctly count active operators on current shift', function () {
    $service = new DashboardAnalyticsService;
    $service->clearCache();

    $today = Carbon::today('Asia/Jakarta');
    $currentShiftNumber = ShiftHelper::getCurrentShift()['number'];
    $currentShiftEnum = WorkShift::from('shift_'.$currentShiftNumber);

    $operator1 = User::factory()->create(['role' => UserRole::PETUGAS, 'is_active' => true]);
    $operator2 = User::factory()->create(['role' => UserRole::PETUGAS, 'is_active' => true]);
    $inactiveOperator = User::factory()->create(['role' => UserRole::PETUGAS, 'is_active' => false]);

    DailyReport::factory()->create([
        'user_id' => $operator1->id,
        'report_date' => $today->toDateString(),
        'shift' => $currentShiftEnum,
        'product_series' => ProductSeries::PCHT,
    ]);

    DailyReport::factory()->create([
        'user_id' => $operator2->id,
        'report_date' => $today->toDateString(),
        'shift' => $currentShiftEnum,
        'product_series' => ProductSeries::MMEA,
    ]);

    $metrics = $service->getTodayMetrics();

    expect($metrics['active_operators'])->toBe(2)
        ->and($metrics['total_active_staff'])->toBe(2);
});
