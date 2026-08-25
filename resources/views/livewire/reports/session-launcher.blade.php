<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Halaman -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="font-bold tracking-tight">Inisiasi Sesi Laporan Produksi</flux:heading>
            <flux:subheading size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">
                Pilih tanggal kerja, kategori seri produk pita cukai, dan shift untuk membuka konsol pemindaian barcode berkecepatan tinggi.
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:badge color="zinc" size="sm" class="font-mono">
                <flux:icon.clock class="size-3.5 mr-1 inline" />
                {{ \App\Helpers\ShiftHelper::getFormattedDateTime() }}
            </flux:badge>
        </div>
    </div>

    <!-- Main Grid Layout: Form Inisiasi & Panel Sesi Hari Ini -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Form Launcher Card -->
        <div class="lg:col-span-8 space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-6">
                <!-- Preview Kode Laporan -->
                <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Kode Laporan Sesi Target</div>
                        <div class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100 tracking-tight mt-0.5" data-test="preview-report-code">
                            {{ $previewCode }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($existingReport)
                            <flux:badge color="amber" size="sm" class="font-medium">
                                <flux:icon.arrow-path class="size-3 mr-1 inline animate-spin" />
                                Sesi Sudah Ada (Lanjutkan)
                            </flux:badge>
                        @else
                            <flux:badge color="emerald" size="sm" class="font-medium">
                                <flux:icon.plus class="size-3 mr-1 inline" />
                                Sesi Baru (Draft)
                            </flux:badge>
                        @endif
                    </div>
                </div>

                <form wire:submit.prevent="launchSession" class="space-y-6">
                    <!-- 1. Pilihan Tanggal Laporan -->
                    <flux:field>
                        <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100">
                            Tanggal Laporan Produksi
                        </flux:label>
                        <flux:input
                            type="date"
                            wire:model.live="report_date"
                            class="w-full"
                            data-test="input-report-date"
                        />
                        <flux:error name="report_date" />
                    </flux:field>

                    <!-- 2. Pilihan Kategori Seri Produk (Radio Cards) -->
                    <div class="space-y-2.5">
                        <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100 block">
                            Kategori Seri Produk Sekuriti (Pita Cukai)
                        </flux:label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($seriesCases as $case)
                                <label
                                    wire:key="series-{{ $case->value }}"
                                    class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all {{ $product_series === $case->value ? 'border-emerald-600 ring-2 ring-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/20 dark:border-emerald-500' : 'border-zinc-200 dark:border-zinc-700/80 bg-white dark:bg-zinc-800/40 hover:border-zinc-300 dark:hover:border-zinc-600' }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="radio"
                                                name="product_series"
                                                value="{{ $case->value }}"
                                                wire:model.live="product_series"
                                                class="text-emerald-600 focus:ring-emerald-500"
                                                data-test="radio-series-{{ $case->value }}"
                                            />
                                            <span class="font-bold text-base text-zinc-900 dark:text-zinc-100">{{ $case->shortLabel() }}</span>
                                        </div>
                                        <flux:badge :color="$case->badgeColor()" size="xs">
                                            {{ $case->code() }}
                                        </flux:badge>
                                    </div>
                                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                                        {{ $case->description() }}
                                    </p>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="product_series" />
                    </div>

                    <!-- 3. Pilihan Shift Kerja -->
                    <div class="space-y-2.5">
                        <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100 block">
                            Shift Kerja
                        </flux:label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($shiftCases as $shiftItem)
                                <label
                                    wire:key="shift-{{ $shiftItem->value }}"
                                    class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $shift === $shiftItem->value ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/50 dark:bg-indigo-950/20 dark:border-indigo-500' : 'border-zinc-200 dark:border-zinc-700/80 bg-white dark:bg-zinc-800/40 hover:border-zinc-300 dark:hover:border-zinc-600' }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="radio"
                                                name="shift"
                                                value="{{ $shiftItem->value }}"
                                                wire:model.live="shift"
                                                class="text-indigo-600 focus:ring-indigo-500"
                                                data-test="radio-shift-{{ $shiftItem->value }}"
                                            />
                                            <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">{{ $shiftItem->label() }}</span>
                                        </div>
                                        <flux:badge :color="$shiftItem->badgeColor()" size="xs">
                                            S{{ $shiftItem->number() }}
                                        </flux:badge>
                                    </div>
                                    <span class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 font-mono">
                                        {{ $shiftItem->timeRange() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="shift" />
                    </div>

                    <!-- 4. Catatan Operasional (Opsional) -->
                    <flux:field>
                        <flux:label class="text-zinc-700 dark:text-zinc-300">
                            Catatan Sesi (Opsional)
                        </flux:label>
                        <flux:textarea
                            wire:model="notes"
                            placeholder="Tambahkan catatan khusus mesin, tim, atau instruksi kerja shift ini jika ada..."
                            rows="2"
                            data-test="input-notes"
                        />
                        <flux:error name="notes" />
                    </flux:field>

                    <!-- Alert Info Jika Sesi Sudah Ada -->
                    @if($existingReport)
                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3 text-sm">
                            <flux:icon.information-circle class="size-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <div class="font-semibold text-amber-900 dark:text-amber-200">
                                    Sesi laporan {{ $existingReport->report_code }} sudah pernah dibuat.
                                </div>
                                <div class="text-xs text-amber-800 dark:text-amber-300">
                                    Pembuat: <span class="font-medium">{{ $existingReport->user?->name ?? 'Petugas' }}</span> • 
                                    Total PO Terscan: <span class="font-semibold font-mono">{{ $existingReport->total_items_count }} order</span> ({{ number_format($existingReport->total_good_quantity, 0, ',', '.') }} lembar) • 
                                    Status: <span class="font-semibold">{{ $existingReport->status->label() }}</span>
                                </div>
                                <div class="text-xs text-amber-700 dark:text-amber-400">
                                    Menekan tombol di bawah akan langsung membuka sesi ini dan memuat seluruh data PO yang sudah tersimpan sebelumnya.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tombol Aksi Utama -->
                    <div class="flex items-center justify-end pt-2">
                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="bolt"
                            class="w-full sm:w-auto h-11 px-6 font-semibold shadow-sm"
                            data-test="btn-launch-session"
                        >
                            {{ $existingReport ? 'Lanjutkan Sesi Pemindaian PO' : 'Buka Sesi Pemindaian PO' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar / Sesi Terdaftar Hari Ini -->
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700/60">
                    <flux:heading size="base" class="font-bold flex items-center gap-2">
                        <flux:icon.document-text class="size-4 text-emerald-600" />
                        Sesi Hari Ini
                    </flux:heading>
                    <flux:badge color="zinc" size="xs">{{ $todayReports->count() }} Sesi</flux:badge>
                </div>

                <div class="space-y-2.5 max-h-[420px] overflow-y-auto pr-1">
                    @forelse($todayReports as $rep)
                        <div
                            wire:key="today-rep-{{ $rep->id }}"
                            class="p-3 rounded-lg border border-zinc-200/80 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors flex flex-col gap-2"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs text-zinc-900 dark:text-zinc-100">{{ $rep->report_code }}</span>
                                <flux:badge :color="$rep->status->badgeColor()" size="xs">
                                    {{ $rep->status === \App\Enums\ReportStatus::DRAFT ? 'Draft' : 'Selesai' }}
                                </flux:badge>
                            </div>
                            <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $rep->user?->name ?? 'Petugas' }}</span>
                                <span class="font-mono">{{ $rep->total_items_count }} PO ({{ number_format($rep->total_good_quantity, 0, ',', '.') }} lbr)</span>
                            </div>
                            <a
                                href="{{ route('reports.entry', ['report' => $rep->report_code]) }}"
                                class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 pt-1 border-t border-zinc-200/50 dark:border-zinc-700/50"
                                wire:navigate
                            >
                                Buka Konsol Scan
                                <flux:icon.arrow-right class="size-3" />
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-zinc-400 dark:text-zinc-500">
                            Belum ada sesi laporan untuk tanggal ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Petunjuk Kerja Singkat -->
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700/60 dark:bg-zinc-800/30 text-xs text-zinc-600 dark:text-zinc-400 space-y-2">
                <div class="font-semibold text-zinc-900 dark:text-zinc-200 flex items-center gap-1.5">
                    <flux:icon.light-bulb class="size-4 text-amber-500" />
                    Panduan Pemindaian Cepat
                </div>
                <p>
                    Setelah sesi dibuka, sistem akan mengarahkan kursor langsung ke kotak input barcode. Arahkan scanner ke <strong>Kartu Mesin Produksi</strong> untuk memulai pencatatan otomatis tanpa mouse.
                </p>
            </div>
        </div>
    </div>
</div>
