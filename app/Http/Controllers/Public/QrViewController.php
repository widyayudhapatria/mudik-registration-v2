<?php

namespace App\Http\Controllers\Public;

use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QrViewController extends Controller
{
    /**
     * Show QR code entry page.
     *
     * GET /scan/entry
     */
    public function entry(Request $request): View
    {
        $token = $request->query('t');

        if ($token) {
            // Redirect ke view route
            return redirect()->route('scan.view', ['token' => $token]);
        }

        return view('public.qr.entry');
    }

    /**
     * View QR code details.
     *
     * GET /scan/{token}
     */
    public function view(string $token): View|RedirectResponse
    {
        $qrCode = QrCode::where('token_qr', $token)
            ->with(['registration.participants', 'registration.formLink'])
            ->first();

        if (!$qrCode) {
            abort(404, ErrorCode::InvalidQR->getMessage());
        }

        // Redirect admin to scanner CMS
        if (auth('admin')->check()) {
            return redirect()->route('cms.scanner.scan', ['token' => $token]);
        }

        // Public view
        return view('public.qr.view', [
            'qrCode' => $qrCode,
            'registration' => $qrCode->registration,
            'participants' => $qrCode->registration->participants,
        ]);
    }
}