<?php

namespace App\Http\Controllers\Public;

use App\Actions\Email\SubmitEmailAction;
use App\Data\SubmitEmailData;
use App\Exceptions\MudikException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EmailSubmissionController extends Controller
{
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
                "Link formulir pendaftaran telah dikirim ke email Anda.<br><br>" .
                    "Silahkan cek inbox atau spam folder Anda.<br>" .
                    "<b>Link akan kadaluarsa dalam 3 hari.</b><br><br>" .
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
