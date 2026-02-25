<?php

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\EmailRequestTracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailRequestLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $limit = config('mudik.email.hourly_submission_limit', 100);

        try {
            // Use transaction with lock to prevent race condition
            DB::beginTransaction();

            $tracker = EmailRequestTracker::getCurrentWindow();

            // Lock untuk prevent race condition (pessimistic locking)
            $tracker = EmailRequestTracker::where('id', $tracker->id)
                ->lockForUpdate()
                ->first();

            // Check if limit reached
            if ($tracker->hasReachedLimit($limit)) {
                DB::rollBack();

                // Prepare human-friendly current and next hour window strings
                $currentStart = $tracker->window_start->format('H:i');
                $currentEnd = $tracker->window_end->format('H:i');

                $nextWindowStart = $tracker->window_end->copy()->addSecond()->format('H:i');
                $nextWindowEnd = $tracker->window_end->copy()->addHour()->format('H:i');

                $message = sprintf(
                    "Kuota request form untuk jam sekarang (%s - %s) sudah penuh.\n\n" .
                        "Silahkan coba lagi satu jam kedepan (%s - %s) dan selama kuota belum habis.\n\n" .
                        "Terima kasih atas pengertiannya.",
                    $currentStart,
                    $currentEnd,
                    $nextWindowStart,
                    $nextWindowEnd
                );

                throw new MudikException(
                    ErrorCode::ServerError,
                    $message
                );
            }

            // Increment counter
            $tracker->incrementRequest();

            DB::commit();

            // Share tracker info dengan request
            $request->attributes->set('email_request_tracker', $tracker);
            $request->attributes->set('remaining_quota', $tracker->getRemainingQuota($limit));
        } catch (MudikException $e) {
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            // Log error but allow request to proceed (graceful degradation)
            Log::error('Email request limiter error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $next($request);
    }
}
