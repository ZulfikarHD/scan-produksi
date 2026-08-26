<div class="space-y-6">
    <!-- Header Halaman & Statistik Ringkasan -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="font-bold tracking-tight">Laporan Produksi</flux:heading>
            <flux:subheading size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">
                Arsip hasil pengemasan dokumen sekuriti harian, verifikasi Kepala Unit, dan dokumen cetak resmi.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:button
                variant="filled"
                icon="arrow-down-tray"
                wire:click="openExportModal"
                data-test="btn-open-export-modal"
            >
                Ekspor Excel
            </flux:button>

            <flux:button
                variant="primary"
                icon="plus"
                :href="route('reports.session')"
                wire:navigate
                data-test="btn-create-report"
            >
                Buat Laporan Baru
            </flux:button>
        </div>
    </div>

    <!-- Ringkasan Statistik Rekapitulasi Cepat -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                    <flux:icon.document-text class="size-5" />
                </div>
                <div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Berkas Laporan</span>
                    <p class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100" data-test="stat-total-reports">
                        {{ number_format($totalReportsCount, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <flux:icon.check-circle class="size-5" />
                </div>
                <div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Lembar Kemas Baik</span>
                    <p class="text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400" data-test="stat-total-good">
                        {{ number_format($totalGoodSheets, 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">lembar</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <flux:icon.cube class="size-5" />
                </div>
                <div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Lembar Reject</span>
                    <p class="text-xl font-bold tracking-tight text-amber-600 dark:text-amber-400" data-test="stat-total-reject">
                        {{ number_format($totalRejectSheets, 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">lembar</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Card Container: Filter Toolbar & Data Table -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-6">
        <!-- Toolbar Filter Multi-Kriteria -->
        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Pencarian Instan -->
                <div class="lg:col-span-2">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="Cari Kode Laporan, No PO, Produk, Petugas..."
                        clearable
                        data-test="input-search-report"
                    />
                </div>

                <!-- Filter Seri Produk -->
                <div>
                    <flux:select wire:model.live="series" placeholder="Semua Seri Produk" data-test="select-series">
                        <flux:select.option value="">Semua Seri Produk</flux:select.option>
                        @foreach($seriesCases as $sc)
                            <flux:select.option :value="$sc->value">{{ $sc->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Filter Status Laporan -->
                <div>
                    <flux:select wire:model.live="status" placeholder="Semua Status" data-test="select-status">
                        <flux:select.option value="">Semua Status</flux:select.option>
                        @foreach($statusCases as $st)
                            <flux:select.option :value="$st->value">{{ $st->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <!-- Baris Filter Sekunder (Preset Tanggal, Shift, Petugas) -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <!-- Preset Tanggal -->
                <div class="w-40">
                    <flux:select wire:model.live="datePreset" data-test="select-date-preset">
                        <flux:select.option value="all">Semua Tanggal</flux:select.option>
                        <flux:select.option value="today">Hari Ini</flux:select.option>
                        <flux:select.option value="yesterday">Kemarin</flux:select.option>
                        <flux:select.option value="last_7_days">7 Hari Terakhir</flux:select.option>
                        <flux:select.option value="this_month">Bulan Ini</flux:select.option>
                    </flux:select>
                </div>

                <!-- Input Tanggal Kustom (Jika tidak 'all') -->
                <div class="flex items-center gap-1.5">
                    <flux:input
                        type="date"
                        wire:model.live="startDate"
                        size="sm"
                        placeholder="Dari"
                        data-test="input-start-date"
                    />
                    <span class="text-xs text-zinc-400">s/d</span>
                    <flux:input
                        type="date"
                        wire:model.live="endDate"
                        size="sm"
                        placeholder="Sampai"
                        data-test="input-end-date"
                    />
                </div>

                <!-- Filter Shift -->
                <div class="w-36">
                    <flux:select wire:model.live="shift" placeholder="Semua Shift" data-test="select-shift">
                        <flux:select.option value="">Semua Shift</flux:select.option>
                        @foreach($shiftCases as $sh)
                            <flux:select.option :value="$sh->value">{{ $sh->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Filter Petugas -->
                <div class="w-44">
                    <flux:select wire:model.live="userId" placeholder="Semua Petugas" data-test="select-user">
                        <flux:select.option value="">Semua Petugas</flux:select.option>
                        @foreach($operators as $op)
                            <flux:select.option :value="(string)$op->id">{{ $op->name }} ({{ $op->nip ?? 'NP -' }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                @if($search !== '' || $series !== '' || $datePreset !== 'all' || $startDate !== '' || $endDate !== '' || $shift !== '' || $status !== '' || $userId !== '')
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                        wire:click="resetFilters"
                        data-test="btn-reset-filters"
                    >
                        Reset Filter
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Tabel Arsip Laporan -->
        <flux:table :paginate="$reports">
            <flux:table.columns>
                <flux:table.column>Kode Laporan</flux:table.column>
                <flux:table.column>Seri Produk</flux:table.column>
                <flux:table.column>Tanggal</flux:table.column>
                <flux:table.column>Shift</flux:table.column>
                <flux:table.column>Petugas Kemas</flux:table.column>
                <flux:table.column align="end">Total PO</flux:table.column>
                <flux:table.column align="end">Hasil Kemas</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($reports as $report)
                    <flux:table.row :key="$report->id" data-test="report-row-{{ $report->id }}">
                        <!-- Kode Laporan -->
                        <flux:table.cell variant="strong">
                            <a
                                href="{{ route('reports.show', ['report' => $report->report_code]) }}"
                                wire:navigate
                                class="font-mono text-xs font-bold text-zinc-900 hover:text-sky-600 dark:text-zinc-100 dark:hover:text-sky-400 hover:underline"
                                data-test="link-detail-{{ $report->id }}"
                            >
                                {{ $report->report_code }}
                            </a>
                            @if($report->notes)
                                <div class="text-[11px] text-zinc-400 truncate max-w-xs font-normal" title="{{ $report->notes }}">
                                    {{ $report->notes }}
                                </div>
                            @endif
                        </flux:table.cell>

                        <!-- Seri Produk -->
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$report->product_series->badgeColor()">
                                {{ $report->product_series->shortLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Tanggal -->
                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-300">
                                {{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d M Y') }}
                            </span>
                        </flux:table.cell>

                        <!-- Shift -->
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$report->shift->badgeColor()">
                                {{ $report->shift->label() }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Petugas Kemas -->
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-zinc-900 dark:text-zinc-100">{{ $report->user?->name ?? 'Petugas' }}</span>
                                @if($report->user?->nip)
                                    <span class="text-[11px] font-mono text-zinc-400">{{ $report->user->nip }}</span>
                                @endif
                            </div>
                        </flux:table.cell>

                        <!-- Total PO -->
                        <flux:table.cell align="end">
                            <span class="font-mono text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                {{ number_format($report->total_items_count ?: $report->items->count(), 0, ',', '.') }} PO
                            </span>
                        </flux:table.cell>

                        <!-- Total Hasil Kemas Baik -->
                        <flux:table.cell align="end">
                            <div class="flex flex-col items-end">
                                <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($report->total_good_quantity, 0, ',', '.') }}
                                </span>
                                @if($report->total_reject_quantity > 0)
                                    <span class="font-mono text-[10px] text-amber-500">
                                        rej: {{ number_format($report->total_reject_quantity, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </flux:table.cell>

                        <!-- Status Verifikasi -->
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$report->status->badgeColor()">
                                {{ $report->status->label() }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Menu Aksi Cepat -->
                        <flux:table.cell align="end">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="xs" icon="ellipsis-horizontal" data-test="action-menu-{{ $report->id }}" />
                                <flux:menu>
                                    <flux:menu.item
                                        :href="route('reports.show', ['report' => $report->report_code])"
                                        icon="eye"
                                        wire:navigate
                                        data-test="btn-view-detail-{{ $report->id }}"
                                    >
                                        Lihat Rincian
                                    </flux:menu.item>

                                    <flux:menu.item
                                        :href="route('reports.print', ['report' => $report->report_code])"
                                        icon="printer"
                                        target="_blank"
                                        data-test="btn-print-report-{{ $report->id }}"
                                    >
                                        Cetak Lembar Fisik
                                    </flux:menu.item>

                                    @can('update', $report)
                                        <flux:menu.item
                                            :href="route('reports.edit', ['report' => $report->report_code])"
                                            icon="pencil-square"
                                            wire:navigate
                                            data-test="btn-edit-report-{{ $report->id }}"
                                        >
                                            Koreksi Laporan
                                        </flux:menu.item>
                                    @endcan

                                    @can('delete', $report)
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="confirmDeleteReport({{ $report->id }})"
                                            icon="trash"
                                            class="text-red-600 dark:text-red-400"
                                            data-test="btn-delete-report-{{ $report->id }}"
                                        >
                                            Hapus Laporan
                                        </flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <flux:icon.document-text class="size-8 text-zinc-400" />
                                <span class="font-medium text-sm">Tidak ada berkas laporan yang sesuai dengan kriteria filter.</span>
                                <flux:button variant="subtle" size="xs" wire:click="resetFilters" class="mt-1">
                                    Bersihkan Filter
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Modal Ekspor Excel -->
    <flux:modal name="export-excel-modal" class="max-w-md space-y-6">
        <div>
            <flux:heading size="lg">Ekspor Rekapitulasi Produksi (Excel)</flux:heading>
            <flux:subheading class="mt-1">
                Unduh rekapitulasi data hasil pengemasan dan audit laporan dalam format spreadsheet Microsoft Excel (.xlsx).
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <flux:field>
                    <flux:label>Tanggal Awal</flux:label>
                    <flux:input type="date" wire:model="exportStartDate" required data-test="export-start-date" />
                </flux:field>

                <flux:field>
                    <flux:label>Tanggal Akhir</flux:label>
                    <flux:input type="date" wire:model="exportEndDate" required data-test="export-end-date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Kategori Seri Produk</flux:label>
                <flux:select wire:model="exportSeries" data-test="export-series">
                    <flux:select.option value="">Semua Seri Produk</flux:select.option>
                    @foreach($seriesCases as $sc)
                        <flux:select.option :value="$sc->value">{{ $sc->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Shift Kerja</flux:label>
                <flux:select wire:model="exportShift" data-test="export-shift">
                    <flux:select.option value="">Semua Shift</flux:select.option>
                    @foreach($shiftCases as $sh)
                        <flux:select.option :value="$sh->value">{{ $sh->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Status Laporan</flux:label>
                <flux:select wire:model="exportStatus" data-test="export-status">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    @foreach($statusCases as $st)
                        <flux:select.option :value="$st->value">{{ $st->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
            <flux:modal.close>
                <flux:button variant="filled">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" icon="arrow-down-tray" wire:click="processExport" data-test="btn-execute-export">
                Unduh File Excel (.xlsx)
            </flux:button>
        </div>
    </flux:modal>

    <!-- Modal Konfirmasi Hapus Laporan (Khusus Supervisor) -->
    <flux:modal name="delete-report-confirm" class="max-w-md space-y-6">
        <div>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">Hapus Berkas Laporan Produksi</flux:heading>
            <flux:subheading class="mt-2 text-zinc-600 dark:text-zinc-300">
                Apakah Anda yakin ingin menghapus berkas Laporan <strong class="text-zinc-900 dark:text-zinc-100 font-mono">{{ $deletingReportCode }}</strong>?
            </flux:subheading>
            <div class="mt-3 p-3 rounded-lg bg-red-50 text-red-800 text-xs dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-900/60">
                <strong>Perhatian:</strong> Tindakan ini akan menghapus seluruh {{ $deletingItemCount }} data item hasil kemas di dalam berkas ini dan mengembalikan status akumulasi order produksi ke antrean. Data yang dihapus tidak dapat dipulihkan.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="filled">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteReport" data-test="btn-confirm-delete-report">
                Hapus Permanen
            </flux:button>
        </div>
    </flux:modal>
</div>
