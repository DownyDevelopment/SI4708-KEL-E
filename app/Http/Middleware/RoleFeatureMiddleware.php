<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleFeatureMiddleware
{
    /**
     * Restrict pengawas-area routes by sub-role feature access.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $role = auth()->user()?->role;

        $allowed = match ($feature) {
            'dashboard' => ['pengawas', 'supervisor', 'relawan'],
            'operasional' => ['pengawas', 'supervisor'],
            'distribusi' => ['pengawas', 'supervisor'],
            'ekonomi' => ['pengawas'],
            'pelaporan' => ['pengawas', 'supervisor', 'relawan'],
            'profiling' => ['pengawas', 'supervisor'],
            'profil-pekerja' => ['pengawas', 'supervisor'],
            default => ['pengawas', 'supervisor', 'relawan'],
        };

        if (!in_array($role, $allowed, true)) {
            return redirect('/pengawas/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}
