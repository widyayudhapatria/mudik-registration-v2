<?php

namespace App\Http\Middleware;

use App\Actions\Quota\GetQuotaAction;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckQuotaAvailable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get today's quota
        $quota = GetQuotaAction::run(Carbon::today());

        // Check if quota exists
        if (!$quota) {
            throw new MudikException(
                ErrorCode::QuotaFull,
                'Kuota untuk hari ini belum tersedia.\nSilahkan coba lagi nanti.'
            );
        }

        // Check if quota is available
        if (!$quota->hasAvailableQuota()) {
            throw new MudikException(ErrorCode::QuotaFull);
        }

        // Share quota with request
        $request->merge(['dailyQuota' => $quota]);

        return $next($request);
    }
}