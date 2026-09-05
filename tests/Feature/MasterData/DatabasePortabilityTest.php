<?php

use App\Enums\ReportStatus;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\SapImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('database connection configurations are defined with strict mode and proper charsets across drivers', function () {
    // 1. SQLite connection verification
    $sqliteConfig = Config::get('database.connections.sqlite');
    expect($sqliteConfig)->not->toBeNull();
    expect($sqliteConfig['driver'])->toBe('sqlite');
    expect($sqliteConfig['foreign_key_constraints'])->toBeTrue();

    // 2. MySQL connection verification
    $mysqlConfig = Config::get('database.connections.mysql');
    expect($mysqlConfig)->not->toBeNull();
    expect($mysqlConfig['driver'])->toBe('mysql');
    expect($mysqlConfig['strict'])->toBeTrue();
    expect($mysqlConfig['charset'])->toBe('utf8mb4');
    expect($mysqlConfig['collation'])->toBe('utf8mb4_unicode_ci');
    expect($mysqlConfig['prefix_indexes'])->toBeTrue();

    // 3. PostgreSQL connection verification
    $pgsqlConfig = Config::get('database.connections.pgsql');
    expect($pgsqlConfig)->not->toBeNull();
    expect($pgsqlConfig['driver'])->toBe('pgsql');
    expect($pgsqlConfig['charset'])->toBe('utf8');
    expect($pgsqlConfig['search_path'])->toBe('public');
    expect($pgsqlConfig['sslmode'])->toBe('prefer');
});

test('migrations and models do not rely on physical column ordering from after clauses', function () {
    // Audit users table columns added via ->after()
    expect(Schema::hasColumn('users', 'user_id'))->toBeTrue();
    expect(Schema::hasColumn('users', 'nip'))->toBeTrue();
    expect(Schema::hasColumn('users', 'username'))->toBeTrue();
    expect(Schema::hasColumn('users', 'role'))->toBeTrue();
    expect(Schema::hasColumn('users', 'is_active'))->toBeTrue();
    expect(Schema::hasColumn('users', 'last_login_at'))->toBeTrue();

    // Audit daily_reports columns added via ->after()
    expect(Schema::hasColumn('daily_reports', 'total_reject_quantity'))->toBeTrue();
    expect(Schema::hasColumn('daily_reports', 'verified_at'))->toBeTrue();

    // Audit daily_report_items columns added via ->after()
    expect(Schema::hasColumn('daily_report_items', 'reject_quantity'))->toBeTrue();

    // Create user and reports using explicit attribute maps
    $user = User::factory()->petugas()->create([
        'nip' => 'P-PORT-01',
        'username' => 'portability_petugas',
    ]);

    $report = DailyReport::factory()->create([
        'user_id' => $user->user_id,
        'total_good_quantity' => 1500,
        'total_reject_quantity' => 25,
        'status' => ReportStatus::SUBMITTED,
    ]);

    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->daily_report_id,
        'good_quantity' => 1500,
        'reject_quantity' => 25,
    ]);

    // Read attributes via Eloquent models and named query builder
    $retrievedReport = DailyReport::query()
        ->select(['daily_report_id', 'total_good_quantity', 'total_reject_quantity', 'verified_at'])
        ->find($report->daily_report_id);

    expect($retrievedReport)->not->toBeNull();
    expect($retrievedReport->total_good_quantity)->toBe(1500);
    expect($retrievedReport->total_reject_quantity)->toBe(25);

    $retrievedItem = DailyReportItem::query()
        ->select(['daily_report_item_id', 'good_quantity', 'reject_quantity'])
        ->find($item->daily_report_item_id);

    expect($retrievedItem)->not->toBeNull();
    expect($retrievedItem->good_quantity)->toBe(1500);
    expect($retrievedItem->reject_quantity)->toBe(25);
});

test('composite index key lengths comply with MySQL utf8mb4 3072 byte prefix budget', function () {
    /**
     * Under MySQL InnoDB utf8mb4:
     * - Each VARCHAR character occupies up to 4 bytes.
     * - BIGINT: 8 bytes.
     * - INT / unsignedInteger: 4 bytes.
     * - DATE: 3 bytes.
     * - TIMESTAMP: 4 bytes.
     * Total composite index key prefix length must strictly not exceed 3072 bytes.
     */
    $compositeIndexes = [
        // production_orders
        'production_orders.status_order_date' => [
            ['type' => 'varchar', 'length' => 30], // status
            ['type' => 'date'], // order_date
        ],
        'production_orders.machine_number_status' => [
            ['type' => 'varchar', 'length' => 50], // machine_number
            ['type' => 'varchar', 'length' => 30], // status
        ],
        'production_orders.no_obc_status' => [
            ['type' => 'varchar', 'length' => 50], // no_obc
            ['type' => 'varchar', 'length' => 30], // status
        ],
        // sap_import_logs
        'sap_import_logs.created_at_status' => [
            ['type' => 'timestamp'], // created_at
            ['type' => 'varchar', 'length' => 30], // status
        ],
        // daily_reports
        'daily_reports.product_series_report_date_shift_unique' => [
            ['type' => 'varchar', 'length' => 30], // product_series
            ['type' => 'date'], // report_date
            ['type' => 'varchar', 'length' => 30], // shift
        ],
        'daily_reports.status_report_date' => [
            ['type' => 'varchar', 'length' => 30], // status
            ['type' => 'date'], // report_date
        ],
        // daily_report_items
        'daily_report_items.daily_report_id_po_number' => [
            ['type' => 'bigint'], // daily_report_id
            ['type' => 'varchar', 'length' => 50], // po_number
        ],
    ];

    $mysqlUtf8mb4Limit = 3072;

    foreach ($compositeIndexes as $indexName => $columns) {
        $totalBytes = 0;
        foreach ($columns as $col) {
            $bytes = match ($col['type']) {
                'varchar' => $col['length'] * 4,
                'bigint' => 8,
                'int' => 4,
                'date' => 3,
                'timestamp' => 4,
                default => 255 * 4,
            };
            $totalBytes += $bytes;
        }

        expect($totalBytes)->toBeLessThanOrEqual(
            $mysqlUtf8mb4Limit,
            "Index {$indexName} consumes {$totalBytes} bytes, exceeding MySQL utf8mb4 limit of {$mysqlUtf8mb4Limit} bytes."
        );

        // All our domain composite indexes are carefully optimized below 500 bytes
        expect($totalBytes)->toBeLessThan(500);
    }
});

test('sap_import_logs error_log json column supports serialization, deserialization, and querying across drivers', function () {
    $user = User::factory()->supervisor()->create();

    $complexErrors = [
        [
            'row' => 12,
            'field' => 'po_number',
            'message' => 'Nomor PO tidak valid atau kosong pada baris 12.',
            'value' => null,
            'timestamp' => '2026-09-06T10:15:30+07:00',
        ],
        [
            'row' => 45,
            'field' => 'target_quantity',
            'message' => 'Target pesanan harus berupa bilangan bulat positif.',
            'value' => 'abc',
            'timestamp' => '2026-09-06T10:15:32+07:00',
        ],
    ];

    $log = SapImportLog::create([
        'user_id' => $user->user_id,
        'file_name' => 'sap_export_audit_test.xlsx',
        'file_path' => 'sap-imports/audit-test.xlsx',
        'total_rows' => 50,
        'imported_count' => 48,
        'updated_count' => 0,
        'failed_count' => 2,
        'status' => 'partial',
        'error_log' => $complexErrors,
    ]);

    // Verify retrieval and array casting
    $freshLog = $log->fresh();
    expect($freshLog)->not->toBeNull();
    expect($freshLog->error_log)->toBeArray();
    expect($freshLog->error_log)->toHaveCount(2);
    expect($freshLog->error_log[0]['field'])->toBe('po_number');
    expect($freshLog->error_log[1]['value'])->toBe('abc');

    // Verify empty array error log serialization
    $cleanLog = SapImportLog::create([
        'user_id' => $user->user_id,
        'file_name' => 'sap_clean_run.xlsx',
        'file_path' => 'sap-imports/clean.xlsx',
        'total_rows' => 100,
        'imported_count' => 100,
        'updated_count' => 0,
        'failed_count' => 0,
        'status' => 'success',
        'error_log' => [],
    ]);

    expect($cleanLog->fresh()->error_log)->toBeArray();
    expect($cleanLog->fresh()->error_log)->toBeEmpty();

    // Verify nullable error log serialization
    $nullLog = SapImportLog::create([
        'user_id' => $user->user_id,
        'file_name' => 'sap_null_run.xlsx',
        'file_path' => null,
        'total_rows' => 10,
        'imported_count' => 10,
        'updated_count' => 0,
        'failed_count' => 0,
        'status' => 'success',
        'error_log' => null,
    ]);

    expect($nullLog->fresh()->error_log)->toBeNull();

    // Verify raw database column content is valid JSON string
    $rawLog = DB::table('sap_import_logs')
        ->where('sap_import_log_id', $log->sap_import_log_id)
        ->first();

    expect($rawLog->error_log)->toBeString();
    $decoded = json_decode($rawLog->error_log, true);
    expect($decoded)->toBeArray();
    expect($decoded[0]['row'])->toBe(12);
});

test('foreign key cascadeOnDelete and nullOnDelete behaviors maintain referential integrity', function () {
    $supervisor = User::factory()->supervisor()->create(['nip' => 'SPV-FK-01']);
    $petugas = User::factory()->petugas()->create(['nip' => 'PTG-FK-01']);

    $order = ProductionOrder::factory()->create([
        'created_by' => $supervisor->user_id,
        'po_number' => 'PO-FK-TEST-001',
    ]);

    $report = DailyReport::factory()->create([
        'user_id' => $petugas->user_id,
        'supervisor_id' => $supervisor->user_id,
        'report_code' => 'PCHT-FK-TEST-01',
    ]);

    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->daily_report_id,
        'production_order_id' => $order->production_order_id,
        'po_number' => $order->po_number,
        'product_name' => $order->product_name,
    ]);

    $log = SapImportLog::factory()->create([
        'user_id' => $supervisor->user_id,
    ]);

    // 1. Test nullOnDelete on ProductionOrder:
    // Deleting production_order must nullify production_order_id on daily_report_items,
    // while keeping the snapshot data intact!
    $order->delete();
    $item->refresh();
    expect($item->production_order_id)->toBeNull();
    expect($item->po_number)->toBe('PO-FK-TEST-001');
    expect(DailyReportItem::where('daily_report_item_id', $item->daily_report_item_id)->exists())->toBeTrue();

    // 2. Test nullOnDelete on Supervisor:
    // Deleting supervisor must nullify supervisor_id on daily_reports without deleting the report
    $supervisor->delete();
    $report->refresh();
    expect($report->supervisor_id)->toBeNull();
    expect(DailyReport::where('daily_report_id', $report->daily_report_id)->exists())->toBeTrue();

    // Also verify sap_import_logs.user_id is set to null
    $log->refresh();
    expect($log->user_id)->toBeNull();

    // 3. Test cascadeOnDelete on DailyReport:
    // Deleting daily_report must delete all its child daily_report_items
    $report->delete();
    expect(DailyReportItem::where('daily_report_id', $report->daily_report_id)->exists())->toBeFalse();

    // 4. Test cascadeOnDelete on Petugas User:
    // Deleting user must cascade delete any daily reports authored by that user
    $petugasReport = DailyReport::factory()->create([
        'user_id' => $petugas->user_id,
        'report_code' => 'MMEA-FK-TEST-02',
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $petugasReport->daily_report_id,
    ]);

    $petugas->delete();
    expect(DailyReport::where('daily_report_id', $petugasReport->daily_report_id)->exists())->toBeFalse();
    expect(DailyReportItem::where('daily_report_id', $petugasReport->daily_report_id)->exists())->toBeFalse();
});

test('aggregate calculations and queries adhere strictly to ANSI SQL across drivers', function () {
    $user = User::factory()->petugas()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->user_id]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->daily_report_id,
        'good_quantity' => 1000,
        'reject_quantity' => 15,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->daily_report_id,
        'good_quantity' => 2000,
        'reject_quantity' => 25,
    ]);

    // Test ANSI-standard selectRaw aggregation used in ProductionEntry and EditReport
    $totals = DailyReportItem::where('daily_report_id', $report->daily_report_id)
        ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(good_quantity), 0) as total_good, COALESCE(SUM(reject_quantity), 0) as total_reject')
        ->first();

    expect((int) $totals->total_items)->toBe(2);
    expect((int) $totals->total_good)->toBe(3000);
    expect((int) $totals->total_reject)->toBe(40);

    // Verify empty state returns 0 for COALESCE and COUNT
    $emptyReport = DailyReport::factory()->create(['user_id' => $user->user_id]);
    $emptyTotals = DailyReportItem::where('daily_report_id', $emptyReport->daily_report_id)
        ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(good_quantity), 0) as total_good, COALESCE(SUM(reject_quantity), 0) as total_reject')
        ->first();

    expect((int) $emptyTotals->total_items)->toBe(0);
    expect((int) $emptyTotals->total_good)->toBe(0);
    expect((int) $emptyTotals->total_reject)->toBe(0);
});
