<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Quota\EditQuotaAction;
use App\Actions\Quota\SetQuotaAction;
use App\Data\EditQuotaData;
use App\Data\SetQuotaData;
use App\Http\Controllers\Controller;
use App\Models\DailyQuota;
use App\Models\Destination;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotaManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Show quota management dashboard.
     *
     * Query Parameters:
     * - start_date: Start date (default: today)
     * - end_date: End date (default: today + 30 days)
     * - destination_id: Filter by destination (optional)
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', DailyQuota::class);

        // Default start date: include recent past so admin can see recently passed quotas
        $startDate = $request->query('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->addDays(30)->toDateString());
        $destinationId = $request->query('destination_id');

        $query = DailyQuota::with('destination')
            ->whereBetween('date', [$startDate, $endDate]);

        // Filter by destination if specified
        if ($destinationId) {
            $query->where('destination_id', $destinationId);
        }

        // Get quotas ordered by date asc, destination asc
        $quotas = $query->orderBy('date', 'asc')
            ->orderBy('destination_id', 'asc')
            ->get();

        // Re-order so that dates >= today (today + future) appear first, then past dates afterwards
        $today = Carbon::today()->toDateString();
        [$futureOrToday, $past] = $quotas->partition(function ($quota) use ($today) {
            return $quota->date->toDateString() >= $today;
        });

        $quotas = $futureOrToday->merge($past);

        // Group quotas by date for view (preserves the above ordering)
        $quotasByDate = $quotas->groupBy(function ($quota) {
            return $quota->date->toDateString();
        });

        // Get all active destinations for filter
        $destinations = Destination::active()->ordered()->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $quotas->map(function ($quota) {
                    return [
                        'id' => $quota->id,
                        'destination_id' => $quota->destination_id,
                        'destination_name' => $quota->destination->name ?? 'N/A',
                        'destination_code' => $quota->destination->code ?? 'N/A',
                        'date' => $quota->date,
                        'quota_daily' => $quota->quota_daily,
                        'used_daily' => $quota->used_daily,
                        'remaining_daily' => $quota->remaining_daily,
                        'created_at' => $quota->created_at,
                        'updated_at' => $quota->updated_at,
                    ];
                }),
                'destinations' => $destinations,
            ]);
        }

        return view('cms.quotas.index', [
            'quotas' => $quotas,
            'quotasByDate' => $quotasByDate,
            'destinations' => $destinations,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedDestinationId' => $destinationId,
        ]);
    }

    /**
     * Get today's quota for all destinations.
     */
    public function today(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DailyQuota::class);

        $destinationId = $request->query('destination_id');

        $query = DailyQuota::with('destination')
            ->where('date', Carbon::today()->toDateString());

        if ($destinationId) {
            $query->where('destination_id', $destinationId);
        }

        $quotas = $query->get()->map(function ($quota) {
            return [
                'id' => $quota->id,
                'destination_id' => $quota->destination_id,
                'destination_name' => $quota->destination->name,
                'destination_code' => $quota->destination->code,
                'date' => $quota->date,
                'quota_daily' => $quota->quota_daily,
                'used_daily' => $quota->used_daily,
                'remaining_daily' => $quota->remaining_daily,
            ];
        });

        if ($quotas->isEmpty()) {
            return $this->responseNotFound('Kuota hari ini belum diset');
        }

        return response()->json([
            'success' => true,
            'data' => $quotas,
        ]);
    }

    /**
     * Get quotas for a specific destination within date range.
     */
    public function getByDestination(Request $request, Destination $destination): JsonResponse
    {
        $this->authorize('viewAny', DailyQuota::class);

        $startDate = $request->query('start_date', Carbon::today()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->addDays(30)->toDateString());

        $quotas = DailyQuota::where('destination_id', $destination->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($quota) use ($destination) {
                return [
                    'id' => $quota->id,
                    'destination_id' => $destination->id,
                    'destination_name' => $destination->name,
                    'destination_code' => $destination->code,
                    'date' => $quota->date,
                    'quota_daily' => $quota->quota_daily,
                    'used_daily' => $quota->used_daily,
                    'remaining_daily' => $quota->remaining_daily,
                    'created_at' => $quota->created_at,
                    'updated_at' => $quota->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'code' => $destination->code,
                'total_quota' => $destination->total_quota,
                'used_quota' => $destination->used_quota,
                'remaining_quota' => $destination->remaining_quota,
            ],
            'quotas' => $quotas,
        ]);
    }

    /**
     * Create or update quota for a date.
     *
     * POST /cms/quotas
     * - Without 'id': Creates new quota
     * - With 'id': Updates existing quota
     *
     * Request Parameters:
     * - id: (optional) Quota ID for update operation
     * - destination_id: Destination ID
     * - date: Date in Y-m-d format
     * - quota_daily: Daily quota amount
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('modify', DailyQuota::class);

        try {
            $quotaId = $request->input('id');

            // EDIT OPERATION: Update existing quota
            if ($quotaId) {
                $data = EditQuotaData::from($request->all());
                $quota = EditQuotaAction::run($data);

                return $this->responseSuccess(
                    'Kuota berhasil diperbarui',
                    $quota
                );
            }

            // CREATE OPERATION: Create new quota
            $data = SetQuotaData::from($request->all());

            // Validate date is not in the past (additional client-side validation)
            if (Carbon::parse($data->date)->lessThan(Carbon::today())) {
                return $this->responseError(
                    'Tanggal tidak boleh di masa lalu. Silahkan pilih tanggal hari ini atau yang akan datang.',
                    'INVALID_DATE',
                    422
                );
            }

            // Fetch destination with pessimistic lock
            $destination = Destination::lockForUpdate()->find($data->destination_id);

            if (!$destination || !$destination->is_active) {
                return $this->responseError(
                    'Destinasi tidak ditemukan atau tidak aktif',
                    'INVALID_DESTINATION',
                    422
                );
            }

            // Calculate total quota scheduled for today and future dates (exclude past dates)
            $totalNotPassedQuota = DailyQuota::where('destination_id', $data->destination_id)
                ->where('date', '>=', Carbon::today()->toDateString())
                ->sum('quota_daily');

            // Validate: total quota from today onwards should not exceed destination total quota
            $newTotal = $totalNotPassedQuota + $data->quota_daily;
            if ($newTotal > $destination->total_quota) {
                $remaining = $destination->total_quota - $totalNotPassedQuota;
                return $this->responseError(
                    "Total kuota hari ini dan ke depan ({$totalNotPassedQuota} + {$data->quota_daily} = {$newTotal}) melebihi total kuota destinasi ({$destination->total_quota}). Sisa yang bisa dialokasikan: {$remaining}.",
                    'EXCEED_REMAINING_QUOTA',
                    422
                );
            }

            // Duplicate check: Prevent creating quota for same date + destination
            $existingQuota = DailyQuota::where('destination_id', $data->destination_id)
                ->where('date', $data->date)
                ->lockForUpdate()
                ->first();

            if ($existingQuota) {
                return $this->responseError(
                    "Kuota untuk tanggal ini sudah ada. Silahkan gunakan tombol 'Edit' untuk memperbarui.",
                    'QUOTA_ALREADY_EXISTS',
                    422
                );
            }

            // Create new quota
            $quota = SetQuotaAction::run($data);

            return $this->responseSuccess(
                'Kuota berhasil dibuat',
                $quota
            );
        } catch (\Throwable $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Quota operation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->responseError(
                'Gagal menyimpan kuota: ' . $e->getMessage(),
                'SAVE_FAILED',
                500
            );
        }
    }

    /**
     * Get destination summary with all daily quotas for detail modal.
     * GET /cms/quotas/destination/{id}/detail
     */
    public function destinationDetail(Destination $destination): JsonResponse
    {
        $this->authorize('viewAny', DailyQuota::class);

        // Get all daily quotas for this destination (all time, not filtered by date range)
        $dailyQuotas = DailyQuota::where('destination_id', $destination->id)
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($quota) {
                return [
                    'id' => $quota->id,
                    'date' => $quota->date->toDateString(),
                    'date_formatted' => $quota->date->format('d M Y'),
                    'quota_daily' => $quota->quota_daily,
                    'used_daily' => $quota->used_daily,
                    'remaining_daily' => $quota->remaining_daily,
                ];
            });

        // Calculate total already plotted and remaining to plot
        $totalPlotted = $dailyQuotas->sum('quota_daily');
        $remainingToPlot = $destination->remaining_quota;

        return response()->json([
            'success' => true,
            'current_date' => Carbon::today()->toDateString(),
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'code' => $destination->code,
                'total_quota' => $destination->total_quota,
                'used_quota' => $destination->used_quota,
                'remaining_quota' => $destination->remaining_quota,
            ],
            'daily_quotas' => $dailyQuotas,
            'total_plotted' => $totalPlotted,
            'remaining_to_plot' => $remainingToPlot,
        ]);
    }
}
