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

test('finalizing report updates status from draft to submitted', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
        'report_code' => 'PCHT-2026-08-26-S1',
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 25000,
    ]);

    $report->update([
        'total_items_count' => 1,
        'total_good_quantity' => 25000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('finalizeReport');

    expect($report->fresh()->status)->toBe(ReportStatus::SUBMITTED);
});

test('finalizing empty report with no items is rejected', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('finalizeReport');

    expect($report->fresh()->status)->toBe(ReportStatus::DRAFT);
});

test('supervisor can reopen a submitted report back to draft', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $report = DailyReport::factory()->create([
        'status' => ReportStatus::SUBMITTED,
        'report_code' => 'MMEA-2026-08-26-S2',
    ]);

    Livewire::actingAs($supervisor)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('reopenReport')
        ->assertDispatched('focus-scan-input');

    expect($report->fresh()->status)->toBe(ReportStatus::DRAFT);
});

test('petugas operator cannot reopen a submitted report', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
    ]);

    Livewire::actingAs($petugas)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('reopenReport');

    expect($report->fresh()->status)->toBe(ReportStatus::SUBMITTED);
});
