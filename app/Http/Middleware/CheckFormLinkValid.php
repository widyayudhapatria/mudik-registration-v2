<?php

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\FormLink;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFormLinkValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        if (!$token) {
            throw new MudikException(ErrorCode::LinkInvalid);
        }

        $formLink = FormLink::where('token', $token)->first();

        if (!$formLink) {
            throw new MudikException(ErrorCode::LinkInvalid);
        }

        if ($formLink->isExpired()) {
            throw new MudikException(ErrorCode::LinkExpired);
        }

        if ($formLink->used_at !== null) {
            throw new MudikException(
                ErrorCode::LinkInvalid,
                'Link sudah digunakan'
            );
        }

        if ($formLink->status !== 'pending') {
            throw new MudikException(
                ErrorCode::LinkInvalid,
                'Link sudah diproses'
            );
        }

        // share ke request agar bisa dipakai di controller
        $request->merge(['formLink' => $formLink]);

        return $next($request);
    }
}