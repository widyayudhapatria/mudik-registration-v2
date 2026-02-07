<?php

namespace App\Enums\Permissions;

use App\Enums\EnumTraits;

enum MudikRoleList: string
{
    use EnumTraits;

    case SuperAdmin = 'super_admin';
    case Validator = 'validator';
    case Scanner = 'scanner';

    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => MudikPermissions::all()->pluck('value')->all(),
            
            self::Validator => [
                MudikPermissions::ViewRegistrations->value,
                MudikPermissions::ApproveRegistration->value,
                MudikPermissions::RejectRegistration->value,
                MudikPermissions::ViewDashboard->value,
                MudikPermissions::ViewStatistics->value,
            ],
            
            self::Scanner => [
                MudikPermissions::ScanQR->value,
                MudikPermissions::ViewScanLogs->value,
            ],
        };
    }
}