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

test('petugas can scan PO and save production item entry', function () {
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
    $report = DailyReport::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'NON-EXISTENT-PO')
        ->call('lookupPo')
        ->assertSet('scannedOrder', null)
        ->assertSet('scanPo', '')
        ->assertDispatched('play-sound', ['type' => 'error'])
        ->assertDispatched('focus-scan-input');
});

test('petugas can inline edit production quantity directly in table row', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id]);
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 20000,
        'target_quantity' => 20000,
    ]);

    $report->update([
        'total_items_count' => 1,
        'total_good_quantity' => 20000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('updateItemQuantity', $item->id, 22500);

    expect($item->fresh()->good_quantity)->toBe(22500);
    expect($report->fresh()->total_good_quantity)->toBe(22500);
});

test('petugas can delete an item row from daily report', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id]);
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 10000,
    ]);

    $report->update([
        'total_items_count' => 1,
        'total_good_quantity' => 10000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('deleteItem', $item->id);

    $this->assertDatabaseMissing('daily_report_items', ['id' => $item->id]);
    expect($report->fresh()->total_items_count)->toBe(0);
    expect($report->fresh()->total_good_quantity)->toBe(0);
});

test('finalizing report updates status to submitted', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('finalizeReport');

    expect($report->fresh()->status)->toBe(ReportStatus::SUBMITTED);
});
