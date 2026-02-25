<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        Paginator::useBootstrap();

        // CRITICAL: Force HTTPS scheme globally if APP_URL uses HTTPS
        // This ensures all URL generation (including signed URLs) use HTTPS
        // preventing signature mismatch between http:// and https://
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            $this->app['url']->forceRootUrl($appUrl);
        }

        // Email Queue Rate Limiter
        // Shared across all queue workers (database cache)
        // Configurable via EMAIL_QUEUE_RATE_LIMIT_PER_HOUR in .env
        $emailRateLimitPerHour = config('mudik.email.queue_rate_limit_per_hour', 90);

        RateLimiter::for('email-queue', function ($job) use ($emailRateLimitPerHour) {
            return Limit::perHour($emailRateLimitPerHour)->by('email-sending-queue');
        });
    }
}
