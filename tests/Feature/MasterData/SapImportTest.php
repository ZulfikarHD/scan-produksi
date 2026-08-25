<?php

use App\Enums\ProductionOrderStatus;
use App\Livewire\MasterData\UpdateDatabase;
use App\Models\ProductionOrder;
use App\Models\SapImportLog;
use App\Models\User;
use App\Services\SapImportService;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * Helper to generate a temporary test XLSX file.
 *
 * @param  list<list<mixed>>  $rows
 */
function createTestXlsx(array $rows): string
{
    $tempPath = tempnam(sys_get_temp_dir(), 'test_sap_').'.xlsx';
    $writer = new XlsxWriter;
    $writer->openToFile($tempPath);

    foreach ($rows as $row) {
        $writer->addRow(Row::fromValues($row));
    }

    $writer->close();

    return $tempPath;
}

/**
 * Helper to generate a temporary test CSV file.
 *
 * @param  list<list<mixed>>  $rows
 */
function createTestCsv(array $rows, string $delimiter = ','): string
{
    $tempPath = tempnam(sys_get_temp_dir(), 'test_sap_').'.csv';
    $fp = fopen($tempPath, 'w');

    foreach ($rows as $row) {
        fputcsv($fp, $row, $delimiter);
    }

    fclose($fp);

    return $tempPath;
}

test('supervisor can access update database page successfully', function () {
    $supervisor = User::factory()->supervisor()->create();

    $this->actingAs($supervisor)
        ->get(route('database.update'))
        ->assertOk()
        ->assertSeeLivewire(UpdateDatabase::class)
        ->assertSee('Update Database SAP')
        ->assertSee('Impor Berkas SAP')
        ->assertSee('Master Data Order Produksi');
});

test('petugas cannot access update database page and receives 403 forbidden', function () {
    $petugas = User::factory()->petugas()->create();

    $this->actingAs($petugas)
        ->get(route('database.update'))
        ->assertForbidden();
});

test('guest cannot access update database page and is redirected to login', function () {
    $this->get(route('database.update'))
        ->assertRedirect(route('login'));
});

test('supervisor can download sample sap master data excel template', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->call('downloadTemplate')
        ->assertFileDownloaded('template_sap_master_data.xlsx');
});

test('sap import service parses valid xlsx spreadsheet and returns accurate preview data (US-2.3)', function () {
    $headers = [
        'NO PO', 'No OBC', 'Perso / non Perso', 'SERI', 'TGL OBC', 'TGL JTempo',
        'TGL_PAKAI_BB', 'TGL_CETAK', 'TGL_VERIFIKASI', 'Tanggal Kemas',
        'QTY PESAN', 'RENCET', 'JML_BB (LK)', 'JLM_CD (LK)', 'TOTAL_BB (LK)',
        'JML_CETAK', 'BAIK_VERIFIKASI', 'RUSAK_VERIFIKASI', 'HASIL BAIK DIJADIKAN HCTS',
        'TOTAL_HCTS', 'KEMAS', 'KIRIM', 'WORK CENTER', 'Material',
    ];

    $row1 = [
        '3000189509', 'ACE211474', 'P', '1', '12/18/2023', '1/10/2024',
        '1/6/2024', '1/9/2024', '1/10/2024', '1/11/2024',
        2000, 2068, 0, 2068, 2068,
        2068, 2037, 31, 37, 68, 2000, 0, 'HDR-1001', '931110001-24',
    ];

    $row2 = [
        '3000189510', 'ACE211475', 'NP', '2', '12/19/2023', '1/12/2024',
        '1/7/2024', '1/10/2024', '1/11/2024', '1/12/2024',
        5000, 5150, 5000, 150, 5150,
        5150, 5130, 20, 0, 20, 5000, 5000, 'MESIN-02', 'Sertifikat A4',
    ];

    $filePath = createTestXlsx([$headers, $row1, $row2]);

    $service = new SapImportService;
    $preview = $service->parsePreview($filePath, 20);

    expect($preview['total_rows'])->toBe(2);
    expect($preview['valid_rows_count'])->toBe(2);
    expect($preview['invalid_rows_count'])->toBe(0);
    expect($preview['preview_rows'])->toHaveCount(2);
    expect($preview['preview_rows'][0]['po_number'])->toBe('3000189509');
    expect($preview['preview_rows'][0]['product_name'])->toBe('931110001-24');
    expect($preview['preview_rows'][0]['target_quantity'])->toBe(2000);
    expect($preview['preview_rows'][0]['is_valid'])->toBeTrue();

    @unlink($filePath);
});

test('sap import service parses valid csv spreadsheet with semicolon delimiter', function () {
    $headers = ['No PO', 'Nama Produk', 'Pelanggan', 'Target Qty', 'No Mesin', 'Tgl Order'];
    $row1 = ['PO-CSV-01', 'Pita Cukai Minuman', 'Kemenkeu', '10.000', 'MESIN-01', '2026-08-20'];
    $row2 = ['PO-CSV-02', 'Sertifikat Standar', 'Kemendikbud', '2500', 'MESIN-02', '2026-08-21'];

    $filePath = createTestCsv([$headers, $row1, $row2], ';');

    $service = new SapImportService;
    $preview = $service->parsePreview($filePath, 20);

    expect($preview['total_rows'])->toBe(2);
    expect($preview['valid_rows_count'])->toBe(2);
    expect($preview['preview_rows'][0]['po_number'])->toBe('PO-CSV-01');
    expect($preview['preview_rows'][0]['product_name'])->toBe('Pita Cukai Minuman');
    expect($preview['preview_rows'][0]['target_quantity'])->toBe(10000);

    @unlink($filePath);
});

test('sap import service identifies invalid rows with Indonesian error messages', function () {
    $headers = ['No PO', 'Nama Produk', 'Pelanggan', 'Target Qty'];
    $validRow = ['PO-VALID-01', 'Produk Valid', 'Bank Indonesia', 1000];
    $missingPoRow = ['', 'Produk Tanpa PO', 'Kemendagri', 500];
    $missingProductRow = ['PO-NOPRODUCT-02', '', 'Kemendagri', 500];

    $filePath = createTestCsv([$headers, $validRow, $missingPoRow, $missingProductRow]);

    $service = new SapImportService;
    $preview = $service->parsePreview($filePath, 20);

    expect($preview['total_rows'])->toBe(3);
    expect($preview['valid_rows_count'])->toBe(1);
    expect($preview['invalid_rows_count'])->toBe(2);
    expect($preview['all_errors'])->toHaveCount(2);
    expect($preview['all_errors'][0])->toContain('Nomor PO tidak boleh kosong');
    expect($preview['all_errors'][1])->toContain('Nama Produk / Material tidak boleh kosong');

    @unlink($filePath);
});

test('livewire update database component triggers preview modal upon uploading valid spreadsheet (US-2.2 & US-2.3)', function () {
    $supervisor = User::factory()->supervisor()->create();

    $headers = ['No PO', 'Nama Produk', 'Pelanggan', 'Target Qty', 'No Mesin'];
    $row1 = ['PO-UPLOAD-01', 'Sertifikat Tanah', 'BPN', 2000, 'MESIN-01'];
    $filePath = createTestXlsx([$headers, $row1]);

    $uploadedFile = UploadedFile::fake()->createWithContent(
        'sap_sample.xlsx',
        (string) file_get_contents($filePath)
    );

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('file', $uploadedFile)
        ->assertSet('hasPreview', true)
        ->assertSet('totalRowsCount', 1)
        ->assertSet('validRowsCount', 1)
        ->assertSet('invalidRowsCount', 0)
        ->assertSee('PO-UPLOAD-01')
        ->assertSee('Sertifikat Tanah');

    @unlink($filePath);
});

test('livewire update database component confirms import and upserts records into production database (US-2.2)', function () {
    $supervisor = User::factory()->supervisor()->create();

    // Create an existing record to test update behavior
    ProductionOrder::factory()->create([
        'po_number' => 'PO-EXISTING-01',
        'product_name' => 'Nama Lama',
        'target_quantity' => 1000,
        'created_by' => $supervisor->id,
    ]);

    $headers = ['No PO', 'Nama Produk', 'Pelanggan', 'Target Qty', 'No Mesin', 'Tgl Order'];
    $newRow = ['PO-NEW-02', 'Produk Baru', 'Institusi Baru', 5000, 'MESIN-02', '2026-08-25'];
    $updateRow = ['PO-EXISTING-01', 'Nama Diperbarui SAP', 'Pelanggan Update', 2500, 'MESIN-01', '2026-08-26'];

    $filePath = createTestXlsx([$headers, $newRow, $updateRow]);

    $uploadedFile = UploadedFile::fake()->createWithContent(
        'sap_batch_update.xlsx',
        (string) file_get_contents($filePath)
    );

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('file', $uploadedFile)
        ->call('confirmImport', false)
        ->assertSet('hasPreview', false);

    expect(ProductionOrder::count())->toBe(2);

    $createdOrder = ProductionOrder::where('po_number', 'PO-NEW-02')->first();
    expect($createdOrder)->not->toBeNull();
    expect($createdOrder->product_name)->toBe('Produk Baru');
    expect($createdOrder->target_quantity)->toBe(5000);
    expect($createdOrder->status)->toBe(ProductionOrderStatus::READY);

    $updatedOrder = ProductionOrder::where('po_number', 'PO-EXISTING-01')->first();
    expect($updatedOrder)->not->toBeNull();
    expect($updatedOrder->product_name)->toBe('Nama Diperbarui SAP');
    expect($updatedOrder->target_quantity)->toBe(2500);

    // Verify Audit Log
    expect(SapImportLog::count())->toBe(1);
    $log = SapImportLog::first();
    expect($log->file_name)->toBe('sap_batch_update.xlsx');
    expect($log->total_rows)->toBe(2);
    expect($log->imported_count)->toBe(1);
    expect($log->updated_count)->toBe(1);
    expect($log->failed_count)->toBe(0);
    expect($log->status)->toBe('success');

    @unlink($filePath);
});

test('livewire update database component handles partial import by skipping invalid rows (US-2.3)', function () {
    $supervisor = User::factory()->supervisor()->create();

    $headers = ['No PO', 'Nama Produk', 'Pelanggan', 'Target Qty'];
    $validRow1 = ['PO-PARTIAL-01', 'Produk Valid 1', 'Pelanggan A', 3000];
    $invalidRow = ['', 'Produk Rusak', 'Pelanggan B', 1000]; // Missing PO
    $validRow2 = ['PO-PARTIAL-02', 'Produk Valid 2', 'Pelanggan C', 4000];

    $filePath = createTestCsv([$headers, $validRow1, $invalidRow, $validRow2]);

    $uploadedFile = UploadedFile::fake()->createWithContent(
        'partial_sap.csv',
        (string) file_get_contents($filePath)
    );

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('file', $uploadedFile)
        ->assertSet('validRowsCount', 2)
        ->assertSet('invalidRowsCount', 1)
        ->call('confirmImport', true);

    expect(ProductionOrder::count())->toBe(2);
    expect(ProductionOrder::where('po_number', 'PO-PARTIAL-01')->first())->not->toBeNull();
    expect(ProductionOrder::where('po_number', 'PO-PARTIAL-02')->first())->not->toBeNull();

    // Check Log Status
    $log = SapImportLog::first();
    expect($log)->not->toBeNull();
    expect($log->status)->toBe('partial');
    expect($log->failed_count)->toBe(1);
    expect($log->imported_count)->toBe(2);

    @unlink($filePath);
});

test('canceling import cleans up state without inserting data into database', function () {
    $supervisor = User::factory()->supervisor()->create();

    $headers = ['No PO', 'Nama Produk', 'Target Qty'];
    $row = ['PO-CANCELLED-01', 'Produk Batal', 1000];
    $filePath = createTestXlsx([$headers, $row]);

    $uploadedFile = UploadedFile::fake()->createWithContent(
        'cancel_test.xlsx',
        (string) file_get_contents($filePath)
    );

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('file', $uploadedFile)
        ->assertSet('hasPreview', true)
        ->call('cancelImport')
        ->assertSet('hasPreview', false)
        ->assertSet('file', null);

    expect(ProductionOrder::count())->toBe(0);
    expect(SapImportLog::count())->toBe(0);

    @unlink($filePath);
});

test('sap import service normalizes various date and number representations accurately', function () {
    $service = new SapImportService;

    // Integer normalizations
    expect($service->normalizeInteger('2.068'))->toBe(2068);
    expect($service->normalizeInteger('10,000'))->toBe(10000);
    expect($service->normalizeInteger('-'))->toBe(0);
    expect($service->normalizeInteger(''))->toBe(0);
    expect($service->normalizeInteger(null))->toBe(0);
    expect($service->normalizeInteger(12500))->toBe(12500);

    // Date normalizations
    expect($service->normalizeDate('12/18/2023'))->toBe('2023-12-18');
    expect($service->normalizeDate('1/10/2024'))->toBe('2024-01-10');
    expect($service->normalizeDate('2026-08-26'))->toBe('2026-08-26');
    expect($service->normalizeDate('-'))->toBeNull();
    expect($service->normalizeDate(''))->toBeNull();
    expect($service->normalizeDate(null))->toBeNull();
});

test('livewire update database switches tabs between import and orders seamlessly', function () {
    $supervisor = User::factory()->supervisor()->create();
    ProductionOrder::factory()->count(3)->create();

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->assertSet('activeTab', 'import')
        ->call('switchTab', 'orders')
        ->assertSet('activeTab', 'orders')
        ->assertSee('No PO / Barcode');
});

test('file upload validation fails for invalid file extension or exceeding 10mb', function () {
    $supervisor = User::factory()->supervisor()->create();

    // Invalid extension (.pdf)
    $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('file', $invalidFile)
        ->assertHasErrors(['file' => 'mimes']);

    // Oversized file (> 10MB = 10241 KB)
    $oversizedFile = UploadedFile::fake()->create('large_sap.xlsx', 10245, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('file', $oversizedFile)
        ->assertHasErrors(['file' => 'max']);
});

test('sap import service handles header only spreadsheet with zero data rows gracefully', function () {
    $headers = ['No PO', 'Nama Produk', 'Target Qty'];
    $filePath = createTestXlsx([$headers]);

    $service = new SapImportService;
    $preview = $service->parsePreview($filePath, 20);

    expect($preview['total_rows'])->toBe(0);
    expect($preview['valid_rows_count'])->toBe(0);
    expect($preview['invalid_rows_count'])->toBe(0);
    expect($preview['preview_rows'])->toBeEmpty();

    @unlink($filePath);
});

test('supervisor can view historical import error log modal detail', function () {
    $supervisor = User::factory()->supervisor()->create();
    $log = SapImportLog::create([
        'user_id' => $supervisor->id,
        'file_name' => 'failed_import.xlsx',
        'total_rows' => 5,
        'imported_count' => 3,
        'updated_count' => 0,
        'failed_count' => 2,
        'status' => 'partial',
        'error_log' => [
            'Baris 3: Nomor PO tidak boleh kosong.',
            'Baris 4: Nama Produk / Material tidak boleh kosong.',
        ],
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->call('showLogErrorDetail', $log->id)
        ->assertSet('selectedLogId', $log->id)
        ->assertSee('Rincian Kesalahan Impor Berkas')
        ->assertSee('Baris 3: Nomor PO tidak boleh kosong.')
        ->assertSee('Baris 4: Nama Produk / Material tidak boleh kosong.');
});

test('master data orders tab supports instant search and status filtering', function () {
    $supervisor = User::factory()->supervisor()->create();

    ProductionOrder::factory()->create([
        'po_number' => 'PO-SEARCH-1234',
        'product_name' => 'Sertifikat Kelulusan 2026',
        'status' => ProductionOrderStatus::READY,
    ]);

    ProductionOrder::factory()->create([
        'po_number' => 'PO-SEARCH-5678',
        'product_name' => 'Ijazah Percetakan',
        'status' => ProductionOrderStatus::IN_PROGRESS,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->set('search', '1234')
        ->assertSee('PO-SEARCH-1234')
        ->assertDontSee('PO-SEARCH-5678')
        ->set('search', '')
        ->set('statusFilter', 'in_progress')
        ->assertSee('PO-SEARCH-5678')
        ->assertDontSee('PO-SEARCH-1234');
});
