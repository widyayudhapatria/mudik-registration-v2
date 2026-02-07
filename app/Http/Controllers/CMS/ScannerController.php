<?php

namespace App\Http\Controllers\CMS;

use App\Actions\Scanner\ConsumeQrCodeAction;
use App\Actions\Scanner\ValidateQrCodeAction;
use App\Data\ScanQrData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
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

            $result = ValidateQrCodeAction::run($tokenQr);

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

            return response()->json($result);

        } catch (MudikException $e) {
            return response()->json($e->toArray(), $e->getCode() ?: 400);
        }
    }
}