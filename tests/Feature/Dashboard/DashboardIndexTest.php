<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Livewire\Dashboard\DashboardIndex;
use App\Models\DailyReport;
use App\Models\User;
use Livewire\Livewire;

test('dashboard index component renders successfully for authenticated users', function () {
    $user = User::factory()->create(['name' => 'Ahmad Teknisi', 'role' => UserRole::PETUGAS]);

    Livewire::actingAs($user)
        ->test(DashboardIndex::class)
        ->assertOk()
        ->assertSee('Ahmad Teknisi')
        ->assertSee('Kemas Baik Hari Ini')
        ->assertSee('Reject / Rusak')
        ->assertSee('Order Diproses')
        ->assertSee('Grafik Tren Produksi')
        ->assertSee('Distribusi Volume per Shift')
        ->assertSee('Top 5 Mesin Cetak')
        ->assertSee('Laporan Produksi Terkini');
});

test('supervisor sees all administrative quick action tiles', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    Livewire::actingAs($supervisor)
        ->test(DashboardIndex::class)
        ->assertSee('Buat Laporan (Scan Barcode)')
        ->assertSee('Update Database SAP')
        ->assertSee('Lihat Semua Laporan')
        ->assertSee('Kelola Data Petugas');
});

test('petugas does not see supervisor-only quick action tiles', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);

    Livewire::actingAs($petugas)
        ->test(DashboardIndex::class)
        ->assertSee('Buat Laporan (Scan Barcode)')
        ->assertSee('Lihat Semua Laporan')
        ->assertSeeHtml('Buat Laporan (Scan Barcode)');
});

test('user can switch date range and chart type dynamically', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DashboardIndex::class)
        ->assertSet('dateRange', 14)
        ->assertSet('chartType', 'area')
        ->call('setDateRange', 7)
        ->assertSet('dateRange', 7)
        ->call('setDateRange', 30)
        ->assertSet('dateRange', 30)
        ->call('setChartType', 'bar')
        ->assertSet('chartType', 'bar')
        ->call('setChartType', 'line')
        ->assertSet('chartType', 'line');
});

test('refresh data action triggers cache clear and success toast', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DashboardIndex::class)
        ->call('refreshData')
        ->assertStatus(200);
});

test('recent reports table displays 5 latest reports with correct action links', function () {
    $user = User::factory()->create(['name' => 'Petugas Satu']);

    $reports = [];
    for ($i = 1; $i <= 6; $i++) {
        $reports[] = DailyReport::factory()->create([
            'user_id' => $user->id,
            'report_code' => "LAPORAN-2026-08-{$i}",
            'product_series' => ProductSeries::PCHT,
            'report_date' => now('Asia/Jakarta')->subDays(10 - $i)->toDateString(),
            'shift' => WorkShift::SHIFT_1,
            'total_good_quantity' => 1000 * $i,
            'status' => ReportStatus::SUBMITTED,
        ]);
    }

    Livewire::actingAs($user)
        ->test(DashboardIndex::class)
        ->assertSee('LAPORAN-2026-08-6')
        ->assertSee('LAPORAN-2026-08-5')
        ->assertSee('LAPORAN-2026-08-4')
        ->assertSee('LAPORAN-2026-08-3')
        ->assertSee('LAPORAN-2026-08-2')
        // Laporan paling lama (#1) tidak masuk top 5
        ->assertDontSee('LAPORAN-2026-08-1');
});
