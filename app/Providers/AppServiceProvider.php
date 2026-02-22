<?php

namespace App\Providers;

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
    }
}
