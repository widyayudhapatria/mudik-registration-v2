<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class CheckEmailRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:check-email-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check current email queue rate limiter status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = config('mudik.email.queue_rate_limit_per_hour', 90);
        $key = 'email-sending-queue';

        // Get attempts count
        $attempts = RateLimiter::attempts($key);
        $remaining = max(0, $limit - $attempts);
        $percentage = $limit > 0 ? round(($attempts / $limit) * 100, 2) : 0;

        // Get time until reset
        $availableIn = RateLimiter::availableIn($key);
        $minutesRemaining = $availableIn > 0 ? round($availableIn / 60, 2) : 0;

        // Get jobs count in queue
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        $this->newLine();
        $this->info('📧 Email Queue Rate Limiter Status');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Status
        if ($percentage >= 100) {
            $this->error("🔴 RATE LIMIT REACHED!");
            $this->warn("   Jobs will be throttled until reset");
        } elseif ($percentage >= 80) {
            $this->warn("🟡 Rate Limit Warning ($percentage%)");
        } else {
            $this->info("🟢 Rate Limit OK ($percentage%)");
        }

        $this->newLine();

        // Details table
        $this->table(
            ['Metric', 'Value'],
            [
                ['Rate Limit (per hour)', $limit . ' emails'],
                ['Current Usage', $attempts . ' emails'],
                ['Remaining', $remaining . ' emails'],
                ['Usage Percentage', $percentage . '%'],
                ['Resets In', $availableIn > 0 ? $minutesRemaining . ' minutes' : 'N/A'],
                ['Pending Jobs in Queue', $pendingJobs],
                ['Failed Jobs', $failedJobs],
                ['Timestamp', now()->format('Y-m-d H:i:s')],
            ]
        );

        $this->newLine();

        // Recommendations
        if ($percentage >= 100) {
            $this->warn('⚠️  Recommendations:');
            $this->line("   • Wait $minutesRemaining minutes for rate limit reset");
            $this->line('   • Check SMTP configuration if many jobs failing');
            $this->line('   • Consider upgrading SMTP plan if consistently hitting limit');
        } elseif ($percentage >= 80) {
            $this->warn('⚠️  Recommendations:');
            $this->line('   • Monitor closely, approaching rate limit');
            $this->line('   • Prepare to pause new dispatches if needed');
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
