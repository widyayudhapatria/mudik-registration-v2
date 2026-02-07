<?php

namespace App\Http\Controllers\Public;

use App\Actions\Email\SubmitEmailAction;
use App\Data\SubmitEmailData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EmailSubmissionController extends Controller
{
    public function __construct()
    {
        // Rate limiting: 5 requests per 10 minutes
        $this->middleware('throttle:5,10');
    }

    /**
     * Submit email for registration link.
     *
     * POST /public/submit-email
     */
    public function submit(SubmitEmailData $data): JsonResponse
    {
        try {
            $formLink = SubmitEmailAction::run($data);

            return $this->responseSuccess(
                "✅ EMAIL BERHASIL DIKIRIM\n" .
                "Link formulir pendaftaran telah dikirim ke email Anda.\n" .
                "Silahkan cek inbox atau spam folder Anda.\n" .
                "Link akan kadaluarsa dalam 3 hari.\n" .
                "Terima kasih!",
                [
                    'email' => $formLink->email,
                    'expired_at' => $formLink->expired_at->toISOString(),
                ]
            );

        } catch (MudikException $e) {
            return response()->json($e->toArray(), 400);
        }
    }
}