<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-1.5 text-center">
        <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            Masuk ke Sistem
        </h1>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Silakan masukkan kredensial akun kerja Anda
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="authenticate" class="flex flex-col gap-5">
        <!-- Login identifier (NP / Username / Email) -->
        <flux:field>
            <flux:label>Nomor Pegawai (NP) / Username / Email</flux:label>
            <flux:input
                wire:model="login"
                name="login"
                type="text"
                required
                autofocus
                placeholder="Contoh: 7426 atau T0087"
                autocomplete="username"
            />
            <flux:error name="login" />
        </flux:field>

        <!-- Password -->
        <flux:field>
            <flux:label>Kata Sandi</flux:label>
            <flux:input
                wire:model="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                viewable
            />
            <flux:error name="password" />
        </flux:field>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <flux:checkbox wire:model="remember" name="remember" label="Ingat Saya" />
        </div>

        <div>
            <flux:button
                variant="primary"
                type="submit"
                class="w-full justify-center"
                data-test="login-button"
            >
                <span wire:loading.remove wire:target="authenticate">Masuk ke Sistem</span>
                <span wire:loading wire:target="authenticate" class="inline-flex items-center gap-2">
                    <flux:icon.arrow-path class="size-4 animate-spin" />
                    <span>Memverifikasi...</span>
                </span>
            </flux:button>
        </div>
    </form>
</div>
