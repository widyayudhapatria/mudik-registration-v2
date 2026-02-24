<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();

        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Hanya super admin yang dapat mengakses halaman ini');
        }

        return $next($request);
    }
}
