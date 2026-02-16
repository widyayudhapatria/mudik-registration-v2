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

        return view('pages.form', [
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
                "Terima kasih telah mendaftar!<br/>" .
                    "Mohon tunggu, pendaftaran Anda sedang diproses.<br/><br/>" .
                    "Tim kami akan melakukan verifikasi data dalam waktu maksimal <b>1x24 jam.</b><br/><br/>" .
                    "Anda akan menerima notifikasi via email jika pendaftaran Anda disetujui atau memerlukan perbaikan data.<br/>",
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
