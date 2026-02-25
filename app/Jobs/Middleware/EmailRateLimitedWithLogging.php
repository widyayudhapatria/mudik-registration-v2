<?php

namespace App\Jobs\Middleware;

use Illuminate\Queue\Middleware\RateLimited as BaseRateLimited;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EmailRateLimitedWithLogging
{
    /**
     * Process the queued job.
     */
    public function handle($job, $next)
    {
        $key = 'email-sending-queue';
        $limit = config('mudik.email.queue_rate_limit_per_hour', 90);
        
        // Check current attempts before processing
        $attempts = RateLimiter::attempts($key);
        $remaining = max(0, $limit - $attempts);
        
        // If rate limit will be exceeded
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $availableIn = RateLimiter::availableIn($key);
            $minutesRemaining = round($availableIn / 60, 2);
            
            Log::warning('📧 Email Rate Limit Reached', [
                'job' => get_class($job),
                'attempts' => $attempts,
                'limit' => $limit,
                'available_in_minutes' => $minutesRemaining,
                'will_retry_at' => now()->addSeconds($availableIn)->format('Y-m-d H:i:s'),
            ]);
            
            // Release job back to queue
            return $job->release($availableIn);
        }
        
        // Log if approaching limit
        $percentage = ($attempts / $limit) * 100;
        if ($percentage >= 80 && $percentage < 90) {
            Log::info('⚠️ Email Rate Limit Warning', [
                'job' => get_class($job),
                'attempts' => $attempts,
                'limit' => $limit,
                'remaining' => $remaining,
                'percentage' => round($percentage, 2) . '%',
            ]);
        }
        
        // Hit the rate limiter
        RateLimiter::hit($key, 3600); // 1 hour decay
        
        // Log successful processing
        Log::debug('📨 Email Job Processing', [
            'job' => get_class($job),
            'attempts' => $attempts + 1,
            'limit' => $limit,
            'remaining' => $remaining - 1,
        ]);
        
        return $next($job);
    }
}
