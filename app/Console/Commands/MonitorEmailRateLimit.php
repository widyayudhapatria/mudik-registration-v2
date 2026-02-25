<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class MonitorEmailRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor-email-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor email queue rate limiter status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = config('mudik.email.queue_rate_limit_per_hour', 90);
        $key = 'email-sending-queue';

        $this->info('📧 Email Queue Rate Limiter Monitor');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        while (true) {
            $this->monitorStatus($key, $limit);
            sleep(5); // Refresh every 5 seconds
        }
    }

    protected function monitorStatus(string $key, int $limit): void
    {
        // Get attempts count
        $attempts = RateLimiter::attempts($key);

        // Calculate remaining
        $remaining = max(0, $limit - $attempts);

        // Calculate percentage
        $percentage = $limit > 0 ? round(($attempts / $limit) * 100, 2) : 0;

        // Get time until reset
        $availableIn = RateLimiter::availableIn($key);
        $minutesRemaining = $availableIn > 0 ? round($availableIn / 60, 2) : 0;

        // Clear screen (optional)
        // echo "\033[2J\033[;H";

        $this->newLine();
        $this->line('⏰ ' . now()->format('Y-m-d H:i:s'));
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Status color
        if ($percentage >= 100) {
            $this->error("🔴 RATE LIMIT REACHED!");
        } elseif ($percentage >= 80) {
            $this->warn("🟡 Rate Limit Warning");
        } else {
            $this->info("🟢 Rate Limit OK");
        }

        $this->line("📊 Usage: $attempts / $limit emails ($percentage%)");
        $this->line("✅ Remaining: $remaining emails");

        if ($availableIn > 0) {
            $this->line("⏳ Resets in: $minutesRemaining minutes");
        } else {
            $this->line("🔄 Rate limit window active");
        }

        // Progress bar
        $this->renderProgressBar($attempts, $limit);

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    protected function renderProgressBar(int $current, int $max): void
    {
        $percentage = $max > 0 ? ($current / $max) * 100 : 0;
        $bars = (int) round($percentage / 2); // 50 chars max
        $spaces = 50 - $bars;

        $color = match (true) {
            $percentage >= 100 => 'red',
            $percentage >= 80 => 'yellow',
            default => 'green'
        };

        $this->line(
            '<fg=' . $color . '>' .
                str_repeat('█', $bars) .
                '</>' .
                str_repeat('░', $spaces) .
                ' ' . round($percentage, 1) . '%'
        );
    }
}
