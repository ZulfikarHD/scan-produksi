<div class="flex flex-col gap-6 pb-12">
    <!-- Header Beranda: Sambutan Dinamis, Konteks Waktu & Shift Banner -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <flux:heading size="xl" class="font-bold tracking-tight">
                    {{ $greeting }}, {{ $user?->name ?? 'Petugas' }}
                </flux:heading>
                @if($user?->isSupervisor())
                    <flux:badge size="sm" color="amber">Supervisor</flux:badge>
                @else
                    <flux:badge size="sm" color="sky">Petugas Kemas</flux:badge>
                @endif
            </div>
            <flux:subheading size="sm" class="flex flex-wrap items-center gap-x-2 gap-y-1 text-zinc-500 dark:text-zinc-400">
                <span>{{ $metrics['date_formatted'] }}</span>
                <span>•</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $metrics['current_shift']['name'] }} ({{ $metrics['current_shift']['time_range'] }})</span>
                </span>
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2.5">
            <flux:button
                variant="ghost"
                size="sm"
                icon="arrow-path"
                wire:click="refreshData"
                title="Segarkan data KPI dan analitik"
            >
                Segarkan Data
            </flux:button>

            <flux:button
                variant="primary"
                size="sm"
                icon="document-plus"
                href="{{ route('reports.session') }}"
                wire:navigate
            >
                Buat Laporan Baru
            </flux:button>
        </div>
    </div>

    <!-- Baris 1: Kartu KPI Produksi Hari Ini (5 Flux Cards) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <!-- KPI 1: Total Kemas Hari Ini -->
        <flux:card class="flex flex-col justify-between p-4 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Kemas Baik Hari Ini</span>
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <flux:icon.check-circle class="size-4" />
                </div>
            </div>

            <div class="mt-3">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ number_format($metrics['total_good'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">lbr</span>
                </div>

                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    @if($metrics['trend_direction'] === 'up')
                        <span class="inline-flex items-center font-medium text-emerald-600 dark:text-emerald-400">
                            <flux:icon.arrow-trending-up class="mr-0.5 size-3.5" />
                            +{{ $metrics['comparison_percentage'] }}%
                        </span>
                    @elseif($metrics['trend_direction'] === 'down')
                        <span class="inline-flex items-center font-medium text-rose-600 dark:text-rose-400">
                            <flux:icon.arrow-trending-down class="mr-0.5 size-3.5" />
                            {{ $metrics['comparison_percentage'] }}%
                        </span>
                    @else
                        <span class="inline-flex items-center font-medium text-zinc-500">
                            0%
                        </span>
                    @endif
                    <span class="text-zinc-500 dark:text-zinc-400">vs kemarin</span>
                </div>
            </div>
        </flux:card>

        <!-- KPI 2: Total Reject Hari Ini -->
        <flux:card class="flex flex-col justify-between p-4 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Reject / Rusak</span>
                <div class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                    <flux:icon.exclamation-triangle class="size-4" />
                </div>
            </div>

            <div class="mt-3">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ number_format($metrics['total_reject'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">lbr</span>
                </div>

                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <flux:badge size="xs" :color="$metrics['reject_rate'] > 1.5 ? 'red' : 'zinc'">
                        Tingkat: {{ $metrics['reject_rate'] }}%
                    </flux:badge>
                    <span class="text-[11px] text-zinc-400">Std &lt;1.5%</span>
                </div>
            </div>
        </flux:card>

        <!-- KPI 3: PO / Item Terscan -->
        <flux:card class="flex flex-col justify-between p-4 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Order Diproses</span>
                <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                    <flux:icon.qr-code class="size-4" />
                </div>
            </div>

            <div class="mt-3">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $metrics['po_count'] }}
                    <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">PO unik</span>
                </div>

                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Terscan di lini kemas
                </div>
            </div>
        </flux:card>

        <!-- KPI 4: Laporan Masuk & Terverifikasi -->
        <flux:card class="flex flex-col justify-between p-4 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Berkas Laporan</span>
                <div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <flux:icon.document-check class="size-4" />
                </div>
            </div>

            <div class="mt-3">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $metrics['total_reports'] }}
                    <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">berkas</span>
                </div>

                <div class="mt-2 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $metrics['verified_reports'] }} Vrf</span>
                    <span>•</span>
                    <span class="font-medium text-amber-600 dark:text-amber-400">{{ $metrics['submitted_reports'] }} Menunggu</span>
                </div>
            </div>
        </flux:card>

        <!-- KPI 5: Petugas Bertugas -->
        <flux:card class="flex flex-col justify-between p-4 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Petugas Shift</span>
                <div class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                    <flux:icon.user-group class="size-4" />
                </div>
            </div>

            <div class="mt-3">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $metrics['active_operators'] }}
                    <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">aktif</span>
                </div>

                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Dari {{ $metrics['total_active_staff'] }} operator terdaftar
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Baris 2: Hub Pintasan Aksi Cepat (Quick Action Hub) -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 {{ $user?->isSupervisor() ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }}">
        <!-- Quick Action 1: Buat Laporan Baru -->
        <a
            href="{{ route('reports.session') }}"
            wire:navigate
            class="group flex items-center gap-3.5 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-2xs transition hover:border-emerald-500 hover:shadow-xs dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:hover:border-emerald-500"
        >
            <div class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-xs group-hover:scale-105 transition-transform">
                <flux:icon.qr-code class="size-5" />
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                    Buat Laporan (Scan Barcode)
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    Buka sesi pemindaian Kartu Mesin
                </span>
            </div>
        </a>

        <!-- Quick Action 2: Update Database SAP (Khusus Supervisor) -->
        @if($user?->isSupervisor())
            <a
                href="{{ route('database.update') }}"
                wire:navigate
                class="group flex items-center gap-3.5 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-2xs transition hover:border-sky-500 hover:shadow-xs dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:hover:border-sky-500"
            >
                <div class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-sky-500 text-white shadow-xs group-hover:scale-105 transition-transform">
                    <flux:icon.arrow-up-tray class="size-5" />
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-sky-600 dark:group-hover:text-sky-400">
                        Update Database SAP
                    </span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                        Unggah berkas master data PO SAP
                    </span>
                </div>
            </a>
        @endif

        <!-- Quick Action 3: Lihat Semua Laporan -->
        <a
            href="{{ route('reports.index') }}"
            wire:navigate
            class="group flex items-center gap-3.5 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-2xs transition hover:border-indigo-500 hover:shadow-xs dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:hover:border-indigo-500"
        >
            <div class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-indigo-500 text-white shadow-xs group-hover:scale-105 transition-transform">
                <flux:icon.document-text class="size-5" />
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                    Lihat Semua Laporan
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    Arsip, pencarian & verifikasi laporan
                </span>
            </div>
        </a>

        <!-- Quick Action 4: Kelola Data Petugas (Khusus Supervisor) -->
        @if($user?->isSupervisor())
            <a
                href="{{ route('petugas.index') }}"
                wire:navigate
                class="group flex items-center gap-3.5 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-2xs transition hover:border-amber-500 hover:shadow-xs dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:hover:border-amber-500"
            >
                <div class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-xs group-hover:scale-105 transition-transform">
                    <flux:icon.users class="size-5" />
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-amber-600 dark:group-hover:text-amber-400">
                        Kelola Data Petugas
                    </span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                        Manajemen NP & akun operator kemas
                    </span>
                </div>
            </a>
        @endif
    </div>

    <!-- Baris 3: Kartu Grafik Analitik Tren Produksi (Centerpiece Card) -->
    <flux:card class="flex flex-col gap-5 p-5 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
        <!-- Header Panel Grafik: Judul, Periode, Ringkasan Stat & Kontrol -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <flux:heading size="lg" class="font-bold tracking-tight">
                        Grafik Tren Produksi & Hasil Kemas
                    </flux:heading>
                    <flux:badge size="sm" color="zinc" class="font-mono">
                        {{ $chartData['summary']['start_date_formatted'] }} - {{ $chartData['summary']['end_date_formatted'] }}
                    </flux:badge>
                </div>
                <flux:subheading size="sm" class="text-zinc-500 dark:text-zinc-400">
                    Visualisasi volume lembar kemas baik dan reject per hari dalam rentang waktu terpilih
                </flux:subheading>
            </div>

            <!-- Kontrol Filter Rentang Waktu & Tipe Grafik -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Toggle Periode Waktu -->
                <div class="inline-flex rounded-lg border border-zinc-200 bg-zinc-100/80 p-0.5 dark:border-zinc-700 dark:bg-zinc-800">
                    <button
                        type="button"
                        wire:click="setDateRange(7)"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition {{ $dateRange === 7 ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        7 Hari
                    </button>
                    <button
                        type="button"
                        wire:click="setDateRange(14)"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition {{ $dateRange === 14 ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        14 Hari
                    </button>
                    <button
                        type="button"
                        wire:click="setDateRange(30)"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition {{ $dateRange === 30 ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        30 Hari
                    </button>
                </div>

                <!-- Toggle Tipe Grafik -->
                <div class="inline-flex rounded-lg border border-zinc-200 bg-zinc-100/80 p-0.5 dark:border-zinc-700 dark:bg-zinc-800">
                    <button
                        type="button"
                        wire:click="setChartType('area')"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition {{ $chartType === 'area' ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        Area
                    </button>
                    <button
                        type="button"
                        wire:click="setChartType('line')"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition {{ $chartType === 'line' ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        Garis
                    </button>
                    <button
                        type="button"
                        wire:click="setChartType('bar')"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition {{ $chartType === 'bar' ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        Batang
                    </button>
                </div>
            </div>
        </div>

        <!-- 3 Badge Ringkasan Statistik Grafik -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-lg bg-zinc-50 px-3.5 py-2.5 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                <div class="flex size-7 items-center justify-center rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                    <flux:icon.chart-bar class="size-4" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Rata-rata Produksi Harian</span>
                    <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        {{ number_format($chartData['summary']['average_daily_good'], 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">lbr/hari</span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg bg-zinc-50 px-3.5 py-2.5 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                <div class="flex size-7 items-center justify-center rounded-md bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                    <flux:icon.arrow-trending-up class="size-4" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Hari Produksi Tertinggi (Peak)</span>
                    <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        {{ number_format($chartData['summary']['peak_good'], 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">lbr ({{ $chartData['summary']['peak_date'] }})</span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg bg-zinc-50 px-3.5 py-2.5 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                <div class="flex size-7 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                    <flux:icon.archive-box class="size-4" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Total Akumulasi Periode</span>
                    <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        {{ number_format($chartData['summary']['total_good'], 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">lbr • Reject {{ $chartData['summary']['reject_rate'] }}%</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Canvas Visualisasi Chart.js via Alpine.js Component -->
        <div
            wire:key="chart-container-{{ $dateRange }}-{{ $chartType }}"
            x-data="productionChartComponent({
                labels: {{ json_encode($chartData['labels']) }},
                fullDates: {{ json_encode($chartData['full_dates']) }},
                good: {{ json_encode($chartData['datasets']['good']) }},
                reject: {{ json_encode($chartData['datasets']['reject']) }},
                poCount: {{ json_encode($chartData['datasets']['po_count']) }},
                reportsCount: {{ json_encode($chartData['datasets']['reports_count']) }},
                chartType: '{{ $chartType }}'
            })"
            class="relative h-72 w-full pt-2"
        >
            <canvas x-ref="canvas" class="size-full"></canvas>
        </div>
    </flux:card>

    <!-- Baris 4: Grid Distribusi Shift & Top 5 Mesin Cetak (2 Side-by-Side Cards) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Widget 1: Distribusi Volume per Shift -->
        <flux:card class="flex flex-col justify-between p-5 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-700/60">
                    <div class="space-y-0.5">
                        <flux:heading size="md" class="font-bold">
                            Distribusi Volume per Shift ({{ $dateRange }} Hari)
                        </flux:heading>
                        <flux:subheading size="xs" class="text-zinc-500 dark:text-zinc-400">
                            Proporsi kontribusi hasil kemas antar shift kerja
                        </flux:subheading>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ number_format($shiftDistribution['total_good'], 0, ',', '.') }}
                        </span>
                        <span class="block text-[10px] text-zinc-500">Total Lembar</span>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach($shiftDistribution['shifts'] as $shiftItem)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="xs" :color="$shiftItem['badge_color']">
                                        {{ $shiftItem['name'] }}
                                    </flux:badge>
                                    <span class="text-[11px] text-zinc-400">{{ $shiftItem['time_range'] }}</span>
                                </div>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($shiftItem['good_quantity'], 0, ',', '.') }} lbr
                                    <span class="text-zinc-400 font-normal">({{ $shiftItem['percentage'] }}%)</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/60">
                                @php
                                    $bgClass = match($shiftItem['badge_color']) {
                                        'sky' => 'bg-sky-500',
                                        'amber' => 'bg-amber-500',
                                        'purple' => 'bg-purple-500',
                                        default => 'bg-emerald-500'
                                    };
                                @endphp
                                <div
                                    class="h-full rounded-full transition-all duration-500 {{ $bgClass }}"
                                    style="width: {{ $shiftItem['percentage'] }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-700/60 text-center text-xs text-zinc-500 dark:text-zinc-400">
                Data dihitung dari seluruh sesi laporan pada periode {{ $dateRange }} hari terakhir
            </div>
        </flux:card>

        <!-- Widget 2: Top 5 Mesin Cetak Terbanyak Dikemas -->
        <flux:card class="flex flex-col justify-between p-5 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-700/60">
                    <div class="space-y-0.5">
                        <flux:heading size="md" class="font-bold">
                            Top 5 Mesin Cetak Terbanyak Dikemas ({{ $dateRange }} Hari)
                        </flux:heading>
                        <flux:subheading size="xs" class="text-zinc-500 dark:text-zinc-400">
                            Peringkat mesin cetak dengan kuantitas output tertinggi
                        </flux:subheading>
                    </div>
                </div>

                <div class="mt-3">
                    @if(count($topMachines) > 0)
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                            @foreach($topMachines as $machine)
                                <div class="flex items-center justify-between py-2.5 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex size-5 items-center justify-center rounded-full font-mono text-[11px] font-bold {{ $machine['rank'] === 1 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : ($machine['rank'] === 2 ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400') }}">
                                            {{ $machine['rank'] }}
                                        </span>
                                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $machine['machine_number'] }}
                                        </span>
                                        <span class="text-[11px] text-zinc-400">({{ $machine['po_count'] }} PO)</span>
                                    </div>

                                    <div class="text-right">
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($machine['good_quantity'], 0, ',', '.') }} lbr
                                        </span>
                                        <span class="block text-[10px] text-zinc-500 dark:text-zinc-400">
                                            {{ $machine['percentage'] }}% dari total
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
                            Belum ada rekaman nomor mesin pada laporan periode ini.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-700/60 text-center text-xs text-zinc-500 dark:text-zinc-400">
                Peringkat diperbarui secara otomatis berdasarkan data pemindaian barcode
            </div>
        </flux:card>
    </div>

    <!-- Baris 5: Tabel Aktivitas Laporan Terbaru (Recent Activity Feed) -->
    <flux:card class="flex flex-col gap-4 p-5 shadow-xs border border-zinc-200 dark:border-zinc-700/80">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-700/60">
            <div class="space-y-0.5">
                <flux:heading size="md" class="font-bold">
                    Laporan Produksi Terkini
                </flux:heading>
                <flux:subheading size="xs" class="text-zinc-500 dark:text-zinc-400">
                    5 berkas laporan produksi terakhir yang diserahkan oleh petugas
                </flux:subheading>
            </div>

            <flux:button
                variant="ghost"
                size="sm"
                icon-trailing="arrow-right"
                href="{{ route('reports.index') }}"
                wire:navigate
            >
                Lihat Semua Laporan
            </flux:button>
        </div>

        @if($recentReports->count() > 0)
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Kode Laporan</flux:table.column>
                        <flux:table.column>Seri Produk</flux:table.column>
                        <flux:table.column>Tanggal Kerja</flux:table.column>
                        <flux:table.column>Shift</flux:table.column>
                        <flux:table.column>Petugas</flux:table.column>
                        <flux:table.column align="end">Hasil Kemas</flux:table.column>
                        <flux:table.column align="center">Status</flux:table.column>
                        <flux:table.column align="end">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($recentReports as $report)
                            <flux:table.row :key="$report->id">
                                <flux:table.cell class="font-mono font-medium text-zinc-900 dark:text-zinc-100">
                                    <a href="{{ route('reports.show', $report->report_code) }}" wire:navigate class="hover:underline text-emerald-600 dark:text-emerald-400">
                                        {{ $report->report_code }}
                                    </a>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="xs" :color="$report->product_series->badgeColor()">
                                        {{ $report->product_series->shortLabel() }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="text-zinc-600 dark:text-zinc-300">
                                    {{ $report->report_date->translatedFormat('d M Y') }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="xs" :color="$report->shift->badgeColor()">
                                        {{ $report->shift->label() }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="text-zinc-600 dark:text-zinc-300">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $report->user?->name ?? '-' }}</span>
                                        <span class="text-[11px] text-zinc-400 font-mono">{{ $report->user?->nip ?? '' }}</span>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell align="end" class="font-mono">
                                    <div class="flex flex-col items-end">
                                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($report->total_good_quantity, 0, ',', '.') }} lbr
                                        </span>
                                        @if($report->total_reject_quantity > 0)
                                            <span class="text-[11px] text-rose-500">
                                                {{ number_format($report->total_reject_quantity, 0, ',', '.') }} rjc
                                            </span>
                                        @endif
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell align="center">
                                    <flux:badge size="xs" :color="$report->status->badgeColor()">
                                        {{ $report->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <flux:button
                                            variant="ghost"
                                            size="xs"
                                            icon="eye"
                                            href="{{ route('reports.show', $report->report_code) }}"
                                            wire:navigate
                                            title="Lihat Detail Laporan"
                                        />
                                        <flux:button
                                            variant="ghost"
                                            size="xs"
                                            icon="printer"
                                            href="{{ route('reports.print', $report->report_code) }}"
                                            target="_blank"
                                            title="Cetak Berkas Laporan"
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <div class="py-10 text-center">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                    <flux:icon.document-text class="size-6" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Belum ada laporan produksi</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Klik tombol "Buat Laporan Baru" untuk memulai pemindaian barcode Kartu Mesin.
                </p>
                <div class="mt-4">
                    <flux:button
                        variant="primary"
                        size="sm"
                        icon="document-plus"
                        href="{{ route('reports.session') }}"
                        wire:navigate
                    >
                        Buat Laporan Sekarang
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:card>
</div>

<script>
    function productionChartComponent(config) {
        return {
            chart: null,
            labels: config.labels,
            fullDates: config.fullDates,
            good: config.good,
            reject: config.reject,
            poCount: config.poCount,
            reportsCount: config.reportsCount,
            chartType: config.chartType,

            init() {
                this.$nextTick(() => {
                    this.renderChart();
                });
            },

            renderChart() {
                if (!this.$refs.canvas || !window.Chart) return;

                if (this.chart) {
                    this.chart.destroy();
                }

                const ctx = this.$refs.canvas.getContext('2d');
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#a1a1aa' : '#71717a';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

                const isBar = this.chartType === 'bar';
                const isArea = this.chartType === 'area';
                const activeType = isBar ? 'bar' : 'line';

                // Gradient backgrounds
                const emeraldGradient = ctx.createLinearGradient(0, 0, 0, 260);
                emeraldGradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
                emeraldGradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

                const roseGradient = ctx.createLinearGradient(0, 0, 0, 260);
                roseGradient.addColorStop(0, 'rgba(244, 63, 94, 0.25)');
                roseGradient.addColorStop(1, 'rgba(244, 63, 94, 0.02)');

                this.chart = new window.Chart(ctx, {
                    type: activeType,
                    data: {
                        labels: this.labels,
                        datasets: [
                            {
                                label: 'Hasil Kemas Baik',
                                data: this.good,
                                borderColor: '#10b981',
                                backgroundColor: isArea ? emeraldGradient : (isBar ? 'rgba(16, 185, 129, 0.85)' : 'transparent'),
                                borderWidth: 2.5,
                                fill: isArea,
                                tension: 0.3,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointHoverRadius: 6,
                                pointRadius: 3.5,
                                borderRadius: isBar ? 4 : 0,
                            },
                            {
                                label: 'Lembar Reject',
                                data: this.reject,
                                borderColor: '#f43f5e',
                                backgroundColor: isArea ? roseGradient : (isBar ? 'rgba(244, 63, 94, 0.85)' : 'transparent'),
                                borderWidth: 2,
                                fill: isArea,
                                tension: 0.3,
                                pointBackgroundColor: '#f43f5e',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointHoverRadius: 6,
                                pointRadius: 3,
                                borderRadius: isBar ? 4 : 0,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'end',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    usePointStyle: true,
                                    color: textColor,
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: isDark ? '#18181b' : '#ffffff',
                                titleColor: isDark ? '#f4f4f5' : '#18181b',
                                bodyColor: isDark ? '#d4d4d8' : '#3f3f46',
                                borderColor: isDark ? '#3f3f46' : '#e4e4e7',
                                borderWidth: 1,
                                padding: 10,
                                cornerRadius: 8,
                                boxPadding: 3,
                                callbacks: {
                                    title: (items) => {
                                        const index = items[0].dataIndex;
                                        return this.fullDates[index] || this.labels[index];
                                    },
                                    label: (context) => {
                                        const val = context.raw || 0;
                                        const formatted = new Intl.NumberFormat('id-ID').format(val);
                                        return ` ${context.dataset.label}: ${formatted} lbr`;
                                    },
                                    afterBody: (items) => {
                                        const index = items[0].dataIndex;
                                        const po = this.poCount[index] || 0;
                                        const rep = this.reportsCount[index] || 0;
                                        return `\nOrder Diproses: ${po} PO\nLaporan Shift: ${rep} Berkas`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor,
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        size: 10
                                    },
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 14
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: gridColor,
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        return new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };
    }
</script>
