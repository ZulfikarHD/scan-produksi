<?php

namespace App\Livewire\Reports;

use App\Enums\ReportStatus;
use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use Flux\Flux;
use Illuminate\Contracts\View\View;
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

        DailyReportItem::create([
            'daily_report_id' => $this->report->id,
            'production_order_id' => $this->scannedOrder->id,
            'po_number' => $this->scannedOrder->po_number,
            'product_name' => $this->scannedOrder->product_name,
            'specification' => $this->scannedOrder->specification,
            'machine_number' => $this->scannedOrder->machine_number,
            'target_quantity' => $this->scannedOrder->target_quantity,
            'good_quantity' => $qty,
            'unit' => $this->scannedOrder->unit ?? 'lembar',
            'notes' => $this->itemNotes ?: null,
            'scanned_at' => now(),
        ]);

        $this->refreshTotals();
        $this->reset(['scanPo', 'scannedOrder', 'productionQty', 'itemNotes']);

        $this->dispatch('play-sound', ['type' => 'success']);
        Flux::toast('Data item PO berhasil ditambahkan ke laporan.', variant: 'success');

        // Kembalikan fokus kursor ke input scan barcode untuk PO berikutnya
        $this->dispatch('focus-scan-input');
    }

    /**
     * Tahap 3: Edit Kuantitas Langsung di Baris Tabel (Inline Edit Tanpa Modal).
     */
    public function updateItemQuantity(int $itemId, int|string $newQty): void
    {
        $qty = (int) $newQty;

        if ($qty <= 0) {
            Flux::toast('Jumlah kemas harus berupa angka positif lebih dari 0.', variant: 'warning');

            return;
        }

        $item = DailyReportItem::where('daily_report_id', $this->report->id)->findOrFail($itemId);
        $item->update(['good_quantity' => $qty]);

        $this->refreshTotals();
        Flux::toast("Jumlah PO {$item->po_number} diperbarui menjadi {$qty} lembar.", variant: 'success');
    }

    /**
     * Hapus baris PO dari laporan.
     */
    public function deleteItem(int $itemId): void
    {
        DailyReportItem::where('daily_report_id', $this->report->id)->where('id', $itemId)->delete();
        $this->refreshTotals();
        Flux::toast('Baris item PO berhasil dihapus dari laporan.', variant: 'info');
    }

    /**
     * Finalisasi laporan (mengubah status Draft -> Terkirim).
     */
    public function finalizeReport(): void
    {
        $this->report->update([
            'status' => ReportStatus::SUBMITTED,
        ]);

        Flux::toast("Laporan {$this->report->report_code} berhasil diselesaikan.", variant: 'success');
    }

    /**
     * Hitung ulang akumulasi total item PO dan kuantitas hasil kemas.
     */
    protected function refreshTotals(): void
    {
        $totalItems = DailyReportItem::where('daily_report_id', $this->report->id)->count();
        $totalGood = (int) DailyReportItem::where('daily_report_id', $this->report->id)->sum('good_quantity');

        $this->report->update([
            'total_items_count' => $totalItems,
            'total_good_quantity' => $totalGood,
        ]);
        $this->report->refresh();
    }
}
