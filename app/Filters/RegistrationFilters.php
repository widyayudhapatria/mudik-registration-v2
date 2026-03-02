<?php

declare(strict_types=1);

namespace App\Filters;

use Essa\APIToolKit\Filters\QueryFilters;

class RegistrationFilters extends QueryFilters
{
    protected array $allowedFilters = [
        'has_child_under_4',
    ];

    protected array $allowedSorts = [
        'created_at',
        'family_count',
    ];

    protected array $columnSearch = [
        'representative_name',
        'representative_nik',
        'kk_number',
    ];

    /**
     * Filter by status (via formLink relation).
     */
    protected function status(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $this->builder->whereHas('formLink', function ($query) use ($value) {
            $query->where('status', $value);
        });
    }

    /**
     * Filter by destination.
     */
    protected function destination_id(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $this->builder->where('destination_id', $value);
    }

    /**
     * Filter from date (inclusive).
     */
    protected function from_date(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $this->builder->whereDate('created_at', '>=', $value);
    }

    /**
     * Filter to date (inclusive).
     */
    protected function to_date(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $this->builder->whereDate('created_at', '<=', $value);
    }

    /**
     * Filter by has child under 4.
     */
    protected function has_child_under_4(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $this->builder->where('has_child_under_4', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Filter by email status (QR code or rejection email based on registration status).
     */
    protected function email_status(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        // Special case: filter registrations without any email log
        if ($value === 'none') {
            $this->builder->where(function ($query) {
                // Approved registrations without QR code emails OR Rejected registrations without rejection emails
                $query->whereHas('formLink', function ($q) {
                    $q->where('status', 'approved');
                })->whereDoesntHave('qrCodeEmails')
                    ->orWhere(function ($q) {
                        $q->whereHas('formLink', function ($subQ) {
                            $subQ->where('status', 'rejected');
                        })->whereDoesntHave('rejectionEmails');
                    });
            });
            return;
        }

        // Filter by specific email status in both QR code and rejection emails
        $this->builder->where(function ($query) use ($value) {
            // Check in QR code emails (for approved registrations)
            $query->whereHas('qrCodeEmails', function ($q) use ($value) {
                $q->where('status', $value)
                    ->whereIn('id', function ($subQuery) {
                        $subQuery->selectRaw('MAX(id)')
                            ->from('email_logs')
                            ->where('email_type', 'qr_code')
                            ->groupBy('form_link_id');
                    });
            })
                // OR check in rejection emails (for rejected registrations)
                ->orWhereHas('rejectionEmails', function ($q) use ($value) {
                    $q->where('status', $value)
                        ->whereIn('id', function ($subQuery) {
                            $subQuery->selectRaw('MAX(id)')
                                ->from('email_logs')
                                ->where('email_type', 'rejection')
                                ->groupBy('form_link_id');
                        });
                });
        });
    }
}
