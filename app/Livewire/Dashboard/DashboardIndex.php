<?php

namespace App\Livewire\Dashboard;

use App\Models\DailyReport;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Beranda & Analitik Produksi')]
class DashboardIndex extends Component
{
    #[Url(as: 'range')]
    public int $dateRange = 14;

    #[Url(as: 'type')]
    public string $chartType = 'area';

    /**
     * Render tampilan utama Beranda & Analitik Produksi (Phase 05).
     */
    public function render(DashboardAnalyticsService $analyticsService): View
    {
        $metrics = $analyticsService->getTodayMetrics();
        $chartData = $analyticsService->getProductionTrend($this->dateRange);
        $shiftDistribution = $analyticsService->getShiftDistribution($this->dateRange);
        $topMachines = $analyticsService->getTopMachines($this->dateRange, 5);
        $recentReports = DailyReport::with(['user', 'supervisor'])
            ->latest('report_date')
            ->latest('id')
            ->take(5)
            ->get();

        $greeting = $this->getDynamicGreeting();
        /** @var User|null $user */
        $user = Auth::user();

        return view('livewire.dashboard.index', [
            'metrics' => $metrics,
            'chartData' => $chartData,
            'shiftDistribution' => $shiftDistribution,
            'topMachines' => $topMachines,
            'recentReports' => $recentReports,
            'greeting' => $greeting,
            'user' => $user,
        ]);
    }

    /**
     * Inisialisasi parameter awal saat komponen dimuat.
     */
    public function mount(): void
    {
        if (! in_array($this->dateRange, [7, 14, 30], true)) {
            $this->dateRange = 14;
        }

        if (! in_array($this->chartType, ['area', 'line', 'bar'], true)) {
            $this->chartType = 'area';
        }
    }

    /**
     * Ubah rentang waktu filter grafik analitik (7, 14, atau 30 hari).
     */
    public function setDateRange(int $range): void
    {
        if (in_array($range, [7, 14, 30], true)) {
            $this->dateRange = $range;
        }
    }

    /**
     * Ubah tipe visualisasi grafik (Area, Garis, atau Batang).
     */
    public function setChartType(string $type): void
    {
        if (in_array($type, ['area', 'line', 'bar'], true)) {
            $this->chartType = $type;
        }
    }

    /**
     * Segarkan data metrik dan bersihkan cache analitik.
     */
    public function refreshData(DashboardAnalyticsService $analyticsService): void
    {
        $analyticsService->clearCache();
        Flux::toast('Data analitik berhasil diperbarui.', variant: 'success');
    }

    /**
     * Buat teks salam pembuka dinamis berdasarkan jam lokal WIB.
     */
    protected function getDynamicGreeting(): string
    {
        $hour = (int) now('Asia/Jakarta')->format('H');

        if ($hour >= 4 && $hour < 11) {
            return 'Selamat Pagi';
        }

        if ($hour >= 11 && $hour < 15) {
            return 'Selamat Siang';
        }

        if ($hour >= 15 && $hour < 19) {
            return 'Selamat Sore';
        }

        return 'Selamat Malam';
    }
}
