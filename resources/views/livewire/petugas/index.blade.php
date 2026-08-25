<div class="space-y-6">
    <!-- Header Halaman & Tombol Aksi Utama -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="font-bold tracking-tight">Data Petugas</flux:heading>
            <flux:subheading size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">
                Kelola akun pengguna, hak akses peran Supervisor & Petugas, serta status keaktifan staf pengemasan.
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal" data-test="btn-tambah-petugas">
                Tambah Petugas
            </flux:button>
        </div>
    </div>

    <!-- Unified Card Container: Filter Toolbar & Data Table -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 space-y-6">
        <!-- Filter Bar & Pencarian Real-Time -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full sm:max-w-xs">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Cari nama, NP, atau username..."
                    clearable
                    data-test="input-search"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <div class="w-36">
                    <flux:select wire:model.live="role" placeholder="Semua Peran" data-test="select-role">
                        <flux:select.option value="">Semua Peran</flux:select.option>
                        <flux:select.option value="supervisor">Supervisor</flux:select.option>
                        <flux:select.option value="petugas">Petugas Kemas</flux:select.option>
                    </flux:select>
                </div>

                <div class="w-36">
                    <flux:select wire:model.live="status" placeholder="Semua Status" data-test="select-status">
                        <flux:select.option value="">Semua Status</flux:select.option>
                        <flux:select.option value="1">Aktif</flux:select.option>
                        <flux:select.option value="0">Nonaktif</flux:select.option>
                    </flux:select>
                </div>

                @if($search !== '' || $role !== '' || $status !== '')
                    <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="resetFilters" title="Reset filter">
                        Reset
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Data Table Petugas -->
        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>Petugas</flux:table.column>
                <flux:table.column>Nomor Pegawai (NP)</flux:table.column>
                <flux:table.column>Username</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Peran</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Login Terakhir</flux:table.column>
                <flux:table.column align="end">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($users as $user)
                    <flux:table.row :key="$user->id" data-test="user-row-{{ $user->id }}">
                        <!-- Nama & Inisial -->
                        <flux:table.cell variant="strong">
                            <div class="flex items-center gap-2.5">
                                <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                                    @if(auth()->id() === $user->id)
                                        <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">(Akun Anda)</span>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>

                        <!-- Nomor Pegawai -->
                        <flux:table.cell>
                            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                                {{ $user->nip ?? '-' }}
                            </span>
                        </flux:table.cell>

                        <!-- Username -->
                        <flux:table.cell>
                            <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $user->username ?? '-' }}</span>
                        </flux:table.cell>

                        <!-- Email -->
                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $user->email }}</span>
                        </flux:table.cell>

                        <!-- Peran / Role -->
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$user->isSupervisor() ? 'amber' : 'sky'">
                                {{ $user->roleLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <!-- Status Aktif -->
                        <flux:table.cell>
                            @if($user->is_active)
                                <flux:badge size="sm" color="green" inset="top bottom">Aktif</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc" inset="top bottom">Nonaktif</flux:badge>
                            @endif
                        </flux:table.cell>

                        <!-- Login Terakhir -->
                        <flux:table.cell>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $user->last_login_at ? $user->last_login_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : 'Belum Pernah' }}
                            </span>
                        </flux:table.cell>

                        <!-- Menu Aksi -->
                        <flux:table.cell align="end">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" data-test="action-menu-{{ $user->id }}" />
                                <flux:menu>
                                    <flux:menu.item wire:click="openEditModal({{ $user->id }})" icon="pencil-square" data-test="btn-edit-{{ $user->id }}">
                                        Edit Data
                                    </flux:menu.item>

                                    @if(auth()->id() !== $user->id)
                                        <flux:menu.item wire:click="toggleStatus({{ $user->id }})" icon="{{ $user->is_active ? 'no-symbol' : 'check-circle' }}" data-test="btn-toggle-status-{{ $user->id }}">
                                            {{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="confirmDelete({{ $user->id }})" icon="trash" class="text-red-600 dark:text-red-400" data-test="btn-delete-{{ $user->id }}">
                                            Hapus Akun
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <flux:icon.users class="size-8 text-zinc-400" />
                                <span class="font-medium text-sm">Tidak ada data petugas yang cocok dengan kriteria pencarian.</span>
                                @if($search !== '' || $role !== '' || $status !== '')
                                    <flux:button variant="subtle" size="xs" wire:click="resetFilters" class="mt-1">
                                        Bersihkan Filter
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Modal Form Tambah / Ubah Data Petugas -->
    <flux:modal name="petugas-form" class="max-w-lg space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingUserId ? 'Ubah Data Petugas' : 'Tambah Petugas Baru' }}</flux:heading>
            <flux:subheading>
                {{ $editingUserId ? 'Perbarui data identitas dan hak akses akun petugas kemas.' : 'Lengkapi formulir berikut untuk mendaftarkan operator kemas baru.' }}
            </flux:subheading>
        </div>

        <form wire:submit.prevent="save" class="space-y-4">
            <flux:field>
                <flux:label>Nama Lengkap</flux:label>
                <flux:input wire:model="name" placeholder="Contoh: Budi Santoso" required data-test="form-name" />
                <flux:error name="name" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Nomor Pegawai (NP)</flux:label>
                    <flux:input wire:model="nip" placeholder="Contoh: 7426 atau T0087" required data-test="form-nip" />
                    <flux:error name="nip" />
                </flux:field>

                <flux:field>
                    <flux:label>Username</flux:label>
                    <flux:input wire:model="username" placeholder="Contoh: budi_kemas" required data-test="form-username" />
                    <flux:error name="username" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Alamat Email</flux:label>
                <flux:input wire:model="email" type="email" placeholder="Contoh: budi@pds.co.id" required data-test="form-email" />
                <flux:error name="email" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Peran / Hak Akses</flux:label>
                    <flux:select wire:model="userRole" data-test="form-role">
                        <flux:select.option value="petugas">Petugas Kemas</flux:select.option>
                        <flux:select.option value="supervisor">Supervisor / Ka. Unit</flux:select.option>
                    </flux:select>
                    <flux:error name="userRole" />
                </flux:field>

                <flux:field class="flex flex-col justify-end">
                    <flux:label class="sr-only">Status Akun</flux:label>
                    <div class="pt-2">
                        <flux:checkbox wire:model="is_active" label="Status Akun Aktif" data-test="form-is-active" />
                    </div>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Kata Sandi</flux:label>
                <flux:input
                    wire:model="password"
                    type="password"
                    viewable
                    :placeholder="$editingUserId ? 'Kosongkan jika tidak ingin mengubah' : 'Minimal 8 karakter'"
                    :description="$editingUserId ? 'Kosongkan jika tidak ingin mengubah kata sandi petugas.' : null"
                    data-test="form-password"
                />
                <flux:error name="password" />
            </flux:field>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" data-test="form-submit">
                    {{ $editingUserId ? 'Simpan Perubahan' : 'Simpan Data' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Konfirmasi Hapus Petugas -->
    <flux:modal name="delete-confirm" class="max-w-md space-y-6">
        <div>
            <flux:heading size="lg">Konfirmasi Hapus Akun</flux:heading>
            <flux:subheading class="mt-2">
                Apakah Anda yakin ingin menghapus akun petugas <strong class="text-zinc-900 dark:text-zinc-100">{{ $confirmingUserName }}</strong>? Tindakan ini tidak dapat dibatalkan.
            </flux:subheading>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="filled">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteUser" data-test="confirm-delete-btn">
                Ya, Hapus Akun
            </flux:button>
        </div>
    </flux:modal>
</div>
