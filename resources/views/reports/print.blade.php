<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produksi Fisik - {{ $report->report_code }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #111827;
            background: #ffffff;
            padding: 24px;
        }

        .no-print-bar {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-print {
            background-color: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: #047857;
        }

        .btn-close {
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            margin-left: 8px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header h1 {
            font-size: 15pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 11pt;
            font-weight: 500;
            color: #4b5563;
            margin: 2px 0 6px;
        }

        .header h3 {
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            margin-top: 6px;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            font-size: 10pt;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #374151;
            padding: 6px 8px;
        }

        .data-table th {
            background-color: #f3f4f6;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }

        .signatures {
            width: 100%;
            margin-top: 28px;
            font-size: 10pt;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .sign-space {
            height: 65px;
        }

        .status-stamp {
            display: inline-block;
            border: 2px solid #059669;
            color: #059669;
            padding: 2px 10px;
            font-weight: 800;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }

            body {
                padding: 0;
                background: #ffffff;
            }

            .no-print-bar {
                display: none !important;
            }

            .data-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Pratinjau Cetak Dokumen Fisik</strong>
            <span style="color: #6b7280; font-size: 12px; margin-left: 8px;">(Format standar A4 portrait)</span>
        </div>
        <div>
            <button class="btn-print" onclick="window.print()">
                Cetak Dokumen Sekarang (Print)
            </button>
            <button class="btn-close" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>

    <div class="header">
        <h1>PT PERCETAKAN DOKUMEN SEKURITI</h1>
        <h2>Unit Pengemasan — Gedung Produksi Ciampel Karawang</h2>
        <h3>LAPORAN DOKUMENTASI HASIL PRODUKSI HARIAN</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 16%;"><strong>Kode Laporan</strong></td>
            <td style="width: 34%;">: <span class="font-mono font-bold">{{ $report->report_code }}</span></td>
            <td style="width: 16%;"><strong>Petugas Kemas</strong></td>
            <td style="width: 34%;">: {{ $report->user?->name ?? 'Petugas' }} {{ $report->user?->nip ? '('.$report->user->nip.')' : '' }}</td>
        </tr>
        <tr>
            <td><strong>Kategori Seri</strong></td>
            <td>: {{ $report->product_series->label() }}</td>
            <td><strong>Shift Kerja</strong></td>
            <td>: {{ $report->shift->label() }} ({{ $report->shift->timeRange() }})</td>
        </tr>
        <tr>
            <td><strong>Tanggal Kerja</strong></td>
            <td>: {{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d F Y') }}</td>
            <td><strong>Status Dokumen</strong></td>
            <td>: <span class="status-stamp">{{ $report->status->label() }}</span></td>
        </tr>
        @if($report->notes)
            <tr>
                <td><strong>Catatan Shift</strong></td>
                <td colspan="3">: <em>{{ $report->notes }}</em></td>
            </tr>
        @endif
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">No PO / Barcode</th>
                <th style="width: 32%;">Nama Produk & Spesifikasi</th>
                <th style="width: 10%;">Mesin</th>
                <th style="width: 15%;">Target SAP</th>
                <th style="width: 18%;">Hasil Kemas Baik</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-mono font-bold">{{ $item->po_number }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->specification)
                            <br><small style="color: #4b5563;">{{ $item->specification }}</small>
                        @endif
                    </td>
                    <td class="text-center font-mono">{{ $item->machine_number ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($item->target_quantity, 0, ',', '.') }} {{ $item->unit }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($item->good_quantity, 0, ',', '.') }} {{ $item->unit }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 24px; color: #6b7280;">
                        Tidak ada item PO tercatat dalam sesi laporan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($report->items->isNotEmpty())
            @php
                $totalTarget = $report->items->sum('target_quantity');
                $totalGood = $report->items->sum('good_quantity');
                $realization = $totalTarget > 0 ? round(($totalGood / $totalTarget) * 100, 1) : 100;
            @endphp
            <tfoot>
                <tr class="font-bold" style="background-color: #f3f4f6;">
                    <td colspan="4" class="text-right">TOTAL AKUMULASI ({{ $report->items->count() }} ORDER)</td>
                    <td class="text-right font-mono">{{ number_format($totalTarget, 0, ',', '.') }} lbr</td>
                    <td class="text-right font-mono">{{ number_format($totalGood, 0, ',', '.') }} lbr</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="signatures">
        <tr>
            <td>
                <p>Dibuat oleh (Petugas Kemas):</p>
                <div class="sign-space"></div>
                <p class="font-bold" style="text-decoration: underline;">{{ $report->user?->name ?? '............................................' }}</p>
                <p style="font-size: 9pt; color: #4b5563;">NIP: {{ $report->user?->nip ?? '............................................' }}</p>
            </td>
            <td>
                <p>Mengetahui (Kepala Unit / Supervisor):</p>
                <div class="sign-space"></div>
                <p class="font-bold" style="text-decoration: underline;">{{ $report->supervisor?->name ?? '............................................' }}</p>
                <p style="font-size: 9pt; color: #4b5563;">NIP: {{ $report->supervisor?->nip ?? '............................................' }}</p>
            </td>
        </tr>
    </table>

</body>
</html>
