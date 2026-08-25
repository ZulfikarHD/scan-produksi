<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-100 antialiased dark:bg-zinc-950">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 text-center font-medium" wire:navigate>
                    <div class="flex size-12 items-center justify-center rounded-xl bg-zinc-900 text-white shadow-sm dark:bg-white dark:text-zinc-900">
                        <x-app-logo-icon class="size-7 fill-current" />
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-base font-bold tracking-tight text-zinc-900 dark:text-zinc-100">PT Percetakan Dokumen Sekuriti</span>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Unit Pengemasan & Produksi</span>
                    </div>
                </a>

                <div class="flex flex-col gap-6">
                    <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
