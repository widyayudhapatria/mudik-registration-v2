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
}
