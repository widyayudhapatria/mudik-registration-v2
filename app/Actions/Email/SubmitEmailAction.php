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

    private const BLOCKED_STATUSES = [
        FormLinkStatus::Pending->value,
        FormLinkStatus::Submitted->value,
        FormLinkStatus::Approved->value,
    ];

    public function handle(SubmitEmailData $data): FormLink
    {
        $existing = FormLink::where('email', $data->email)->get();
        Log::info('DEBUG submit email', [
            'email' => $data->email,
            'all_records' => $existing->toArray(),
            'eligible_check' => FormLink::where('email', $data->email)
                ->whereIn('status', [
                    FormLinkStatus::Submitted->value,
                    FormLinkStatus::Approved->value,
                ])
                ->exists(),
        ]);
        
        try {
            DB::beginTransaction();

            $this->ensureEmailEligible($data->email);

            $formLink = $this->resolveFormLink($data->email);

            DB::commit();

            Log::info('Before dispatching email job', [
                'form_link_id' => $formLink->id,
                'token' => $formLink->token,
                'generated_link' => $formLink->generated_link,
                'expired_at' => $formLink->expired_at,
            ]);

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

    private function ensureEmailEligible(string $email): void
    {
        $hasBlockedStatus = FormLink::where('email', $email)
            ->whereIn('status', self::BLOCKED_STATUSES)
            ->exists();

        if ($hasBlockedStatus) {
            throw new MudikException(ErrorCode::EmailExists);
        }
    }

    private function resolveFormLink(string $email): FormLink
    {
        $pendingLink = FormLink::where('email', $email)
            ->where('status', FormLinkStatus::Pending->value)
            ->latest()
            ->first();

        return $pendingLink
            ? $this->recreateLink($pendingLink)
            : $this->createNewLink($email);
    }

    private function createNewLink(string $email): FormLink
    {
        $token = Str::random(64);

        return FormLink::create([
            'email' => $email,
            'token' => $token,
            'generated_link' => route('public.registration.form', ['token' => $token]),
            'expired_at' => Carbon::now()->addDays(config('mudik.form_link_expiry_days', 3)),
            'status' => FormLinkStatus::Pending->value,
        ]);
    }

    private function recreateLink(FormLink $formLink): FormLink
    {
        $token = Str::random(64);

        $formLink->update([
            'token' => $token,
            'generated_link' => route('public.registration.form', ['token' => $token]),
            'expired_at' => Carbon::now()->addDays(config('mudik.form_link_expiry_days', 3)),
            'used_at' => null,
            'status' => FormLinkStatus::Pending->value,
            'resend_count' => $formLink->resend_count + 1,
        ]);

        return $formLink->fresh();
    }
}
