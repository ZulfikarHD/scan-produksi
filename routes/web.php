<?php

use App\Livewire\Petugas\PetugasIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('petugas', PetugasIndex::class)->name('petugas.index');
    });
});

require __DIR__.'/settings.php';
