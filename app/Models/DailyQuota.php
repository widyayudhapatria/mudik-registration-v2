<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuota extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'quota',
        'used',
        'remaining',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'quota' => 'integer',
        'used' => 'integer',
        'remaining' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->remaining = $model->quota - $model->used;
        });
    }

    /**
     * Get quota for specific date.
     */
    public static function getQuotaForDate(Carbon $date): ?self
    {
        return self::whereDate('date', $date)->first();
    }

    /**
     * Get or create quota for specific date.
     */
    public static function getOrCreateQuotaForDate(Carbon $date, int $defaultQuota = 0): self
    {
        return self::firstOrCreate(
            ['date' => $date->format('Y-m-d')],
            [
                'quota' => $defaultQuota,
                'used' => 0,
                'remaining' => $defaultQuota,
            ]
        );
    }

    /**
     * Check if quota is available.
     */
    public function hasAvailableQuota(): bool
    {
        return $this->remaining > 0;
    }

    /**
     * Increment used quota.
     */
    public function incrementUsed(int $amount = 1): bool
    {
        if ($this->remaining < $amount) {
            return false;
        }

        $this->used += $amount;
        $this->remaining -= $amount;
        
        return $this->save();
    }

    /**
     * Decrement used quota.
     */
    public function decrementUsed(int $amount = 1): bool
    {
        if ($this->used < $amount) {
            return false;
        }

        $this->used -= $amount;
        $this->remaining += $amount;
        
        return $this->save();
    }

    /**
     * Update quota amount.
     */
    public function updateQuota(int $newQuota): bool
    {
        $this->quota = $newQuota;
        $this->remaining = $newQuota - $this->used;
        
        return $this->save();
    }

    /**
     * Get today's quota.
     */
    public static function getTodayQuota(): ?self
    {
        return self::getQuotaForDate(Carbon::today());
    }

    /**
     * Check if today has available quota.
     */
    public static function todayHasAvailableQuota(): bool
    {
        $quota = self::getTodayQuota();
        
        return $quota && $quota->hasAvailableQuota();
    }
}