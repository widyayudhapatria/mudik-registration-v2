<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qr_code_id',
        'admin_id',
        'scan_result',
        'failure_reason',
        'ip_address',
        'user_agent',
        'scanned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Get the QR code that owns the scan log.
     */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    /**
     * Get the admin who performed the scan.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Create a success scan log.
     */
    public static function logSuccess(
        int $qrCodeId,
        int $adminId,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'qr_code_id' => $qrCodeId,
            'admin_id' => $adminId,
            'scan_result' => 'success',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'scanned_at' => now(),
        ]);
    }

    /**
     * Create a failed scan log.
     */
    public static function logFailure(
        ?int $qrCodeId,
        int $adminId,
        string $reason,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'qr_code_id' => $qrCodeId,
            'admin_id' => $adminId,
            'scan_result' => 'failed',
            'failure_reason' => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'scanned_at' => now(),
        ]);
    }

    /**
     * Check if scan was successful.
     */
    public function isSuccess(): bool
    {
        return $this->scan_result === 'success';
    }

    /**
     * Check if scan failed.
     */
    public function isFailed(): bool
    {
        return $this->scan_result === 'failed';
    }

    /**
     * Scope to filter successful scans.
     */
    public function scopeSuccess($query)
    {
        return $query->where('scan_result', 'success');
    }

    /**
     * Scope to filter failed scans.
     */
    public function scopeFailed($query)
    {
        return $query->where('scan_result', 'failed');
    }

    /**
     * Scope to filter by admin.
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scanned_at', [$startDate, $endDate]);
    }
}
