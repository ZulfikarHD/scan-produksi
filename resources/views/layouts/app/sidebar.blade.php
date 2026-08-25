<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold tracking-tight text-zinc-900 dark:text-zinc-100">PT PDS</span>
                        <span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Unit Pengemasan</span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <flux:badge size="xs" color="zinc" class="hidden font-mono sm:inline-flex">v1.0.0</flux:badge>
                    <flux:sidebar.collapse class="lg:hidden" />
                </div>
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Menu Utama" class="grid gap-1">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>

                    @if(auth()->user()?->isSupervisor())
                        <flux:sidebar.item
                            icon="arrow-path"
                            :href="Route::has('database.update') ? route('database.update') : '#'"
                            :current="request()->routeIs('database.*')"
                            wire:navigate
                        >
                            Update Database
                        </flux:sidebar.item>
                    @endif

                    <flux:sidebar.item
                        icon="document-plus"
                        :href="Route::has('laporan.create') ? route('laporan.create') : '#'"
                        :current="request()->routeIs('laporan.create')"
                        wire:navigate
                    >
                        Buat Laporan
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="document-text"
                        :href="Route::has('laporan.index') ? route('laporan.index') : '#'"
                        :current="request()->routeIs('laporan.index*')"
                        wire:navigate
                    >
                        Lihat Laporan
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()?->isSupervisor())
                    <flux:sidebar.group heading="Administrasi" class="grid gap-1 mt-4">
                        <flux:sidebar.item
                            icon="users"
                            :href="Route::has('petugas.index') ? route('petugas.index') : url('/petugas')"
                            :current="request()->is('petugas*') || request()->routeIs('petugas.*')"
                            wire:navigate
                        >
                            Data Petugas
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                <flux:sidebar.group heading="Sistem" class="grid gap-1 mt-4">
                    <flux:sidebar.item
                        icon="cog-6-tooth"
                        :href="route('profile.edit')"
                        :current="request()->routeIs('profile.*') || request()->routeIs('settings.*')"
                        wire:navigate
                    >
                        Pengaturan
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700/60">
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()?->name ?? 'User'" />
            </div>
        </flux:sidebar>

        <!-- Top Header for Shift Banner, Timezone Clock, Theme Toggle & Mobile User Menu -->
        <flux:header class="border-b border-zinc-200 bg-zinc-50/80 px-4 backdrop-blur-md dark:border-zinc-700/80 dark:bg-zinc-900/80">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            @php
                $shift = \App\Helpers\ShiftHelper::getCurrentShift();
                $currentWib = \App\Helpers\ShiftHelper::getFormattedDateTime();
                $user = auth()->user();
                $isSupervisor = $user?->isSupervisor() ?? false;
            @endphp

            <!-- Shift Banner & Real-time WIB Clock -->
            <div class="flex items-center gap-2.5 sm:gap-4">
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

            <flux:spacer />

            <!-- Dark / Light Mode Switcher & User Profile Menu -->
            <div class="flex items-center gap-2">
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" size="sm" icon="moon" class="dark:hidden" aria-label="Toggle Theme" />
                    <flux:button variant="ghost" size="sm" icon="sun" class="hidden dark:inline-flex" aria-label="Toggle Theme" />
                    <flux:menu>
                        <flux:menu.item x-on:click="$flux.appearance = 'light'" icon="sun">Mode Terang</flux:menu.item>
                        <flux:menu.item x-on:click="$flux.appearance = 'dark'" icon="moon">Mode Gelap</flux:menu.item>
                        <flux:menu.item x-on:click="$flux.appearance = 'system'" icon="computer-desktop">Ikuti Sistem</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                <!-- Mobile Profile Dropdown -->
                <div class="lg:hidden">
                    <flux:dropdown position="bottom" align="end">
                        <flux:profile
                            :name="$user?->name"
                            :initials="$user?->initials() ?? 'US'"
                            icon-trailing="chevron-down"
                        />
                        <flux:menu class="w-64">
                            <div class="flex items-center gap-2.5 px-2 py-2 text-start text-sm">
                                <flux:avatar
                                    :name="$user?->name"
                                    :initials="$user?->initials() ?? 'US'"
                                />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate font-medium">{{ $user?->name }}</flux:heading>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <flux:badge size="xs" :color="$isSupervisor ? 'amber' : 'sky'">
                                            {{ $isSupervisor ? 'Supervisor' : 'Petugas' }}
                                        </flux:badge>
                                        @if($user?->nip)
                                            <span class="text-[11px] font-mono text-zinc-500">{{ $user->nip }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <flux:menu.separator />
                            <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                                Pengaturan
                            </flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item
                                    as="button"
                                    type="submit"
                                    icon="arrow-right-start-on-rectangle"
                                    class="w-full cursor-pointer text-red-600 dark:text-red-400"
                                    data-test="logout-button"
                                >
                                    Keluar / Logout
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
