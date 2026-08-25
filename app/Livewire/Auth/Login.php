<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth.card')]
#[Title('Masuk ke Sistem')]
class Login extends Component
{
    public string $login = '';

    public string $password = '';

    public bool $remember = false;

    /**
     * Render view formulir login.
     */
    public function render(): View
    {
        return view('livewire.auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function authenticate(): mixed
    {
        $this->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Nomor Pegawai, Username, atau Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited();

        $user = User::query()
            ->where('email', $this->login)
            ->orWhere('username', $this->login)
            ->orWhere('nip', $this->login)
            ->first();

        if (! $user || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Nomor Pegawai/Username atau Kata Sandi yang dimasukkan salah.',
            ]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Supervisor.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $user->update(['last_login_at' => now()]);

        Auth::login($user, $this->remember);

        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Pastikan request login belum melebihi batas percobaan (rate limit).
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan login gagal. Demi keamanan, silakan coba lagi setelah {$seconds} detik.",
        ]);
    }

    /**
     * Buat throttle key unik berdasarkan kredensial login dan IP pengguna.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->login).'|'.request()->ip());
    }
}
