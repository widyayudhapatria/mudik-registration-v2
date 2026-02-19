<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'total_quota',
        'used_quota',
        'is_active',
        'display_order',
        'description',
    ];

    protected $casts = [
        'total_quota' => 'integer',
        'used_quota' => 'integer',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    protected $appends = ['remaining_quota'];

    /**
     * Get remaining quota (calculated).
     */
    public function getRemainingQuotaAttribute(): int
    {
        return max(0, $this->total_quota - $this->used_quota);
    }

    /**
     * Get daily quotas for this destination.
     */
    public function dailyQuotas(): HasMany
    {
        return $this->hasMany(DailyQuota::class);
    }

    /**
     * Get registrations for this destination.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the seat counter for this destination.
     */
    public function seatCounter(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SeatCounter::class);
    }

    /**
     * Get seat allocations for this destination.
     */
    public function seatAllocations(): HasMany
    {
        return $this->hasMany(SeatAllocation::class);
    }

    /**
     * Scope for active destinations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered destinations by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
