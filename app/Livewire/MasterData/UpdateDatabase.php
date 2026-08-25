<?php

namespace App\Livewire\MasterData;

use App\Models\ProductionOrder;
use App\Models\SapImportLog;
use App\Models\User;
use App\Services\SapImportService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

#[Layout('layouts.app')]
#[Title('Update Database SAP')]
class UpdateDatabase extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url]
    public string $activeTab = 'import'; // 'import' vs 'orders'

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    // File Upload State
    public mixed $file = null;

    public ?string $storedTempPath = null;

    public ?string $originalFileName = null;

    // US-2.3 Preview State
    public bool $hasPreview = false;

    public int $totalRowsCount = 0;

    public int $validRowsCount = 0;

    public int $invalidRowsCount = 0;

    /** @var list<array<string, mixed>> */
    public array $previewRows = [];

    /** @var list<string> */
    public array $previewErrors = [];

    public bool $isProcessing = false;

    // Error Log Inspection Modal State
    public ?int $selectedLogId = null;

    public ?SapImportLog $selectedLog = null;

    /**
     * Render view Update Database dengan tab Impor Berkas SAP dan Master Data Order Produksi.
     */
    public function render(SapImportService $importService): View
    {
        $importLogs = SapImportLog::with('user')
            ->latest('id')
            ->paginate(10, ['*'], 'logsPage');

        $ordersQuery = ProductionOrder::query()->with('creator');

        if ($this->search !== '') {
            $ordersQuery->search($this->search);
        }

        if ($this->statusFilter !== '') {
            $ordersQuery->byStatus($this->statusFilter);
        }

        $orders = $ordersQuery->latest('id')->paginate(15, ['*'], 'ordersPage');

        $latestLog = SapImportLog::latest('id')->first();
        $totalOrdersCount = ProductionOrder::count();

        return view('livewire.master-data.update-database', [
            'importLogs' => $importLogs,
            'orders' => $orders,
            'latestLog' => $latestLog,
            'totalOrdersCount' => $totalOrdersCount,
        ]);
    }

    /**
     * Reset pagination when search keyword changes on orders tab.
     */
    public function updatingSearch(): void
    {
        $this->resetPage('ordersPage');
    }

    /**
     * Reset pagination when status filter changes on orders tab.
     */
    public function updatingStatusFilter(): void
    {
        $this->resetPage('ordersPage');
    }

    /**
     * Switch active tab between Import and Master Data Explorer.
     */
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage('logsPage');
        $this->resetPage('ordersPage');
    }

    /**
     * Handle file selection, validate, store temporarily, and trigger preview modal (US-2.2 & US-2.3).
     */
    public function updatedFile(SapImportService $importService): void
    {
        $this->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv,txt',
                'max:10240', // Maksimal 10 MB
            ],
        ], [
            'file.required' => 'Silakan pilih berkas spreadsheet SAP untuk diunggah.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.mimes' => 'Format berkas tidak didukung. Harap unggah berkas spreadsheet dengan ekstensi .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas melebihi batas maksimum 10 MB. Harap perkecil berkas sebelum mengunggah.',
        ]);

        if (! $this->file instanceof UploadedFile) {
            return;
        }

        try {
            $this->originalFileName = $this->file->getClientOriginalName();
            $path = $this->file->store('sap-temp', 'local');
            if (! is_string($path)) {
                Flux::toast('Gagal menyimpan berkas sementara pada server.', variant: 'danger');

                return;
            }

            $this->storedTempPath = $path;

            $fullPath = storage_path('app/private/'.$path);
            if (! file_exists($fullPath)) {
                $fullPath = storage_path('app/'.$path);
            }

            $preview = $importService->parsePreview($fullPath, 20);

            $this->totalRowsCount = $preview['total_rows'];
            $this->validRowsCount = $preview['valid_rows_count'];
            $this->invalidRowsCount = $preview['invalid_rows_count'];
            $this->previewRows = $preview['preview_rows'];
            $this->previewErrors = $preview['all_errors'];
            $this->hasPreview = true;

            Flux::modal('import-preview')->show();
        } catch (Throwable $e) {
            $this->cancelImport();
            Flux::toast('Gagal membaca berkas spreadsheet: '.$e->getMessage(), variant: 'danger');
        }
    }

    /**
     * Commit the parsed records into production database (US-2.3).
     */
    public function confirmImport(SapImportService $importService, bool $skipInvalid = true): void
    {
        if (! $this->storedTempPath) {
            Flux::toast('Tidak ada berkas yang siap diimpor. Silakan unggah berkas kembali.', variant: 'danger');
            Flux::modal('import-preview')->close();

            return;
        }

        $fullPath = storage_path('app/private/'.$this->storedTempPath);
        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/'.$this->storedTempPath);
        }

        if (! file_exists($fullPath)) {
            Flux::toast('Berkas sementara tidak ditemukan pada server. Harap unggah kembali berkas Anda.', variant: 'danger');
            Flux::modal('import-preview')->close();
            $this->cancelImport();

            return;
        }

        $this->isProcessing = true;

        try {
            /** @var User $currentUser */
            $currentUser = Auth::user();

            $result = $importService->import(
                $fullPath,
                $this->originalFileName ?? 'sap-export.xlsx',
                $currentUser,
                $skipInvalid
            );

            // Clean up temporary stored file
            Storage::disk('local')->delete($this->storedTempPath);

            Flux::modal('import-preview')->close();
            $this->reset(['file', 'storedTempPath', 'originalFileName', 'hasPreview', 'previewRows', 'previewErrors', 'totalRowsCount', 'validRowsCount', 'invalidRowsCount', 'isProcessing']);

            if ($result['skipped'] > 0) {
                Flux::toast("Impor sebagian selesai: {$result['imported']} data baru ditambahkan, {$result['updated']} data diperbarui, {$result['skipped']} baris dilewati.", variant: 'warning');
            } else {
                Flux::toast("Berhasil memperbarui {$result['imported']} data baru dan {$result['updated']} data yang sudah ada dari SAP.", variant: 'success');
            }
        } catch (Throwable $e) {
            $this->isProcessing = false;
            Flux::toast('Terjadi kesalahan saat menyimpan data ke database: '.$e->getMessage(), variant: 'danger');
        }
    }

    /**
     * Cancel the import process and cleanup temporary uploaded files.
     */
    public function cancelImport(): void
    {
        if ($this->storedTempPath) {
            Storage::disk('local')->delete($this->storedTempPath);
        }

        $this->reset(['file', 'storedTempPath', 'originalFileName', 'hasPreview', 'previewRows', 'previewErrors', 'totalRowsCount', 'validRowsCount', 'invalidRowsCount', 'isProcessing']);
        Flux::modal('import-preview')->close();
    }

    /**
     * Download sample Excel template matching standard SAP export format.
     */
    public function downloadTemplate(SapImportService $importService): BinaryFileResponse|StreamedResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'sap_template_').'.xlsx';
        $importService->generateSampleXlsx($tempFile);

        return response()->download($tempFile, 'template_sap_master_data.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Open error log modal for historical import inspection (US-2.4 helper).
     */
    public function showLogErrorDetail(int $logId): void
    {
        $this->selectedLog = SapImportLog::findOrFail($logId);
        $this->selectedLogId = $logId;

        Flux::modal('log-error-detail')->show();
    }
}
