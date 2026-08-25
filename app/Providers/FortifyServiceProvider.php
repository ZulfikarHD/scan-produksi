<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureAuthentication();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $login = $request->input('login', $request->input('email', $request->input('username', $request->input('nip'))));
            $throttleKey = Str::transliterate(Str::lower((string) $login).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    /**
     * Configure custom Fortify authentication to support NP, Username, or Email,
     * along with account active status verification and last login timestamp.
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $login = $request->input('login', $request->input('email', $request->input('username', $request->input('nip'))));

            if (empty($login) || empty($request->password)) {
                return null;
            }

            $user = User::query()
                ->where('email', $login)
                ->orWhere('username', $login)
                ->orWhere('nip', $login)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if (! $user->is_active) {
                    throw ValidationException::withMessages([
                        'email' => ['Akun Anda sedang dinonaktifkan. Silakan hubungi Supervisor.'],
                        'login' => ['Akun Anda sedang dinonaktifkan. Silakan hubungi Supervisor.'],
                    ]);
                }

                $user->update(['last_login_at' => now()]);

                return $user;
            }

            return null;
        });
    }
}
