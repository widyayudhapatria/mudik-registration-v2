<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\FormLink;
use App\Models\ScanLog;
use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clear expired form links daily
Schedule::call(function () {
    $count = FormLink::where('expired_at', '<', now())
        ->where('status', 'pending')
        ->delete();
    
    Log::info("Cleared {$count} expired form links");
})->daily()->at('00:00');

// Clear old scan logs (keep last 90 days)
Schedule::call(function () {
    $count = ScanLog::where('scanned_at', '<', now()->subDays(90))
        ->delete();
    
    Log::info("Cleared {$count} old scan logs");
})->weekly()->sundays()->at('02:00');

// Clear old email logs (keep last 90 days)
Schedule::call(function () {
    $count = EmailLog::where('created_at', '<', now()->subDays(90))
        ->delete();
    
    Log::info("Cleared {$count} old email logs");
})->weekly()->sundays()->at('02:30');

// Retry failed emails
Schedule::call(function () {
    EmailService::retryFailedEmails();
})->hourly();