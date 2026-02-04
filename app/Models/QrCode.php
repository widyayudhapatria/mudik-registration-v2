<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'token_qr',
        'valid_from',
        'valid_until',
        'scanned_at',
        'scanned_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'scanned_at' => 'datetime',
    ];

    /**
     * Get the registration that owns the QR code.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the admin who scanned the QR code.
     */
    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'scanned_by');
    }

    /**
     * Get the scan logs for this QR code.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    /**
     * Check if QR code is valid (not expired and within valid period).
     */
    public function isValid(): bool
    {
        $now = Carbon::now();
        
        return $now->between($this->valid_from, $this->valid_until);
    }

    /**
     * Check if QR code has been scanned.
     */
    public function isScanned(): bool
    {
        return !is_null($this->scanned_at);
    }

    /**
     * Check if QR code can be scanned.
     */
    public function canBeScanned(): bool
    {
        return $this->isValid() && !$this->isScanned();
    }

    /**
     * Mark QR code as scanned.
     */
    public function markAsScanned(int $adminId): bool
    {
        $this->scanned_at = Carbon::now();
        $this->scanned_by = $adminId;
        
        return $this->save();
    }

    /**
     * Check if QR code is expired.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->valid_until);
    }

    /**
     * Check if QR code is not yet valid.
     */
    public function isNotYetValid(): bool
    {
        return Carbon::now()->isBefore($this->valid_from);
    }

    /**
     * Get validation status message.
     */
    public function getValidationMessage(): string
    {
        if ($this->isScanned()) {
            return 'QR Code sudah pernah digunakan';
        }
        
        if ($this->isExpired()) {
            return 'QR Code sudah expired';
        }
        
        if ($this->isNotYetValid()) {
            return 'QR Code belum dapat digunakan';
        }
        
        if ($this->isValid()) {
            return 'QR Code valid';
        }
        
        return 'QR Code tidak valid';
    }

    /**
     * Scope to filter valid QR codes.
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        
        return $query->where('valid_from', '<=', $now)
                     ->where('valid_until', '>=', $now)
                     ->whereNull('scanned_at');
    }

    /**
     * Scope to filter scanned QR codes.
     */
    public function scopeScanned($query)
    {
        return $query->whereNotNull('scanned_at');
    }

    /**
     * Scope to filter unscanned QR codes.
     */
    public function scopeUnscanned($query)
    {
        return $query->whereNull('scanned_at');
    }

    /**
     * Scope to filter expired QR codes.
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', Carbon::now());
    }
}