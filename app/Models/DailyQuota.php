<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DailyQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'quota',
        'used',
        'remaining',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'quota' => 'integer',
        'used' => 'integer',
        'remaining' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->remaining = $model->quota - $model->used;
        });

        static::saved(function ($model) {
            $cacheKey = "quota.{$model->date->toDateString()}";
            Cache::forget($cacheKey);
        });

        static::deleted(function ($model) {
            $cacheKey = "quota.{$model->date->toDateString()}";
            Cache::forget($cacheKey);
        });
    }

    public static function getQuotaForDate(Carbon $date): ?self
    {
        return self::whereDate('date', $date)->first();
    }

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

    public function hasAvailableQuota(): bool
    {
        return $this->remaining > 0;
    }

    public function incrementUsed(int $amount = 1): bool
    {
        if ($this->remaining < $amount) {
            return false;
        }

        $this->used += $amount;
        $this->remaining -= $amount;
        
        return $this->save();
    }

    public function decrementUsed(int $amount = 1): bool
    {
        if ($this->used < $amount) {
            return false;
        }

        $this->used -= $amount;
        $this->remaining += $amount;
        
        return $this->save();
    }

    public function updateQuota(int $newQuota): bool
    {
        $this->quota = $newQuota;
        $this->remaining = $newQuota - $this->used;
        
        return $this->save();
    }

    public static function getTodayQuota(): ?self
    {
        return self::getQuotaForDate(Carbon::today());
    }

    public static function todayHasAvailableQuota(): bool
    {
        $quota = self::getTodayQuota();
        
        return $quota && $quota->hasAvailableQuota();
    }
}