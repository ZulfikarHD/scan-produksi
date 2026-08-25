<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $shift = \App\Helpers\ShiftHelper::getCurrentShift();
            $currentWib = \App\Helpers\ShiftHelper::getFormattedDateTime();
            $user = auth()->user();
            $isSupervisor = $user?->isSupervisor() ?? false;
        @endphp

        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <div class="flex items-center gap-2 mr-4">
                <div class="flex size-8 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs">
                    <x-app-logo-icon class="size-4 fill-current" />
                </div>
                <span class="text-sm font-bold tracking-tight text-zinc-900 dark:text-zinc-100">PT PDS</span>
            </div>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    Dashboard
                </flux:navbar.item>
                @if($isSupervisor)
                    <flux:navbar.item icon="arrow-path" :href="Route::has('database.update') ? route('database.update') : '#'" wire:navigate>
                        Update Database
                    </flux:navbar.item>
                @endif
                <flux:navbar.item icon="document-plus" :href="Route::has('laporan.create') ? route('laporan.create') : '#'" wire:navigate>
                    Buat Laporan
                </flux:navbar.item>
                <flux:navbar.item icon="document-text" :href="Route::has('laporan.index') ? route('laporan.index') : '#'" wire:navigate>
                    Lihat Laporan
                </flux:navbar.item>
                @if($isSupervisor)
                    <flux:navbar.item icon="users" :href="Route::has('petugas.index') ? route('petugas.index') : url('/petugas')" wire:navigate>
                        Data Petugas
                    </flux:navbar.item>
                @endif
            </flux:navbar>

            <flux:spacer />

            <!-- Shift Banner & Real-time WIB Clock -->
            <div class="flex items-center gap-2.5 sm:gap-4 mr-3">
                <div class="flex items-center gap-1.5" title="{{ $shift['time_range'] }}">
                    <span class="flex size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <flux:badge :color="$shift['badge_color']" size="sm" class="font-medium shadow-xs">
                        {{ $shift['name'] }}
                    </flux:badge>
                </div>
                <div class="hidden md:flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                    <flux:icon.clock class="size-3.5 text-zinc-400" />
                    <span>{{ $currentWib }}</span>
                </div>
            </div>

            <!-- Dark / Light Mode Switcher -->
            <flux:dropdown position="bottom" align="end">
                <flux:button variant="ghost" size="sm" icon="moon" class="dark:hidden" aria-label="Toggle Theme" />
                <flux:button variant="ghost" size="sm" icon="sun" class="hidden dark:inline-flex" aria-label="Toggle Theme" />
                <flux:menu>
                    <flux:menu.item x-on:click="$flux.appearance = 'light'" icon="sun">Mode Terang</flux:menu.item>
                    <flux:menu.item x-on:click="$flux.appearance = 'dark'" icon="moon">Mode Gelap</flux:menu.item>
                    <flux:menu.item x-on:click="$flux.appearance = 'system'" icon="computer-desktop">Ikuti Sistem</flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs">
                        <x-app-logo-icon class="size-4 fill-current" />
                    </div>
                    <span class="text-sm font-bold tracking-tight text-zinc-900 dark:text-zinc-100">PT PDS</span>
                </div>
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Menu Utama">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                    @if($isSupervisor)
                        <flux:sidebar.item icon="arrow-path" :href="Route::has('database.update') ? route('database.update') : '#'" wire:navigate>
                            Update Database
                        </flux:sidebar.item>
                    @endif
                    <flux:sidebar.item icon="document-plus" :href="Route::has('laporan.create') ? route('laporan.create') : '#'" wire:navigate>
                        Buat Laporan
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="Route::has('laporan.index') ? route('laporan.index') : '#'" wire:navigate>
                        Lihat Laporan
                    </flux:sidebar.item>
                    @if($isSupervisor)
                        <flux:sidebar.item icon="users" :href="Route::has('petugas.index') ? route('petugas.index') : url('/petugas')" wire:navigate>
                            Data Petugas
                        </flux:sidebar.item>
                    @endif
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>
                        Pengaturan
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
