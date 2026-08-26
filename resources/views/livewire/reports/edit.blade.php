<div class="space-y-6">
    <!-- Header Formulir Koreksi -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button
                variant="ghost"
                size="sm"
                icon="arrow-left"
                :href="route('reports.show', ['report' => $report->report_code])"
                wire:navigate
                data-test="btn-cancel-edit-back"
            >
                Batal
            </flux:button>
            <div>
                <flux:heading size="xl" level="1" class="font-bold tracking-tight">
                    Koreksi Laporan Produksi
                </flux:heading>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Kode Berkas: <strong class="font-mono text-zinc-800 dark:text-zinc-200">{{ $report->report_code }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:badge size="sm" :color="$report->status->badgeColor()">
                {{ $report->status->label() }}
            </flux:badge>
        </div>
    </div>

    <!-- Metadata Ringkas Laporan -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/40 text-xs">
        <div>
            <span class="text-zinc-500 dark:text-zinc-400">Seri Produk:</span>
            <p class="font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $report->product_series->label() }}</p>
        </div>
        <div>
            <span class="text-zinc-500 dark:text-zinc-400">Shift Kerja:</span>
            <p class="font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $report->shift->label() }}</p>
        </div>
        <div>
            <span class="text-zinc-500 dark:text-zinc-400">Tanggal Kerja:</span>
            <p class="font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">{{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d F Y') }}</p>
        </div>
        <div>
            <span class="text-zinc-500 dark:text-zinc-400">Petugas Pembuat:</span>
            <p class="font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $report->user?->name ?? 'Petugas' }}</p>
        </div>
    </div>

    <!-- Formulir Catatan Operasional Shift -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-3">
        <flux:field>
            <flux:label>Catatan Operasional Shift</flux:label>
            <flux:input
                wire:model="notes"
                placeholder="Contoh: Pergantian roll kertas pada mesin 02, kendala pemotongan lembar..."
                data-test="input-report-notes"
            />
            <flux:description>Catatan kendala mesin, bahan baku, atau catatan penyerahan shift.</flux:description>
        </flux:field>
    </div>

    <!-- Tabel Koreksi Kuantitas Item PO Terdaftar -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Daftar Item PO Dalam Laporan</flux:heading>
            <span class="text-xs text-zinc-500 font-medium">
                {{ $activeCount }} Item Aktif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 text-xs font-semibold text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        <th class="py-2.5 px-3">No PO & Produk</th>
                        <th class="py-2.5 px-3">Mesin</th>
                        <th class="py-2.5 px-3 text-right">Target SAP</th>
                        <th class="py-2.5 px-3 text-right w-36">Kemas Baik (lbr)</th>
                        <th class="py-2.5 px-3 text-right w-32">Reject (lbr)</th>
                        <th class="py-2.5 px-3">Keterangan Item</th>
                        <th class="py-2.5 px-3 text-center w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($items as $index => $item)
                        @if(!($item['is_deleted'] ?? false))
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" data-test="edit-item-row-{{ $index }}">
                                <td class="py-3 px-3">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-xs text-zinc-900 dark:text-zinc-100">
                                            {{ $item['po_number'] }}
                                        </span>
                                        <span class="text-xs text-zinc-600 dark:text-zinc-300">
                                            {{ $item['product_name'] }}
                                        </span>
                                        @if($item['specification'])
                                            <span class="text-[11px] text-zinc-400">
                                                {{ $item['specification'] }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                        {{ $item['machine_number'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($item['target_quantity'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <flux:input
                                        type="number"
                                        min="1"
                                        wire:model.live="items.{{ $index }}.good_quantity"
                                        class="text-right font-mono"
                                        size="sm"
                                        required
                                        data-test="input-good-qty-{{ $index }}"
                                    />
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <flux:input
                                        type="number"
                                        min="0"
                                        wire:model.live="items.{{ $index }}.reject_quantity"
                                        class="text-right font-mono"
                                        size="sm"
                                        data-test="input-reject-qty-{{ $index }}"
                                    />
                                </td>
                                <td class="py-3 px-3">
                                    <flux:input
                                        wire:model="items.{{ $index }}.notes"
                                        placeholder="Keterangan..."
                                        size="sm"
                                        data-test="input-item-notes-{{ $index }}"
                                    />
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <flux:button
                                        variant="ghost"
                                        size="xs"
                                        icon="trash"
                                        wire:click="removeItem({{ $index }})"
                                        class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40"
                                        title="Hapus baris item"
                                        data-test="btn-remove-item-{{ $index }}"
                                    />
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Belum ada item PO dalam laporan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bagian Penambahan PO Susulan -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-4">
        <flux:heading size="lg">Tambah Item PO Susulan</flux:heading>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Pindai barcode atau masukkan No PO dari Master Data SAP untuk menambahkan item order yang tertinggal.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <flux:field>
                    <flux:label>No PO / Barcode SAP</flux:label>
                    <div class="flex gap-2">
                        <flux:input
                            wire:model.live="newPo"
                            wire:keydown.enter.prevent="lookupNewPo"
                            placeholder="Contoh: 3000189509"
                            class="font-mono"
                            data-test="input-new-po"
                        />
                        <flux:button size="sm" icon="magnifying-glass" wire:click="lookupNewPo" data-test="btn-lookup-new-po">
                            Cari
                        </flux:button>
                    </div>
                </flux:field>
            </div>

            @if($newScannedOrder)
                <div class="sm:col-span-3">
                    <flux:field>
                        <flux:label>Kemas Baik (Lembar)</flux:label>
                        <flux:input
                            type="number"
                            min="1"
                            wire:model="newGoodQty"
                            class="font-mono"
                            required
                            data-test="input-new-good-qty"
                        />
                    </flux:field>
                </div>

                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Reject (Lembar)</flux:label>
                        <flux:input
                            type="number"
                            min="0"
                            wire:model="newRejectQty"
                            class="font-mono"
                            data-test="input-new-reject-qty"
                        />
                    </flux:field>
                </div>

                <div class="sm:col-span-3 flex justify-end">
                    <flux:button
                        variant="primary"
                        icon="plus"
                        wire:click="addSupplementalItem"
                        class="w-full"
                        data-test="btn-add-supplemental-item"
                    >
                        Tambahkan ke Form
                    </flux:button>
                </div>
            @endif
        </div>

        @if($newScannedOrder)
            <div class="p-3 rounded-lg bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-900/60 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <span class="font-bold text-sky-900 dark:text-sky-200">{{ $newScannedOrder->product_name }}</span>
                    @if($newScannedOrder->specification)
                        <span class="text-sky-700 dark:text-sky-400"> — {{ $newScannedOrder->specification }}</span>
                    @endif
                </div>
                <div class="font-mono text-sky-800 dark:text-sky-300">
                    Mesin: {{ $newScannedOrder->machine_number ?? '-' }} | Target SAP: {{ number_format($newScannedOrder->target_quantity, 0, ',', '.') }} {{ $newScannedOrder->unit }}
                </div>
            </div>
        @endif
    </div>

    <!-- Ringkasan Akumulasi & Tombol Simpan Bawah -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-4 text-xs font-mono">
            <div>
                <span class="text-zinc-500">Total PO Aktif:</span>
                <strong class="text-zinc-900 dark:text-zinc-100">{{ $activeCount }}</strong>
            </div>
            <div>
                <span class="text-zinc-500">Total Kemas Baik:</span>
                <strong class="text-emerald-600 dark:text-emerald-400">{{ number_format($totalGood, 0, ',', '.') }} lbr</strong>
            </div>
            <div>
                <span class="text-zinc-500">Total Reject:</span>
                <strong class="text-amber-600 dark:text-amber-400">{{ number_format($totalReject, 0, ',', '.') }} lbr</strong>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button
                variant="filled"
                :href="route('reports.show', ['report' => $report->report_code])"
                wire:navigate
                data-test="btn-cancel-edit-bottom"
            >
                Batal
            </flux:button>

            <flux:button
                variant="primary"
                icon="check"
                wire:click="saveChanges"
                data-test="btn-save-report-changes"
            >
                Simpan Perubahan Laporan
            </flux:button>
        </div>
    </div>
</div>
