<?php

namespace App\Http\Controllers\CMS;

use App\Enums\FormLinkStatus;
use App\Enums\Permissions\MudikPermissions;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DailyQuota;
use App\Models\FormLink;
use App\Models\QrCode;
use App\Models\Registration;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Show dashboard.
     */
    public function index(): View
    {
        // $this->authorize('view', Admin::class); 

        $statistics = $this->getStatistics();

        return view('cms.dashboard.index', [
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function statistics(): JsonResponse
    {
        // $this->authorize('viewStatistics', Admin::class);

        $statistics = $this->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get statistics data.
     */
    protected function getStatistics(): array
    {
        $today = Carbon::today();

        $totalEmailSubmissions = FormLink::count();
        $todayEmailSubmissions = FormLink::whereDate('created_at', $today)->count();

        $totalRegistrations = Registration::count();
        $todayRegistrations = Registration::whereDate('created_at', $today)->count();
        $pendingRegistrations = Registration::pending()->count();
        $approvedRegistrations = Registration::approved()->count();
        $rejectedRegistrations = Registration::rejected()->count();

        $totalQrGenerated = QrCode::count();
        $totalQrScanned = QrCode::scanned()->count();
        $todayQrScanned = QrCode::whereDate('scanned_at', $today)->count();

        $todayQuota = DailyQuota::where('date', $today)->first();

        $recentScans = ScanLog::with(['qrCode.registration', 'admin'])
            ->latest('scanned_at')
            ->limit(10)
            ->get()
            ->map(function ($scanLog) {
                return [
                    'id' => $scanLog->id,
                    'scan_result' => $scanLog->scan_result,
                    'scanned_at' => $scanLog->scanned_at->toISOString(),
                    'admin_name' => $scanLog->admin->name,
                    'representative_name' => $scanLog->qrCode->registration->representative_name ?? 'N/A',
                ];
            });

        return [
            'email_submissions' => [
                'total' => $totalEmailSubmissions,
                'today' => $todayEmailSubmissions,
            ],
            'registrations' => [
                'total' => $totalRegistrations,
                'today' => $todayRegistrations,
                'pending' => $pendingRegistrations,
                'approved' => $approvedRegistrations,
                'rejected' => $rejectedRegistrations,
            ],
            'qr_codes' => [
                'total_generated' => $totalQrGenerated,
                'total_scanned' => $totalQrScanned,
                'today_scanned' => $todayQrScanned,
                'remaining' => $totalQrGenerated - $totalQrScanned,
            ],
            'quota' => [
                'date' => $todayQuota?->date->toDateString(),
                'total' => $todayQuota?->quota ?? 0,
                'used' => $todayQuota?->used ?? 0,
                'remaining' => $todayQuota?->remaining ?? 0,
                'percentage_used' => $todayQuota && $todayQuota->quota > 0 
                    ? round(($todayQuota->used / $todayQuota->quota) * 100, 2) 
                    : 0,
            ],
            'recent_scans' => $recentScans,
        ];
    }
}