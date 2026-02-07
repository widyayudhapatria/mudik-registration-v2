<?php

namespace App\Policies;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;

class AdminPolicy
{
    /**
     * Determine if the user can view any admins.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can(MudikPermissions::ViewAdmins->value);
    }

    /**
     * Determine if the user can view the admin.
     */
    public function view(Admin $admin, Admin $targetAdmin): bool
    {
        return $admin->can(MudikPermissions::ViewAdmins->value);
    }

    /**
     * Determine if the user can create/update/delete admins.
     */
    public function modify(Admin $admin): bool
    {
        return $admin->can(MudikPermissions::ModifyAdmins->value);
    }
}