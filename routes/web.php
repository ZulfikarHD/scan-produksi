<?php

use App\Http\Controllers\ReportExportController;
use App\Livewire\MasterData\UpdateDatabase;
use App\Livewire\Petugas\PetugasIndex;
use App\Livewire\Reports\EditReport;
use App\Livewire\Reports\ProductionEntry;
use App\Livewire\Reports\ReportDetail;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Reports\ReportSessionLauncher;
use App\Models\DailyReport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Modul Laporan Produksi — Buat Laporan & Sesi Pemindaian (Phase 03)
    Route::get('reports/session', ReportSessionLauncher::class)->name('reports.session');
    Route::get('reports/create', ReportSessionLauncher::class)->name('reports.create');
    Route::get('laporan/buat', ReportSessionLauncher::class)->name('laporan.create');
    Route::get('reports/export', [ReportExportController::class, 'export'])->name('reports.export');

    // Modul Laporan Produksi — Arsip & Penjelajah Laporan (Phase 04)
    Route::get('reports', ReportIndex::class)->name('reports.index');
    Route::get('laporan', ReportIndex::class)->name('laporan.index');

    Route::get('reports/{report:report_code}/entry', ProductionEntry::class)->name('reports.entry');
    Route::get('reports/{report:report_code}/edit', EditReport::class)->name('reports.edit');
    Route::get('reports/{report:report_code}/pdf', [ReportExportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/{report:report_code}/print', function (DailyReport $report) {
        $report->load(['user', 'supervisor', 'items' => fn ($q) => $q->oldest('id')]);

        return view('reports.print', compact('report'));
    })->name('reports.print');
    Route::get('reports/{report:report_code}', ReportDetail::class)->name('reports.show');

    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('petugas', PetugasIndex::class)->name('petugas.index');
        Route::get('update-database', UpdateDatabase::class)->name('database.update');
    });
});

require __DIR__.'/settings.php';
