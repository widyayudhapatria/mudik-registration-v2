<?php

namespace App\Http\Middleware;

use App\Actions\FormLink\ValidateFormLinkAction;
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
        try {
            $token = $request->route('token');

            if (!$token) {
                throw new MudikException(ErrorCode::LinkInvalid);
            }

            // Get formLink with lock for update (prevent race condition)
            $formLink = FormLink::where('token', $token)
                ->lockForUpdate()
                ->first();

            if (!$formLink) {
                throw new MudikException(ErrorCode::LinkInvalid);
            }

            // Validate form link using action
            ValidateFormLinkAction::run($formLink);

            // Share form link to request for controller usage
            $request->merge(['formLink' => $formLink]);

            return $next($request);
        } catch (MudikException $e) {
            throw $e;
        }
    }
}
