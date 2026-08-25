<?php

use App\Livewire\MasterData\UpdateDatabase;
use App\Livewire\Petugas\PetugasIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('petugas', PetugasIndex::class)->name('petugas.index');
        Route::get('update-database', UpdateDatabase::class)->name('database.update');
    });
});

require __DIR__.'/settings.php';
