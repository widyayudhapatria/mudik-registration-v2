<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Essa\APIToolKit\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'form_link_id',
        'destination_id',
        'representative_name',
        'representative_nik',
        'representative_birth_date',
        'family_count',
        'kk_number',
        'kk_document_path',
        'has_child_under_4',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'is_bypass',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'destination_id' => 'integer',
        'representative_birth_date' => 'date',
        'has_child_under_4' => 'boolean',
        'family_count' => 'integer',
        'is_bypass' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the form link that owns the registration.
     */
    public function formLink(): BelongsTo
    {
        return $this->belongsTo(FormLink::class);
    }

    /**
     * Get the destination for this registration.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Get the participants for the registration.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get the QR code for the registration.
     */
    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class);
    }

    /**
     * Get the seat allocations for the registration.
     */
    public function seatAllocations(): HasMany
    {
        return $this->hasMany(SeatAllocation::class);
    }

    /**
     * Get the admin who approved the registration.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /**
     * Get the admin who rejected the registration.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'rejected_by');
    }

    /**
     * Get the status of the registration.
     */
    public function getStatus(): string
    {
        return $this->formLink->status ?? 'unknown';
    }

    /**
     * Check if registration is pending.
     */
    public function isPending(): bool
    {
        return $this->getStatus() === 'submitted';
    }

    /**
     * Check if registration is approved.
     */
    public function isApproved(): bool
    {
        return $this->getStatus() === 'approved';
    }

    /**
     * Check if registration is rejected.
     */
    public function isRejected(): bool
    {
        return $this->getStatus() === 'rejected';
    }

    /**
     * Check if registration is from bypass import.
     */
    public function isBypass(): bool
    {
        return $this->is_bypass === true;
    }

    /**
     * Get display status with bypass indicator.
     */
    public function getDisplayStatus(): string
    {
        $status = $this->getStatus();
        if ($this->isBypass()) {
            return $status . ' [BYPASS]';
        }
        return $status;
    }

    /**
     * Approve the registration.
     */
    public function approve(int $adminId, ?string $notes = null): bool
    {
        $this->approved_by = $adminId;
        $this->approved_at = now();
        $this->admin_notes = $notes;

        $saved = $this->save();

        if ($saved) {
            $this->formLink->markAsApproved();
        }

        return $saved;
    }

    /**
     * Reject the registration.
     */
    public function reject(int $adminId, string $reason, ?string $notes = null): bool
    {
        $this->rejected_by = $adminId;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        $this->admin_notes = $notes;

        $saved = $this->save();

        if ($saved) {
            $this->formLink->markAsRejected();
        }

        return $saved;
    }

    /**
     * Check if participants count matches family count.
     */
    public function hasValidParticipantsCount(): bool
    {
        return $this->participants()->count() === $this->family_count;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->whereHas('formLink', function ($q) use ($status) {
            $q->where('status', $status);
        });
    }

    /**
     * Scope to filter pending registrations.
     */
    public function scopePending($query)
    {
        return $query->status('submitted');
    }

    /**
     * Scope to filter approved registrations.
     */
    public function scopeApproved($query)
    {
        return $query->status('approved');
    }

    /**
     * Scope to filter rejected registrations (including soft-deleted).
     */
    public function scopeRejected($query)
    {
        return $query->status('rejected');
    }

    /**
     * Scope to filter registrations by approved_at timestamp.
     */
    public function scopeApprovedByTimestamp($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Scope to filter registrations by rejected_at timestamp (including soft-deleted).
     */
    public function scopeRejectedByTimestamp($query)
    {
        return $query->withTrashed()->whereNotNull('rejected_at');
    }
}
