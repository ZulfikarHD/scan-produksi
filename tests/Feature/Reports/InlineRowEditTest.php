<?php

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\ProductionEntry;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('petugas can inline edit production quantity directly in table row', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-0001',
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

test('inline edit with zero or negative quantity is rejected without updating database', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 15000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('updateItemQuantity', $item->id, 0);

    expect($item->fresh()->good_quantity)->toBe(15000);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('updateItemQuantity', $item->id, -500);

    expect($item->fresh()->good_quantity)->toBe(15000);
});

test('petugas can delete an item row from daily report with total recalculation', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create(['user_id' => $user->id, 'status' => ReportStatus::DRAFT]);
    $item1 = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 10000,
    ]);
    $item2 = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 15000,
    ]);

    $report->update([
        'total_items_count' => 2,
        'total_good_quantity' => 25000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('deleteItem', $item1->id);

    $this->assertDatabaseMissing('daily_report_items', ['id' => $item1->id]);
    $this->assertDatabaseHas('daily_report_items', ['id' => $item2->id]);

    expect($report->fresh()->total_items_count)->toBe(1);
    expect($report->fresh()->total_good_quantity)->toBe(15000);
});

test('operator cannot inline edit or delete items on a submitted report', function () {
    $operator = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $operator->id,
        'status' => ReportStatus::SUBMITTED,
    ]);

    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 10000,
    ]);

    Livewire::actingAs($operator)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('updateItemQuantity', $item->id, 20000)
        ->call('deleteItem', $item->id);

    expect($item->fresh()->good_quantity)->toBe(10000);
    $this->assertDatabaseHas('daily_report_items', ['id' => $item->id]);
});
