<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Registration\ApproveRegistrationAction;
use App\Actions\Registration\RejectRegistrationAction;
use App\Actions\Registration\ResendQrCodeEmailAction;
use App\Data\ApproveRegistrationData;
use App\Data\RejectRegistrationData;
use App\Enums\Permissions\MudikPermissions;
use App\Exceptions\MudikException;
use App\Filters\RegistrationFilters;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Destination;
use App\Exports\RegistrationAbsenExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * List all registrations.
     *
     * GET /cms/registrations
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Registration::class);

        $registrations = Registration::withTrashed()
            ->with([
                'formLink',
                'destination',
                'participants',
                'approvedBy',
                'rejectedBy'
            ])
            ->useFilters(RegistrationFilters::class)
            ->latest('created_at')
            ->paginate(20)->withQueryString();

        $destinations = Destination::ordered()->get();

        if ($request->wantsJson()) {
            return response()->json($registrations);
        }

        return view('cms.registrations.index', [
            'registrations' => $registrations,
            'destinations' => $destinations,
        ]);
    }

    /**
     * Show registration detail.
     *
     * GET /cms/registrations/{registration}
     */
    public function show(Registration $registration): View|JsonResponse
    {
        $this->authorize('view', $registration);

        $registration->load([
            'formLink',
            'participants' => fn($q) => $q->withTrashed(),
            'approvedBy',
            'rejectedBy',
            'qrCode'
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $registration,
            ]);
        }

        return view('cms.registrations.show', [
            'registration' => $registration,
        ]);
    }

    /**
     * Approve registration.
     *
     * POST /cms/registrations/{registration}/approve
     */
    public function approve(
        Registration $registration,
        ApproveRegistrationData $data
    ): JsonResponse {
        $this->authorize('approve', $registration);

        try {
            $admin = auth('admin')->user();

            $registration = ApproveRegistrationAction::run($registration, $admin, $data);

            return $this->responseSuccess(
                'Pendaftaran berhasil disetujui',
                $registration
            );
        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }

    /**
     * Reject registration.
     *
     * POST /cms/registrations/{registration}/reject
     */
    public function reject(
        Registration $registration,
        RejectRegistrationData $data
    ): JsonResponse {
        $this->authorize('reject', $registration);

        try {
            $admin = auth('admin')->user();

            $registration = RejectRegistrationAction::run($registration, $admin, $data);

            return $this->responseSuccess(
                'Pendaftaran berhasil ditolak',
                $registration
            );
        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }

    /**
     * Resend QR code email.
     *
     * POST /cms/registrations/{registration}/resend-qr-code
     */
    public function resendQrCode(Registration $registration): JsonResponse
    {
        $this->authorize('view', $registration);

        try {
            $admin = auth('admin')->user();

            $result = ResendQrCodeEmailAction::run($registration, $admin);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Registration::class);

        $registrations = Registration::with([
            'participants',
            'destination',
            'seatAllocations.destination',
            'qrCode',
        ])
            ->useFilters(RegistrationFilters::class)
            ->whereHas('formLink', fn($q) => $q->where('status', 'approved'))
            ->latest('created_at')
            ->get();

        return (new RegistrationAbsenExport($registrations))->download();
    }
}
