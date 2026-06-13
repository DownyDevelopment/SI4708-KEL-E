<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $allowed = explode(',', $roles);

        if (!auth()->check() || !in_array(auth()->user()->role, $allowed, true)) {
            if (auth()->check()) {
                $role = auth()->user()->role;
                if ($role === 'admin') {
                    return redirect('/admin/dashboard');
                }
                if ($role === 'pengawas') {
                    return redirect('/pengawas/dashboard');
                }
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
