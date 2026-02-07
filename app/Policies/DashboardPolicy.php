<?php

namespace App\Policies;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;

class DashboardPolicy
{
    /**
     * Determine if the user can view dashboard.
     */
    public function view(Admin $admin): bool
    {
        return $admin->can(MudikPermissions::ViewDashboard->value);
    }

    /**
     * Determine if the user can view statistics.
     */
    public function viewStatistics(Admin $admin): bool
    {
        return $admin->can(MudikPermissions::ViewStatistics->value);
    }
}