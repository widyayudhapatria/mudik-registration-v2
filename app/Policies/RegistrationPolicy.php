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
        return $admin->can(MudikPermissions::ViewRegistrations->value);
    }

    /**
     * Determine if the user can view the registration.
     */
    public function view(Admin $admin, Registration $registration): bool
    {
        return $admin->can(MudikPermissions::ViewRegistrations->value);
    }

    /**
     * Determine if the user can approve the registration.
     */
    public function approve(Admin $admin, Registration $registration): bool
    {
        return $admin->can(MudikPermissions::ApproveRegistration->value)
            && $registration->isPending();
    }

    /**
     * Determine if the user can reject the registration.
     */
    public function reject(Admin $admin, Registration $registration): bool
    {
        return $admin->can(MudikPermissions::RejectRegistration->value)
            && $registration->isPending();
    }
}