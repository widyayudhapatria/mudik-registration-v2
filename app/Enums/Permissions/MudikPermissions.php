<?php

namespace App\Enums\Permissions;

use App\Enums\EnumTraits;

enum MudikPermissions: string
{
    use EnumTraits;

    // Registration Management
    case ViewRegistrations = 'mudik_v_registrations';
    case ApproveRegistration = 'mudik_approve_registration';
    case RejectRegistration = 'mudik_reject_registration';
    
    // Quota Management
    case ViewQuota = 'mudik_v_quota';
    case ModifyQuota = 'mudik_m_quota';
    
    // Scanner
    case ScanQR = 'mudik_scan_qr';
    case ViewScanLogs = 'mudik_v_scan_logs';
    
    // Dashboard & Statistics
    case ViewDashboard = 'mudik_v_dashboard';
    case ViewStatistics = 'mudik_v_statistics';
    
    // Admin Management
    case ViewAdmins = 'mudik_v_admins';
    case ModifyAdmins = 'mudik_m_admins';
}