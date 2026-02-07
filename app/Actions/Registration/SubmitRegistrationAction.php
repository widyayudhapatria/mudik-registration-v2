<?php

namespace App\Actions\Registration;

use App\Data\RegistrationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\DailyQuota;
use App\Models\FormLink;
use App\Models\Participant;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class SubmitRegistrationAction
{
    use AsAction;

    public function handle(FormLink $formLink, RegistrationData $data): Registration
    {
        try {
            DB::beginTransaction();

            // 1. Lock form link FOR UPDATE
            $formLink = FormLink::where('id', $formLink->id)
                ->lockForUpdate()
                ->first();

            // 2. Validate form link status
            if ($formLink->status !== 'pending') {
                throw new MudikException(ErrorCode::LinkInvalid);
            }

            if ($formLink->isExpired()) {
                throw new MudikException(ErrorCode::LinkExpired);
            }

            if ($formLink->used_at !== null) {
                throw new MudikException(ErrorCode::LinkInvalid);
            }

            // 3. Check daily quota (with lock)
            $quota = DailyQuota::where('date', Carbon::today())
                ->lockForUpdate()
                ->first();

            if (!$quota || $quota->remaining <= 0) {
                throw new MudikException(ErrorCode::QuotaFull);
            }

            // 4. Check duplicates
            $this->checkDuplicates($data);

            // 5. Upload KK document
            $kkPath = $this->uploadKKDocument($data->kk_document);

            // 6. Create registration
            $registration = Registration::create([
                ...$data->toModelArray(),
                'form_link_id' => $formLink->id,
                'kk_document_path' => $kkPath,
            ]);

            // 7. Create participants
            foreach ($data->getParticipantsArray() as $participantData) {
                Participant::create([
                    'registration_id' => $registration->id,
                    ...$participantData,
                ]);
            }

            // 8. Update form link & quota
            $formLink->markAsUsed();
            $quota->incrementUsed();

            // 9. Clear quota cache
            Cache::forget('quota:' . Carbon::today()->toDateString());

            DB::commit();

            Log::info('Registration submitted successfully', [
                'registration_id' => $registration->id,
                'form_link_id' => $formLink->id,
                'email' => $formLink->email,
            ]);

            return $registration->load('participants');

        } catch (MudikException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Registration submission failed', [
                'form_link_id' => $formLink->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }

    protected function checkDuplicates(RegistrationData $data): void
    {
        // Check KK duplicate
        if (Registration::where('kk_number', $data->kk_number)->exists()) {
            throw new MudikException(ErrorCode::DuplicateKK);
        }

        // Check representative NIK
        if (Registration::where('representative_nik', $data->representative_nik)->exists()) {
            throw new MudikException(ErrorCode::DuplicateNIK);
        }

        // Check all participants NIK/KIA
        foreach ($data->participants as $participant) {
            if (Participant::where('nik_kia', $participant->nik_kia)->exists()) {
                throw new MudikException(ErrorCode::DuplicateKIA);
            }
        }
    }

    protected function uploadKKDocument($file): string
    {
        $path = config('mudik.upload.kk_document_path', 'uploads/kk_documents');
        return $file->store($path, 'public');
    }
}