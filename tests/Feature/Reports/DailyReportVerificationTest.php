<?php

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\ReportDetail;
use App\Livewire\Reports\ReportIndex;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('supervisor can verify submitted report and lock it', function () {
    $supervisor = User::factory()->create([
        'name' => 'Supervisor Handoko',
        'nip' => '198001012005011001',
        'role' => UserRole::SUPERVISOR,
    ]);

    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);

    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
        'report_code' => 'PCHT-2026-08-26-VERIFY',
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 20000,
    ]);

    Livewire::actingAs($supervisor)
        ->test(ReportDetail::class, ['report' => $report])
        ->call('verifyReport');

    $report->refresh();
    expect($report->status)->toBe(ReportStatus::VERIFIED);
    expect($report->supervisor_id)->toBe($supervisor->id);
    expect($report->verified_at)->not->toBeNull();
});

test('petugas is unauthorized to verify report', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
    ]);

    Livewire::actingAs($petugas)
        ->test(ReportDetail::class, ['report' => $report])
        ->call('verifyReport')
        ->assertForbidden();

    expect($report->fresh()->status)->toBe(ReportStatus::SUBMITTED);
});

test('supervisor can delete report from detail view with cascading items', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $order = ProductionOrder::factory()->create([
        'kemas_quantity' => 20000,
    ]);

    $report = DailyReport::factory()->create();
    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'production_order_id' => $order->id,
        'good_quantity' => 20000,
    ]);

    Livewire::actingAs($supervisor)
        ->test(ReportDetail::class, ['report' => $report])
        ->call('deleteReport')
        ->assertRedirect(route('reports.index'));

    expect(DailyReport::where('id', $report->id)->exists())->toBeFalse();
    expect(DailyReportItem::where('id', $item->id)->exists())->toBeFalse();

    $order->refresh();
    expect($order->kemas_quantity)->toBe(0);
});

test('supervisor can delete report from index view', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $report = DailyReport::factory()->create(['report_code' => 'DELETE-FROM-INDEX']);
    $item = DailyReportItem::factory()->create(['daily_report_id' => $report->id]);

    Livewire::actingAs($supervisor)
        ->test(ReportIndex::class)
        ->call('confirmDeleteReport', $report->id)
        ->assertSet('deletingReportId', $report->id)
        ->call('deleteReport')
        ->assertDispatched('close-modal', 'delete-report-confirm');

    expect(DailyReport::where('id', $report->id)->exists())->toBeFalse();
    expect(DailyReportItem::where('id', $item->id)->exists())->toBeFalse();
});

test('petugas is unauthorized to delete report', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create(['user_id' => $petugas->id]);

    Livewire::actingAs($petugas)
        ->test(ReportDetail::class, ['report' => $report])
        ->call('deleteReport')
        ->assertForbidden();

    expect(DailyReport::where('id', $report->id)->exists())->toBeTrue();
});
