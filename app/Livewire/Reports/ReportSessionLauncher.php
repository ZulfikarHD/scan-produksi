<?php

namespace App\Livewire\Reports;

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\WorkShift;
use App\Helpers\ShiftHelper;
use App\Models\DailyReport;
use App\Services\ReportCodeGenerator;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Inisiasi Sesi Laporan')]
class ReportSessionLauncher extends Component
{
    public string $report_date = '';

    public string $product_series = 'pcht';

    public string $shift = 'shift_1';

    public string $notes = '';

    /**
     * Render halaman inisiasi sesi laporan harian.
     */
    public function render(): View
    {
        $currentDate = $this->report_date ?: Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $previewCode = ReportCodeGenerator::generateWithShift($this->product_series, $currentDate, $this->shift);

        // Cek apakah sesi laporan untuk kombinasi tanggal, seri produk, dan shift sudah tercatat
        $existingReport = DailyReport::where('product_series', $this->product_series)
            ->whereDate('report_date', $currentDate)
            ->where('shift', $this->shift)
            ->first();

        // Daftar riwayat sesi laporan hari ini untuk akses cepat
        $todayReports = DailyReport::with('user')
            ->whereDate('report_date', $currentDate)
            ->latest('daily_report_id')
            ->get();

        return view('livewire.reports.session-launcher', [
            'seriesCases' => ProductSeries::cases(),
            'shiftCases' => WorkShift::cases(),
            'previewCode' => $previewCode,
            'existingReport' => $existingReport,
            'todayReports' => $todayReports,
        ]);
    }

    /**
     * Inisialisasi nilai awal tanggal (WIB) dan shift aktif saat form dibuka.
     */
    public function mount(): void
    {
        $this->report_date = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $shiftInfo = ShiftHelper::getCurrentShift();
        $this->shift = 'shift_'.$shiftInfo['number'];
        $this->product_series = ProductSeries::PCHT->value;
    }

    /**
     * Buka sesi pemindaian: lanjutkan jika sudah ada atau buat record baru.
     */
    public function launchSession(): ?RedirectResponse
    {
        $validated = $this->validate([
            'report_date' => ['required', 'date'],
            'product_series' => ['required', 'string', 'in:pcht,mmea,hptl'],
            'shift' => ['required', 'string', 'in:shift_1,shift_2,shift_3'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'report_date.required' => 'Tanggal laporan wajib diisi.',
            'product_series.required' => 'Kategori seri produk wajib dipilih.',
            'shift.required' => 'Shift kerja wajib dipilih.',
        ]);

        // Cek ketersediaan sesi laporan
        $existingReport = DailyReport::where('product_series', $validated['product_series'])
            ->whereDate('report_date', $validated['report_date'])
            ->where('shift', $validated['shift'])
            ->first();

        if ($existingReport) {
            Flux::toast("Sesi laporan {$existingReport->report_code} ditemukan. Membuka sesi aktif...", variant: 'info');

            return redirect()->route('reports.entry', ['report' => $existingReport->report_code]);
        }

        // Generate format kode laporan standar
        $code = ReportCodeGenerator::generateWithShift(
            $validated['product_series'],
            $validated['report_date'],
            $validated['shift']
        );

        if (DailyReport::where('report_code', $code)->exists()) {
            $code = $code.'-'.strtoupper(substr(uniqid(), -4));
        }

        // Lindungi dari race condition TOCTOU saat dua operator membuka sesi yang sama secara bersamaan
        try {
            $report = DailyReport::create([
                'report_code' => $code,
                'product_series' => $validated['product_series'],
                'report_date' => $validated['report_date'],
                'shift' => $validated['shift'],
                'user_id' => Auth::id() ?? 1,
                'status' => ReportStatus::DRAFT,
                'notes' => $validated['notes'] ?: null,
            ]);
        } catch (QueryException $e) {
            $existingReport = DailyReport::where('product_series', $validated['product_series'])
                ->whereDate('report_date', $validated['report_date'])
                ->where('shift', $validated['shift'])
                ->first();

            if ($existingReport) {
                Flux::toast("Sesi laporan {$existingReport->report_code} sudah dibuka oleh operator lain. Mengarahkan ke sesi aktif...", variant: 'info');

                return redirect()->route('reports.entry', ['report' => $existingReport->report_code]);
            }

            throw $e;
        }

        Flux::toast("Sesi laporan baru {$report->report_code} berhasil dibuat.", variant: 'success');

        return redirect()->route('reports.entry', ['report' => $report->report_code]);
    }
}
