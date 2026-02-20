<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class DailyQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_id',
        'date',
        'quota_daily',
        'used_daily',
        'remaining_daily',
    ];

    protected $casts = [
        'destination_id' => 'integer',
        'date' => 'date:Y-m-d',
        'quota_daily' => 'integer',
        'used_daily' => 'integer',
        'remaining_daily' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->remaining_daily = $model->quota_daily - $model->used_daily;
        });

        static::saved(function ($model) {
            // Clear cache for specific destination and date
            $cacheKey = "quota.destination.{$model->destination_id}.{$model->date->toDateString()}";
            Cache::forget($cacheKey);

            // Clear general cache for date
            $cacheKey = "quota.{$model->date->toDateString()}";
            Cache::forget($cacheKey);
        });

        static::deleted(function ($model) {
            $cacheKey = "quota.destination.{$model->destination_id}.{$model->date->toDateString()}";
            Cache::forget($cacheKey);

            $cacheKey = "quota.{$model->date->toDateString()}";
            Cache::forget($cacheKey);
        });
    }

    /**
     * Get the destination for this daily quota.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Get quota for a specific destination and date.
     */
    public static function getQuotaForDestinationAndDate(int $destinationId, Carbon $date): ?self
    {
        return self::where('destination_id', $destinationId)
            ->where('date', $date->format('Y-m-d'))
            ->first();
    }

    /**
     * Get or create quota for destination and date.
     */
    public static function getOrCreateQuotaForDestinationAndDate(
        int $destinationId,
        Carbon $date,
        int $defaultQuota = 0
    ): self {
        return self::firstOrCreate(
            [
                'destination_id' => $destinationId,
                'date' => $date->format('Y-m-d')
            ],
            [
                'quota_daily' => $defaultQuota,
                'used_daily' => 0,
                'remaining_daily' => $defaultQuota,
            ]
        );
    }

    /**
     * Check if quota is available.
     */
    public function hasAvailableQuota(int $amount = 1): bool
    {
        return $this->remaining_daily >= $amount;
    }

    /**
     * Increment used quota by family count.
     */
    public function incrementUsed(int $amount = 1): bool
    {
        if ($this->remaining_daily < $amount) {
            return false;
        }

        $this->used_daily += $amount;
        $this->remaining_daily -= $amount;

        return $this->save();
    }

    /**
     * Decrement used quota (refund).
     */
    public function decrementUsed(int $amount = 1): bool
    {
        if ($this->used_daily < $amount) {
            return false;
        }

        $this->used_daily -= $amount;
        $this->remaining_daily += $amount;

        return $this->save();
    }

    /**
     * Update quota amount.
     */
    public function updateQuota(int $newQuota): bool
    {
        $this->quota_daily = $newQuota;
        $this->remaining_daily = $newQuota - $this->used_daily;

        return $this->save();
    }
}
