<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalQuotaConfig extends Model
{
    use HasFactory;

    protected $table = 'global_quota_config';

    protected $fillable = [
        'year',
        'total_quota',
        'is_active',
        'description',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_quota' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get allocated quota (sum of all destinations).
     */
    public function getAllocatedQuotaAttribute(): int
    {
        return Destination::sum('total_quota');
    }

    /**
     * Get remaining global quota.
     */
    public function getRemainingGlobalQuotaAttribute(): int
    {
        return max(0, $this->total_quota - $this->getAllocatedQuotaAttribute());
    }

    /**
     * Scope for active config.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get current year config.
     */
    public static function getCurrentYear()
    {
        return self::where('year', date('Y'))->where('is_active', true)->first();
    }
}
