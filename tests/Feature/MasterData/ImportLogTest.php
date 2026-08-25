<?php

use App\Livewire\MasterData\UpdateDatabase;
use App\Models\SapImportLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('supervisor can view paginated sap import logs on update database page', function () {
    $supervisor = User::factory()->supervisor()->create();

    SapImportLog::factory()->count(12)->create([
        'user_id' => $supervisor->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->assertSee('Riwayat Pembaruan Database')
        ->assertViewHas('importLogs', fn ($logs) => $logs->total() === 12 && $logs->count() === 10);
});

test('supervisor can view error log detail modal for failed or partial import', function () {
    $supervisor = User::factory()->supervisor()->create();

    $log = SapImportLog::factory()->partial()->create([
        'user_id' => $supervisor->id,
        'file_name' => 'sap_export_with_errors.xlsx',
        'error_log' => [
            'Baris 14: Nomor PO tidak boleh kosong.',
            'Baris 22: Target Qty (Jumlah Pesan) harus berupa angka positif.',
        ],
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->call('showLogErrorDetail', $log->id)
        ->assertSet('selectedLogId', $log->id)
        ->assertSee('Rincian Kesalahan Impor Berkas')
        ->assertSee('sap_export_with_errors.xlsx')
        ->assertSee('Baris 14: Nomor PO tidak boleh kosong.')
        ->assertSee('Baris 22: Target Qty (Jumlah Pesan) harus berupa angka positif.');
});

test('supervisor can download original uploaded sap spreadsheet file', function () {
    Storage::fake('local');
    $supervisor = User::factory()->supervisor()->create();

    $testContent = 'dummy excel content';
    $filePath = 'sap-imports/20260826_audit_test.xlsx';
    Storage::disk('local')->put($filePath, $testContent);

    $log = SapImportLog::create([
        'user_id' => $supervisor->id,
        'file_name' => 'original_sap_batch.xlsx',
        'file_path' => $filePath,
        'total_rows' => 10,
        'imported_count' => 10,
        'updated_count' => 0,
        'failed_count' => 0,
        'status' => 'success',
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->call('downloadOriginalFile', $log->id)
        ->assertFileDownloaded('original_sap_batch.xlsx');
});

test('supervisor can download audit error log report as text file', function () {
    $supervisor = User::factory()->supervisor()->create();

    $log = SapImportLog::create([
        'user_id' => $supervisor->id,
        'file_name' => 'daily_batch_errors.xlsx',
        'file_path' => 'sap-imports/dummy.xlsx',
        'total_rows' => 50,
        'imported_count' => 48,
        'updated_count' => 0,
        'failed_count' => 2,
        'status' => 'partial',
        'error_log' => [
            'Baris 5: Nomor PO tidak boleh kosong.',
            'Baris 12: Target Qty harus angka.',
        ],
    ]);

    $test = Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->call('downloadAuditErrorLog', $log->id)
        ->assertFileDownloaded();

    $downloadName = data_get($test->effects, 'download.name');
    expect($downloadName)->toStartWith('audit_error_log_daily_batch_errors')->toEndWith('.txt');
});

test('downloading missing original file handles error gracefully with warning toast', function () {
    Storage::fake('local');
    $supervisor = User::factory()->supervisor()->create();

    $log = SapImportLog::create([
        'user_id' => $supervisor->id,
        'file_name' => 'missing_file.xlsx',
        'file_path' => 'sap-imports/non_existent.xlsx',
        'total_rows' => 5,
        'imported_count' => 5,
        'updated_count' => 0,
        'failed_count' => 0,
        'status' => 'success',
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->call('downloadOriginalFile', $log->id)
        ->assertHasNoErrors();
});
