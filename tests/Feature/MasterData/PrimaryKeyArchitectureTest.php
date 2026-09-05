<?php

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\SapImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * US-6.1 Pathway 2: physical primary keys must be tablename_id.
 *
 * @return array<string, string>
 */
function expectedPrimaryKeys(): array
{
    return [
        'users' => 'user_id',
        'production_orders' => 'production_order_id',
        'daily_reports' => 'daily_report_id',
        'daily_report_items' => 'daily_report_item_id',
        'sap_import_logs' => 'sap_import_log_id',
    ];
}

test('physical schema uses tablename_id primary keys on all domain tables', function () {
    foreach (expectedPrimaryKeys() as $table => $pkColumn) {
        expect(Schema::hasTable($table))->toBeTrue("Table {$table} is missing.");
        expect(Schema::hasColumn($table, $pkColumn))
            ->toBeTrue("Column {$pkColumn} is missing from {$table}.");
        expect(Schema::hasColumn($table, 'id'))
            ->toBeFalse("Legacy column id must not exist on {$table}.");

        $columns = collect(DB::select("PRAGMA table_info(\"{$table}\")"));
        $pk = $columns->first(fn ($col) => (int) $col->pk === 1);

        expect($pk)->not->toBeNull("Primary key missing on {$table}.");
        expect($pk->name)->toBe($pkColumn);
    }
});

test('eloquent models declare matching primaryKey properties', function () {
    expect((new User)->getKeyName())->toBe('user_id');
    expect((new ProductionOrder)->getKeyName())->toBe('production_order_id');
    expect((new DailyReport)->getKeyName())->toBe('daily_report_id');
    expect((new DailyReportItem)->getKeyName())->toBe('daily_report_item_id');
    expect((new SapImportLog)->getKeyName())->toBe('sap_import_log_id');
});

test('id accessor aliases return the physical primary key value', function () {
    $user = User::factory()->create();
    $order = ProductionOrder::factory()->create(['created_by' => $user->user_id]);
    $report = DailyReport::factory()->create(['user_id' => $user->user_id]);
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->daily_report_id,
        'production_order_id' => $order->production_order_id,
    ]);
    $log = SapImportLog::factory()->create(['user_id' => $user->user_id]);

    expect($user->id)->toBe($user->user_id)
        ->and($user->id)->toBe($user->getKey());
    expect($order->id)->toBe($order->production_order_id)
        ->and($order->id)->toBe($order->getKey());
    expect($report->id)->toBe($report->daily_report_id)
        ->and($report->id)->toBe($report->getKey());
    expect($item->id)->toBe($item->daily_report_item_id)
        ->and($item->id)->toBe($item->getKey());
    expect($log->id)->toBe($log->sap_import_log_id)
        ->and($log->id)->toBe($log->getKey());
});

test('qualified foreign key relationships resolve correctly', function () {
    $supervisor = User::factory()->supervisor()->create();
    $petugas = User::factory()->petugas()->create();
    $order = ProductionOrder::factory()->create(['created_by' => $supervisor->user_id]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->user_id,
        'supervisor_id' => $supervisor->user_id,
    ]);
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->daily_report_id,
        'production_order_id' => $order->production_order_id,
    ]);
    $log = SapImportLog::factory()->create(['user_id' => $supervisor->user_id]);

    expect($order->creator->user_id)->toBe($supervisor->user_id);
    expect($supervisor->productionOrders)->toHaveCount(1);
    expect($report->user->user_id)->toBe($petugas->user_id);
    expect($report->supervisor->user_id)->toBe($supervisor->user_id);
    expect($petugas->dailyReports)->toHaveCount(1);
    expect($item->dailyReport->daily_report_id)->toBe($report->daily_report_id);
    expect($item->productionOrder->production_order_id)->toBe($order->production_order_id);
    expect($report->items)->toHaveCount(1);
    expect($log->user->user_id)->toBe($supervisor->user_id);
});

test('foreign key constraints reference tablename_id parent columns', function () {
    $foreignKeys = collect(DB::select("PRAGMA foreign_key_list('production_orders')"));
    $createdBy = $foreignKeys->firstWhere('from', 'created_by');
    expect($createdBy)->not->toBeNull();
    expect($createdBy->table)->toBe('users');
    expect($createdBy->to)->toBe('user_id');

    $reportFks = collect(DB::select("PRAGMA foreign_key_list('daily_reports')"));
    expect($reportFks->firstWhere('from', 'user_id')->to)->toBe('user_id');
    expect($reportFks->firstWhere('from', 'supervisor_id')->to)->toBe('user_id');

    $itemFks = collect(DB::select("PRAGMA foreign_key_list('daily_report_items')"));
    expect($itemFks->firstWhere('from', 'daily_report_id')->to)->toBe('daily_report_id');
    expect($itemFks->firstWhere('from', 'production_order_id')->to)->toBe('production_order_id');

    $logFks = collect(DB::select("PRAGMA foreign_key_list('sap_import_logs')"));
    expect($logFks->firstWhere('from', 'user_id')->to)->toBe('user_id');
});

test('find and where on physical primary key work for edge cases', function () {
    $user = User::factory()->create();
    $missingId = $user->user_id + 9999;

    expect(User::find($user->user_id)?->user_id)->toBe($user->user_id);
    expect(User::find($missingId))->toBeNull();
    expect(User::where('user_id', $user->user_id)->exists())->toBeTrue();
    expect(User::where('user_id', $missingId)->exists())->toBeFalse();
});
