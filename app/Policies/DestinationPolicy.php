<?php

namespace App\Policies;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;
use App\Models\Destination;

class DestinationPolicy
{
    /**
     * Determine if the user can view destinations.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(MudikPermissions::ViewQuota);
    }

    /**
     * Determine if the user can view specific destination.
     */
    public function view(Admin $admin, Destination $destination): bool
    {
        return $admin->hasPermission(MudikPermissions::ViewQuota);
    }

    /**
     * Determine if the user can create destinations.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(MudikPermissions::ModifyQuota);
    }

    /**
     * Determine if the user can update destinations.
     */
    public function update(Admin $admin, Destination $destination): bool
    {
        return $admin->hasPermission(MudikPermissions::ModifyQuota);
    }

    /**
     * Determine if the user can delete destinations.
     */
    public function delete(Admin $admin, Destination $destination): bool
    {
        return $admin->hasPermission(MudikPermissions::ModifyQuota);
    }
}
