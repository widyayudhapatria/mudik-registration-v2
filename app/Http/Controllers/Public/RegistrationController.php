<?php

namespace App\Http\Controllers\Public;

use App\Actions\Registration\SubmitRegistrationAction;
use App\Data\RegistrationData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show registration form.
     * 
     * Middleware sudah handle: signed, form.link.valid, quota.available
     */
    public function show(Request $request, string $token): View
    {
        // FormLink sudah divalidasi oleh middleware
        $formLink = $request->formLink;

        return view('public.registration.form', [
            'formLink' => $formLink,
            'token' => $token,
        ]);
    }

    /**
     * Submit registration form.
     * 
     * Middleware sudah handle: signed, form.link.valid, quota.available
     */
    public function submit(Request $request, string $token, RegistrationData $data): JsonResponse
    {
        try {
            // FormLink sudah divalidasi oleh middleware
            $formLink = $request->formLink;

            // Submit registration
            $registration = SubmitRegistrationAction::run($formLink, $data);

            return $this->responseSuccess(
                "✅ PENDAFTARAN BERHASIL\n" .
                "Terima kasih telah mendaftar!\n" .
                "Pendaftaran Anda sedang diproses.\n" .
                "Tim kami akan melakukan verifikasi data dalam waktu maksimal 2x24 jam.\n" .
                "Anda akan menerima notifikasi via email jika pendaftaran Anda\n" .
                "disetujui atau memerlukan perbaikan data.",
                [
                    'registration_id' => $registration->id,
                    'email' => $formLink->email,
                ]
            );

        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }
}