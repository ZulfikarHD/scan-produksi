<?php

namespace App\Livewire\Reports;

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\ProductionOrder;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Daftar Arsip Laporan Produksi')]
class ReportIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $series = '';

    #[Url]
    public string $datePreset = 'all';

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    #[Url]
    public string $shift = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $userId = '';

    public int $perPage = 10;

    // State untuk Modal Konfirmasi Hapus Laporan
    public ?int $deletingReportId = null;

    public string $deletingReportCode = '';

    public int $deletingItemCount = 0;

    // State untuk Modal Ekspor Excel
    public string $exportStartDate = '';

    public string $exportEndDate = '';

    public string $exportSeries = '';

    public string $exportShift = '';

    public string $exportStatus = '';

    /**
     * Render tampilan daftar arsip laporan dengan filter multi-kriteria dan paginasi.
     */
    public function render(): View
    {
        $query = $this->buildReportQuery();

        // Hitung total akumulasi untuk ringkasan header
        $totalReportsCount = (clone $query)->count();
        $totalGoodSheets = (int) (clone $query)->sum('total_good_quantity');
        $totalRejectSheets = (int) (clone $query)->sum('total_reject_quantity');

        $reports = $query->paginate($this->perPage);

        $operators = User::where('is_active', true)->orderBy('name')->get();

        return view('livewire.reports.index', [
            'reports' => $reports,
            'totalReportsCount' => $totalReportsCount,
            'totalGoodSheets' => $totalGoodSheets,
            'totalRejectSheets' => $totalRejectSheets,
            'operators' => $operators,
            'seriesCases' => ProductSeries::cases(),
            'shiftCases' => WorkShift::cases(),
            'statusCases' => ReportStatus::cases(),
        ]);
    }

    /**
     * Inisialisasi parameter awal saat komponen dimuat.
     */
    public function mount(): void
    {
        $this->exportStartDate = Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
        $this->exportEndDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
    }

    /**
     * Reset nomor halaman saat query pencarian atau filter diubah.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSeries(): void
    {
        $this->resetPage();
    }

    public function updatingDatePreset(): void
    {
        $this->resetPage();
    }

    public function updatingShift(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingUserId(): void
    {
        $this->resetPage();
    }

    /**
     * Terapkan preset tanggal cepat.
     */
    public function updatedDatePreset(string $preset): void
    {
        $today = Carbon::now('Asia/Jakarta');

        match ($preset) {
            'today' => [
                $this->startDate = $today->format('Y-m-d'),
                $this->endDate = $today->format('Y-m-d'),
            ],
            'yesterday' => [
                $this->startDate = $today->copy()->subDay()->format('Y-m-d'),
                $this->endDate = $today->copy()->subDay()->format('Y-m-d'),
            ],
            'last_7_days' => [
                $this->startDate = $today->copy()->subDays(6)->format('Y-m-d'),
                $this->endDate = $today->format('Y-m-d'),
            ],
            'this_month' => [
                $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d'),
                $this->endDate = $today->format('Y-m-d'),
            ],
            default => [
                $this->startDate = '',
                $this->endDate = '',
            ],
        };
    }

    /**
     * Bersihkan seluruh filter dan kembalikan ke kondisi default.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'series', 'datePreset', 'startDate', 'endDate', 'shift', 'status', 'userId']);
        $this->resetPage();
    }

    /**
     * Buka modal ekspor data ke Excel dengan parameter filter aktif.
     */
    public function openExportModal(): void
    {
        $this->exportStartDate = $this->startDate ?: Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
        $this->exportEndDate = $this->endDate ?: Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->exportSeries = $this->series;
        $this->exportShift = $this->shift;
        $this->exportStatus = $this->status;

        $this->dispatch('open-modal', 'export-excel-modal');
    }

    /**
     * Siapkan URL unduhan Excel berdasarkan pilihan di modal.
     */
    public function processExport(): void
    {
        $params = array_filter([
            'from' => $this->exportStartDate,
            'to' => $this->exportEndDate,
            'series' => $this->exportSeries,
            'shift' => $this->exportShift,
            'status' => $this->exportStatus,
        ]);

        $exportUrl = route('reports.export', $params);

        $this->dispatch('close-modal', 'export-excel-modal');
        $this->redirect($exportUrl);
    }

    /**
     * Buka modal konfirmasi hapus berkas laporan (Khusus Supervisor).
     */
    public function confirmDeleteReport(int $reportId): void
    {
        $report = DailyReport::withCount('items')->findOrFail($reportId);

        $this->authorize('delete', $report);

        $this->deletingReportId = $report->id;
        $this->deletingReportCode = $report->report_code;
        $this->deletingItemCount = $report->items_count;

        $this->dispatch('open-modal', 'delete-report-confirm');
    }

    /**
     * Eksekusi penghapusan laporan, kembalikan status order terkait, dan bersihkan item.
     */
    public function deleteReport(): void
    {
        if (! $this->deletingReportId) {
            return;
        }

        $report = DailyReport::with('items')->findOrFail($this->deletingReportId);

        $this->authorize('delete', $report);

        $reportCode = $report->report_code;

        // Kembalikan status data order produksi ke status siap kemas jika diperlukan
        foreach ($report->items as $item) {
            if ($item->production_order_id) {
                $order = ProductionOrder::find($item->production_order_id);
                if ($order) {
                    $order->update([
                        'kemas_quantity' => max(0, $order->kemas_quantity - $item->good_quantity),
                    ]);
                }
            }
        }

        // Hapus laporan dan seluruh relasi item (tercascade di database)
        $report->delete();

        $this->reset(['deletingReportId', 'deletingReportCode', 'deletingItemCount']);
        $this->dispatch('close-modal', 'delete-report-confirm');

        Flux::toast("Laporan {$reportCode} berhasil dihapus permanen.", variant: 'success');
    }

    /**
     * Bangun query Eloquent untuk penelusuran data laporan.
     */
    protected function buildReportQuery(): Builder
    {
        $query = DailyReport::query()
            ->with(['user', 'supervisor', 'items'])
            ->latest('report_date')
            ->latest('daily_report_id');

        // Filter pencarian teks bebas (Kode Laporan, No PO, atau Nama Produk)
        if (trim($this->search) !== '') {
            $searchTerm = trim($this->search);
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('report_code', 'like', "%{$searchTerm}%")
                    ->orWhere('notes', 'like', "%{$searchTerm}%")
                    ->orWhereHas('items', function (Builder $itemQuery) use ($searchTerm) {
                        $itemQuery->where('po_number', 'like', "%{$searchTerm}%")
                            ->orWhere('product_name', 'like', "%{$searchTerm}%")
                            ->orWhere('machine_number', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('user', function (Builder $userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('nip', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Filter seri produk
        if ($this->series !== '' && $this->series !== 'all') {
            $query->where('product_series', $this->series);
        }

        // Filter tanggal
        if ($this->startDate !== '') {
            $query->whereDate('report_date', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('report_date', '<=', $this->endDate);
        }

        // Filter shift kerja
        if ($this->shift !== '' && $this->shift !== 'all') {
            $query->where('shift', $this->shift);
        }

        // Filter status laporan
        if ($this->status !== '' && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Filter petugas kemas
        if ($this->userId !== '' && $this->userId !== 'all') {
            $query->where('user_id', $this->userId);
        }

        return $query;
    }
}
