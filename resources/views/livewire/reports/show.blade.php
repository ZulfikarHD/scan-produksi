<div class="space-y-6">
    <!-- Navigasi Breadcrumbs & Toolbar Aksi Atas -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button
                variant="ghost"
                size="sm"
                icon="arrow-left"
                :href="route('reports.index')"
                wire:navigate
                data-test="btn-back-to-index"
            >
                Kembali
            </flux:button>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100" data-test="header-report-code">
                        {{ $report->report_code }}
                    </span>
                    <flux:badge size="sm" :color="$report->status->badgeColor()" data-test="header-status-badge">
                        {{ $report->status->label() }}
                    </flux:badge>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Dibuat pada {{ $report->created_at ? $report->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') : '-' }} WIB
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Toolbar -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Cetak Fisik -->
            <flux:button
                variant="filled"
                icon="printer"
                :href="route('reports.print', ['report' => $report->report_code])"
                target="_blank"
                data-test="btn-print-report"
            >
                Cetak Lembar Fisik
            </flux:button>

            <!-- Download PDF -->
            <flux:button
                variant="filled"
                icon="arrow-down-tray"
                :href="route('reports.pdf', ['report' => $report->report_code])"
                target="_blank"
                data-test="btn-download-pdf"
            >
                Download PDF
            </flux:button>

            <!-- Edit / Koreksi Laporan -->
            @if($canEdit)
                <flux:button
                    variant="subtle"
                    icon="pencil-square"
                    :href="route('reports.edit', ['report' => $report->report_code])"
                    wire:navigate
                    data-test="btn-edit-report"
                >
                    Koreksi Laporan
                </flux:button>
            @endif

            <!-- Verifikasi Laporan (Khusus Supervisor jika belum verified) -->
            @if($canVerify)
                <flux:button
                    variant="primary"
                    icon="check-circle"
                    wire:click="openVerifyModal"
                    data-test="btn-open-verify-modal"
                >
                    Verifikasi Laporan
                </flux:button>
            @endif

            <!-- Hapus Laporan (Khusus Supervisor) -->
            @if($canDelete)
                <flux:button
                    variant="danger"
                    icon="trash"
                    wire:click="openDeleteModal"
                    data-test="btn-open-delete-modal"
                >
                    Hapus
                </flux:button>
            @endif
        </div>
    </div>

    <!-- Banner Audit Trail & Verifikasi Supervisor -->
    @if($report->status === \App\Enums\ReportStatus::VERIFIED)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/30">
            <div class="flex items-start gap-3">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-xs">
                    <flux:icon.check class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-emerald-950 dark:text-emerald-100">
                            Laporan Resmi Terverifikasi
                        </span>
                        <flux:badge size="xs" color="emerald">FINAL</flux:badge>
                    </div>
                    <p class="text-xs text-emerald-800 dark:text-emerald-300 leading-relaxed">
                        Laporan telah diverifikasi oleh Kepala Unit / Supervisor 
                        <strong>{{ $report->supervisor?->name ?? 'Supervisor' }}</strong> 
                        @if($report->supervisor?->nip)
                            (NP: {{ $report->supervisor->nip }})
                        @endif
                        pada 
                        <strong>{{ $report->verified_at ? $report->verified_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') : '-' }} WIB</strong>. 
                        Dokumen ini telah berstatus resmi dan dikunci dari perubahan petugas.
                    </p>
                </div>
            </div>
        </div>
    @elseif($report->status === \App\Enums\ReportStatus::SUBMITTED)
        <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-xs">
                    <flux:icon.clock class="size-5" />
                </div>
                <div class="space-y-1">
                    <span class="text-sm font-bold text-amber-950 dark:text-amber-100">
                        Menunggu Verifikasi Supervisor
                    </span>
                    <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                        Sesi pemindaian telah diselesaikan oleh <strong>{{ $report->user?->name ?? 'Petugas' }}</strong>. 
                        Silakan periksa kebenaran rincian hasil kemas fisik sebelum memberikan persetujuan verifikasi.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/40">
            <div class="flex items-start gap-3">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-400 text-white shadow-xs">
                    <flux:icon.pencil class="size-5" />
                </div>
                <div class="space-y-1">
                    <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        Status Sesi: Draft (Sedang Dikerjakan)
                    </span>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Laporan ini masih berada dalam tahap pengisian data atau sesi pemindaian barcode aktif.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Kartu Metadata Laporan -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <!-- Seri Produk -->
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Seri Pita Cukai</span>
            <div class="mt-1 flex items-center gap-2">
                <flux:badge size="sm" :color="$report->product_series->badgeColor()">
                    {{ $report->product_series->label() }}
                </flux:badge>
            </div>
        </div>

        <!-- Tanggal & Shift -->
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Tanggal & Shift Kerja</span>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    {{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d M Y') }}
                </span>
                <flux:badge size="xs" :color="$report->shift->badgeColor()">
                    {{ $report->shift->label() }}
                </flux:badge>
            </div>
            <span class="text-[11px] text-zinc-400">{{ $report->shift->timeRange() }}</span>
        </div>

        <!-- Petugas Kemas -->
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Petugas Pembuat</span>
            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                {{ $report->user?->name ?? 'Petugas' }}
            </p>
            <span class="text-[11px] font-mono text-zinc-400">NP: {{ $report->user?->nip ?? '-' }}</span>
        </div>

        <!-- Supervisor Verifikator -->
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Supervisor Verifikator</span>
            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                {{ $report->supervisor?->name ?? 'Belum Diverifikasi' }}
            </p>
            <span class="text-[11px] text-zinc-400">
                {{ $report->verified_at ? $report->verified_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB' : '-' }}
            </span>
        </div>
    </div>

    <!-- Catatan Tambahan (Jika Ada) -->
    @if($report->notes)
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Catatan Operasional Shift:</span>
            <p class="mt-1 text-xs text-zinc-700 dark:text-zinc-300 italic">
                &ldquo;{{ $report->notes }}&rdquo;
            </p>
        </div>
    @endif

    <!-- Rekapitulasi Metrik Akumulasi -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs text-zinc-500 dark:text-zinc-400">Total Item PO</span>
            <p class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100 mt-0.5" data-test="metric-po-count">
                {{ number_format($items->count(), 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs text-zinc-500 dark:text-zinc-400">Target Order SAP</span>
            <p class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100 mt-0.5" data-test="metric-target-qty">
                {{ number_format($totalTarget, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Hasil Kemas Baik</span>
            <p class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5" data-test="metric-good-qty">
                {{ number_format($totalGood, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Hasil Reject</span>
            <p class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400 mt-0.5" data-test="metric-reject-qty">
                {{ number_format($totalReject, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Tabel Rincian Item PO Yang Dipindai -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Rincian Item Hasil Pengemasan</flux:heading>
            <span class="text-xs text-zinc-500">
                Tingkat Realisasi: <strong class="text-emerald-600 font-mono">{{ $realizationRate }}%</strong>
            </span>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column align="center">No</flux:table.column>
                <flux:table.column>No PO / Barcode</flux:table.column>
                <flux:table.column>Nama Produk & Spesifikasi Kertas</flux:table.column>
                <flux:table.column align="center">Mesin</flux:table.column>
                <flux:table.column align="end">Target SAP</flux:table.column>
                <flux:table.column align="end">Hasil Baik</flux:table.column>
                <flux:table.column align="end">Reject</flux:table.column>
                <flux:table.column>Keterangan</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($items as $idx => $item)
                    <flux:table.row :key="$item->id" data-test="detail-item-row-{{ $item->id }}">
                        <!-- Nomor Urut -->
                        <flux:table.cell align="center">
                            <span class="text-xs text-zinc-400">{{ $idx + 1 }}</span>
                        </flux:table.cell>

                        <!-- No PO -->
                        <flux:table.cell variant="strong">
                            <span class="font-mono text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $item->po_number }}
                            </span>
                        </flux:table.cell>

                        <!-- Nama Produk & Spesifikasi -->
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $item->product_name }}
                                </span>
                                @if($item->specification)
                                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                        {{ $item->specification }}
                                    </span>
                                @endif
                            </div>
                        </flux:table.cell>

                        <!-- No Mesin -->
                        <flux:table.cell align="center">
                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                {{ $item->machine_number ?? '-' }}
                            </span>
                        </flux:table.cell>

                        <!-- Target Order SAP -->
                        <flux:table.cell align="end">
                            <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                {{ number_format($item->target_quantity, 0, ',', '.') }} {{ $item->unit }}
                            </span>
                        </flux:table.cell>

                        <!-- Hasil Kemas Baik -->
                        <flux:table.cell align="end">
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($item->good_quantity, 0, ',', '.') }} {{ $item->unit }}
                            </span>
                        </flux:table.cell>

                        <!-- Reject -->
                        <flux:table.cell align="end">
                            <span class="font-mono text-xs text-amber-600 dark:text-amber-400">
                                {{ number_format($item->reject_quantity, 0, ',', '.') }} {{ $item->unit }}
                            </span>
                        </flux:table.cell>

                        <!-- Keterangan -->
                        <flux:table.cell>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item->notes ?: '-' }}
                            </span>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                            Tidak ada rincian item PO pada laporan ini.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Modal Konfirmasi Verifikasi (Khusus Supervisor) -->
    @if($canVerify)
        <flux:modal name="verify-report-confirm" class="max-w-md space-y-6">
            <div>
                <flux:heading size="lg" class="text-emerald-700 dark:text-emerald-400">Verifikasi Laporan Produksi</flux:heading>
                <flux:subheading class="mt-2 text-zinc-600 dark:text-zinc-300">
                    Apakah Anda yakin ingin memberikan persetujuan resmi verifikasi untuk Laporan <strong class="text-zinc-900 dark:text-zinc-100 font-mono">{{ $report->report_code }}</strong>?
                </flux:subheading>
                
                <div class="mt-4 rounded-lg bg-zinc-50 p-3 text-xs space-y-1.5 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Petugas Kemas:</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $report->user?->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Total PO Terscan:</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $items->count() }} Order</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Total Hasil Kemas Baik:</span>
                        <span class="font-bold text-emerald-600">{{ number_format($totalGood, 0, ',', '.') }} lembar</span>
                    </div>
                </div>

                <p class="mt-3 text-[11px] text-zinc-500 dark:text-zinc-400">
                    Setelah diverifikasi, laporan ini akan ditandai resmi dan tidak dapat diubah kembali oleh petugas.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check-circle" wire:click="verifyReport" data-test="btn-confirm-verify">
                    Ya, Verifikasi Laporan
                </flux:button>
            </div>
        </flux:modal>
    @endif

    <!-- Modal Konfirmasi Hapus Laporan (Khusus Supervisor) -->
    @if($canDelete)
        <flux:modal name="delete-report-confirm" class="max-w-md space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">Hapus Berkas Laporan Produksi</flux:heading>
                <flux:subheading class="mt-2 text-zinc-600 dark:text-zinc-300">
                    Apakah Anda yakin ingin menghapus Laporan <strong class="text-zinc-900 dark:text-zinc-100 font-mono">{{ $report->report_code }}</strong>?
                </flux:subheading>
                <div class="mt-3 p-3 rounded-lg bg-red-50 text-red-800 text-xs dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-900/60">
                    <strong>Perhatian:</strong> Tindakan ini akan menghapus seluruh data item kemas terkait dan mengembalikan status order produksi ke daftar antrean. Tindakan ini tidak dapat dibatalkan.
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteReport" data-test="btn-confirm-delete-report">
                    Hapus Permanen
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
