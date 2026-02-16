<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Registration\ApproveRegistrationAction;
use App\Actions\Registration\RejectRegistrationAction;
use App\Data\ApproveRegistrationData;
use App\Data\RejectRegistrationData;
use App\Enums\Permissions\MudikPermissions;
use App\Exceptions\MudikException;
use App\Filters\RegistrationFilters;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
                'participants',
                'approvedBy',
                'rejectedBy'
            ])
            ->useFilters(RegistrationFilters::class)
            ->latest('created_at')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($registrations);
        }

        return view('cms.registrations.index', [
            'registrations' => $registrations,
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
}
