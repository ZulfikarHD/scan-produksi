<?php

namespace App\Livewire\Reports;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Koreksi Laporan Produksi')]
class EditReport extends Component
{
    public DailyReport $report;

    public string $notes = '';

    /**
     * Data array baris item untuk form pengeditan reaktif.
     *
     * @var list<array{
     *     id: int|null,
     *     production_order_id: int|null,
     *     po_number: string,
     *     product_name: string,
     *     specification: string|null,
     *     machine_number: string|null,
     *     target_quantity: int,
     *     good_quantity: int,
     *     reject_quantity: int,
     *     unit: string,
     *     notes: string|null,
     *     is_deleted: bool
     * }>
     */
    public array $items = [];

    // State untuk form penambahan PO susulan
    public string $newPo = '';

    public ?ProductionOrder $newScannedOrder = null;

    public ?int $newGoodQty = null;

    public int $newRejectQty = 0;

    public string $newItemNotes = '';

    /**
     * Render formulir koreksi laporan produksi.
     */
    public function render(): View
    {
        $activeItems = array_filter($this->items, fn ($i) => ! ($i['is_deleted'] ?? false));

        $totalGood = array_sum(array_column($activeItems, 'good_quantity'));
        $totalReject = array_sum(array_column($activeItems, 'reject_quantity'));
        $totalTarget = array_sum(array_column($activeItems, 'target_quantity'));
        $activeCount = count($activeItems);

        return view('livewire.reports.edit', [
            'activeItems' => $activeItems,
            'totalGood' => $totalGood,
            'totalReject' => $totalReject,
            'totalTarget' => $totalTarget,
            'activeCount' => $activeCount,
        ]);
    }

    /**
     * Inisialisasi data laporan dan periksa kebijakan otorisasi hak edit.
     */
    public function mount(DailyReport $report): void
    {
        $this->report = $report->load(['user', 'supervisor', 'items']);

        // Verifikasi izin akses edit laporan
        $this->authorize('update', $this->report);

        $this->notes = $this->report->notes ?? '';

        $this->items = $this->report->items->map(fn (DailyReportItem $item) => [
            'id' => $item->id,
            'production_order_id' => $item->production_order_id,
            'po_number' => $item->po_number,
            'product_name' => $item->product_name,
            'specification' => $item->specification,
            'machine_number' => $item->machine_number,
            'target_quantity' => (int) $item->target_quantity,
            'good_quantity' => (int) $item->good_quantity,
            'reject_quantity' => (int) $item->reject_quantity,
            'unit' => $item->unit ?? 'lembar',
            'notes' => $item->notes ?? '',
            'is_deleted' => false,
        ])->values()->all();
    }

    /**
     * Cari No PO dari Master Data SAP untuk penambahan item susulan.
     */
    public function lookupNewPo(): void
    {
        $po = trim($this->newPo);

        if ($po === '') {
            return;
        }

        $order = ProductionOrder::where('po_number', $po)->first();

        if (! $order) {
            Flux::toast("No PO '{$po}' tidak ditemukan di database Master Data SAP.", variant: 'danger');
            $this->newScannedOrder = null;
            $this->newPo = '';

            return;
        }

        $this->newScannedOrder = $order;
        $this->newGoodQty = $order->target_quantity > 0 ? $order->target_quantity : null;
        $this->newRejectQty = 0;

        Flux::toast("Master Data PO '{$po}' ditemukan.", variant: 'info');
    }

    /**
     * Tambahkan baris PO susulan ke daftar item form koreksi.
     */
    public function addSupplementalItem(): void
    {
        if (! $this->newScannedOrder) {
            $this->lookupNewPo();

            return;
        }

        $goodQty = (int) $this->newGoodQty;
        $rejectQty = (int) $this->newRejectQty;

        if ($goodQty <= 0) {
            Flux::toast('Jumlah hasil kemas baik minimal 1 lembar.', variant: 'warning');

            return;
        }

        $this->items[] = [
            'id' => null, // null menandakan item baru yang belum tersimpan di database
            'production_order_id' => $this->newScannedOrder->id,
            'po_number' => $this->newScannedOrder->po_number,
            'product_name' => $this->newScannedOrder->product_name,
            'specification' => $this->newScannedOrder->specification,
            'machine_number' => $this->newScannedOrder->machine_number,
            'target_quantity' => (int) $this->newScannedOrder->target_quantity,
            'good_quantity' => $goodQty,
            'reject_quantity' => max(0, $rejectQty),
            'unit' => $this->newScannedOrder->unit ?? 'lembar',
            'notes' => $this->newItemNotes ?: '',
            'is_deleted' => false,
        ];

        $poNumber = $this->newScannedOrder->po_number;
        $this->reset(['newPo', 'newScannedOrder', 'newGoodQty', 'newRejectQty', 'newItemNotes']);

        Flux::toast("PO susulan '{$poNumber}' ditambahkan ke daftar koreksi.", variant: 'success');
    }

    /**
     * Tandai baris item untuk dihapus saat disimpan.
     */
    public function removeItem(int $index): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['is_deleted'] = true;
            Flux::toast('Baris PO ditandai untuk dihapus.', variant: 'info');
        }
    }

    /**
     * Simpan seluruh hasil koreksi ke database dan sinkronisasi total akumulasi.
     */
    public function saveChanges(): ?RedirectResponse
    {
        $this->authorize('update', $this->report);

        $activeItems = array_filter($this->items, fn ($i) => ! ($i['is_deleted'] ?? false));

        if (empty($activeItems)) {
            Flux::toast('Laporan harus memiliki minimal 1 item PO aktif.', variant: 'danger');

            return null;
        }

        // Validasi setiap item aktif
        foreach ($activeItems as $idx => $item) {
            if ((int) $item['good_quantity'] <= 0) {
                Flux::toast("Kuantitas kemas baik pada baris PO {$item['po_number']} harus lebih besar dari 0.", variant: 'warning');

                return null;
            }
        }

        // Simpan seluruh koreksi item dan sinkronisasi agregat dalam satu transaksi atomik
        DB::transaction(function () {
            foreach ($this->items as $itemData) {
                if (! empty($itemData['id'])) {
                    $dbItem = DailyReportItem::where('daily_report_id', $this->report->id)->find($itemData['id']);

                    if ($dbItem) {
                        if ($itemData['is_deleted']) {
                            $dbItem->delete();
                        } else {
                            $dbItem->update([
                                'good_quantity' => (int) $itemData['good_quantity'],
                                'reject_quantity' => max(0, (int) $itemData['reject_quantity']),
                                'notes' => $itemData['notes'] ?: null,
                            ]);
                        }
                    }
                } elseif (! $itemData['is_deleted']) {
                    // Item baru susulan
                    DailyReportItem::create([
                        'daily_report_id' => $this->report->id,
                        'production_order_id' => $itemData['production_order_id'],
                        'po_number' => $itemData['po_number'],
                        'product_name' => $itemData['product_name'],
                        'specification' => $itemData['specification'],
                        'machine_number' => $itemData['machine_number'],
                        'target_quantity' => (int) $itemData['target_quantity'],
                        'good_quantity' => (int) $itemData['good_quantity'],
                        'reject_quantity' => max(0, (int) $itemData['reject_quantity']),
                        'unit' => $itemData['unit'],
                        'notes' => $itemData['notes'] ?: null,
                        'scanned_at' => now(),
                    ]);
                }
            }

            $this->report->update([
                'notes' => $this->notes ?: null,
            ]);

            $this->refreshTotals();
        });

        Flux::toast("Perubahan Laporan {$this->report->report_code} berhasil disimpan.", variant: 'success');

        return redirect()->route('reports.show', ['report' => $this->report->report_code]);
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
}
