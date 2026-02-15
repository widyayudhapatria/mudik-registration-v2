<?php

namespace App\Actions\FormLink;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\FormLink;
use Lorisleiva\Actions\Concerns\AsAction;

class ValidateFormLinkAction
{
    use AsAction;

    public function handle(FormLink $formLink): FormLink
    {
        // Check if link is in valid status
        if ($formLink->status !== 'pending') {
            throw new MudikException(ErrorCode::LinkInvalid);
        }

        // Check if link is expired
        if ($formLink->isExpired()) {
            throw new MudikException(ErrorCode::LinkExpired);
        }

        // Check if link has been used
        if ($formLink->used_at !== null) {
            throw new MudikException(ErrorCode::LinkInvalid);
        }

        return $formLink;
    }
}
