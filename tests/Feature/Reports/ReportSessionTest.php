<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Helpers\ShiftHelper;
use App\Livewire\Reports\ProductionEntry;
use App\Livewire\Reports\ReportSessionLauncher;
use App\Models\DailyReport;
use App\Models\User;
use App\Services\ReportCodeGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected to login when accessing report session page', function () {
    $this->get(route('reports.session'))->assertRedirect(route('login'));
    $this->get(route('reports.create'))->assertRedirect(route('login'));
    $this->get(route('laporan.create'))->assertRedirect(route('login'));
});

test('authenticated petugas and supervisor can access report session page', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    $this->actingAs($petugas)
        ->get(route('reports.session'))
        ->assertOk()
        ->assertSeeLivewire(ReportSessionLauncher::class);

    $this->actingAs($supervisor)
        ->get(route('reports.session'))
        ->assertOk()
        ->assertSeeLivewire(ReportSessionLauncher::class);
});

test('session launcher mounts with default today date and current shift in jakarta timezone', function () {
    $user = User::factory()->create();
    $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
    $currentShift = 'shift_'.ShiftHelper::getCurrentShift()['number'];

    Livewire::actingAs($user)
        ->test(ReportSessionLauncher::class)
        ->assertSet('report_date', $today)
        ->assertSet('shift', $currentShift)
        ->assertSet('product_series', ProductSeries::PCHT->value)
        ->assertSee('Inisiasi Sesi Laporan Produksi')
        ->assertSee('PCHT-');
});

test('session launcher validates required inputs', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ReportSessionLauncher::class)
        ->set('report_date', '')
        ->set('product_series', '')
        ->set('shift', '')
        ->call('launchSession')
        ->assertHasErrors(['report_date', 'product_series', 'shift']);
});

test('session launcher creates a new daily report and redirects to entry page', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $date = '2026-08-26';

    $expectedCode = ReportCodeGenerator::generateWithShift(ProductSeries::PCHT, $date, WorkShift::SHIFT_1);

    Livewire::actingAs($user)
        ->test(ReportSessionLauncher::class)
        ->set('report_date', $date)
        ->set('product_series', ProductSeries::PCHT->value)
        ->set('shift', WorkShift::SHIFT_1->value)
        ->set('notes', 'Shift 1 Pita Cukai Reguler')
        ->call('launchSession')
        ->assertRedirect(route('reports.entry', ['report' => $expectedCode]));

    $this->assertDatabaseHas('daily_reports', [
        'report_code' => $expectedCode,
        'product_series' => ProductSeries::PCHT->value,
        'shift' => WorkShift::SHIFT_1->value,
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT->value,
        'notes' => 'Shift 1 Pita Cukai Reguler',
    ]);

    $savedReport = DailyReport::where('report_code', $expectedCode)->firstOrFail();
    expect($savedReport->report_date->format('Y-m-d'))->toBe($date);
});

test('session launcher resumes existing session without duplicate creation', function () {
    $author = User::factory()->create(['name' => 'Budi Santoso']);
    $viewer = User::factory()->create(['name' => 'Agus Pratama']);
    $date = '2026-08-26';

    $existingReport = DailyReport::create([
        'report_code' => 'MMEA-2026-08-26-S2',
        'product_series' => ProductSeries::MMEA,
        'report_date' => $date,
        'shift' => WorkShift::SHIFT_2,
        'user_id' => $author->id,
        'status' => ReportStatus::DRAFT,
        'notes' => 'Sesi eksisting',
    ]);

    Livewire::actingAs($viewer)
        ->test(ReportSessionLauncher::class)
        ->set('report_date', $date)
        ->set('product_series', ProductSeries::MMEA->value)
        ->set('shift', WorkShift::SHIFT_2->value)
        ->assertSee('Sesi Sudah Ada (Lanjutkan)')
        ->call('launchSession')
        ->assertRedirect(route('reports.entry', ['report' => $existingReport->report_code]));

    // Memastikan tidak ada duplikasi data di database
    expect(DailyReport::where('product_series', ProductSeries::MMEA)->count())->toBe(1);
});

test('entry page loads existing daily report session successfully', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'report_code' => 'HPTL-2026-08-26-S1',
    ]);

    $this->actingAs($user)
        ->get(route('reports.entry', ['report' => $report->report_code]))
        ->assertOk()
        ->assertSeeLivewire(ProductionEntry::class)
        ->assertSee('HPTL-2026-08-26-S1');
});
