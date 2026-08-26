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

test('guests are redirected to login when accessing export or pdf endpoints', function () {
    $report = DailyReport::factory()->create();

    $this->get(route('reports.export'))->assertRedirect(route('login'));
    $this->get(route('reports.pdf', ['report' => $report->report_code]))->assertRedirect(route('login'));
});

test('authenticated user can stream download excel export file', function () {
    $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    $report1 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-EXPORT-01',
        'product_series' => ProductSeries::PCHT,
        'shift' => WorkShift::SHIFT_1,
        'report_date' => '2026-08-26',
        'status' => ReportStatus::VERIFIED,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report1->id,
        'po_number' => 'PO-EXPORT-888',
        'product_name' => 'Produk Cukai PCHT',
        'good_quantity' => 20000,
        'reject_quantity' => 100,
    ]);

    $response = $this->actingAs($user)
        ->get(route('reports.export', [
            'from' => '2026-08-01',
            'to' => '2026-08-31',
            'series' => 'pcht',
            'shift' => 'shift_1',
            'status' => 'verified',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('authenticated user can access pdf download document view', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-PDF-TEST-01',
        'product_series' => ProductSeries::PCHT,
        'status' => ReportStatus::SUBMITTED,
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-PDF-999',
        'product_name' => 'Pita Cukai Rokok SKM',
        'good_quantity' => 15000,
    ]);

    $response = $this->actingAs($user)
        ->get(route('reports.pdf', ['report' => $report->report_code]));

    $response->assertOk();
    $response->assertSee('PT PERCETAKAN DOKUMEN SEKURITI');
    $response->assertSee('PCHT-PDF-TEST-01');
    $response->assertSee('PO-PDF-999');
});
