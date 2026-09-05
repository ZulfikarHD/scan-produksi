<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Services\SapImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('production_orders migration defines bounded varchar lengths for key lookup columns', function () {
    $migration = File::get(database_path('migrations/2026_08_26_000002_create_production_orders_table.php'));

    expect($migration)->toContain("string('po_number', 50)");
    expect($migration)->toContain("string('no_obc', 50)");
    expect($migration)->toContain("string('perso_type', 50)");
    expect($migration)->toContain("string('seri', 50)");
    expect($migration)->toContain("string('product_code', 50)");
    expect($migration)->toContain("string('machine_number', 50)");
    expect($migration)->toContain("string('unit', 30)");
    expect($migration)->toContain("string('status', 30)");
    expect($migration)->toContain('Operational Packaging Attributes');
    expect($migration)->toContain('Passthrough SAP Metadata');
});

test('daily_reports migration removes redundant report_code index and bounds string columns', function () {
    $migration = File::get(database_path('migrations/2026_08_26_000004_create_daily_reports_and_items_tables.php'));

    expect($migration)->toContain("string('report_code', 50)->unique()");
    expect($migration)->not->toContain('->unique()->index()');
    expect($migration)->toContain("string('product_series', 30)");
    expect($migration)->toContain("string('shift', 30)");
    expect($migration)->toContain("string('status', 30)");
    expect($migration)->toContain("string('po_number', 50)");
    expect($migration)->toContain("string('machine_number', 50)");
    expect($migration)->toContain("string('unit', 30)");
    expect($migration)->toContain("text('notes')->nullable()");
});

test('daily_reports report_code has unique index without redundant non-unique index', function () {
    expect(Schema::hasTable('daily_reports'))->toBeTrue();

    $indexes = collect(DB::select('PRAGMA index_list("daily_reports")'));

    $reportCodeIndexes = $indexes->filter(function ($index) {
        $info = DB::select('PRAGMA index_info("'.$index->name.'")');

        return count($info) === 1 && ($info[0]->name ?? null) === 'report_code';
    });

    expect($reportCodeIndexes)->toHaveCount(1);
    expect((int) $reportCodeIndexes->first()->unique)->toBe(1);
});

test('daily_report_items notes column stores text longer than 255 characters without truncation', function () {
    $user = User::factory()->petugas()->create();
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
    ]);

    $longNotes = str_repeat('Catatan operator kemas detail. ', 20);
    expect(mb_strlen($longNotes))->toBeGreaterThan(255);

    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'notes' => $longNotes,
    ]);

    expect($item->fresh()->notes)->toBe($longNotes);
    expect(mb_strlen($item->fresh()->notes))->toBe(mb_strlen($longNotes));
});

test('production_orders accepts values at bounded varchar maxima without error', function () {
    $user = User::factory()->supervisor()->create();

    $order = ProductionOrder::factory()->create([
        'created_by' => $user->id,
        'po_number' => str_repeat('P', 50),
        'no_obc' => str_repeat('O', 50),
        'perso_type' => str_repeat('T', 50),
        'seri' => str_repeat('S', 50),
        'product_code' => str_repeat('C', 50),
        'machine_number' => str_repeat('M', 50),
        'unit' => str_repeat('U', 30),
    ]);

    expect($order->fresh()->po_number)->toHaveLength(50);
    expect($order->fresh()->no_obc)->toHaveLength(50);
    expect($order->fresh()->machine_number)->toHaveLength(50);
    expect($order->fresh()->unit)->toHaveLength(30);
});

test('sap import service truncates overlong bounded strings before upsert', function () {
    $service = app(SapImportService::class);
    $user = User::factory()->supervisor()->create();

    $overlongPo = str_repeat('9', 80);
    $overlongObc = str_repeat('OBC', 30);
    $overlongMachine = str_repeat('HDR-', 20);

    $result = $service->mapAndValidateRow([
        $overlongPo,
        $overlongObc,
        'P',
        '1',
        '12/18/2023',
        '1/10/2024',
        '1/6/2024',
        '1/9/2024',
        '1/10/2024',
        '1/11/2024',
        2000,
        2068,
        0,
        2068,
        2068,
        2068,
        2037,
        31,
        37,
        68,
        2000,
        0,
        $overlongMachine,
        '931110001-24',
    ], null, 2);

    expect($result['is_valid'])->toBeTrue();
    expect(mb_strlen($result['data']['po_number']))->toBe(50);
    expect(mb_strlen($result['data']['no_obc']))->toBe(50);
    expect(mb_strlen($result['data']['machine_number']))->toBe(50);

    $unitResult = $service->mapAndValidateRow(
        ['3000189999', '', '', '', '12/18/2023', '', '', '', '', '', 100, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'HDR-1', 'Material Test'],
        null,
        3
    );
    // Inject overlong unit via header map path
    $unitCells = ['3000189998', 'Material Unit', 'HDR-2', str_repeat('lembar-extra-', 5)];
    $unitMapped = $service->mapAndValidateRow($unitCells, [
        'po_number' => 0,
        'product_name' => 1,
        'machine_number' => 2,
        'unit' => 3,
    ], 4);

    expect($unitMapped['is_valid'])->toBeTrue();
    expect(mb_strlen($unitMapped['data']['unit']))->toBe(30);

    $order = ProductionOrder::updateOrCreate(
        ['po_number' => $result['data']['po_number']],
        [
            ...$result['data'],
            'status' => 'ready',
            'created_by' => $user->id,
        ]
    );

    expect($order->fresh()->po_number)->toHaveLength(50);
    expect($order->fresh()->no_obc)->toHaveLength(50);
    expect($order->fresh()->machine_number)->toHaveLength(50);
    expect($unitResult['is_valid'])->toBeTrue();
});

test('sap import service upserts real sap-shaped row into bounded columns cleanly', function () {
    $user = User::factory()->supervisor()->create();
    $service = app(SapImportService::class);

    $parsed = $service->mapAndValidateRow([
        '3000189509', 'ACE211474', 'P', '1', '12/18/2023', '1/10/2024',
        '1/6/2024', '1/9/2024', '1/10/2024', '1/11/2024',
        2000, 2068, 0, 2068, 2068,
        2068, 2037, 31, 37, 68, 2000, 0, 'HDR-1001', '931110001-24',
    ], null, 2);

    expect($parsed['is_valid'])->toBeTrue();

    $order = ProductionOrder::updateOrCreate(
        ['po_number' => $parsed['data']['po_number']],
        [
            ...$parsed['data'],
            'status' => 'ready',
            'created_by' => $user->id,
        ]
    );

    expect($order->po_number)->toBe('3000189509');
    expect($order->no_obc)->toBe('ACE211474');
    expect($order->machine_number)->toBe('HDR-1001');
    expect($order->product_code)->toBe('931110001-24');
    expect(ProductionOrder::where('po_number', '3000189509')->count())->toBe(1);
});

test('daily_reports and items accept bounded session and line values', function () {
    $user = User::factory()->petugas()->create();

    $report = DailyReport::create([
        'report_code' => 'PCHT-2026-09-06-S1',
        'product_series' => ProductSeries::PCHT,
        'report_date' => '2026-09-06',
        'shift' => WorkShift::SHIFT_1,
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
        'notes' => 'Sesi shift 1',
    ]);

    $item = DailyReportItem::create([
        'daily_report_id' => $report->id,
        'po_number' => str_repeat('1', 50),
        'product_name' => 'Pita Cukai Test',
        'machine_number' => str_repeat('M', 50),
        'unit' => str_repeat('u', 30),
        'good_quantity' => 1000,
        'notes' => str_repeat('n', 400),
        'scanned_at' => now(),
    ]);

    expect($report->fresh()->report_code)->toBe('PCHT-2026-09-06-S1');
    expect($item->fresh()->po_number)->toHaveLength(50);
    expect($item->fresh()->machine_number)->toHaveLength(50);
    expect($item->fresh()->unit)->toHaveLength(30);
    expect(mb_strlen($item->fresh()->notes))->toBe(400);
});
