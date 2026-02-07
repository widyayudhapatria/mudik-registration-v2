<?php

declare(strict_types=1);

namespace App\Filters;

use Essa\APIToolKit\Filters\QueryFilters;

class RegistrationFilters extends QueryFilters
{
    protected array $allowedFilters = [
        'status',
        'created_at',
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
     * Filter by status.
     */
    protected function status(string $value): void
    {
        $this->builder->whereHas('formLink', function ($query) use ($value) {
            $query->where('status', $value);
        });
    }

    /**
     * Filter by has child under 4.
     */
    protected function has_child_under_4(bool $value): void
    {
        $this->builder->where('has_child_under_4', $value);
    }
}