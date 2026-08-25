<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Services\ReportCodeGenerator;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('report code generator generates standard and shift-specific codes correctly', function () {
    $date = '2026-08-26';

    // Standard base code format [SERIES]-[YYYY-MM-DD]
    $codePcht = ReportCodeGenerator::generate(ProductSeries::PCHT, $date);
    expect($codePcht)->toBe('PCHT-2026-08-26');

    $codeMmea = ReportCodeGenerator::generate(ProductSeries::MMEA, $date);
    expect($codeMmea)->toBe('MMEA-2026-08-26');

    $codeHptl = ReportCodeGenerator::generate(ProductSeries::HPTL, $date);
    expect($codeHptl)->toBe('HPTL-2026-08-26');

    // Shift-specific code format [SERIES]-[YYYY-MM-DD]-S[SHIFT]
    $codeShift1 = ReportCodeGenerator::generateWithShift(ProductSeries::PCHT, $date, WorkShift::SHIFT_1);
    expect($codeShift1)->toBe('PCHT-2026-08-26-S1');

    $codeShift2 = ReportCodeGenerator::generateWithShift(ProductSeries::MMEA, $date, WorkShift::SHIFT_2);
    expect($codeShift2)->toBe('MMEA-2026-08-26-S2');

    $codeShift3 = ReportCodeGenerator::generateWithShift(ProductSeries::HPTL, $date, WorkShift::SHIFT_3);
    expect($codeShift3)->toBe('HPTL-2026-08-26-S3');
});

test('product series enum provides proper labels, descriptions and badge colors', function () {
    expect(ProductSeries::PCHT->label())->toContain('Pita Cukai Hasil Tembakau');
    expect(ProductSeries::PCHT->shortLabel())->toBe('PCHT');
    expect(ProductSeries::PCHT->badgeColor())->toBe('emerald');

    expect(ProductSeries::MMEA->label())->toContain('Minuman Mengandung Etil Alkohol');
    expect(ProductSeries::MMEA->shortLabel())->toBe('MMEA');
    expect(ProductSeries::MMEA->badgeColor())->toBe('purple');

    expect(ProductSeries::HPTL->label())->toContain('Hasil Pengolahan Tembakau Lainnya');
    expect(ProductSeries::HPTL->shortLabel())->toBe('HPTL / REL');
    expect(ProductSeries::HPTL->badgeColor())->toBe('amber');
});

test('work shift enum provides proper labels and time ranges', function () {
    expect(WorkShift::SHIFT_1->number())->toBe(1);
    expect(WorkShift::SHIFT_1->label())->toContain('Shift 1');
    expect(WorkShift::SHIFT_1->timeRange())->toContain('07:00');

    expect(WorkShift::SHIFT_2->number())->toBe(2);
    expect(WorkShift::SHIFT_2->label())->toContain('Shift 2');

    expect(WorkShift::SHIFT_3->number())->toBe(3);
    expect(WorkShift::SHIFT_3->label())->toContain('Shift 3');
});

test('daily report can be created with relationships and casts', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    $report = DailyReport::create([
        'report_code' => 'PCHT-2026-08-26',
        'product_series' => ProductSeries::PCHT,
        'report_date' => '2026-08-26',
        'shift' => WorkShift::SHIFT_1,
        'user_id' => $petugas->id,
        'supervisor_id' => $supervisor->id,
        'total_items_count' => 1,
        'total_good_quantity' => 15000,
        'notes' => 'Catatan operasional shift pagi',
        'status' => ReportStatus::DRAFT,
    ]);

    expect($report->product_series)->toBe(ProductSeries::PCHT);
    expect($report->shift)->toBe(WorkShift::SHIFT_1);
    expect($report->status)->toBe(ReportStatus::DRAFT);
    expect($report->report_date)->toBeInstanceOf(CarbonInterface::class);
    expect($report->user->id)->toBe($petugas->id);
    expect($report->supervisor->id)->toBe($supervisor->id);
});

test('daily report items relation and production order link work properly', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-9901',
        'product_name' => 'Pita Cukai Rokok SKT',
        'specification' => 'Kertas Berpengaman UV',
        'machine_number' => 'MESIN-02',
        'target_quantity' => 20000,
        'unit' => 'lembar',
    ]);

    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
    ]);

    $item = DailyReportItem::create([
        'daily_report_id' => $report->id,
        'production_order_id' => $order->id,
        'po_number' => $order->po_number,
        'product_name' => $order->product_name,
        'specification' => $order->specification,
        'machine_number' => $order->machine_number,
        'target_quantity' => $order->target_quantity,
        'good_quantity' => 20000,
        'unit' => $order->unit,
        'notes' => 'Selesai kemas shift 1',
        'scanned_at' => now(),
    ]);

    expect($report->items)->toHaveCount(1);
    expect($report->items->first()->id)->toBe($item->id);
    expect($item->dailyReport->id)->toBe($report->id);
    expect($item->productionOrder->id)->toBe($order->id);
});

test('user dailyReports relationship is accessible', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id]);

    expect($user->dailyReports)->toHaveCount(1);
    expect($user->dailyReports->first()->id)->toBe($report->id);
    expect($user->productionReports->first()->id)->toBe($report->id);
});
