<?php

namespace App\Http\Controllers\Public;

use App\Actions\Registration\SubmitRegistrationAction;
use App\Data\RegistrationData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show registration form.
     */
    public function show(Request $request, string $token): View
    {
        $formLink = $request->formLink;

        $submitUrl = URL::temporarySignedRoute(
            'public.registration.submit',
            $formLink->expired_at,
            ['token' => $token]
        );

        return view('public.registration.form', [
            'formLink' => $formLink,
            'token' => $token,
            'submitUrl' => $submitUrl,
        ]);
    }

    /**
     * Submit registration form.
     */
    public function submit(Request $request, string $token, RegistrationData $data): JsonResponse
    {
        try {
            $formLink = $request->formLink;
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