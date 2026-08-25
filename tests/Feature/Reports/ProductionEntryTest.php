<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Livewire\Reports\ProductionEntry;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('petugas can scan PO and save production item entry in continuous loop', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-2026-08-26-S1',
        'product_series' => ProductSeries::PCHT,
        'shift' => WorkShift::SHIFT_1,
        'status' => ReportStatus::DRAFT,
    ]);

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-1234',
        'product_name' => 'Pita Cukai Rokok SKM',
        'specification' => 'Kertas Berpengaman UV & Watermark',
        'machine_number' => 'MESIN-01',
        'target_quantity' => 25000,
        'unit' => 'lembar',
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'PO-2026-1234')
        ->call('lookupPo')
        ->assertSet('scannedOrder.id', $order->id)
        ->assertSet('productionQty', 25000)
        ->assertDispatched('focus-qty-input')
        ->assertDispatched('play-sound', ['type' => 'success'])
        ->call('saveEntry')
        ->assertDispatched('focus-scan-input')
        ->assertDispatched('play-sound', ['type' => 'success'])
        ->assertSet('scanPo', '')
        ->assertSet('scannedOrder', null);

    $this->assertDatabaseHas('daily_report_items', [
        'daily_report_id' => $report->id,
        'production_order_id' => $order->id,
        'po_number' => 'PO-2026-1234',
        'good_quantity' => 25000,
    ]);

    $report->refresh();
    expect($report->total_items_count)->toBe(1);
    expect($report->total_good_quantity)->toBe(25000);
});

test('scanning non-existent PO number shows error toast and resets scan input', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'NON-EXISTENT-PO')
        ->call('lookupPo')
        ->assertSet('scannedOrder', null)
        ->assertSet('scanPo', '')
        ->assertDispatched('play-sound', ['type' => 'error'])
        ->assertDispatched('focus-scan-input');
});

test('scanning duplicate PO displays warning notice while populating order', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
    ]);

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-8888',
        'target_quantity' => 15000,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-8888',
        'good_quantity' => 15000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'PO-2026-8888')
        ->call('lookupPo')
        ->assertSet('scannedOrder.id', $order->id)
        ->assertDispatched('focus-qty-input');
});

test('saving entry with invalid or zero quantity is rejected', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);
    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-9999',
        'target_quantity' => 20000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'PO-2026-9999')
        ->call('lookupPo')
        ->set('productionQty', 0)
        ->call('saveEntry')
        ->assertDispatched('focus-qty-input');

    expect(DailyReportItem::where('daily_report_id', $report->id)->count())->toBe(0);
});

test('empty scan input triggers no action on lookupPo', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', '   ')
        ->call('lookupPo')
        ->assertSet('scannedOrder', null);
});

test('saving entry without lookup auto-triggers lookup first', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);
    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-AUTO',
        'target_quantity' => 30000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'PO-2026-AUTO')
        ->call('saveEntry')
        ->assertSet('scannedOrder.id', $order->id)
        ->assertDispatched('focus-qty-input');
});

test('operator cannot scan or save new items when report is submitted', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::SUBMITTED,
    ]);

    $order = ProductionOrder::factory()->create(['po_number' => 'PO-2026-LOCKED']);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'PO-2026-LOCKED')
        ->call('lookupPo')
        ->assertSet('scannedOrder', null);
});
