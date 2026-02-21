<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;

class ValidateFormLinkSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = config('app.url');

        Log::info('ValidateFormLinkSignature: Middleware started', [
            'app_url' => $appUrl,
            'request_url' => $request->fullUrl(),
            'request_scheme' => $request->getScheme(),
            'is_secure' => $request->secure(),
            'has_signature' => $request->has('signature'),
            'has_expires' => $request->has('expires'),
        ]);

        // CRITICAL FIX: Force HTTPS in request if APP_URL uses HTTPS
        // This ensures signature validation uses the same scheme as URL generation
        if (str_starts_with($appUrl, 'https://')) {
            // Force HTTPS scheme in the request
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', 443);

            // Also set the X-Forwarded-Proto header for consistency
            if (!$request->headers->has('X-Forwarded-Proto')) {
                $request->headers->set('X-Forwarded-Proto', 'https');
            }

            Log::info('ValidateFormLinkSignature: Forced HTTPS in request', [
                'new_scheme' => $request->getScheme(),
                'new_is_secure' => $request->secure(),
            ]);
        }

        // Log the URL that will be validated
        $fullUrl = $request->fullUrl();
        Log::info('ValidateFormLinkSignature: Validating URL', [
            'full_url' => $fullUrl,
            'url_scheme' => parse_url($fullUrl, PHP_URL_SCHEME),
            'signature_param' => $request->get('signature') ? substr($request->get('signature'), 0, 20) . '...' : 'N/A',
            'expires_param' => $request->get('expires'),
            'expires_human' => $request->has('expires') ? date('Y-m-d H:i:s', $request->get('expires')) : 'N/A',
        ]);

        // Validate the signature
        if (!$request->hasValidSignature()) {
            Log::error('ValidateFormLinkSignature: INVALID SIGNATURE', [
                'full_url' => $fullUrl,
                'token' => $request->route('token'),
            ]);

            // Check if link has expired
            if ($request->has('expires') && now()->timestamp > $request->get('expires')) {
                Log::error('ValidateFormLinkSignature: Link EXPIRED', [
                    'expires_timestamp' => $request->get('expires'),
                    'current_timestamp' => now()->timestamp,
                    'expired_since' => now()->diffForHumans(now()->createFromTimestamp($request->get('expires'))),
                ]);

                abort(403, 'Link formulir sudah kedaluwarsa. Silakan minta link baru melalui email.');
            }

            abort(403, 'Link formulir tidak valid. Pastikan Anda menggunakan link yang dikirim melalui email.');
        }

        Log::info('ValidateFormLinkSignature: ✅ SIGNATURE VALID - Proceeding to controller');

        return $next($request);
    }
}
