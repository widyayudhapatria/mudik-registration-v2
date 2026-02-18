<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Quota\CreateDestinationAction;
use App\Actions\Quota\UpdateDestinationAction;
use App\Data\DestinationData;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\GlobalQuotaConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Show destination management dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Destination::class);

        $destinations = Destination::withCount(['dailyQuotas', 'registrations'])
            ->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($destination) {
                return [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'code' => $destination->code,
                    'total_quota' => $destination->total_quota,
                    'used_quota' => $destination->used_quota,
                    'remaining_quota' => $destination->remaining_quota,
                    'is_active' => $destination->is_active,
                    'display_order' => $destination->display_order,
                    'description' => $destination->description,
                    'daily_quotas_count' => $destination->daily_quotas_count,
                    'registrations_count' => $destination->registrations_count,
                    'created_at' => $destination->created_at,
                    'updated_at' => $destination->updated_at,
                ];
            });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $destinations,
            ]);
        }

        $globalConfig = GlobalQuotaConfig::getCurrentYear();

        $globalInfo = [
            'total_quota' => $globalConfig?->total_quota ?? 0,
            'allocated_quota' => $globalConfig?->allocated_quota ?? 0,
            'remaining_global_quota' => $globalConfig?->remaining_global_quota ?? 0,
        ];

        return view('cms.destinations.index', [
            'destinations' => $destinations,
            'globalInfo' => $globalInfo,
        ]);
    }

    /**
     * Show single destination details.
     */
    public function show(Destination $destination): JsonResponse
    {
        $this->authorize('view', $destination);

        $destination->loadCount(['dailyQuotas', 'registrations']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'code' => $destination->code,
                'total_quota' => $destination->total_quota,
                'used_quota' => $destination->used_quota,
                'remaining_quota' => $destination->remaining_quota,
                'is_active' => $destination->is_active,
                'display_order' => $destination->display_order,
                'description' => $destination->description,
                'daily_quotas_count' => $destination->daily_quotas_count,
                'registrations_count' => $destination->registrations_count,
                'created_at' => $destination->created_at,
                'updated_at' => $destination->updated_at,
            ],
        ]);
    }

    /**
     * Create new destination.
     */
    public function store(DestinationData $data): JsonResponse
    {
        $this->authorize('create', Destination::class);

        try {
            $destination = CreateDestinationAction::run($data);

            return $this->responseSuccess(
                'Destination berhasil dibuat',
                [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'code' => $destination->code,
                    'total_quota' => $destination->total_quota,
                    'used_quota' => $destination->used_quota,
                    'remaining_quota' => $destination->remaining_quota,
                    'is_active' => $destination->is_active,
                    'display_order' => $destination->display_order,
                ]
            );
        } catch (\Throwable $e) {
            return $this->responseError(
                'Gagal membuat destination: ' . $e->getMessage(),
                'CREATE_FAILED'
            );
        }
    }

    /**
     * Update existing destination.
     */
    public function update(Destination $destination, DestinationData $data): JsonResponse
    {
        $this->authorize('update', $destination);

        try {
            $updated = UpdateDestinationAction::run($destination, $data);

            $response = [
                'id' => $updated->id,
                'name' => $updated->name,
                'code' => $updated->code,
                'total_quota' => $updated->total_quota,
                'used_quota' => $updated->used_quota,
                'remaining_quota' => $updated->remaining_quota,
                'is_active' => $updated->is_active,
                'display_order' => $updated->display_order,
            ];

            return $this->responseSuccess(
                'Destination berhasil diupdate',
                $response
            );
        } catch (\Throwable $e) {
            return $this->responseError(
                'Gagal mengupdate destination: ' . $e->getMessage(),
                'UPDATE_FAILED'
            );
        }
    }

    /**
     * Delete destination.
     * Can only delete if no registrations exist.
     */
    public function destroy(Destination $destination): JsonResponse
    {
        $this->authorize('delete', $destination);

        try {
            // Check if destination has any registrations
            $registrationsCount = $destination->registrations()->count();

            if ($registrationsCount > 0) {
                return $this->responseError(
                    "Tidak dapat menghapus destination {$destination->name}. Terdapat {$registrationsCount} registrations yang menggunakan destination ini.",
                    'DELETE_FORBIDDEN'
                );
            }

            // Check if destination has any daily quotas
            $dailyQuotasCount = $destination->dailyQuotas()->count();

            if ($dailyQuotasCount > 0) {
                return $this->responseError(
                    "Tidak dapat menghapus destination {$destination->name}. Terdapat {$dailyQuotasCount} daily quotas yang terkait. Silakan hapus daily quotas terlebih dahulu atau non-aktifkan destination ini.",
                    'DELETE_FORBIDDEN'
                );
            }

            $name = $destination->name;
            $destination->delete();

            return $this->responseSuccess(
                "Destination {$name} berhasil dihapus"
            );
        } catch (\Throwable $e) {
            return $this->responseError(
                'Gagal menghapus destination: ' . $e->getMessage(),
                'DELETE_FAILED'
            );
        }
    }

    /**
     * Toggle destination active status.
     */
    public function toggleActive(Destination $destination): JsonResponse
    {
        $this->authorize('update', $destination);

        try {
            $destination->is_active = !$destination->is_active;
            $destination->save();

            $status = $destination->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return $this->responseSuccess(
                "Destination {$destination->name} berhasil {$status}",
                [
                    'id' => $destination->id,
                    'is_active' => $destination->is_active,
                ]
            );
        } catch (\Throwable $e) {
            return $this->responseError(
                'Gagal mengubah status destination: ' . $e->getMessage(),
                'TOGGLE_FAILED'
            );
        }
    }
}
