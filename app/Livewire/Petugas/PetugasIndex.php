<?php

namespace App\Livewire\Petugas;

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data Petugas')]
class PetugasIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    #[Url]
    public string $status = '';

    // State untuk form modal tambah / edit petugas
    public ?int $editingUserId = null;

    public string $name = '';

    public string $nip = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $userRole = 'petugas';

    public bool $is_active = true;

    // State untuk konfirmasi hapus data petugas
    public ?int $confirmingUserId = null;

    public string $confirmingUserName = '';

    /**
     * Render view daftar data petugas dengan filter dan pagination.
     */
    public function render(): View
    {
        $query = User::query();

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->role !== '') {
            $query->where('role', $this->role);
        }

        if ($this->status !== '') {
            $query->where('is_active', $this->status === '1');
        }

        $users = $query->latest('id')->paginate(10);

        return view('livewire.petugas.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Reset pagination saat kata kunci pencarian berubah.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination saat filter peran diubah.
     */
    public function updatingRole(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination saat filter status diubah.
     */
    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Reset seluruh filter ke kondisi bawaan.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'role', 'status']);
        $this->resetPage();
    }

    /**
     * Buka modal dialog untuk menambah petugas baru.
     */
    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['editingUserId', 'name', 'nip', 'username', 'email', 'password']);
        $this->userRole = 'petugas';
        $this->is_active = true;

        Flux::modal('petugas-form')->show();
    }

    /**
     * Buka modal dialog untuk mengedit data petugas yang dipilih.
     */
    public function openEditModal(int $userId): void
    {
        $this->resetValidation();

        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->nip = (string) ($user->nip ?? '');
        $this->username = (string) ($user->username ?? '');
        $this->email = $user->email;
        $this->password = '';
        $this->userRole = $user->role->value;
        $this->is_active = (bool) $user->is_active;

        Flux::modal('petugas-form')->show();
    }

    /**
     * Simpan data petugas baru atau perbarui data yang diedit.
     */
    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:30', 'unique:users,nip,'.($this->editingUserId ?? 'NULL')],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.($this->editingUserId ?? 'NULL')],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.($this->editingUserId ?? 'NULL')],
            'userRole' => ['required', 'in:supervisor,petugas'],
            'password' => [$this->editingUserId ? 'nullable' : 'required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'nip.required' => 'Nomor Pegawai (NP) wajib diisi.',
            'nip.unique' => 'Nomor Pegawai (NP) sudah terdaftar dalam sistem.',
            'nip.max' => 'Nomor Pegawai (NP) maksimal 30 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'username.max' => 'Username maksimal 50 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar dalam sistem.',
            'userRole.required' => 'Peran pengguna wajib dipilih.',
            'userRole.in' => 'Peran pengguna harus berupa Supervisor atau Petugas.',
            'password.required' => 'Kata sandi wajib diisi untuk akun baru.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $payload = [
            'name' => $this->name,
            'nip' => $this->nip,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->userRole,
            'is_active' => $this->is_active,
        ];

        if (! empty($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($payload);

            Flux::toast('Data petugas berhasil diperbarui.', variant: 'success');
        } else {
            User::create($payload);

            Flux::toast('Data petugas berhasil ditambahkan.', variant: 'success');
        }

        Flux::modal('petugas-form')->close();
        $this->reset(['editingUserId', 'name', 'nip', 'username', 'email', 'password', 'userRole', 'is_active']);
    }

    /**
     * Beralih status aktif / nonaktif untuk akun petugas.
     */
    public function toggleStatus(int $userId): void
    {
        if (Auth::id() === $userId) {
            Flux::toast('Anda tidak dapat menonaktifkan akun yang sedang digunakan.', variant: 'danger');

            return;
        }

        $user = User::findOrFail($userId);
        $user->is_active = ! $user->is_active;
        $user->save();

        Flux::toast('Status akun petugas berhasil diperbarui.', variant: 'success');
    }

    /**
     * Buka modal dialog konfirmasi penghapusan akun petugas.
     */
    public function confirmDelete(int $userId): void
    {
        if (Auth::id() === $userId) {
            Flux::toast('Anda tidak dapat menghapus akun Anda sendiri.', variant: 'danger');

            return;
        }

        $user = User::findOrFail($userId);

        $this->confirmingUserId = $user->id;
        $this->confirmingUserName = $user->name;

        Flux::modal('delete-confirm')->show();
    }

    /**
     * Eksekusi penghapusan akun petugas setelah konfirmasi.
     */
    public function deleteUser(): void
    {
        if (! $this->confirmingUserId) {
            return;
        }

        if (Auth::id() === $this->confirmingUserId) {
            Flux::toast('Anda tidak dapat menghapus akun Anda sendiri.', variant: 'danger');
            Flux::modal('delete-confirm')->close();

            return;
        }

        $user = User::findOrFail($this->confirmingUserId);

        // Proteksi jika user tercatat sebagai pembuat dokumen laporan produksi
        if (method_exists($user, 'productionReports') && $user->productionReports()->exists()) {
            Flux::toast('Akun petugas yang sudah tercatat pada Laporan Hasil Produksi tidak dapat dihapus permanen untuk menjaga keabsahan riwayat dokumen. Silakan gunakan opsi Nonaktifkan Akun.', variant: 'danger');
            Flux::modal('delete-confirm')->close();
            $this->reset(['confirmingUserId', 'confirmingUserName']);

            return;
        }

        $user->delete();

        Flux::modal('delete-confirm')->close();
        Flux::toast('Data petugas berhasil dihapus.', variant: 'success');
        $this->reset(['confirmingUserId', 'confirmingUserName']);
    }
}
