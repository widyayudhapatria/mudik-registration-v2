<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Scanner\ConsumeQrCodeAction;
use App\Actions\Scanner\ValidateQrCodeAction;
use App\Data\ScanQrData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Validate QR Code (read-only).
     *
     * Middleware 'scanner.permission' sudah handle authorization
     */
    public function validateQrCode(Request $request): JsonResponse
    {
        try {
            $tokenQr = $request->query('token_qr');

            if (!$tokenQr) {
                return $this->responseError(
                    'Token QR diperlukan',
                    'TOKEN_REQUIRED',
                    null,
                    400
                );
            }

            $admin = auth('admin')->user();
            $result = ValidateQrCodeAction::run($tokenQr, $admin);

            return response()->json($result);
        } catch (MudikException $e) {
            return response()->json($e->toArray(), $e->getCode() ?: 400);
        }
    }

    /**
     * Consume QR Code (scan and mark as used).
     *
     * Middleware 'scanner.permission' sudah handle authorization
     */
    public function consumeQrCode(ScanQrData $data): JsonResponse
    {
        try {
            $admin = auth('admin')->user();
            $result = ConsumeQrCodeAction::run($data, $admin);

            // Invalidate scanner caches on successful consume so dashboard updates quickly
            if (isset($result['success']) && $result['success']) {
                try {
                    // file cache driver supports basic forget
                    \Illuminate\Support\Facades\Cache::forget('cms.scanner.statistics');
                    \Illuminate\Support\Facades\Cache::forget('cms.scanner.scan_by_destination');
                } catch (\Exception $e) {
                    // don't block response on cache errors
                }
            }

            return response()->json($result);
        } catch (MudikException $e) {
            return response()->json($e->toArray(), $e->getCode() ?: 400);
        }
    }

    /**
     * Get last 10 scan logs (success and failed).
     */
    public function scanHistory(): JsonResponse
    {
        try {
            // Get current authenticated admin
            $admin = auth('admin')->user();

            $logs = \App\Models\ScanLog::query()
                ->with('qrCode.registration', 'admin')
                ->where('admin_id', $admin->id) // Filter by current logged-in admin
                ->whereNotNull('scan_result')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'qr_code_id' => $log->qr_code_id,
                        'status' => $log->scan_result, // already 'success' or 'failed' from model
                        'failure_reason' => $log->failure_reason ?? '-',
                        'representative_name' => $log->qrCode?->registration?->representative_name ?? 'Unknown',
                        'family_count' => $log->qrCode?->registration?->family_count ?? 0,
                        'admin_name' => $log->admin?->name ?? '-',
                        'scanned_at' => $log->scanned_at->toIso8601String(),
                        'formatted_time' => $log->scanned_at->format('d M Y H:i:s'),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch scan history',
            ], 500);
        }
    }
}
