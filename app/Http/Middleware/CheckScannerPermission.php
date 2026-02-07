<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\QrCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckScannerPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'error_code' => 'UNAUTHORIZED',
                ], 401);
            }
            abort(401);
        }

        // policy check
        if (!$admin->can('scan', QrCode::class)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk scan QR Code',
                    'error_code' => 'FORBIDDEN',
                ], 403);
            }
            abort(403, 'Anda tidak memiliki akses untuk scan QR Code');
        }

        return $next($request);
    }
}