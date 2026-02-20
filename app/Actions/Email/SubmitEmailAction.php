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

            // Check if email already exists
            $existingLink = FormLink::where('email', $data->email)->first();

            // Check if email already has form_link with submitted/approved (sedang direview atau sudah disetujui)
            if ($existingLink && in_array($existingLink->status, [FormLinkStatus::Submitted->value, FormLinkStatus::Approved->value])) {
                throw new MudikException(ErrorCode::EmailExists);
            }

            // Check if status pending -- Update (recreate) karena belum ada registration
            if ($existingLink && $existingLink->status === FormLinkStatus::Pending->value) {
                $formLink = $this->recreateLink($existingLink);
            } else {
                // Create new form_link -- Status rejected atau tidak ada form_link sama sekali
                // Rejected -- Create new karena form_link_id sudah punya registration (unique constraint)
                $formLink = $this->createNewLink($data->email);
            }

            DB::commit();
            Log::info('Before dispatching email job', [
                'form_link_id' => $formLink->id,
                'token' => $formLink->token,
                'generated_link' => $formLink->generated_link,
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
        $token = Str::random(64);

        return FormLink::create([
            'email' => $email,
            'token' => $token,
            'generated_link' => route('public.registration.form', ['token' => $token]),
            'expired_at' => Carbon::now()->addDays($expiryDays),
            'status' => FormLinkStatus::Pending->value,
        ]);
    }

    protected function recreateLink(FormLink $formLink): FormLink
    {
        $expiryDays = config('mudik.form_link_expiry_days', 3);
        $token = Str::random(64);

        $formLink->update([
            'token' => $token,
            'generated_link' => route('public.registration.form', ['token' => $token]),
            'expired_at' => Carbon::now()->addDays($expiryDays),
            'used_at' => null,
            'status' => FormLinkStatus::Pending->value,
            'resend_count' => $formLink->resend_count + 1,
        ]);

        return $formLink->fresh();
    }
}
