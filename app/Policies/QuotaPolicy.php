<?php

namespace App\Policies;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;
use App\Models\DailyQuota;

class QuotaPolicy
{
    /**
     * Determine if the user can view quotas.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(MudikPermissions::ViewQuota);
    }

    /**
     * Determine if the user can view specific quota.
     */
    public function view(Admin $admin, DailyQuota $quota): bool
    {
        return $admin->hasPermission(MudikPermissions::ViewQuota);
    }

    /**
     * Determine if the user can create/update quotas.
     */
    public function modify(Admin $admin): bool
    {
        return $admin->hasPermission(MudikPermissions::ModifyQuota);
    }
}