<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login when accessing report print page', function () {
    $report = DailyReport::factory()->create();

    $this->get(route('reports.print', ['report' => $report->report_code]))
        ->assertRedirect(route('login'));
});

test('authenticated user can view standard A4 physical printable report', function () {
    $user = User::factory()->create([
        'name' => 'Budi Operator',
        'nip' => '199001012020121001',
        'role' => UserRole::PETUGAS,
    ]);

    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-2026-08-26-S1',
        'product_series' => ProductSeries::PCHT,
        'shift' => WorkShift::SHIFT_1,
        'report_date' => '2026-08-26',
        'status' => ReportStatus::SUBMITTED,
        'notes' => 'Catatan shift pagi lancar',
    ]);

    $item = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-2026-PRINT-01',
        'product_name' => 'Pita Cukai Rokok SKT',
        'specification' => 'Kertas Berpengaman UV',
        'machine_number' => 'MESIN-01',
        'target_quantity' => 20000,
        'good_quantity' => 20000,
        'unit' => 'lembar',
    ]);

    $this->actingAs($user)
        ->get(route('reports.print', ['report' => $report->report_code]))
        ->assertOk()
        ->assertSee('PT PERCETAKAN DOKUMEN SEKURITI')
        ->assertSee('LAPORAN DOKUMENTASI HASIL PRODUKSI HARIAN')
        ->assertSee('PCHT-2026-08-26-S1')
        ->assertSee('Budi Operator')
        ->assertSee('PO-2026-PRINT-01')
        ->assertSee('Pita Cukai Rokok SKT')
        ->assertSee('20.000 lembar');
});
