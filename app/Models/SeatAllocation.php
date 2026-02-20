<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'participant_id',
        'destination_id',
        'scan_log_id',
        'bus_number',
        'seat_number',
        'seat_code',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'bus_number' => 'integer',
        'seat_number' => 'integer',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the registration for this seat.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the participant for this seat.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get the destination for this seat.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Get the scan log that triggered this allocation.
     */
    public function scanLog(): BelongsTo
    {
        return $this->belongsTo(ScanLog::class);
    }

    /**
     * Get the admin who assigned this seat.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }

    /**
     * Check if this is a "no seat" allocation (child <4).
     */
    public function isNoSeat(): bool
    {
        return $this->seat_code === 'NO-SEAT';
    }
}
