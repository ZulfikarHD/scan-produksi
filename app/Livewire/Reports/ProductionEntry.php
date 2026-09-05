<?php

namespace App\Livewire\Reports;

use App\Enums\ReportStatus;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Konsol Pemindaian Produksi')]
class ProductionEntry extends Component
{
    public DailyReport $report;

    // State untuk konsol pemindaian barcode & input kuantitas kemas
    public string $scanPo = '';

    public ?ProductionOrder $scannedOrder = null;

    public ?int $productionQty = null;

    public string $itemNotes = '';

    /**
     * Render view konsol pemindaian dan tabel rekapitulasi PO.
     */
    public function render(): View
    {
        $items = DailyReportItem::where('daily_report_id', $this->report->id)
            ->latest('id')
            ->get();

        return view('livewire.reports.production-entry', [
            'items' => $items,
        ]);
    }

    /**
     * Inisialisasi data laporan pada saat halaman dibuka.
     */
    public function mount(DailyReport $report): void
    {
        $this->report = $report->load('user', 'supervisor');
    }

    /**
     * Tahap 1: Pindai Barcode & Cari Data Master Order SAP (Code 128 lookup).
     */
    public function lookupPo(): void
    {
        // Cek status laporan: jika bukan draft dan bukan supervisor, tolak pemindaian
        if ($this->report->status !== ReportStatus::DRAFT && ! $this->isSupervisor()) {
            Flux::toast('Laporan sudah diselesaikan dan terkunci dari penambahan item baru.', variant: 'warning');
            $this->scanPo = '';

            return;
        }

        $po = trim($this->scanPo);

        if ($po === '') {
            return;
        }

        // Cari master order di tabel production_orders
        $order = ProductionOrder::where('po_number', $po)->first();

        if (! $order) {
            Flux::toast("No PO '{$po}' tidak ditemukan pada Master Data SAP. Pastikan file SAP sudah diimpor.", variant: 'danger');
            $this->dispatch('play-sound', ['type' => 'error']);
            $this->scanPo = '';
            $this->scannedOrder = null;
            $this->dispatch('focus-scan-input');

            return;
        }

        // Deteksi apakah No PO sudah pernah discan sebelumnya dalam sesi laporan ini
        $alreadyScanned = DailyReportItem::where('daily_report_id', $this->report->id)
            ->where('po_number', $po)
            ->exists();

        if ($alreadyScanned) {
            Flux::toast("Peringatan: No PO '{$po}' sudah pernah dicatat dalam sesi laporan ini.", variant: 'warning');
        }

        $this->scannedOrder = $order;
        $this->productionQty = $order->target_quantity > 0 ? $order->target_quantity : null;

        $this->dispatch('play-sound', ['type' => 'success']);
        // Pindahkan autofocus kursor seketika ke input kuantitas kemas
        $this->dispatch('focus-qty-input');
    }

    /**
     * Tahap 2: Simpan Baris Produksi & Kembalikan Fokus ke Input Barcode.
     */
    public function saveEntry(): void
    {
        // Cek status laporan: jika bukan draft dan bukan supervisor, tolak penyimpanan
        if ($this->report->status !== ReportStatus::DRAFT && ! $this->isSupervisor()) {
            Flux::toast('Laporan sudah diselesaikan dan tidak dapat ditambahkan item baru.', variant: 'warning');

            return;
        }

        if (! $this->scannedOrder) {
            $this->lookupPo();

            return;
        }

        $qty = (int) $this->productionQty;

        if ($qty <= 0) {
            Flux::toast('Jumlah kemas harus lebih besar dari 0 lembar.', variant: 'warning');
            $this->dispatch('focus-qty-input');

            return;
        }

        // Simpan item dan sinkronisasi agregat header dalam satu transaksi atomik (ACID)
        DB::transaction(function () use ($qty) {
            DailyReportItem::create([
                'daily_report_id' => $this->report->id,
                'production_order_id' => $this->scannedOrder->id,
                'po_number' => $this->scannedOrder->po_number,
                'product_name' => $this->scannedOrder->product_name,
                'specification' => $this->scannedOrder->specification,
                'machine_number' => $this->scannedOrder->machine_number,
                'target_quantity' => $this->scannedOrder->target_quantity,
                'good_quantity' => $qty,
                'reject_quantity' => 0,
                'unit' => $this->scannedOrder->unit ?? 'lembar',
                'notes' => $this->itemNotes ?: null,
                'scanned_at' => now(),
            ]);

            $this->refreshTotals();
        });

        $poNumber = $this->scannedOrder->po_number;

        $this->reset(['scanPo', 'scannedOrder', 'productionQty', 'itemNotes']);

        $this->dispatch('play-sound', ['type' => 'success']);
        Flux::toast("Data item PO '{$poNumber}' berhasil ditambahkan ke laporan.", variant: 'success');

        // Kembalikan fokus kursor ke input scan barcode untuk PO berikutnya
        $this->dispatch('focus-scan-input');
    }

    /**
     * Tahap 3: Edit Kuantitas Langsung di Baris Tabel (Inline Edit Tanpa Modal).
     */
    public function updateItemQuantity(int $itemId, int|string $newQty): void
    {
        // Cek status laporan: jika bukan draft dan bukan supervisor, tolak perubahan
        if ($this->report->status !== ReportStatus::DRAFT && ! $this->isSupervisor()) {
            Flux::toast('Laporan sudah diselesaikan dan terkunci dari pengeditan.', variant: 'warning');

            return;
        }

        $qty = (int) $newQty;

        if ($qty <= 0) {
            Flux::toast('Jumlah kemas harus berupa angka positif lebih dari 0.', variant: 'warning');

            return;
        }

        $item = null;

        DB::transaction(function () use ($itemId, $qty, &$item) {
            $item = DailyReportItem::where('daily_report_id', $this->report->id)->findOrFail($itemId);
            $item->update(['good_quantity' => $qty]);
            $this->refreshTotals();
        });

        Flux::toast("Jumlah PO {$item->po_number} diperbarui menjadi {$qty} lembar.", variant: 'success');
    }

    /**
     * Hapus baris PO dari laporan.
     */
    public function deleteItem(int $itemId): void
    {
        // Cek status laporan: jika bukan draft dan bukan supervisor, tolak penghapusan
        if ($this->report->status !== ReportStatus::DRAFT && ! $this->isSupervisor()) {
            Flux::toast('Laporan sudah diselesaikan dan baris item tidak dapat dihapus.', variant: 'warning');

            return;
        }

        DB::transaction(function () use ($itemId) {
            DailyReportItem::where('daily_report_id', $this->report->id)->where('id', $itemId)->delete();
            $this->refreshTotals();
        });

        Flux::toast('Baris item PO berhasil dihapus dari laporan.', variant: 'info');
    }

    /**
     * Finalisasi laporan (mengubah status Draft -> Terkirim).
     */
    public function finalizeReport(): void
    {
        $itemCount = DailyReportItem::where('daily_report_id', $this->report->id)->count();

        if ($itemCount === 0) {
            Flux::toast('Laporan belum memiliki item PO yang dipindai. Silakan scan minimal 1 PO sebelum menyelesaikan laporan.', variant: 'warning');

            return;
        }

        $this->report->update([
            'status' => ReportStatus::SUBMITTED,
        ]);
        $this->report->refresh();

        Flux::toast("Laporan {$this->report->report_code} berhasil diselesaikan dengan total {$this->report->total_good_quantity} lembar.", variant: 'success');
    }

    /**
     * Buka kembali sesi laporan ke status Draft (Khusus Supervisor).
     */
    public function reopenReport(): void
    {
        if (! $this->isSupervisor()) {
            Flux::toast('Hanya Supervisor yang berhak membuka kembali sesi laporan yang telah diselesaikan.', variant: 'danger');

            return;
        }

        $this->report->update([
            'status' => ReportStatus::DRAFT,
        ]);
        $this->report->refresh();

        Flux::toast("Sesi laporan {$this->report->report_code} berhasil dibuka kembali (Draft).", variant: 'success');
        $this->dispatch('focus-scan-input');
    }

    /**
     * Hitung ulang akumulasi total item, kuantitas baik, dan reject dalam satu query atomik.
     */
    protected function refreshTotals(): void
    {
        $totals = DailyReportItem::where('daily_report_id', $this->report->id)
            ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(good_quantity), 0) as total_good, COALESCE(SUM(reject_quantity), 0) as total_reject')
            ->first();

        $this->report->update([
            'total_items_count' => (int) ($totals->total_items ?? 0),
            'total_good_quantity' => (int) ($totals->total_good ?? 0),
            'total_reject_quantity' => (int) ($totals->total_reject ?? 0),
        ]);
        $this->report->refresh();
    }

    /**
     * Periksa apakah pengguna yang sedang login memiliki peran Supervisor.
     */
    private function isSupervisor(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->isSupervisor();
    }
}
