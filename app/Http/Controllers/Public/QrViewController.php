<?php

namespace App\Http\Controllers\Public;

use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\SeatAllocation;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class QrViewController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService) {}
    /**
     * Show QR code entry page.
     *
     * GET /scan/entry
     */
    public function entry(Request $request): View|RedirectResponse
    {
        $token = $request->query('t');

        if ($token) {
            // Redirect ke view route
            return redirect()->route('scan.view', ['token' => $token]);
        }

        // return 404
        abort(404, ErrorCode::InvalidQR->getMessage());
    }

    /**
     * View QR code details.
     *
     * GET /scan/{token}
     */
    public function view(string $token): View|RedirectResponse
    {
        // Validate token format (should be 64 chars alphanumeric)
        if (!preg_match('/^[a-zA-Z0-9]{64}$/', $token)) {
            Log::warning('Invalid token format', [
                'token' => substr($token, 0, 32) . '...',
                'length' => strlen($token),
            ]);
            abort(404, ErrorCode::InvalidQR->getMessage());
        }

        // Find QR code by token
        $qrCode = QrCode::where('token_qr', $token)
            ->with(['registration.participants', 'registration.formLink', 'registration.destination'])
            ->first();

        if (!$qrCode) {
            Log::warning('QR Code not found in database', [
                'searched_token' => substr($token, 0, 32) . '...',
                'token_length' => strlen($token),
            ]);
            abort(404, ErrorCode::InvalidQR->getMessage());
        }

        // Redirect admin to scanner CMS with proper token_qr
        if (auth('admin')->check()) {
            return redirect()->route('cms.scanner.scan', ['token' => $qrCode->token_qr]);
        }

        $seatAllocations = $qrCode->isScanned()
            ? SeatAllocation::where('registration_id', $qrCode->registration_id)
                ->with('participant')
                ->get()
                ->keyBy('participant_id') 
            : collect();

        // Public view
        return view('public.qr.view', [
            'qrCode'       => $qrCode,
            'registration' => $qrCode->registration,
            'participants' => $qrCode->registration->participants,
            'seatAllocations' => $seatAllocations,
            'qrBase64'     => !$qrCode->isScanned()
                                ? $this->qrCodeService->generateBase64Image($qrCode)
                                : null,
        ]);
    }
}
