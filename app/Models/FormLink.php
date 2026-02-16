<?php

namespace App\Models;

use Carbon\Carbon;
use Essa\APIToolKit\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormLink extends Model
{
    use HasFactory, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'expired_at',
        'used_at',
        'resend_count',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
        'resend_count' => 'integer',
    ];

    /**
     * Get the registration for this form link.
     */
    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class);
    }

    /**
     * Get the email logs for this form link.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Check if the form link is expired.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expired_at);
    }

    /**
     * Check if the form link can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->status === 'pending'
            && !$this->isExpired()
            && is_null($this->used_at);
    }

    /**
     * Check if the form link can be recreated.
     */
    public function canRecreate(): bool
    {
        return in_array($this->status, ['rejected']) || $this->isExpired();
    }

    /**
     * Mark the form link as used.
     */
    public function markAsUsed(): bool
    {
        $this->used_at = Carbon::now();
        $this->status = 'submitted';
        
        return $this->save();
    }

    /**
     * Mark the form link as approved.
     */
    public function markAsApproved(): bool
    {
        $this->status = 'approved';
        
        return $this->save();
    }

    /**
     * Mark the form link as rejected.
     */
    public function markAsRejected(): bool
    {
        $this->status = 'rejected';
        
        return $this->save();
    }

    /**
     * Increment resend count.
     */
    public function incrementResendCount(): bool
    {
        $this->resend_count++;
        
        return $this->save();
    }

    /**
     * Check if max resend limit reached.
     */
    public function hasReachedMaxResend(): bool
    {
        $maxResend = config('mudik.form_link_max_resend', 5);
        
        return $this->resend_count >= $maxResend;
    }

    /**
     * Generate new expiry date.
     */
    public function refreshExpiry(): bool
    {
        $expiryDays = config('mudik.form_link_expiry_days', 3);
        $this->expired_at = Carbon::now()->addDays($expiryDays);
        
        return $this->save();
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter expired links.
     */
    public function scopeExpired($query)
    {
        return $query->where('expired_at', '<', Carbon::now());
    }

    /**
     * Scope to filter active links.
     */
    public function scopeActive($query)
    {
        return $query->where('expired_at', '>', Carbon::now())
                     ->whereNull('used_at');
    }
}