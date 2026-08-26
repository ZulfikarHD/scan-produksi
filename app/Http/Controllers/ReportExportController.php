<?php

namespace App\Http\Controllers;

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    /**
     * Tampilkan pratinjau dokumen resmi cetak / salinan digital PDF.
     */
    public function pdf(DailyReport $report): Response
    {
        $report->load(['user', 'supervisor', 'items' => fn ($q) => $q->oldest('id')]);

        return response()->view('reports.print', [
            'report' => $report,
            'isPdfDownload' => true,
        ]);
    }

    /**
     * Ekspor rekapitulasi data laporan produksi ke format spreadsheet Excel (.xlsx) menggunakan OpenSpout streaming.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $query = DailyReport::query()
            ->with(['user', 'supervisor', 'items' => fn ($q) => $q->oldest('id')])
            ->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc');

        // Filter rentang tanggal
        $from = $request->input('from') ?: $request->input('start_date');
        $to = $request->input('to') ?: $request->input('end_date');

        if ($from) {
            $query->whereDate('report_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('report_date', '<=', $to);
        }

        // Filter seri produk
        if ($request->filled('series') && $request->input('series') !== 'all') {
            $query->where('product_series', $request->input('series'));
        } elseif ($request->filled('product_series') && $request->input('product_series') !== 'all') {
            $query->where('product_series', $request->input('product_series'));
        }

        // Filter shift kerja
        if ($request->filled('shift') && $request->input('shift') !== 'all') {
            $query->where('shift', $request->input('shift'));
        }

        // Filter status laporan
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $reports = $query->get();

        // Siapkan berkas sementara untuk streaming writer OpenSpout
        $fileName = 'rekapitulasi-laporan-produksi-'.now('Asia/Jakarta')->format('Ymd-His').'.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'export_report_');
        $xlsxPath = $tempPath.'.xlsx';
        rename($tempPath, $xlsxPath);

        $writer = new XlsxWriter;
        $writer->openToFile($xlsxPath);

        // Header kolom laporan rekapitulasi standar
        $headers = [
            'No',
            'Kode Laporan',
            'Seri Produk',
            'Tanggal Laporan',
            'Shift Kerja',
            'Petugas Kemas',
            'NP Petugas',
            'Supervisor Verifikator',
            'Waktu Verifikasi (WIB)',
            'Status Laporan',
            'No PO / Barcode',
            'Nama Produk & Spesifikasi',
            'No Mesin',
            'Target Order SAP',
            'Hasil Kemas Baik',
            'Jumlah Reject',
            'Satuan',
            'Catatan Shift',
        ];

        $writer->addRow(Row::fromValues($headers));

        $rowNumber = 1;

        foreach ($reports as $report) {
            $reportSeries = $report->product_series instanceof ProductSeries ? $report->product_series->label() : (string) $report->product_series;
            $reportShift = $report->shift instanceof WorkShift ? $report->shift->label() : (string) $report->shift;
            $reportStatus = $report->status instanceof ReportStatus ? $report->status->label() : (string) $report->status;
            $reportDate = $report->report_date ? Carbon::parse($report->report_date)->format('Y-m-d') : '-';
            $verifiedAt = $report->verified_at ? Carbon::parse($report->verified_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') : '-';
            $petugasName = $report->user?->name ?? 'Petugas';
            $petugasNip = $report->user?->nip ?? '-';
            $supervisorName = $report->supervisor?->name ?? '-';

            if ($report->items->isEmpty()) {
                $writer->addRow(Row::fromValues([
                    $rowNumber++,
                    $report->report_code,
                    $reportSeries,
                    $reportDate,
                    $reportShift,
                    $petugasName,
                    $petugasNip,
                    $supervisorName,
                    $verifiedAt,
                    $reportStatus,
                    '-',
                    '-',
                    '-',
                    0,
                    $report->total_good_quantity,
                    $report->total_reject_quantity,
                    'lembar',
                    $report->notes ?? '-',
                ]));
            } else {
                foreach ($report->items as $item) {
                    $writer->addRow(Row::fromValues([
                        $rowNumber++,
                        $report->report_code,
                        $reportSeries,
                        $reportDate,
                        $reportShift,
                        $petugasName,
                        $petugasNip,
                        $supervisorName,
                        $verifiedAt,
                        $reportStatus,
                        $item->po_number,
                        $item->product_name.($item->specification ? ' ('.$item->specification.')' : ''),
                        $item->machine_number ?? '-',
                        $item->target_quantity,
                        $item->good_quantity,
                        $item->reject_quantity,
                        $item->unit ?? 'lembar',
                        $item->notes ?: ($report->notes ?? '-'),
                    ]));
                }
            }
        }

        $writer->close();

        return response()->download($xlsxPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
