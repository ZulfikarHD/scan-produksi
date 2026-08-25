<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request by verifying user role access.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Ambil string role yang diizinkan dalam format lowercase
        $allowedRoles = array_map(function (string $role): string {
            return strtolower(trim($role));
        }, $roles);

        $userRoleValue = $user->role->value;

        if (! in_array(strtolower($userRoleValue), $allowedRoles, true)) {
            abort(403, 'Akses tidak diizinkan. Halaman ini hanya dapat diakses oleh peran: '.implode(', ', $roles).'.');
        }

        return $next($request);
    }
}
