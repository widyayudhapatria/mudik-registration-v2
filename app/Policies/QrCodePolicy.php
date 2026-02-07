<?php

namespace App\Policies;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;
use App\Models\QrCode;

class QrCodePolicy
{
    /**
     * Determine if the user can scan QR codes.
     */
    public function scan(Admin $admin): bool
    {
        return $admin->can(MudikPermissions::ScanQR->value) 
            && $admin->canScanQr()
            && $admin->is_active; 
    }

    /**
     * Determine if the user can view scan logs.
     */
    public function viewScanLogs(Admin $admin): bool
    {
        return $admin->can(MudikPermissions::ViewScanLogs->value);
    }
}