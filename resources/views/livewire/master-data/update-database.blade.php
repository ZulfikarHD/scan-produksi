<div class="space-y-6">
    <!-- Header Halaman & Tombol Unduh Template SAP -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="font-bold tracking-tight">Update Database SAP</flux:heading>
            <flux:subheading size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">
                Sinkronisasi master data order produksi dari berkas ekspor SAP (.xlsx, .xls, .csv), validasi skema data, dan riwayat pembaruan basis data.
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button
                variant="filled"
                icon="arrow-down-tray"
                wire:click="downloadTemplate"
                data-test="btn-download-template"
            >
                Unduh Template SAP
            </flux:button>
        </div>
    </div>

    <!-- Ringkasan Statistik Sinkronisasi -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <flux:icon.cube class="size-5" />
                </div>
                <div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Order Tersimpan</span>
                    <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalOrdersCount, 0, ',', '.') }} Order</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <flux:icon.clock class="size-5" />
                </div>
                <div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Sinkronisasi Terakhir</span>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $latestLog?->created_at ? $latestLog->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum Pernah' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                    <flux:icon.arrow-path class="size-5" />
                </div>
                <div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status Impor Terakhir</span>
                    <div class="mt-0.5">
                        @if($latestLog)
                            <flux:badge size="sm" :color="$latestLog->statusBadgeColor()">
                                {{ $latestLog->statusLabel() }} ({{ $latestLog->imported_count + $latestLog->updated_count }} baris)
                            </flux:badge>
                        @else
                            <span class="text-xs text-zinc-500">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Bar: Impor Berkas SAP vs Master Data Order Produksi -->
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button
                type="button"
                wire:click="switchTab('import')"
                class="flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition-colors {{ $activeTab === 'import' ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
                data-test="tab-import"
            >
                <flux:icon.arrow-up-tray class="size-4" />
                Impor Berkas SAP
            </button>

            <button
                type="button"
                wire:click="switchTab('orders')"
                class="flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition-colors {{ $activeTab === 'orders' ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
                data-test="tab-orders"
            >
                <flux:icon.table-cells class="size-4" />
                Master Data Order Produksi
                <span class="ml-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    {{ $totalOrdersCount }}
                </span>
            </button>
        </nav>
    </div>

    <!-- TAB 1: Impor Berkas SAP -->
    @if($activeTab === 'import')
        <div class="space-y-6">
            <!-- Card Area Unggah Berkas SAP -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg">Unggah Berkas Ekspor SAP</flux:heading>
                            <flux:subheading size="sm">
                                Pilih berkas spreadsheet hasil ekspor SAP (.xlsx, .xls, .csv). Sistem akan membuka pratinjau validasi secara otomatis sebelum menyimpan.
                            </flux:subheading>
                        </div>
                    </div>

                    <!-- Upload Input & Drag-and-Drop Area -->
                    <div
                        class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 p-8 text-center hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-600 transition-colors bg-zinc-50/50 dark:bg-zinc-800/40"
                    >
                        <flux:icon.arrow-up-tray class="mx-auto size-10 text-zinc-400" />
                        <div class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            <label for="file-upload" class="relative cursor-pointer font-semibold text-blue-600 focus-within:outline-hidden hover:text-blue-500 dark:text-blue-400">
                                <span>Pilih Berkas SAP</span>
                                <input
                                    id="file-upload"
                                    name="file-upload"
                                    type="file"
                                    class="sr-only"
                                    wire:model="file"
                                    accept=".xlsx,.xls,.csv"
                                    data-test="input-file-sap"
                                />
                            </label>
                            <span class="pl-1">atau seret dan lepas berkas di sini</span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            Format yang didukung: Excel (.xlsx, .xls) atau CSV (.csv) hingga 10 MB
                        </p>

                        <!-- Loading State Spinner saat upload -->
                        <div wire:loading wire:target="file" class="mt-4 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 font-medium">
                            <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Sedang mengunggah dan membaca baris spreadsheet...</span>
                        </div>
                    </div>

                    <flux:error name="file" />
                </div>
            </div>

            <!-- Tabel Riwayat Log Impor Database -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Riwayat Pembaruan Database</flux:heading>
                        <flux:subheading size="sm">Catatan audit riwayat unggah berkas ekspor SAP yang telah diproses oleh sistem.</flux:subheading>
                    </div>
                </div>

                <flux:table :paginate="$importLogs">
                    <flux:table.columns>
                        <flux:table.column>Waktu Impor (WIB)</flux:table.column>
                        <flux:table.column>Nama Berkas</flux:table.column>
                        <flux:table.column align="end">Total Baris</flux:table.column>
                        <flux:table.column align="end">Baru</flux:table.column>
                        <flux:table.column align="end">Diperbarui</flux:table.column>
                        <flux:table.column align="end">Bermasalah</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Pengunggah</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($importLogs as $log)
                            <flux:table.row :key="$log->id" data-test="log-row-{{ $log->id }}">
                                <flux:table.cell>
                                    <span class="font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                        {{ $log->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell variant="strong">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.document-text class="size-4 text-zinc-400" />
                                        <span class="truncate max-w-xs font-medium text-zinc-900 dark:text-zinc-100" title="{{ $log->file_name }}">
                                            {{ $log->file_name }}
                                        </span>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <span class="font-mono text-xs">{{ number_format($log->total_rows, 0, ',', '.') }}</span>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($log->imported_count, 0, ',', '.') }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <span class="font-mono text-xs text-blue-600 dark:text-blue-400">
                                        {{ number_format($log->updated_count, 0, ',', '.') }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    @if($log->failed_count > 0)
                                        <span class="font-mono text-xs font-semibold text-red-600 dark:text-red-400">
                                            {{ number_format($log->failed_count, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="font-mono text-xs text-zinc-400">0</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$log->statusBadgeColor()">
                                        {{ $log->statusLabel() }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $log->user?->name ?? 'Sistem' }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    @if($log->failed_count > 0 && !empty($log->error_log))
                                        <flux:button
                                            variant="ghost"
                                            size="xs"
                                            icon="exclamation-triangle"
                                            wire:click="showLogErrorDetail({{ $log->id }})"
                                            class="text-amber-600 dark:text-amber-400"
                                            data-test="btn-view-log-error-{{ $log->id }}"
                                        >
                                            Lihat Error
                                        </flux:button>
                                    @else
                                        <span class="text-xs text-zinc-400">-</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="9" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <flux:icon.arrow-up-tray class="size-7 text-zinc-400" />
                                        <span class="text-sm font-medium">Belum ada riwayat pembaruan database dari SAP.</span>
                                        <span class="text-xs text-zinc-400">Unggah berkas spreadsheet pada form di atas untuk memulai impor.</span>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    @endif

    <!-- TAB 2: Master Data Order Produksi -->
    @if($activeTab === 'orders')
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-6">
            <!-- Filter Toolbar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="w-full sm:max-w-sm">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="Cari No PO, OBC, atau nama produk..."
                        clearable
                        data-test="input-search-order"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="w-40">
                        <flux:select wire:model.live="statusFilter" placeholder="Semua Status" data-test="select-order-status">
                            <flux:select.option value="">Semua Status</flux:select.option>
                            <flux:select.option value="ready">Siap Kemas</flux:select.option>
                            <flux:select.option value="in_progress">Sedang Diproses</flux:select.option>
                            <flux:select.option value="completed">Selesai</flux:select.option>
                            <flux:select.option value="cancelled">Dibatalkan</flux:select.option>
                        </flux:select>
                    </div>
                </div>
            </div>

            <!-- Table Production Orders -->
            <flux:table :paginate="$orders">
                <flux:table.columns>
                    <flux:table.column>No PO / Barcode</flux:table.column>
                    <flux:table.column>No OBC</flux:table.column>
                    <flux:table.column>Nama Produk / Material</flux:table.column>
                    <flux:table.column>Pelanggan</flux:table.column>
                    <flux:table.column>No Mesin</flux:table.column>
                    <flux:table.column align="end">Target Kemas</flux:table.column>
                    <flux:table.column>Tgl Order</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($orders as $order)
                        <flux:table.row :key="$order->id" data-test="order-row-{{ $order->id }}">
                            <flux:table.cell variant="strong">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                    {{ $order->po_number }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $order->no_obc ?? '-' }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="font-medium text-xs text-zinc-900 dark:text-zinc-100">{{ $order->product_name }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $order->customer_name ?? '-' }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $order->machine_number ?? '-' }}</span>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <span class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $order->formattedTargetQuantity() }} {{ $order->unit }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $order->order_date?->translatedFormat('d M Y') ?? '-' }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$order->statusBadgeColor()">
                                    {{ $order->statusLabel() }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <flux:icon.cube class="size-7 text-zinc-400" />
                                    <span class="text-sm font-medium">Tidak ada data order produksi yang sesuai.</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    @endif

    <!-- MODAL PRATINJAU IMPOR BERKAS SAP (US-2.3) -->
    <flux:modal name="import-preview" class="max-w-5xl space-y-6">
        <div>
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Pratinjau Impor Berkas SAP</flux:heading>
                <div class="flex items-center gap-2">
                    <flux:badge size="sm" color="zinc">{{ $totalRowsCount }} Total Baris</flux:badge>
                    <flux:badge size="sm" color="green">{{ $validRowsCount }} Valid</flux:badge>
                    @if($invalidRowsCount > 0)
                        <flux:badge size="sm" color="red">{{ $invalidRowsCount }} Bermasalah</flux:badge>
                    @endif
                </div>
            </div>
            <flux:subheading class="mt-1">
                Berkas: <strong class="text-zinc-900 dark:text-zinc-100">{{ $originalFileName }}</strong>. Periksa sampel data di bawah ini sebelum mengonfirmasi penyimpanan ke basis data produksi.
            </flux:subheading>
        </div>

        @if($invalidRowsCount > 0)
            <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-700/80 dark:bg-amber-950/40">
                <div class="flex items-start gap-3">
                    <flux:icon.exclamation-triangle class="size-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <div class="text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-semibold">Perhatian: Ditemukan {{ $invalidRowsCount }} baris data dengan format tidak lengkap atau tidak valid.</p>
                        <p>Anda dapat memilih untuk melewati baris bermasalah dan hanya menyimpan {{ $validRowsCount }} baris yang valid, atau membatalkan proses untuk memperbaiki berkas spreadsheet Anda terlebih dahulu.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabel Sampel Baris Pratinjau (Maksimal 20 Baris) -->
        <div class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0 z-10 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">Baris</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">Status</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">No PO</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">Nama Produk</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">Pelanggan</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">No Mesin</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300 text-right">Target Qty</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">Tgl Order</th>
                        <th class="py-2.5 px-3 font-semibold text-zinc-700 dark:text-zinc-300">Catatan Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                    @forelse($previewRows as $row)
                        <tr class="{{ $row['is_valid'] ? 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' : 'bg-red-50/50 dark:bg-red-950/20' }}" data-test="preview-row-{{ $row['row_index'] }}">
                            <td class="py-2 px-3 font-mono text-zinc-500">{{ $row['row_index'] }}</td>
                            <td class="py-2 px-3">
                                @if($row['is_valid'])
                                    <flux:badge size="xs" color="green">Valid</flux:badge>
                                @else
                                    <flux:badge size="xs" color="red">Error</flux:badge>
                                @endif
                            </td>
                            <td class="py-2 px-3 font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $row['po_number'] }}</td>
                            <td class="py-2 px-3 text-zinc-800 dark:text-zinc-200 truncate max-w-xs" title="{{ $row['product_name'] }}">{{ $row['product_name'] }}</td>
                            <td class="py-2 px-3 text-zinc-600 dark:text-zinc-400 truncate max-w-30">{{ $row['customer_name'] }}</td>
                            <td class="py-2 px-3 font-mono text-zinc-600 dark:text-zinc-400">{{ $row['machine_number'] }}</td>
                            <td class="py-2 px-3 font-mono font-semibold text-right text-zinc-900 dark:text-zinc-100">{{ number_format($row['target_quantity'], 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-zinc-600 dark:text-zinc-400">{{ $row['order_date'] }}</td>
                            <td class="py-2 px-3">
                                @if(!empty($row['errors']))
                                    <ul class="text-[11px] text-red-600 dark:text-red-400 space-y-0.5">
                                        @foreach($row['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">Siap disimpan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-6 text-zinc-500">Tidak ada baris data yang ditemukan dalam berkas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($previewRows) < $totalRowsCount)
            <p class="text-xs text-zinc-500 dark:text-zinc-400 text-center">
                Menampilkan {{ count($previewRows) }} baris pertama dari total {{ $totalRowsCount }} baris data.
            </p>
        @endif

        <!-- Action Footer -->
        <div class="flex items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button variant="filled" wire:click="cancelImport" data-test="btn-cancel-import">
                Batal Impor
            </flux:button>

            <div class="flex items-center gap-2">
                @if($invalidRowsCount === 0 && $validRowsCount > 0)
                    <flux:button
                        variant="primary"
                        wire:click="confirmImport(false)"
                        wire:loading.attr="disabled"
                        wire:target="confirmImport"
                        data-test="btn-confirm-import"
                    >
                        <span wire:loading.remove wire:target="confirmImport">Simpan Semua ke Database ({{ $validRowsCount }} Order)</span>
                        <span wire:loading wire:target="confirmImport">Sedang Menyimpan Data...</span>
                    </flux:button>
                @elseif($validRowsCount > 0)
                    <flux:button
                        variant="primary"
                        wire:click="confirmImport(true)"
                        wire:loading.attr="disabled"
                        wire:target="confirmImport"
                        data-test="btn-confirm-partial-import"
                    >
                        <span wire:loading.remove wire:target="confirmImport">Lewati Baris Error & Simpan Baris Valid ({{ $validRowsCount }} Order)</span>
                        <span wire:loading wire:target="confirmImport">Sedang Menyimpan Data...</span>
                    </flux:button>
                @else
                    <flux:button variant="primary" disabled>
                        Tidak Ada Baris Valid untuk Disimpan
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <!-- MODAL DETAIL ERROR LOG AUDIT (US-2.4 helper) -->
    <flux:modal name="log-error-detail" class="max-w-xl space-y-4">
        <div>
            <flux:heading size="lg">Rincian Kesalahan Impor Berkas</flux:heading>
            <flux:subheading class="mt-1">
                Berkas: <strong class="text-zinc-900 dark:text-zinc-100">{{ $selectedLog?->file_name }}</strong>
            </flux:subheading>
        </div>

        <div class="max-h-72 overflow-y-auto rounded-lg border border-red-200 bg-red-50/50 p-4 dark:border-red-900/50 dark:bg-red-950/20">
            @if(!empty($selectedLog?->error_log))
                <ul class="space-y-1.5 text-xs text-red-700 dark:text-red-400">
                    @foreach($selectedLog->error_log as $errItem)
                        <li class="flex items-start gap-1.5">
                            <flux:icon.exclamation-circle class="size-3.5 shrink-0 mt-0.5" />
                            <span>{{ $errItem }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-zinc-500">Tidak ada rincian kesalahan yang tercatat.</p>
            @endif
        </div>

        <div class="flex items-center justify-end pt-2">
            <flux:modal.close>
                <flux:button variant="filled">Tutup Modal</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>
