<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Livewire\Reports\ReportIndex;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected to login when accessing report index', function () {
    $this->get(route('reports.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view report index and see list of daily reports', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS, 'name' => 'Budi Kemas']);
    $report1 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-2026-08-26-S1',
        'product_series' => ProductSeries::PCHT,
        'shift' => WorkShift::SHIFT_1,
        'total_items_count' => 5,
        'total_good_quantity' => 25000,
        'status' => ReportStatus::SUBMITTED,
    ]);

    $report2 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'MMEA-2026-08-26-S2',
        'product_series' => ProductSeries::MMEA,
        'shift' => WorkShift::SHIFT_2,
        'total_items_count' => 3,
        'total_good_quantity' => 12000,
        'status' => ReportStatus::VERIFIED,
    ]);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Laporan Produksi')
        ->assertSee('PCHT-2026-08-26-S1')
        ->assertSee('MMEA-2026-08-26-S2')
        ->assertSee('Budi Kemas');
});

test('user can search reports in real-time by report code or PO number', function () {
    $user = User::factory()->create();
    $report1 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-TARGET-999',
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $report1->id,
        'po_number' => 'PO-SECRET-1234',
        'product_name' => 'Produk Khusus SKM',
    ]);

    $report2 = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'MMEA-OTHER-001',
    ]);
    DailyReportItem::factory()->create([
        'daily_report_id' => $report2->id,
        'po_number' => 'PO-REGULAR-5678',
        'product_name' => 'Produk Reguler MMEA',
    ]);

    Livewire::actingAs($user)
        ->test(ReportIndex::class)
        ->assertSee('PCHT-TARGET-999')
        ->assertSee('MMEA-OTHER-001')
        ->set('search', 'TARGET-999')
        ->assertSee('PCHT-TARGET-999')
        ->assertDontSee('MMEA-OTHER-001')
        ->set('search', 'SECRET-1234')
        ->assertSee('PCHT-TARGET-999')
        ->assertDontSee('MMEA-OTHER-001');
});

test('user can filter reports by product series, shift, and status', function () {
    $user = User::factory()->create();

    $pchtReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'PCHT-CODE-001',
        'product_series' => ProductSeries::PCHT,
        'shift' => WorkShift::SHIFT_1,
        'status' => ReportStatus::DRAFT,
    ]);

    $mmeaReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'MMEA-CODE-002',
        'product_series' => ProductSeries::MMEA,
        'shift' => WorkShift::SHIFT_2,
        'status' => ReportStatus::VERIFIED,
    ]);

    Livewire::actingAs($user)
        ->test(ReportIndex::class)
        ->set('series', 'pcht')
        ->assertSee('PCHT-CODE-001')
        ->assertDontSee('MMEA-CODE-002')
        ->set('series', 'mmea')
        ->assertDontSee('PCHT-CODE-001')
        ->assertSee('MMEA-CODE-002')
        ->set('series', '')
        ->set('status', 'draft')
        ->assertSee('PCHT-CODE-001')
        ->assertDontSee('MMEA-CODE-002');
});

test('user can apply date presets like today, yesterday, and last 7 days', function () {
    $user = User::factory()->create();

    $todayReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'TODAY-REPORT-01',
        'report_date' => now('Asia/Jakarta')->toDateString(),
    ]);

    $oldReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'OLD-REPORT-99',
        'report_date' => now('Asia/Jakarta')->subDays(20)->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test(ReportIndex::class)
        ->set('datePreset', 'today')
        ->assertSee('TODAY-REPORT-01')
        ->assertDontSee('OLD-REPORT-99');
});

test('reset filters button restores all parameters to defaults', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ReportIndex::class)
        ->set('search', 'something')
        ->set('series', 'pcht')
        ->set('status', 'verified')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('series', '')
        ->assertSet('status', '');
});
