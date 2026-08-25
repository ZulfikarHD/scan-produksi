@props([
    'name' => auth()->user()?->name ?? 'User',
])

@php
    $user = auth()->user();
    $roleLabel = $user?->roleLabel() ?? 'Petugas Kemas';
    $isSupervisor = $user?->isSupervisor() ?? false;
@endphp

<flux:dropdown position="top" align="start" class="w-full">
    <flux:sidebar.profile
        :name="$name"
        :initials="$user?->initials() ?? 'US'"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    >
        <x-slot:subtitle>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $isSupervisor ? 'text-amber-600 dark:text-amber-400' : 'text-sky-600 dark:text-sky-400' }}">
                {{ $roleLabel }}
            </span>
        </x-slot:subtitle>
    </flux:sidebar.profile>

    <flux:menu class="w-64">
        <div class="flex items-center gap-3 px-2 py-2 text-start text-sm">
            <flux:avatar
                :name="$user?->name"
                :initials="$user?->initials() ?? 'US'"
                size="sm"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate font-medium">{{ $user?->name }}</flux:heading>
                <div class="flex items-center gap-1.5 my-0.5">
                    <flux:badge size="xs" :color="$isSupervisor ? 'amber' : 'sky'">
                        {{ $isSupervisor ? 'Supervisor' : 'Petugas' }}
                    </flux:badge>
                    @if($user?->nip)
                        <span class="text-[11px] font-mono text-zinc-500">{{ $user->nip }}</span>
                    @endif
                </div>
                <flux:text class="truncate text-xs text-zinc-500">{{ $user?->email }}</flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                Pengaturan Akun
            </flux:menu.item>
        </flux:menu.radio.group>

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
