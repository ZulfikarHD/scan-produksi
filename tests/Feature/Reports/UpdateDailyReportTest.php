<?php

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\EditReport;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('petugas can edit own submitted report within 24 hours', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
        'report_code' => 'PCHT-EDITABLE-01',
        'created_at' => now()->subHours(2),
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-1111',
        'good_quantity' => 10000,
        'reject_quantity' => 0,
    ]);

    $this->actingAs($petugas)
        ->get(route('reports.edit', ['report' => $report->report_code]))
        ->assertOk()
        ->assertSee('Koreksi Laporan Produksi')
        ->assertSee('PO-2026-1111');
});

test('petugas is forbidden from editing another petugas report', function () {
    $petugas1 = User::factory()->create(['role' => UserRole::PETUGAS]);
    $petugas2 = User::factory()->create(['role' => UserRole::PETUGAS]);

    $report = DailyReport::factory()->create([
        'user_id' => $petugas1->id,
        'status' => ReportStatus::SUBMITTED,
    ]);

    $this->actingAs($petugas2)
        ->get(route('reports.edit', ['report' => $report->report_code]))
        ->assertForbidden();
});

test('petugas is forbidden from editing a verified report', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->verified()->create([
        'user_id' => $petugas->id,
        'created_at' => now()->subHours(2),
    ]);

    $this->actingAs($petugas)
        ->get(route('reports.edit', ['report' => $report->report_code]))
        ->assertForbidden();
});

test('petugas is forbidden from editing a report older than 24 hours', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
        'created_at' => now()->subHours(26),
    ]);

    $this->actingAs($petugas)
        ->get(route('reports.edit', ['report' => $report->report_code]))
        ->assertForbidden();
});

test('supervisor can edit any report even if older than 24 hours', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $otherPetugas = User::factory()->create(['role' => UserRole::PETUGAS]);

    $report = DailyReport::factory()->create([
        'user_id' => $otherPetugas->id,
        'status' => ReportStatus::SUBMITTED,
        'created_at' => now()->subDays(3),
    ]);

    $this->actingAs($supervisor)
        ->get(route('reports.edit', ['report' => $report->report_code]))
        ->assertOk()
        ->assertSee('Koreksi Laporan Produksi');
});

test('editing item quantities and notes updates database and recalculates totals', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
        'total_good_quantity' => 10000,
        'total_reject_quantity' => 0,
        'total_items_count' => 1,
    ]);

    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-CORRECT',
        'good_quantity' => 10000,
        'reject_quantity' => 0,
    ]);

    Livewire::actingAs($petugas)
        ->test(EditReport::class, ['report' => $report])
        ->set('items.0.good_quantity', 9800)
        ->set('items.0.reject_quantity', 200)
        ->set('items.0.notes', 'Koreksi cacat cetak 200 lembar')
        ->set('notes', 'Catatan koreksi shift pagi')
        ->call('saveChanges')
        ->assertRedirect(route('reports.show', ['report' => $report->report_code]));

    $item->refresh();
    expect($item->good_quantity)->toBe(9800);
    expect($item->reject_quantity)->toBe(200);
    expect($item->notes)->toBe('Koreksi cacat cetak 200 lembar');

    $report->refresh();
    expect($report->total_good_quantity)->toBe(9800);
    expect($report->total_reject_quantity)->toBe(200);
    expect($report->notes)->toBe('Catatan koreksi shift pagi');
});

test('adding supplemental item and removing existing item in edit form', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $report = DailyReport::factory()->create();

    $itemOld = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-REMOVE-ME',
        'good_quantity' => 5000,
    ]);

    $newOrder = ProductionOrder::factory()->create([
        'po_number' => 'PO-SUPPLEMENTAL-99',
        'product_name' => 'Produk Susulan MMEA',
        'target_quantity' => 12000,
    ]);

    Livewire::actingAs($supervisor)
        ->test(EditReport::class, ['report' => $report])
        ->call('removeItem', 0)
        ->set('newPo', 'PO-SUPPLEMENTAL-99')
        ->call('lookupNewPo')
        ->set('newGoodQty', 12000)
        ->set('newRejectQty', 50)
        ->call('addSupplementalItem')
        ->call('saveChanges')
        ->assertRedirect(route('reports.show', ['report' => $report->report_code]));

    expect(DailyReportItem::where('daily_report_item_id', $itemOld->id)->exists())->toBeFalse();

    $this->assertDatabaseHas('daily_report_items', [
        'daily_report_id' => $report->id,
        'po_number' => 'PO-SUPPLEMENTAL-99',
        'good_quantity' => 12000,
        'reject_quantity' => 50,
    ]);

    $report->refresh();
    expect($report->total_good_quantity)->toBe(12000);
    expect($report->total_reject_quantity)->toBe(50);
    expect($report->total_items_count)->toBe(1);
});
