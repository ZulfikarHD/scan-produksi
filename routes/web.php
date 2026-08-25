<?php

use App\Livewire\MasterData\UpdateDatabase;
use App\Livewire\Petugas\PetugasIndex;
use App\Livewire\Reports\ProductionEntry;
use App\Livewire\Reports\ReportSessionLauncher;
use App\Models\DailyReport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Modul Laporan Produksi & Sesi Pemindaian Barcode (Phase 03)
    Route::get('reports/session', ReportSessionLauncher::class)->name('reports.session');
    Route::get('reports/create', ReportSessionLauncher::class)->name('reports.create');
    Route::get('laporan/buat', ReportSessionLauncher::class)->name('laporan.create');
    Route::get('reports/{report:report_code}/entry', ProductionEntry::class)->name('reports.entry');
    Route::get('reports/{report:report_code}/print', function (DailyReport $report) {
        $report->load(['user', 'supervisor', 'items' => fn ($q) => $q->oldest('id')]);

        return view('reports.print', compact('report'));
    })->name('reports.print');

    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('petugas', PetugasIndex::class)->name('petugas.index');
        Route::get('update-database', UpdateDatabase::class)->name('database.update');
    });
});

require __DIR__.'/settings.php';
