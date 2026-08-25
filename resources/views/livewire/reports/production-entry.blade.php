<div 
    x-data="{
        init() {
            this.$nextTick(() => {
                this.$refs.scanInput?.focus();
            });
        },
        playAudio(type) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);

                if (type === 'success') {
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                    osc.frequency.setValueAtTime(880, ctx.currentTime + 0.08); // A5
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.25);
                } else {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.35);
                }
            } catch (e) {
                // Audio context fallback jika browser memblokir autoplay
            }
        }
    }" 
    @focus-scan-input.window="$nextTick(() => { $refs.scanInput?.focus(); })"
    @focus-qty-input.window="$nextTick(() => { $refs.qtyInput?.focus(); $refs.qtyInput?.select(); })"
    @play-sound.window="playAudio($event.detail?.type || 'success')"
    class="space-y-6 max-w-7xl mx-auto"
>
    <!-- Header Konsol Sesi Laporan -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2.5">
                <flux:heading size="xl" level="1" class="font-bold tracking-tight font-mono">
                    {{ $report->report_code }}
                </flux:heading>
                <flux:badge :color="$report->product_series->badgeColor()" size="sm">
                    {{ $report->product_series->shortLabel() }}
                </flux:badge>
                <flux:badge :color="$report->shift->badgeColor()" size="sm">
                    {{ $report->shift->label() }}
                </flux:badge>
                <flux:badge :color="$report->status->badgeColor()" size="sm">
                    {{ $report->status->label() }}
                </flux:badge>
            </div>
            <flux:subheading size="sm" class="text-zinc-500 dark:text-zinc-400">
                Tanggal: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $report->report_date->format('d/m/Y') }}</span> • 
                Petugas: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $report->user?->name ?? 'Petugas' }}</span>
                @if($report->notes)
                    • <span class="italic text-zinc-600 dark:text-zinc-400">"{{ $report->notes }}"</span>
                @endif
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2.5">
            <flux:button
                variant="ghost"
                icon="arrow-left"
                :href="route('reports.session')"
                wire:navigate
                data-test="btn-change-session"
            >
                Ganti Sesi
            </flux:button>

            @if($report->status === \App\Enums\ReportStatus::DRAFT)
                <flux:button
                    variant="primary"
                    icon="check-circle"
                    wire:click="finalizeReport"
                    wire:confirm="Pastikan seluruh Kartu Mesin pada shift ini sudah dipindai. Selesaikan laporan sekarang?"
                    data-test="btn-finalize-report"
                >
                    Selesaikan Laporan
                </flux:button>
            @endif
        </div>
    </div>

    <!-- Top Scan Console (Continuous Scan Loop) -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <div class="flex items-center gap-2">
                <span class="flex size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                    High-Speed Barcode Scanner Console
                </span>
            </div>
            <span class="text-xs text-zinc-400 dark:text-zinc-500 hidden sm:inline">
                Arahkan scanner ke Kartu Mesin & tekan Enter
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Input 1: Scan Barcode No PO -->
            <div class="md:col-span-5">
                <flux:field>
                    <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <flux:icon.qr-code class="size-4 text-emerald-600" />
                        1. Scan / Masukkan No PO Barcode
                    </flux:label>
                    <input
                        type="text"
                        x-ref="scanInput"
                        wire:model="scanPo"
                        wire:keydown.enter.prevent="lookupPo"
                        placeholder="Contoh: PO-2026-0001 lalu tekan Enter..."
                        class="w-full h-11 px-3.5 text-base font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50/80 dark:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                        data-test="input-scan-po"
                        autocomplete="off"
                    />
                </flux:field>
            </div>

            <!-- Input 2: Jumlah Kemas / Produksi -->
            <div class="md:col-span-4">
                <flux:field>
                    <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <flux:icon.calculator class="size-4 text-indigo-600" />
                        2. Jumlah Kemas (Lembar)
                    </flux:label>
                    <input
                        type="number"
                        x-ref="qtyInput"
                        wire:model="productionQty"
                        wire:keydown.enter.prevent="saveEntry"
                        placeholder="Kuantitas kemas..."
                        class="w-full h-11 px-3.5 text-base font-bold text-right rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        data-test="input-production-qty"
                    />
                </flux:field>
            </div>

            <!-- Tombol Simpan Baris -->
            <div class="md:col-span-3">
                <flux:button
                    variant="primary"
                    wire:click="saveEntry"
                    icon="plus"
                    class="w-full h-11 font-semibold text-base shadow-sm"
                    data-test="btn-save-entry"
                >
                    Simpan Item (Enter)
                </flux:button>
            </div>
        </div>

        <!-- Matched SAP Specification Pill -->
        @if($scannedOrder)
            <div class="p-3.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm animate-fadeIn" data-test="matched-order-pill">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="font-bold font-mono text-emerald-900 dark:text-emerald-200">{{ $scannedOrder->po_number }}</span>
                        <span class="text-zinc-400">•</span>
                        <span class="font-semibold text-emerald-800 dark:text-emerald-300">{{ $scannedOrder->product_name }}</span>
                    </div>
                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                        {{ $scannedOrder->specification ?? 'Spesifikasi pita cukai sekuriti' }}
                        @if($scannedOrder->machine_number)
                            • Mesin: <span class="font-mono font-medium">{{ $scannedOrder->machine_number }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Target SAP:</span>
                    <span class="font-mono font-bold text-emerald-800 dark:text-emerald-200 text-base ml-1">
                        {{ number_format($scannedOrder->target_quantity, 0, ',', '.') }} {{ $scannedOrder->unit }}
                    </span>
                </div>
            </div>
        @endif
    </div>

    <!-- Rekapitulasi Tabel Hasil Scan & Inline Edit -->
    <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <flux:heading size="base" class="font-bold">Daftar Hasil Pemindaian Barcode</flux:heading>
                <flux:subheading size="xs" class="text-zinc-500 dark:text-zinc-400">
                    Kuantitas kemas dapat diedit langsung pada kotak angka di tabel bawah tanpa dialog popup.
                </flux:subheading>
            </div>
            <flux:badge color="zinc" size="sm" class="font-mono">
                Total: {{ $items->count() }} Order
            </flux:badge>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 w-12 text-center">No</th>
                        <th class="p-3.5">No PO / Barcode</th>
                        <th class="p-3.5">Nama Produk & Spesifikasi</th>
                        <th class="p-3.5 text-center">Mesin</th>
                        <th class="p-3.5 text-right">Target SAP</th>
                        <th class="p-3.5 w-48 text-right font-bold text-indigo-700 dark:text-indigo-400">Jumlah Kemas (Edit Langsung)</th>
                        <th class="p-3.5 text-center">Waktu Scan</th>
                        <th class="p-3.5 text-center w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($items as $index => $item)
                        <tr wire:key="item-row-{{ $item->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" data-test="row-item-{{ $item->id }}">
                            <td class="p-3.5 text-center text-zinc-400 font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="p-3.5 font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $item->po_number }}</td>
                            <td class="p-3.5">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product_name }}</div>
                                @if($item->specification)
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-1">{{ $item->specification }}</div>
                                @endif
                            </td>
                            <td class="p-3.5 text-center font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $item->machine_number ?? '-' }}
                            </td>
                            <td class="p-3.5 text-right font-mono text-zinc-600 dark:text-zinc-400">
                                {{ number_format($item->target_quantity, 0, ',', '.') }}
                            </td>
                            
                            <!-- Inline Editable Cell -->
                            <td class="p-2.5 text-right">
                                <input
                                    type="number"
                                    value="{{ $item->good_quantity }}"
                                    wire:change="updateItemQuantity({{ $item->id }}, $event.target.value)"
                                    wire:keydown.enter.prevent="updateItemQuantity({{ $item->id }}, $event.target.value)"
                                    class="w-36 h-9 px-2.5 text-right font-bold font-mono text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition-all"
                                    data-test="input-edit-qty-{{ $item->id }}"
                                />
                            </td>

                            <td class="p-3.5 text-center text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                {{ $item->scanned_at ? $item->scanned_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="deleteItem({{ $item->id }})"
                                    wire:confirm="Yakin ingin menghapus item PO {{ $item->po_number }} dari laporan ini?"
                                    class="text-rose-600 hover:text-rose-700 dark:text-rose-400"
                                    data-test="btn-delete-item-{{ $item->id }}"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-zinc-400 dark:text-zinc-500 text-sm">
                                <flux:icon.qr-code class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                                Belum ada item PO yang dipindai. Silakan scan barcode pada Kartu Mesin Produksi di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Summary Footer Row -->
                @if($items->isNotEmpty())
                    @php
                        $sumTarget = $items->sum('target_quantity');
                        $sumGood = $items->sum('good_quantity');
                        $percentage = $sumTarget > 0 ? round(($sumGood / $sumTarget) * 100, 1) : 100;
                    @endphp
                    <tfoot class="bg-zinc-100 dark:bg-zinc-800/90 font-semibold text-zinc-900 dark:text-zinc-100 border-t-2 border-zinc-300 dark:border-zinc-700">
                        <tr>
                            <td colspan="4" class="p-3.5 text-right text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Total Rekapitulasi Sesi:
                            </td>
                            <td class="p-3.5 text-right font-mono font-bold">
                                {{ number_format($sumTarget, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-mono font-bold text-indigo-700 dark:text-indigo-400 text-base">
                                {{ number_format($sumGood, 0, ',', '.') }} lbr
                            </td>
                            <td class="p-3.5 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                {{ $percentage }}%
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
