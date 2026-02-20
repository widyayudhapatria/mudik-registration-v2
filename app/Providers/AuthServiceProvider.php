<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\DailyQuota;
use App\Models\Destination;
use App\Models\QrCode;
use App\Models\Registration;
use App\Policies\AdminPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\DestinationPolicy;
use App\Policies\QrCodePolicy;
use App\Policies\QuotaPolicy;
use App\Policies\RegistrationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Registration::class => RegistrationPolicy::class,
        QrCode::class => QrCodePolicy::class,
        DailyQuota::class => QuotaPolicy::class,
        Destination::class => DestinationPolicy::class,
        Admin::class => AdminPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
