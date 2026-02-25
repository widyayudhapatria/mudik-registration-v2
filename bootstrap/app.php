<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\CheckFormLinkValid;
use App\Http\Middleware\CheckQuotaAvailable;
use App\Http\Middleware\CheckScannerPermission;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\ValidateFormLinkSignature;
use App\Http\Middleware\CheckRegistrationPeriod;
use App\Http\Middleware\CheckEmailRequestLimit;
use App\Exceptions\MudikException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Built-in Laravel
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,

            // Custom Middleware
            'signed.form' => ValidateFormLinkSignature::class, // Custom signed URL validator with HTTPS support
            'form.link.valid' => CheckFormLinkValid::class,
            'quota.available' => CheckQuotaAvailable::class,
            'scanner.permission' => CheckScannerPermission::class,
            'registration.period' => CheckRegistrationPeriod::class,
            'super.admin' => SuperAdminMiddleware::class,
            'email.hourly.limit' => CheckEmailRequestLimit::class,
        ]);

        $middleware->api(prepend: [
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle Throttle Requests Exception
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Silahkan tunggu beberapa saat.',
                    'retry_after' => (int) ($e->getHeaders()['Retry-After'] ?? 60),
                ], 429);
            }

            return response()->view('errors.throttle', [
                'retry_after' => (int) ($e->getHeaders()['Retry-After'] ?? 60),
            ], 429);
        });

        // Custom exception handling
        $exceptions->render(function (MudikException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json($e->toArray(), 400);
            }

            // For form link validation errors, show error page instead of redirecting back
            $linkErrorCodes = ['LINK_INVALID', 'LINK_EXPIRED', 'QUOTA_FULL'];
            if (in_array($e->getErrorCode()->value, $linkErrorCodes)) {
                return response()->view('errors.form-link-error', [
                    'errorCode' => $e->getErrorCode()->value,
                    'errorMessage' => $e->getMessage(),
                ], 400);
            }

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        });
    })->create();
