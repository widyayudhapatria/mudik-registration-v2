<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckRegistrationPeriod
{
    public function handle(Request $request, Closure $next)
    {
        $now   = Carbon::now();
        $start = Carbon::parse(config('mudik.registration.start_date'));
        $end   = Carbon::parse(config('mudik.registration.end_date'));

        if ($now->lt($start) || $now->gt($end)) {
            $message = $now->lt($start)
                ? "Pendaftaran belum dibuka. Pendaftaran dibuka mulai {$start->translatedFormat('d F Y H:i')} WIB."
                : "Pendaftaran telah ditutup pada {$end->translatedFormat('d F Y H:i')} WIB.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'REGISTRATION_CLOSED',
                    'data' => [
                        'start_date' => $start->toISOString(),
                        'end_date'   => $end->toISOString(),
                    ],
                ], 403);
            }

            return back()->withErrors(['registration' => $message]);
        }

        return $next($request);
    }
}