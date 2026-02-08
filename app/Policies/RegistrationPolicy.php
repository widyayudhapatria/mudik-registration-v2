<?php

namespace App\Policies;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;
use App\Models\Registration;

class RegistrationPolicy
{
    /**
     * Determine if the user can view any registrations.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(MudikPermissions::ViewRegistrations);
    }

    /**
     * Determine if the user can view the registration.
     */
    public function view(Admin $admin, Registration $registration): bool
    {
        return $admin->hasPermission(MudikPermissions::ViewRegistrations);
    }

    /**
     * Determine if the user can approve the registration.
     */
    public function approve(Admin $admin, Registration $registration): bool
    {
        return $admin->hasPermission(MudikPermissions::ApproveRegistration)
            && $registration->isPending();
    }

    /**
     * Determine if the user can reject the registration.
     */
    public function reject(Admin $admin, Registration $registration): bool
    {
        return $admin->hasPermission(MudikPermissions::RejectRegistration)
            && $registration->isPending();
    }
}