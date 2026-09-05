<?php

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Livewire\Reports\EditReport;
use App\Livewire\Reports\ProductionEntry;
use App\Livewire\Reports\ReportDetail;
use App\Livewire\Reports\ReportSessionLauncher;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Services\ReportCodeGenerator;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('saveEntry persists item and synchronizes all three aggregates atomically', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
        'total_items_count' => 0,
        'total_good_quantity' => 0,
        'total_reject_quantity' => 0,
    ]);

    // Item awal dengan reject untuk memastikan refreshTotals menjumlahkan reject_quantity
    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'good_quantity' => 10000,
        'reject_quantity' => 150,
    ]);

    $report->update([
        'total_items_count' => 1,
        'total_good_quantity' => 10000,
        'total_reject_quantity' => 150,
    ]);

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-TX-ATOMIC-01',
        'target_quantity' => 20000,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->set('scanPo', 'PO-TX-ATOMIC-01')
        ->call('lookupPo')
        ->set('productionQty', 20000)
        ->call('saveEntry');

    $report->refresh();

    expect($report->total_items_count)->toBe(2)
        ->and($report->total_good_quantity)->toBe(30000)
        ->and($report->total_reject_quantity)->toBe(150);

    $this->assertDatabaseHas('daily_report_items', [
        'daily_report_id' => $report->id,
        'po_number' => 'PO-TX-ATOMIC-01',
        'good_quantity' => 20000,
        'reject_quantity' => 0,
    ]);
});

test('refreshTotals recalculates reject quantity after inline edit and delete', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
    ]);

    $itemKeep = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-KEEP',
        'good_quantity' => 8000,
        'reject_quantity' => 200,
    ]);

    $itemDelete = DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-DELETE',
        'good_quantity' => 5000,
        'reject_quantity' => 50,
    ]);

    $report->update([
        'total_items_count' => 2,
        'total_good_quantity' => 13000,
        'total_reject_quantity' => 250,
    ]);

    Livewire::actingAs($user)
        ->test(ProductionEntry::class, ['report' => $report])
        ->call('updateItemQuantity', $itemKeep->id, 7500)
        ->call('deleteItem', $itemDelete->id);

    $report->refresh();

    expect($report->total_items_count)->toBe(1)
        ->and($report->total_good_quantity)->toBe(7500)
        ->and($report->total_reject_quantity)->toBe(200);

    expect(DailyReportItem::where('id', $itemDelete->id)->exists())->toBeFalse();
});

test('saveEntry rolls back item insert when aggregate update fails inside transaction', function () {
    $user = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $user->id,
        'status' => ReportStatus::DRAFT,
        'total_items_count' => 0,
        'total_good_quantity' => 0,
        'total_reject_quantity' => 0,
    ]);

    ProductionOrder::factory()->create([
        'po_number' => 'PO-TX-ROLLBACK',
        'target_quantity' => 15000,
    ]);

    // Paksa kegagalan pada UPDATE header setelah INSERT item agar transaksi digulung balik
    DailyReport::updating(function () {
        throw new RuntimeException('Simulated aggregate update failure');
    });

    try {
        Livewire::actingAs($user)
            ->test(ProductionEntry::class, ['report' => $report])
            ->set('scanPo', 'PO-TX-ROLLBACK')
            ->call('lookupPo')
            ->set('productionQty', 15000)
            ->call('saveEntry');

        $this->fail('Expected RuntimeException was not thrown');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Simulated aggregate update failure');
    } finally {
        DailyReport::flushEventListeners();
    }

    // Item tidak boleh tersisa jika agregat gagal (transaksi digulung balik)
    expect(DailyReportItem::where('daily_report_id', $report->id)->count())->toBe(0);

    $fresh = DailyReport::find($report->id);
    expect($fresh->total_items_count)->toBe(0)
        ->and($fresh->total_good_quantity)->toBe(0)
        ->and($fresh->total_reject_quantity)->toBe(0);
});

test('session launcher redirects when session already exists without creating duplicate', function () {
    $firstOperator = User::factory()->create(['role' => UserRole::PETUGAS]);
    $secondOperator = User::factory()->create(['role' => UserRole::PETUGAS]);
    $date = '2026-09-06';

    $existingCode = ReportCodeGenerator::generateWithShift(ProductSeries::PCHT, $date, WorkShift::SHIFT_1);

    $existingReport = DailyReport::factory()->create([
        'report_code' => $existingCode,
        'product_series' => ProductSeries::PCHT,
        'report_date' => $date,
        'shift' => WorkShift::SHIFT_1,
        'user_id' => $firstOperator->id,
        'status' => ReportStatus::DRAFT,
    ]);

    Livewire::actingAs($secondOperator)
        ->test(ReportSessionLauncher::class)
        ->set('report_date', $date)
        ->set('product_series', ProductSeries::PCHT->value)
        ->set('shift', WorkShift::SHIFT_1->value)
        ->call('launchSession')
        ->assertRedirect(route('reports.entry', ['report' => $existingReport->report_code]));

    expect(DailyReport::where('product_series', ProductSeries::PCHT)
        ->whereDate('report_date', $date)
        ->where('shift', WorkShift::SHIFT_1)
        ->count())->toBe(1);
});

test('session launcher catches QueryException race and redirects to winning session', function () {
    $firstOperator = User::factory()->create(['role' => UserRole::PETUGAS]);
    $secondOperator = User::factory()->create(['role' => UserRole::PETUGAS]);
    $date = '2026-09-06';

    // Simulasikan TOCTOU: sesi belum ada saat early-check, tetapi baris pemenang
    // disisipkan tepat sebelum INSERT operator kedua sehingga unique constraint gagal.
    DailyReport::creating(function (DailyReport $model) use ($firstOperator) {
        static $injected = false;

        if ($injected) {
            return;
        }

        $injected = true;

        // Sisipkan baris pemenang via query builder (hindari rekursi model event)
        DB::table('daily_reports')->insert([
            'report_code' => $model->report_code,
            'product_series' => $model->product_series instanceof BackedEnum
                ? $model->product_series->value
                : $model->product_series,
            'report_date' => $model->report_date instanceof CarbonInterface
                ? $model->report_date->format('Y-m-d')
                : $model->report_date,
            'shift' => $model->shift instanceof BackedEnum
                ? $model->shift->value
                : $model->shift,
            'user_id' => $firstOperator->id,
            'status' => ReportStatus::DRAFT->value,
            'total_items_count' => 0,
            'total_good_quantity' => 0,
            'total_reject_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    try {
        $expectedCode = ReportCodeGenerator::generateWithShift(ProductSeries::MMEA, $date, WorkShift::SHIFT_2);

        Livewire::actingAs($secondOperator)
            ->test(ReportSessionLauncher::class)
            ->set('report_date', $date)
            ->set('product_series', ProductSeries::MMEA->value)
            ->set('shift', WorkShift::SHIFT_2->value)
            ->call('launchSession')
            ->assertRedirect(route('reports.entry', ['report' => $expectedCode]));

        expect(DailyReport::where('product_series', ProductSeries::MMEA)
            ->whereDate('report_date', $date)
            ->where('shift', WorkShift::SHIFT_2)
            ->count())->toBe(1);

        $winner = DailyReport::where('report_code', $expectedCode)->firstOrFail();
        expect($winner->user_id)->toBe($firstOperator->id);
    } finally {
        DailyReport::flushEventListeners();
    }
});

test('supervisor verification requires submitted status via atomic precondition update', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);

    $draftReport = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::DRAFT,
        'report_code' => 'PCHT-VERIFY-DRAFT',
    ]);

    Livewire::actingAs($supervisor)
        ->test(ReportDetail::class, ['report' => $draftReport])
        ->call('verifyReport');

    $draftReport->refresh();

    expect($draftReport->status)->toBe(ReportStatus::DRAFT)
        ->and($draftReport->supervisor_id)->toBeNull()
        ->and($draftReport->verified_at)->toBeNull();
});

test('supervisor verification succeeds only once for submitted report', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $otherSupervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);

    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
        'report_code' => 'PCHT-VERIFY-ONCE',
    ]);

    Livewire::actingAs($supervisor)
        ->test(ReportDetail::class, ['report' => $report])
        ->call('verifyReport');

    $report->refresh();
    expect($report->status)->toBe(ReportStatus::VERIFIED)
        ->and($report->supervisor_id)->toBe($supervisor->id)
        ->and($report->verified_at)->not->toBeNull();

    $firstVerifiedAt = $report->verified_at;

    // Percobaan verifikasi kedua harus gagal precondition (status sudah VERIFIED)
    Livewire::actingAs($otherSupervisor)
        ->test(ReportDetail::class, ['report' => $report])
        ->call('verifyReport');

    $report->refresh();
    expect($report->status)->toBe(ReportStatus::VERIFIED)
        ->and($report->supervisor_id)->toBe($supervisor->id)
        ->and($report->verified_at->equalTo($firstVerifiedAt))->toBeTrue();
});

test('edit report saveChanges recalculates all three aggregates inside transaction', function () {
    $petugas = User::factory()->create(['role' => UserRole::PETUGAS]);
    $report = DailyReport::factory()->create([
        'user_id' => $petugas->id,
        'status' => ReportStatus::SUBMITTED,
        'total_items_count' => 1,
        'total_good_quantity' => 10000,
        'total_reject_quantity' => 0,
        'created_at' => now()->subHours(1),
    ]);

    DailyReportItem::factory()->create([
        'daily_report_id' => $report->id,
        'po_number' => 'PO-EDIT-TX',
        'good_quantity' => 10000,
        'reject_quantity' => 0,
    ]);

    Livewire::actingAs($petugas)
        ->test(EditReport::class, ['report' => $report])
        ->set('items.0.good_quantity', 9200)
        ->set('items.0.reject_quantity', 300)
        ->call('saveChanges')
        ->assertRedirect(route('reports.show', ['report' => $report->report_code]));

    $report->refresh();

    expect($report->total_items_count)->toBe(1)
        ->and($report->total_good_quantity)->toBe(9200)
        ->and($report->total_reject_quantity)->toBe(300);
});
