<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Registration\ResendSeatAllocationEmailAction;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Registration;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ScanMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Display scan monitoring page with filters.
     *
     * GET /cms/scan-monitoring
     */
    public function index(Request $request): View|JsonResponse
    {
        // Build query for approved registrations only
        $query = Registration::with([
            'formLink',
            'destination',
            'participants',
            'qrCode.scannedBy',
            'seatAllocations.participant',
            'seatAllocations.destination'
        ])
            ->whereHas('formLink', function ($q) {
                $q->where('status', 'approved');
            })
            ->whereNotNull('approved_at');

        // Filter by destination
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        // Filter by scan status
        if ($request->filled('scan_status')) {
            if ($request->scan_status === 'scanned') {
                $query->whereHas('qrCode', function ($q) {
                    $q->whereNotNull('scanned_at');
                });
            } elseif ($request->scan_status === 'not_scanned') {
                $query->whereHas('qrCode', function ($q) {
                    $q->whereNull('scanned_at');
                });
            }
        }

        // Filter by email log status (only for scanned registrations)
        if ($request->filled('email_status')) {
            $query->whereHas('qrCode', function ($q) {
                $q->whereNotNull('scanned_at');
            });

            if ($request->email_status === 'sent') {
                $query->whereHas('seatAllocations.registration.formLink', function ($q) {
                    $q->whereHas('emailLogs', function ($subQ) {
                        $subQ->where('email_type', 'seat_allocation')
                            ->where('status', 'sent');
                    });
                });
            } elseif ($request->email_status === 'failed') {
                $query->whereHas('seatAllocations.registration.formLink', function ($q) {
                    $q->whereHas('emailLogs', function ($subQ) {
                        $subQ->where('email_type', 'seat_allocation')
                            ->where('status', 'failed');
                    });
                });
            } elseif ($request->email_status === 'pending') {
                $query->whereHas('seatAllocations.registration.formLink', function ($q) {
                    $q->whereHas('emailLogs', function ($subQ) {
                        $subQ->where('email_type', 'seat_allocation')
                            ->where('status', 'pending');
                    });
                });
            } elseif ($request->email_status === 'not_sent') {
                // Registrations scanned but no email log for seat allocation
                $query->whereHas('qrCode', function ($q) {
                    $q->whereNotNull('scanned_at');
                })->whereDoesntHave('formLink.emailLogs', function ($q) {
                    $q->where('email_type', 'seat_allocation');
                });
            }
        }

        // Search by representative name, email, NIK, or KK
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('representative_name', 'like', "%{$search}%")
                    ->orWhere('representative_nik', 'like', "%{$search}%")
                    ->orWhere('kk_number', 'like', "%{$search}%")
                    ->orWhereHas('formLink', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // Order by scan date (scanned first, then by created_at)
        $registrations = $query->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $destinations = Destination::ordered()->get();

        if ($request->wantsJson()) {
            return response()->json($registrations);
        }

        return view('cms.scan-monitoring.index', [
            'registrations' => $registrations,
            'destinations' => $destinations,
        ]);
    }

    /**
     * Get registration detail for modal.
     *
     * GET /cms/scan-monitoring/{registration}/detail
     */
    public function detail(Registration $registration): JsonResponse
    {
        $registration->load([
            'formLink',
            'destination',
            'participants',
            'qrCode.scannedBy',
            'seatAllocations.participant',
            'seatAllocations.destination'
        ]);

        // Check if registration is approved
        if (!$registration->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not approved'
            ], 403);
        }

        // Get email log info
        $isScanned = $registration->qrCode && $registration->qrCode->scanned_at;
        $emailLog = null;

        if ($isScanned) {
            $emailLog = EmailLog::where('form_link_id', $registration->form_link_id)
                ->where('email_type', 'seat_allocation')
                ->latest('created_at')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'registration' => $registration,
                'scan_info' => [
                    'is_scanned' => $isScanned,
                    'scanned_at' => $registration->qrCode?->scanned_at,
                    'scanned_by' => $registration->qrCode?->scannedBy?->name,
                ],
                'email_info' => [
                    'is_scanned' => $isScanned,
                    'status' => $emailLog?->status,
                    'sent_at' => $emailLog?->sent_at,
                    'failed_at' => $emailLog?->failed_at,
                    'error_message' => $emailLog?->error_message,
                    'retry_count' => $emailLog?->retry_count ?? 0,
                ],
                'participants' => $registration->participants->map(function ($participant) use ($registration) {
                    $seatAllocation = $registration->seatAllocations
                        ->where('participant_id', $participant->id)
                        ->first();

                    return [
                        'id' => $participant->id,
                        'full_name' => $participant->full_name,
                        'nik_kia' => $participant->nik_kia,
                        'birth_date' => $participant->birth_date->format('d/m/Y'),
                        'age' => $participant->getAge(),
                        'is_child_under_4' => $participant->is_child_under_4,
                        'seat' => $seatAllocation ? [
                            'bus_name' => $seatAllocation->bus_name,
                            'seat_label' => $seatAllocation->seat_label,
                            'seat_code' => $seatAllocation->seat_code,
                        ] : null
                    ];
                })
            ]
        ]);
    }

    /**
     * Resend seat allocation email.
     *
     * POST /cms/scan-monitoring/{registration}/resend-seat-allocation
     */
    public function resendSeatAllocationEmail(Registration $registration): JsonResponse
    {
        try {
            $admin = auth('admin')->user();

            $result = ResendSeatAllocationEmailAction::run($registration, $admin);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }
}
