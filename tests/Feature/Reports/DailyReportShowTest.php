<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Livewire\Reports\ReportDetail;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected to login when accessing report detail', function () {
    $report = DailyReport::factory()->create();

    $this->get(route('reports.show', ['report' => $report->report_code]))
        ->assertRedirect(route('login'));
});

test('user can view report detail with summary headers and scanned items table', function () {
    $petugas = User::factory()->create([
        'name' => 'Ahmad Operator',
        'nip' => '199203152020121002',
        'role' => UserRole::PETUGAS,
    ]);

    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'report_code' => 'PCHT-2026-08-26-S1',
        'product_series' => ProductSeries::PCHT,
        'shift' => WorkShift::SHIFT_1,
        'report_date' => '2026-08-26',
        'status' => ReportStatus::SUBMITTED,
        'notes' => 'Catatan shift berjalan aman lancar',
    ]);

    $item1 = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-ITEM-01',
        'product_name' => 'Pita Cukai Rokok SKM 12 Batang',
        'specification' => 'Kertas Berpengaman UV & Microtext',
        'machine_number' => 'M-01',
        'target_quantity' => 20000,
        'good_quantity' => 20000,
        'reject_quantity' => 50,
        'unit' => 'lembar',
    ]);

    $item2 = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-ITEM-02',
        'product_name' => 'Pita Cukai Rokok SKT 10 Batang',
        'specification' => 'Kertas Berpengaman Watermark',
        'machine_number' => 'M-02',
        'target_quantity' => 15000,
        'good_quantity' => 14950,
        'reject_quantity' => 50,
        'unit' => 'lembar',
    ]);

    $this->actingAs($petugas)
        ->get(route('reports.show', ['report' => $report->report_code]))
        ->assertOk()
        ->assertSee('PCHT-2026-08-26-S1')
        ->assertSee('Ahmad Operator')
        ->assertSee('PO-2026-ITEM-01')
        ->assertSee('Pita Cukai Rokok SKM 12 Batang')
        ->assertSee('PO-2026-ITEM-02')
        ->assertSee('Catatan shift berjalan aman lancar');
});

test('verified report displays verification audit banner with supervisor details', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $supervisor = User::factory()->create([
        'name' => 'Pak Joko Supervisor',
        'nip' => '198501012010011005',
        'role' => UserRole::SUPERVISOR,
    ]);

    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'supervisor_id' => $supervisor->id,
        'verified_at' => now(),
        'status' => ReportStatus::VERIFIED,
        'report_code' => 'PCHT-VERIFIED-01',
    ]);

    $this->actingAs($petugas)
        ->get(route('reports.show', ['report' => $report->report_code]))
        ->assertOk()
        ->assertSee('Laporan Resmi Terverifikasi')
        ->assertSee('Pak Joko Supervisor')
        ->assertSee('198501012010011005');
});

test('supervisor sees verify and delete buttons on unverified report', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $report = DailyReport::factory()->create([
        'status' => ReportStatus::SUBMITTED,
        'report_code' => 'MMEA-SUBMITTED-01',
    ]);

    Livewire::actingAs($supervisor)
        ->test(ReportDetail::class, ['report' => $report])
        ->assertSee('data-test="btn-open-verify-modal"', false)
        ->assertSee('data-test="btn-open-delete-modal"', false);
});

test('petugas operator does not see verify button', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
    ]);

    Livewire::actingAs($petugas)
        ->test(ReportDetail::class, ['report' => $report])
        ->assertDontSee('data-test="btn-open-verify-modal"', false);
});
