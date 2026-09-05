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
#[Title('Rincian Laporan Produksi')]
class ReportDetail extends Component
{
    public DailyReport $report;

    /**
     * Render tampilan detail rekapitulasi laporan, rincian item PO, dan status audit verifikasi.
     */
    public function render(): View
    {
        $items = DailyReportItem::where('daily_report_id', $this->report->id)
            ->oldest('daily_report_item_id')
            ->get();

        $totalTarget = (int) $items->sum('target_quantity');
        $totalGood = (int) $items->sum('good_quantity');
        $totalReject = (int) $items->sum('reject_quantity');

        $realizationRate = $totalTarget > 0 ? round(($totalGood / $totalTarget) * 100, 1) : 100.0;
        $rejectRate = ($totalGood + $totalReject) > 0 ? round(($totalReject / ($totalGood + $totalReject)) * 100, 2) : 0.0;

        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $canVerify = $currentUser && $currentUser->isSupervisor() && $this->report->status !== ReportStatus::VERIFIED;
        $canEdit = $currentUser && $currentUser->can('update', $this->report);
        $canDelete = $currentUser && $currentUser->can('delete', $this->report);

        return view('livewire.reports.show', [
            'items' => $items,
            'totalTarget' => $totalTarget,
            'totalGood' => $totalGood,
            'totalReject' => $totalReject,
            'realizationRate' => $realizationRate,
            'rejectRate' => $rejectRate,
            'canVerify' => $canVerify,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }

    /**
     * Inisialisasi data laporan dan muat relasi pengguna serta supervisor.
     */
    public function mount(DailyReport $report): void
    {
        $this->report = $report->load(['user', 'supervisor']);
    }

    /**
     * Buka modal dialog verifikasi persetujuan resmi (Khusus Supervisor).
     */
    public function openVerifyModal(): void
    {
        $this->authorize('verify', $this->report);

        $this->dispatch('open-modal', 'verify-report-confirm');
    }

    /**
     * Eksekusi verifikasi laporan: ubah status ke Terverifikasi hanya jika status masih Terkirim.
     */
    public function verifyReport(): void
    {
        $this->authorize('verify', $this->report);

        // Update atomik dengan precondition status SUBMITTED (mencegah race / double-verify)
        $updated = DailyReport::where('daily_report_id', $this->report->id)
            ->where('status', ReportStatus::SUBMITTED)
            ->update([
                'status' => ReportStatus::VERIFIED,
                'supervisor_id' => Auth::id(),
                'verified_at' => now(),
            ]);

        $this->report->refresh();
        $this->report->load(['user', 'supervisor']);
        $this->dispatch('close-modal', 'verify-report-confirm');

        if (! $updated) {
            Flux::toast("Gagal memverifikasi: Laporan {$this->report->report_code} belum diselesaikan atau sudah diverifikasi sebelumnya.", variant: 'warning');

            return;
        }

        Flux::toast("Laporan {$this->report->report_code} berhasil diverifikasi dan dikunci secara resmi.", variant: 'success');
    }

    /**
     * Buka modal konfirmasi hapus laporan (Khusus Supervisor).
     */
    public function openDeleteModal(): void
    {
        $this->authorize('delete', $this->report);

        $this->dispatch('open-modal', 'delete-report-confirm');
    }

    /**
     * Eksekusi penghapusan berkas laporan.
     */
    public function deleteReport(): void
    {
        $this->authorize('delete', $this->report);

        $reportCode = $this->report->report_code;

        DB::transaction(function () {
            // Kembalikan status akumulasi order terkait jika ada
            foreach ($this->report->items as $item) {
                if ($item->production_order_id) {
                    $order = ProductionOrder::find($item->production_order_id);
                    if ($order) {
                        $order->update([
                            'kemas_quantity' => max(0, $order->kemas_quantity - $item->good_quantity),
                        ]);
                    }
                }
            }

            $this->report->delete();
        });

        Flux::toast("Laporan {$reportCode} berhasil dihapus.", variant: 'success');
        $this->redirect(route('reports.index'));
    }
}
