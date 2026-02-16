<?php

namespace App\Actions\Email;

use App\Data\SubmitEmailData;
use App\Enums\ErrorCode;
use App\Enums\FormLinkStatus;
use App\Exceptions\MudikException;
use App\Models\FormLink;
use App\Jobs\SendFormLinkEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class SubmitEmailAction
{
    use AsAction;

    public function handle(SubmitEmailData $data): FormLink
    {
        try {
            DB::beginTransaction();

            // Check if email already exists with valid status
            $existingLink = FormLink::where('email', $data->email)->first();

            if ($existingLink && in_array($existingLink->status, [FormLinkStatus::Submitted->value, FormLinkStatus::Approved->value])) {
                throw new MudikException(ErrorCode::EmailExists);
            }


            if ($existingLink) {
                // Recreate link
                $formLink = $this->recreateLink($existingLink);
            } else {
                // Create new link
                $formLink = $this->createNewLink($data->email);
            }

            DB::commit();
            Log::info('Before dispatching email job', [
                'form_link_id' => $formLink->id,
                'token' => $formLink->token,
                'expired_at' => $formLink->expired_at,
            ]);

            // Queue email job
            dispatch(new SendFormLinkEmail($formLink));

            return $formLink;
        } catch (MudikException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Failed to submit email', [
                'email' => $data->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }

    protected function createNewLink(string $email): FormLink
    {
        $expiryDays = config('mudik.form_link_expiry_days', 3);

        return FormLink::create([
            'email' => $email,
            'token' => Str::random(64),
            'expired_at' => Carbon::now()->addDays($expiryDays),
            'status' => FormLinkStatus::Pending->value,
        ]);
    }

    protected function recreateLink(FormLink $formLink): FormLink
    {
        $expiryDays = config('mudik.form_link_expiry_days', 3);

        $formLink->update([
            'token' => Str::random(64),
            'expired_at' => Carbon::now()->addDays($expiryDays),
            'used_at' => null,
            'status' => FormLinkStatus::Pending->value,
            'resend_count' => $formLink->resend_count + 1,
        ]);

        return $formLink->fresh();
    }
}
