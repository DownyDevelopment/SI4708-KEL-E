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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check() || auth()->user()->role !== $role) {
            if (auth()->check()) {
                $redirect = auth()->user()->role === 'admin' ? '/admin/dashboard' : '/pengawas/dashboard';
                return redirect($redirect);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
