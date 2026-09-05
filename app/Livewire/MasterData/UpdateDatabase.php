<?php

namespace App\Livewire\MasterData;

use App\Enums\ProductionOrderStatus;
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

    // Master Data Explorer Filters (US-2.5)
    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $machineFilter = '';

    #[Url]
    public string $customerFilter = '';

    #[Url]
    public string $startDateFilter = '';

    #[Url]
    public string $endDateFilter = '';

    // File Upload State (US-2.2)
    public mixed $file = null;

    public ?string $storedTempPath = null;

    public ?string $originalFileName = null;

    // Preview State (US-2.3)
    public bool $hasPreview = false;

    public int $totalRowsCount = 0;

    public int $validRowsCount = 0;

    public int $invalidRowsCount = 0;

    /** @var list<array<string, mixed>> */
    public array $previewRows = [];

    /** @var list<string> */
    public array $previewErrors = [];

    public bool $isProcessing = false;

    // Error Log Inspection Modal State (US-2.4)
    public ?int $selectedLogId = null;

    public ?SapImportLog $selectedLog = null;

    // Manual Order Form State (US-2.5)
    public bool $isEditingOrder = false;

    public ?int $editingOrderId = null;

    public string $order_po_number = '';

    public string $order_no_obc = '';

    public string $order_perso_type = '';

    public string $order_seri = '';

    public string $order_product_code = '';

    public string $order_product_name = '';

    public string $order_customer_name = '';

    public string $order_specification = '';

    public string $order_paper_type = '';

    public string $order_machine_number = '';

    public int|string $order_target_quantity = 0;

    public int|string $order_rencet_quantity = 0;

    public string $order_unit = 'lembar';

    public string $order_date = '';

    public string $order_delivery_deadline = '';

    public string $order_status = 'ready';

    // Order Detail Specification Modal State (US-2.5)
    public ?int $selectedOrderId = null;

    public ?ProductionOrder $selectedOrder = null;

    // Order Deletion Confirmation Modal State (US-2.5)
    public ?int $confirmingDeleteOrderId = null;

    public string $confirmingDeleteOrderPo = '';

    /**
     * Render view Update Database dengan tab Impor Berkas SAP dan Master Data Order Produksi.
     */
    public function render(SapImportService $importService): View
    {
        $importLogs = SapImportLog::with('user')
            ->latest('sap_import_log_id')
            ->paginate(10, ['*'], 'logsPage');

        $ordersQuery = ProductionOrder::query()->with('creator');

        if ($this->search !== '') {
            $term = trim($this->search);
            $ordersQuery->where(function ($q) use ($term) {
                $q->where('po_number', 'like', "%{$term}%")
                    ->orWhere('no_obc', 'like', "%{$term}%")
                    ->orWhere('product_code', 'like', "%{$term}%")
                    ->orWhere('product_name', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%");
            });
        }

        if ($this->statusFilter !== '') {
            $ordersQuery->where('status', $this->statusFilter);
        }

        if ($this->machineFilter !== '') {
            $ordersQuery->where('machine_number', $this->machineFilter);
        }

        if ($this->customerFilter !== '') {
            $ordersQuery->where('customer_name', 'like', "%{$this->customerFilter}%");
        }

        if ($this->startDateFilter !== '' && $this->endDateFilter !== '') {
            $ordersQuery->whereBetween('order_date', [$this->startDateFilter, $this->endDateFilter]);
        } elseif ($this->startDateFilter !== '') {
            $ordersQuery->where('order_date', '>=', $this->startDateFilter);
        } elseif ($this->endDateFilter !== '') {
            $ordersQuery->where('order_date', '<=', $this->endDateFilter);
        }

        $availableMachines = ProductionOrder::query()
            ->whereNotNull('machine_number')
            ->where('machine_number', '!=', '')
            ->distinct()
            ->orderBy('machine_number')
            ->pluck('machine_number')
            ->toArray();

        $orders = $ordersQuery->latest('production_order_id')->paginate(15, ['*'], 'ordersPage');

        $latestLog = SapImportLog::latest('sap_import_log_id')->first();
        $totalOrdersCount = ProductionOrder::count();

        return view('livewire.master-data.update-database', [
            'importLogs' => $importLogs,
            'orders' => $orders,
            'availableMachines' => $availableMachines,
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
     * Reset pagination when machine filter changes on orders tab.
     */
    public function updatingMachineFilter(): void
    {
        $this->resetPage('ordersPage');
    }

    /**
     * Reset pagination when customer filter changes on orders tab.
     */
    public function updatingCustomerFilter(): void
    {
        $this->resetPage('ordersPage');
    }

    /**
     * Reset pagination when start date filter changes on orders tab.
     */
    public function updatingStartDateFilter(): void
    {
        $this->resetPage('ordersPage');
    }

    /**
     * Reset pagination when end date filter changes on orders tab.
     */
    public function updatingEndDateFilter(): void
    {
        $this->resetPage('ordersPage');
    }

    /**
     * Reset all filters on master data orders tab to default state.
     */
    public function resetOrderFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'machineFilter', 'customerFilter', 'startDateFilter', 'endDateFilter']);
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
     * Commit the parsed records into production database (US-2.3 & US-2.4).
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

            // Store permanent audit copy in private storage
            $permanentRelativePath = 'sap-imports/'.now()->format('Ymd_His').'_'.basename($this->storedTempPath);
            Storage::disk('local')->copy($this->storedTempPath, $permanentRelativePath);

            $result = $importService->import(
                $fullPath,
                $this->originalFileName ?? 'sap-export.xlsx',
                $currentUser,
                $skipInvalid,
                $permanentRelativePath
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
     * Open error log modal for historical import inspection (US-2.4).
     */
    public function showLogErrorDetail(int $logId): void
    {
        $this->selectedLog = SapImportLog::findOrFail($logId);
        $this->selectedLogId = $logId;

        Flux::modal('log-error-detail')->show();
    }

    /**
     * Download original uploaded SAP file for supervisor audit (US-2.4).
     */
    public function downloadOriginalFile(int $logId): BinaryFileResponse|StreamedResponse
    {
        $log = SapImportLog::findOrFail($logId);

        if ($log->file_path && Storage::disk('local')->exists($log->file_path)) {
            return response()->download(Storage::disk('local')->path($log->file_path), $log->file_name);
        }

        // Fallback check for absolute path if recorded
        if ($log->file_path && file_exists($log->file_path)) {
            return response()->download($log->file_path, $log->file_name);
        }

        Flux::toast('Berkas asli tidak ditemukan pada penyimpanan server.', variant: 'danger');

        return response()->download(tempnam(sys_get_temp_dir(), 'missing_'), $log->file_name);
    }

    /**
     * Download audit error log as a readable text file (US-2.4).
     */
    public function downloadAuditErrorLog(int $logId): BinaryFileResponse|StreamedResponse
    {
        $log = SapImportLog::findOrFail($logId);

        $tempFile = tempnam(sys_get_temp_dir(), 'audit_error_').'.txt';
        $content = "LAPORAN AUDIT KESALAHAN IMPOR BERKAS SAP\n";
        $content .= "========================================\n";
        $content .= "Nama Berkas   : {$log->file_name}\n";
        $content .= 'Waktu Impor   : '.($log->created_at ? $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB' : '-')."\n";
        $content .= 'Pengunggah    : '.($log->user ? $log->user->name : 'Sistem')."\n";
        $content .= "Total Baris   : {$log->total_rows}\n";
        $content .= 'Baris Sukses  : '.($log->imported_count + $log->updated_count)."\n";
        $content .= "Baris Gagal   : {$log->failed_count}\n";
        $statusLabel = match ($log->status) {
            'success' => 'Berhasil Penuh',
            'partial' => 'Sebagian Berhasil',
            'failed' => 'Gagal Diproses',
            default => ucfirst($log->status),
        };
        $content .= "Status Impor  : {$statusLabel}\n";
        $content .= "========================================\n\n";
        $content .= "RINCIAN KESALAHAN:\n";

        if (! empty($log->error_log)) {
            foreach ($log->error_log as $idx => $err) {
                $num = $idx + 1;
                $content .= "{$num}. {$err}\n";
            }
        } else {
            $content .= "Tidak ada rincian kesalahan yang tercatat.\n";
        }

        file_put_contents($tempFile, $content);

        $downloadName = 'audit_error_log_'.pathinfo($log->file_name, PATHINFO_FILENAME).'_'.now()->format('Ymd_His').'.txt';

        return response()->download($tempFile, $downloadName)->deleteFileAfterSend(true);
    }

    /**
     * Open modal dialog to create a manual emergency production order (US-2.5).
     */
    public function openCreateOrderModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'isEditingOrder',
            'editingOrderId',
            'order_po_number',
            'order_no_obc',
            'order_perso_type',
            'order_seri',
            'order_product_code',
            'order_product_name',
            'order_customer_name',
            'order_specification',
            'order_paper_type',
            'order_machine_number',
            'order_target_quantity',
            'order_rencet_quantity',
            'order_delivery_deadline',
        ]);

        $this->order_unit = 'lembar';
        $this->order_status = 'ready';
        $this->order_date = now()->toDateString();
        $this->isEditingOrder = false;

        Flux::modal('order-form')->show();
    }

    /**
     * Open modal dialog to edit an existing production order (US-2.5).
     */
    public function openEditOrderModal(int $orderId): void
    {
        $this->resetValidation();

        $order = ProductionOrder::findOrFail($orderId);

        $this->isEditingOrder = true;
        $this->editingOrderId = $order->id;
        $this->order_po_number = $order->po_number;
        $this->order_no_obc = (string) ($order->no_obc ?? '');
        $this->order_perso_type = (string) ($order->perso_type ?? '');
        $this->order_seri = (string) ($order->seri ?? '');
        $this->order_product_code = (string) ($order->product_code ?? '');
        $this->order_product_name = $order->product_name;
        $this->order_customer_name = (string) ($order->customer_name ?? '');
        $this->order_specification = (string) ($order->specification ?? '');
        $this->order_paper_type = (string) ($order->paper_type ?? '');
        $this->order_machine_number = (string) ($order->machine_number ?? '');
        $this->order_target_quantity = $order->target_quantity;
        $this->order_rencet_quantity = $order->rencet_quantity;
        $this->order_unit = $order->unit ?? 'lembar';
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->order_delivery_deadline = $order->delivery_deadline ? $order->delivery_deadline->format('Y-m-d') : '';
        $this->order_status = $order->status->value;

        Flux::modal('order-form')->show();
    }

    /**
     * Save newly created or modified production order (US-2.5).
     */
    public function saveOrder(): void
    {
        $this->validate([
            'order_po_number' => [
                'required',
                'string',
                'max:50',
                'unique:production_orders,po_number,'.($this->editingOrderId ?? 'NULL').',production_order_id',
            ],
            'order_product_name' => ['required', 'string', 'max:255'],
            'order_target_quantity' => ['required', 'integer', 'min:1'],
            'order_date' => ['required', 'date'],
            'order_delivery_deadline' => ['nullable', 'date'],
            'order_status' => ['required', 'in:ready,in_progress,completed,cancelled'],
            'order_no_obc' => ['nullable', 'string', 'max:50'],
            'order_product_code' => ['nullable', 'string', 'max:100'],
            'order_customer_name' => ['nullable', 'string', 'max:255'],
            'order_machine_number' => ['nullable', 'string', 'max:50'],
            'order_unit' => ['required', 'string', 'max:30'],
            'order_specification' => ['nullable', 'string'],
            'order_paper_type' => ['nullable', 'string', 'max:100'],
            'order_rencet_quantity' => ['nullable', 'integer', 'min:0'],
            'order_perso_type' => ['nullable', 'string', 'max:50'],
            'order_seri' => ['nullable', 'string', 'max:50'],
        ], [
            'order_po_number.required' => 'Nomor PO (Barcode) wajib diisi.',
            'order_po_number.unique' => 'Nomor PO sudah terdaftar dalam basis data produksi.',
            'order_po_number.max' => 'Nomor PO maksimal 50 karakter.',
            'order_product_name.required' => 'Nama Produk / Material wajib diisi.',
            'order_product_name.max' => 'Nama Produk maksimal 255 karakter.',
            'order_target_quantity.required' => 'Target Kuantitas Kemas wajib diisi.',
            'order_target_quantity.min' => 'Target Kuantitas Kemas minimal 1 lembar/satuan.',
            'order_target_quantity.integer' => 'Target Kuantitas Kemas harus berupa angka bulat.',
            'order_date.required' => 'Tanggal Order wajib diisi.',
            'order_date.date' => 'Format Tanggal Order tidak valid.',
            'order_delivery_deadline.date' => 'Format Batas Waktu Kirim tidak valid.',
            'order_status.required' => 'Status order produksi wajib dipilih.',
            'order_status.in' => 'Status order produksi tidak valid.',
            'order_unit.required' => 'Satuan kemas wajib dipilih.',
        ]);

        $payload = [
            'po_number' => trim($this->order_po_number),
            'no_obc' => filled($this->order_no_obc) ? trim($this->order_no_obc) : null,
            'perso_type' => filled($this->order_perso_type) ? trim($this->order_perso_type) : null,
            'seri' => filled($this->order_seri) ? trim($this->order_seri) : null,
            'product_code' => filled($this->order_product_code) ? trim($this->order_product_code) : null,
            'product_name' => trim($this->order_product_name),
            'customer_name' => filled($this->order_customer_name) ? trim($this->order_customer_name) : null,
            'specification' => filled($this->order_specification) ? trim($this->order_specification) : null,
            'paper_type' => filled($this->order_paper_type) ? trim($this->order_paper_type) : null,
            'machine_number' => filled($this->order_machine_number) ? trim($this->order_machine_number) : null,
            'target_quantity' => (int) $this->order_target_quantity,
            'rencet_quantity' => (int) ($this->order_rencet_quantity ?: 0),
            'unit' => trim($this->order_unit) ?: 'lembar',
            'order_date' => $this->order_date,
            'delivery_deadline' => filled($this->order_delivery_deadline) ? $this->order_delivery_deadline : null,
            'status' => ProductionOrderStatus::from($this->order_status),
        ];

        if ($this->editingOrderId) {
            $order = ProductionOrder::findOrFail($this->editingOrderId);
            $order->update($payload);

            Flux::toast("Data order produksi {$order->po_number} berhasil diperbarui.", variant: 'success');
        } else {
            $payload['created_by'] = Auth::id();
            $order = ProductionOrder::create($payload);

            Flux::toast("Order manual {$order->po_number} berhasil ditambahkan.", variant: 'success');
        }

        Flux::modal('order-form')->close();
        $this->reset([
            'isEditingOrder',
            'editingOrderId',
            'order_po_number',
            'order_no_obc',
            'order_perso_type',
            'order_seri',
            'order_product_code',
            'order_product_name',
            'order_customer_name',
            'order_specification',
            'order_paper_type',
            'order_machine_number',
            'order_target_quantity',
            'order_rencet_quantity',
            'order_unit',
            'order_date',
            'order_delivery_deadline',
            'order_status',
        ]);
    }

    /**
     * Show specification detail modal for a given production order (US-2.5).
     */
    public function showOrderDetail(int $orderId): void
    {
        $this->selectedOrder = ProductionOrder::with('creator')->findOrFail($orderId);
        $this->selectedOrderId = $orderId;

        Flux::modal('order-detail')->show();
    }

    /**
     * Open confirmation modal for deleting a production order (US-2.5).
     */
    public function confirmDeleteOrder(int $orderId): void
    {
        $order = ProductionOrder::findOrFail($orderId);

        $this->confirmingDeleteOrderId = $order->id;
        $this->confirmingDeleteOrderPo = $order->po_number;

        Flux::modal('delete-order-confirm')->show();
    }

    /**
     * Delete production order with status safety guardrails (US-2.5).
     */
    public function deleteOrder(): void
    {
        if (! $this->confirmingDeleteOrderId) {
            return;
        }

        $order = ProductionOrder::findOrFail($this->confirmingDeleteOrderId);

        // Guardrail: Cannot delete order that is currently in progress
        if ($order->status === ProductionOrderStatus::IN_PROGRESS) {
            Flux::modal('delete-order-confirm')->close();
            Flux::toast('Order yang sedang berjalan dalam status Sedang Diproses tidak dapat dihapus untuk mencegah inkonsistensi proses.', variant: 'danger');
            $this->reset(['confirmingDeleteOrderId', 'confirmingDeleteOrderPo']);

            return;
        }

        $poNumber = $order->po_number;
        $order->delete();

        Flux::modal('delete-order-confirm')->close();
        Flux::toast("Order produksi {$poNumber} berhasil dihapus.", variant: 'success');
        $this->reset(['confirmingDeleteOrderId', 'confirmingDeleteOrderPo']);
    }

    /**
     * Export currently filtered production orders into an Excel spreadsheet (US-2.5).
     */
    public function exportOrders(SapImportService $importService): BinaryFileResponse|StreamedResponse
    {
        $ordersQuery = ProductionOrder::query()->with('creator');

        if ($this->search !== '') {
            $term = trim($this->search);
            $ordersQuery->where(function ($q) use ($term) {
                $q->where('po_number', 'like', "%{$term}%")
                    ->orWhere('no_obc', 'like', "%{$term}%")
                    ->orWhere('product_code', 'like', "%{$term}%")
                    ->orWhere('product_name', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%");
            });
        }

        if ($this->statusFilter !== '') {
            $ordersQuery->where('status', $this->statusFilter);
        }

        if ($this->machineFilter !== '') {
            $ordersQuery->where('machine_number', $this->machineFilter);
        }

        if ($this->customerFilter !== '') {
            $ordersQuery->where('customer_name', 'like', "%{$this->customerFilter}%");
        }

        if ($this->startDateFilter !== '' && $this->endDateFilter !== '') {
            $ordersQuery->whereBetween('order_date', [$this->startDateFilter, $this->endDateFilter]);
        } elseif ($this->startDateFilter !== '') {
            $ordersQuery->where('order_date', '>=', $this->startDateFilter);
        } elseif ($this->endDateFilter !== '') {
            $ordersQuery->where('order_date', '<=', $this->endDateFilter);
        }

        $orders = $ordersQuery->latest('production_order_id')->get();

        $tempFile = tempnam(sys_get_temp_dir(), 'sap_export_orders_').'.xlsx';
        $importService->exportOrdersToXlsx($orders, $tempFile);

        $fileName = 'master_data_order_produksi_'.now()->format('Ymd_His').'.xlsx';

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
