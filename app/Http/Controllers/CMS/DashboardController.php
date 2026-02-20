<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\FormLink;
use App\Models\Participant;
use App\Models\Registration;
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

        // Section 1: Aktivitas Hari Ini (Today's Activity)
        $todayEmailSubmissions = FormLink::whereDate('created_at', $today)->count();

        // Today's status counts (via FormLink)
        $todayPending = FormLink::status('pending')
            ->whereDate('created_at', $today)
            ->count();

        $todaySubmitted = FormLink::status('submitted')
            ->whereDate('created_at', $today)
            ->count();

        $todayApproved = Registration::approvedByTimestamp()->whereDate('approved_at', $today)->count();

        $todayRejected = Registration::rejectedByTimestamp()->whereDate('rejected_at', $today)->count();

        // Section 2: Statistik Keseluruhan (All Time Overview)
        $totalPending = FormLink::status('pending')->count();
        $totalSubmitted = FormLink::status('submitted')->count();
        $totalApproved = Registration::approvedByTimestamp()->count();
        $totalRejected = Registration::rejectedByTimestamp()->count();

        // Section 3: Participants vs Registrations
        $totalRegistrations = Registration::count();

        // Total participants from approved registrations only
        $totalParticipants = Participant::whereHas('registration', function ($query) {
            $query->approved();
        })->count();

        // Section 3: Quota per Destination
        $destinations = Destination::active()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function ($destination) {
                // Count participants from approved registrations for this destination
                $usedQuota = Participant::whereHas('registration', function ($query) use ($destination) {
                    $query->approved()->where('destination_id', $destination->id);
                })->count();

                $remaining = max(0, $destination->total_quota - $usedQuota);
                $percentage = $destination->total_quota > 0
                    ? round(($usedQuota / $destination->total_quota) * 100, 1)
                    : 0;

                return [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'total_quota' => $destination->total_quota,
                    'used_quota' => $usedQuota,
                    'remaining_quota' => $remaining,
                    'percentage_used' => $percentage,
                ];
            });

        // Section 4: Daily Statistics (Today only)
        $dailyStats = Destination::active()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function ($destination) use ($today) {
                // Registrations created today for this destination
                $registrationsToday = Registration::where('destination_id', $destination->id)
                    ->whereDate('created_at', $today)
                    ->count();

                // Participants from approved registrations created today
                $participantsToday = Participant::whereHas('registration', function ($query) use ($destination, $today) {
                    $query->approved()
                        ->where('destination_id', $destination->id)
                        ->whereDate('created_at', $today);
                })->count();

                // Registrations approved today
                $approvedToday = Registration::where('destination_id', $destination->id)
                    ->whereDate('approved_at', $today)
                    ->count();

                return [
                    'destination_name' => $destination->name,
                    'registrations_today' => $registrationsToday,
                    'participants_today' => $participantsToday,
                    'approved_today' => $approvedToday,
                ];
            });

        return [
            // Section 1: Aktivitas Hari Ini (Today's Activity)
            'today' => [
                'email_submissions' => $todayEmailSubmissions,
                'pending' => $todayPending,
                'submitted' => $todaySubmitted,
                'approved' => $todayApproved,
                'rejected' => $todayRejected,
            ],

            // Section 2: Statistik Keseluruhan (All Time Overview)
            'overview' => [
                'total_pending' => $totalPending,
                'total_submitted' => $totalSubmitted,
                'total_approved' => $totalApproved,
                'total_rejected' => $totalRejected,
            ],

            // Section 3: Participants vs Registrations
            'summary' => [
                'total_registrations' => $totalRegistrations,
                'total_participants' => $totalParticipants,
            ],

            // Section 4: Quota per Destination
            'quotas' => $destinations,

            // Section 5: Daily Statistics per Destination
            'daily_stats' => $dailyStats,
        ];
    }
}
