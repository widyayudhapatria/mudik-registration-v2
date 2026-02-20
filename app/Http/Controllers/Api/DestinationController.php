<?php

namespace App\Http\Controllers\Api;

use App\Actions\Quota\GetAvailableDestinationsAction;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    /**
     * Get available destinations with today's daily quota.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function available(Request $request): JsonResponse
    {
        try {
            // Get date from query or use today
            $dateString = $request->query('date');
            $date = $dateString ? Carbon::parse($dateString) : null;

            // Get available destinations
            $destinations = GetAvailableDestinationsAction::run($date);

            return response()->json([
                'success' => true,
                'data' => $destinations,
            ]);
        } catch (MudikException $e) {
            // Handle DailyQuotaNotSet error specifically
            if ($e->getErrorCode() === ErrorCode::DailyQuotaNotSet) {
                return response()->json([
                    'success' => false,
                    'error_code' => ErrorCode::DailyQuotaNotSet->value,
                    'message' => 'Kuota harian untuk hari ini belum diatur oleh admin.',
                    'friendly_message' => 'Silakan kembali lagi dalam 1 jam ke depan atau kunjungi secara berkala.',
                ], 200); // Still 200 for better UX
            }

            // Other MudikException errors
            return response()->json([
                'success' => false,
                'error_code' => $e->getErrorCode()->value ?? 'UNKNOWN_ERROR',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            // Unexpected errors
            return response()->json([
                'success' => false,
                'error_code' => 'SERVER_ERROR',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
    }
}
