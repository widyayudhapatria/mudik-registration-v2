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

        // ---- Section 1: Aktivitas Hari Ini (Today's Activity)
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
        //--- End of Section 1 ---


        // ---- Section 2: Daily Statistics (Today only)
        $dailyStats = Destination::active()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function ($destination) use ($today) {
                // Registrations created today for this destination
                $registrationsToday = Registration::where('destination_id', $destination->id)
                    ->whereDate('created_at', $today)
                    ->count();

                // Participants from registrations created today for this destination
                $adultParticipantsToday = Participant::whereHas('registration', function ($query) use ($destination, $today) {
                    $query->where('destination_id', $destination->id)
                        ->whereDate('created_at', $today);
                })->where('is_child_under_4', false)->count();

                $childUnder4ParticipantsToday = Participant::whereHas('registration', function ($query) use ($destination, $today) {
                    $query->where('destination_id', $destination->id)
                        ->whereDate('created_at', $today);
                })->where('is_child_under_4', true)->count();

                $totalParticipantsToday = $adultParticipantsToday + $childUnder4ParticipantsToday;


                // Participants from approved registrations created today
                $adultParticipantsApprovedToday = Participant::whereHas('registration', function ($query) use ($destination, $today) {
                    $query->where('destination_id', $destination->id)
                        ->whereDate('approved_at', $today);
                })->where('is_child_under_4', false)->count();

                $childUnder4ParticipantsApprovedToday = Participant::whereHas('registration', function ($query) use ($destination, $today) {
                    $query->where('destination_id', $destination->id)
                        ->whereDate('approved_at', $today);
                })->where('is_child_under_4', true)->count();

                $totalParticipantsApprovedToday = Participant::whereHas('registration', function ($query) use ($destination, $today) {
                    $query->where('destination_id', $destination->id)
                        ->whereDate('approved_at', $today);
                })->count();


                // Registrations approved today
                $approvedToday = Registration::where('destination_id', $destination->id)
                    ->whereDate('approved_at', $today)
                    ->count();

                return [
                    'destination_name' => $destination->name,
                    'registrations_today' => $registrationsToday,
                    'participants_adult_today' => $adultParticipantsToday,
                    'participants_under4_today' => $childUnder4ParticipantsToday,
                    'participants_total_today' => $totalParticipantsToday,
                    'participants_adult_approved_today' => $adultParticipantsApprovedToday,
                    'participants_under4_approved_today' => $childUnder4ParticipantsApprovedToday,
                    'participants_total_approved_today' => $totalParticipantsApprovedToday,
                    'approved_today' => $approvedToday,
                ];
            });
        //--- End of Section 2 ---


        // ---- Section 3: Statistik Keseluruhan (All Time Overview)
        $totalPending = FormLink::status('pending')->count();
        $totalSubmitted = FormLink::status('submitted')->count();
        $totalApproved = Registration::approvedByTimestamp()->count();
        $totalRejected = Registration::rejectedByTimestamp()->count();
        //--- End of Section 3 ---


        // ---- Section 4: Participants vs Registrations
        $totalRegistrations = Registration::count();

        // Total participants: registered (all) split by adult / <4
        $totalParticipantsRegisteredAdult = Participant::whereHas('registration', function ($q) {
            $q;
        })->where('is_child_under_4', false)->count();

        $totalParticipantsRegisteredUnder4 = Participant::whereHas('registration', function ($q) {
            $q;
        })->where('is_child_under_4', true)->count();

        $totalParticipantsRegistered = $totalParticipantsRegisteredAdult + $totalParticipantsRegisteredUnder4;

        // Total participants from approved registrations only (split by adult / <4)
        $totalParticipantsApprovedAdult = Participant::whereHas('registration', function ($query) {
            $query->approved();
        })->where('is_child_under_4', false)->count();

        $totalParticipantsApprovedUnder4 = Participant::whereHas('registration', function ($query) {
            $query->approved();
        })->where('is_child_under_4', true)->count();

        $totalParticipantsApproved = $totalParticipantsApprovedAdult + $totalParticipantsApprovedUnder4;
        //--- End of Section 4 ---


        // ---- Section 5: Quota per Destination
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
                // registered (mendaftar)
                'total_participants_registered' => $totalParticipantsRegistered,
                'total_participants_registered_adult' => $totalParticipantsRegisteredAdult,
                'total_participants_registered_under4' => $totalParticipantsRegisteredUnder4,
                // approved
                'total_participants_approved' => $totalParticipantsApproved,
                'total_participants_approved_adult' => $totalParticipantsApprovedAdult,
                'total_participants_approved_under4' => $totalParticipantsApprovedUnder4,
            ],

            // Section 4: Quota per Destination
            'quotas' => $destinations,

            // Section 5: Daily Statistics per Destination
            'daily_stats' => $dailyStats,
        ];
    }
}
