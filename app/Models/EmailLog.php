<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'form_link_id',
        'email_to',
        'email_type',
        'subject',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'retry_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Get the form link associated with this email log.
     */
    public function formLink(): BelongsTo
    {
        return $this->belongsTo(FormLink::class);
    }

    /**
     * Mark email as sent.
     */
    public function markAsSent(): bool
    {
        $this->status = 'sent';
        $this->sent_at = now();

        return $this->save();
    }

    /**
     * Mark email as failed.
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->status = 'failed';
        $this->failed_at = now();
        $this->error_message = $errorMessage;

        return $this->save();
    }

    /**
     * Increment retry count.
     */
    public function incrementRetry(): bool
    {
        $this->retry_count++;
        $this->status = 'pending';

        return $this->save();
    }

    /**
     * Check if email was sent successfully.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if email failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if email is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if can retry.
     */
    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->retry_count < $maxRetries && $this->isFailed();
    }

    /**
     * Create a form link email log.
     */
    public static function logFormLinkEmail(
        int $formLinkId,
        string $emailTo,
        string $subject
    ): self {
        return self::create([
            'form_link_id' => $formLinkId,
            'email_to' => $emailTo,
            'email_type' => 'form_link',
            'subject' => $subject,
            'status' => 'pending',
        ]);
    }

    /**
     * Create a QR code email log.
     */
    public static function logQrCodeEmail(
        int $formLinkId,
        string $emailTo,
        string $subject
    ): self {
        return self::create([
            'form_link_id' => $formLinkId,
            'email_to' => $emailTo,
            'email_type' => 'qr_code',
            'subject' => $subject,
            'status' => 'pending',
        ]);
    }

    /**
     * Create a rejection email log.
     */
    public static function logRejectionEmail(
        int $formLinkId,
        string $emailTo,
        string $subject
    ): self {
        return self::create([
            'form_link_id' => $formLinkId,
            'email_to' => $emailTo,
            'email_type' => 'rejection',
            'subject' => $subject,
            'status' => 'pending',
        ]);
    }

    /**
     * Create a seat allocation email log.
     */
    public static function logSeatAllocationEmail(
        int $formLinkId,
        string $emailTo,
        string $subject
    ): self {
        return self::create([
            'form_link_id' => $formLinkId,
            'email_to' => $emailTo,
            'email_type' => 'seat_allocation',
            'subject' => $subject,
            'status' => 'pending',
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by email type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('email_type', $type);
    }

    /**
     * Scope to filter pending emails.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter failed emails that can be retried.
     */
    public function scopeCanRetry($query, int $maxRetries = 3)
    {
        return $query->where('status', 'failed')
            ->where('retry_count', '<', $maxRetries);
    }
}
