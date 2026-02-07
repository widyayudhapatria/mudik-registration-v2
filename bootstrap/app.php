<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckFormLinkValid;
use App\Http\Middleware\CheckQuotaAvailable;
use App\Http\Middleware\CheckScannerPermission;
use App\Exceptions\MudikException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
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
            'form.link.valid' => CheckFormLinkValid::class,
            'quota.available' => CheckQuotaAvailable::class,
            'scanner.permission' => CheckScannerPermission::class,
        ]);

        $middleware->api(prepend: [
            SubstituteBindings::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling
        $exceptions->render(function (MudikException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json($e->toArray(), 400);
            }

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        });
    })->create();
