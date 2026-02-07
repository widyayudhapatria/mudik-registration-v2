<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Quota\GetQuotaAction;
use App\Actions\Quota\SetQuotaAction;
use App\Data\SetQuotaData;
use App\Http\Controllers\Controller;
use App\Models\DailyQuota;
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
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', DailyQuota::class);

        $startDate = $request->query('start_date', Carbon::today()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->addDays(30)->toDateString());

        $quotas = DailyQuota::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $quotas,
            ]);
        }

        return view('cms.quotas.index', [
            'quotas' => $quotas,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Get today's quota.
     */
    public function today(): JsonResponse
    {
        $this->authorize('viewAny', DailyQuota::class);

        $quota = GetQuotaAction::run(Carbon::today());

        if (!$quota) {
            return $this->responseNotFound('Kuota hari ini belum diset');
        }

        return response()->json([
            'success' => true,
            'data' => $quota,
        ]);
    }

    /**
     * Set quota for a date.
     */
    public function store(SetQuotaData $data): JsonResponse
    {
        $this->authorize('modify', DailyQuota::class);

        try {
            $quota = SetQuotaAction::run($data);

            return $this->responseSuccess(
                'Kuota berhasil di-set',
                $quota
            );

        } catch (\Throwable $e) {
            return $this->responseError(
                'Gagal menyimpan kuota: ' . $e->getMessage(),
                'SAVE_FAILED'
            );
        }
    }
}