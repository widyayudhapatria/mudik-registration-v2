<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmailRequestTracker extends Model
{
    protected $fillable = [
        'date',
        'hour',
        'request_count',
        'window_start',
        'window_end',
    ];

    protected $casts = [
        'date' => 'date',
        'window_start' => 'datetime',
        'window_end' => 'datetime',
    ];

    /**
     * Get or create tracker for current hour window
     */
    public static function getCurrentWindow(): self
    {
        $now = Carbon::now();
        $date = $now->toDateString();
        $hour = $now->hour;

        return self::firstOrCreate(
            [
                'date' => $date,
                'hour' => $hour,
            ],
            [
                'request_count' => 0,
                'window_start' => $now->copy()->startOfHour(),
                'window_end' => $now->copy()->endOfHour(),
            ]
        );
    }

    /**
     * Increment request count atomically
     */
    public function incrementRequest(): bool
    {
        return $this->increment('request_count') > 0;
    }

    /**
     * Check if limit reached
     */
    public function hasReachedLimit(int $limit = 100): bool
    {
        return $this->request_count >= $limit;
    }

    /**
     * Get remaining quota
     */
    public function getRemainingQuota(int $limit = 100): int
    {
        return max(0, $limit - $this->request_count);
    }

    /**
     * Get next available window
     */
    public function getNextAvailableWindow(): Carbon
    {
        return $this->window_end->copy()->addSecond();
    }
}
