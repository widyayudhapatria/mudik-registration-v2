<?php

namespace App\Http\Controllers\Public;

use App\Actions\Registration\SubmitRegistrationAction;
use App\Data\RegistrationData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show registration form.
     */
    public function show(Request $request, string $token): View
    {
        $formLink = $request->formLink;

        // CRITICAL: Force HTTPS if APP_URL uses HTTPS
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            Log::info('RegistrationController::show - Forced HTTPS scheme', [
                'app_url' => $appUrl,
            ]);
        }

        Log::info('RegistrationController::show - Generating submit URL', [
            'token' => $token,
            'expired_at' => $formLink->expired_at,
            'app_url' => $appUrl,
            'request_url' => $request->url(),
            'request_scheme' => $request->getScheme(),
            'is_secure' => $request->secure(),
        ]);

        $submitUrl = URL::temporarySignedRoute(
            'public.registration.submit',
            $formLink->expired_at,
            ['token' => $token]
        );

        Log::info('RegistrationController::show - Generated submit URL', [
            'token' => $token,
            'submit_url' => $submitUrl,
            'submit_scheme' => parse_url($submitUrl, PHP_URL_SCHEME),
        ]);

        return view('pages.form', [
            'formLink' => $formLink,
            'token' => $token,
            'submitUrl' => $submitUrl,
        ]);
    }

    /**
     * Submit registration form.
     */
    public function submit(Request $request, string $token, RegistrationData $data): JsonResponse
    {
        Log::info('RegistrationController::submit - Request received', [
            'token' => $token,
            'request_url' => $request->url(),
            'request_full_url' => $request->fullUrl(),
            'request_scheme' => $request->getScheme(),
            'is_secure' => $request->secure(),
            'has_signature' => $request->has('signature'),
            'has_expires' => $request->has('expires'),
            'app_url' => config('app.url'),
        ]);

        // Rate limiting: max 5 submissions per hour per token+IP
        $key = 'registration-submit:' . $token . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            Log::warning('RegistrationController::submit - Rate limit exceeded', [
                'token' => $token,
                'ip' => $request->ip(),
                'available_in_seconds' => $seconds,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan submit. Silakan coba lagi dalam {$minutes} menit."
            ], 429);
        }

        RateLimiter::hit($key, 3600); // 1 hour

        try {
            $formLink = $request->formLink;
            $registration = SubmitRegistrationAction::run($formLink, $data);

            return $this->responseSuccess(
                "Terima kasih telah mendaftar!<br/>" .
                    "Mohon tunggu, pendaftaran Anda sedang diproses.<br/><br/>" .
                    "Tim kami akan melakukan verifikasi data dalam waktu maksimal <b>1x24 jam.</b><br/><br/>" .
                    "Anda akan menerima notifikasi via email jika pendaftaran Anda disetujui atau memerlukan perbaikan data.<br/>",
                [
                    'registration_id' => $registration->id,
                    'email' => $formLink->email,
                ]
            );
        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }
}
