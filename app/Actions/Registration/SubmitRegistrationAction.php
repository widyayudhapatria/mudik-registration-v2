<?php

namespace App\Actions\Registration;

use App\Actions\FormLink\ValidateFormLinkAction;
use App\Data\RegistrationData;
use App\Data\ParticipantData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\DailyQuota;
use App\Models\Destination;
use App\Models\FormLink;
use App\Models\Participant;
use App\Models\Registration;
use App\Jobs\SendRegistrationSubmittedEmail;
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

            // 1. Validate form link (with lock early to prevent race condition)
            $formLink = FormLink::where('id', $formLink->id)
                ->lockForUpdate()
                ->first();

            ValidateFormLinkAction::run($formLink);


            // 2. Validate destination exists and is active
            $destination = Destination::where('id', $data->destination_id)
                ->where('is_active', true)
                ->first();

            if (!$destination) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Destination tidak valid atau tidak aktif'
                );
            }

            // 3. Atomic - lock quota EARLY and keep lock to prevent race condition
            $quota = DailyQuota::where('destination_id', $data->destination_id)
                ->where('date', Carbon::today()->toDateString())
                ->lockForUpdate()  // 🔒 LOCK ACQUIRED
                ->first();

            if (!$quota) {
                throw new MudikException(
                    ErrorCode::DailyQuotaNotSet,
                    'Kuota harian untuk tujuan ini belum diset. Silakan coba lagi nanti.'
                );
            }

            // Participant count
            $totalParticipantCount = count($data->participants);
            $quotaParticipantCount = $data->getQuotaParticipantCount(); // exclude under 4 years old from quota count

            // if participant is more than family count
            if ($totalParticipantCount !== $data->family_count) {
                //throw error
                throw new MudikException(
                    ErrorCode::InvalidFamilyCount,
                    sprintf(
                        'Jumlah peserta (%d) tidak sesuai dengan jumlah peserta mudik (%d)',
                        $totalParticipantCount,
                        $data->family_count
                    )
                );
            }

            // CRITICAL: CHECK QUOTA - hanya peserta >= 4 tahun yang dihitung
            if ($quota->remaining_daily < $quotaParticipantCount) {
                throw new MudikException(
                    ErrorCode::DailyQuotaFull,
                    sprintf(
                        'Kuota harian tidak mencukupi. Tersisa: %d orang, Yang Diinputkan: %d orang',
                        $quota->remaining_daily,
                        $quotaParticipantCount
                    )
                );
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

            // 8. Update form link & quota (lock still active on quota)
            $formLink->markAsUsed();

            // CRITICAL: DECREMENT QUOTA - MASIH DALAM LOCK
            // ATOMIC: Check + Decrement dalam satu lock scope
            $quota->used_daily += $quotaParticipantCount;
            $quota->save();

            // 9. Clear quota cache
            Cache::forget('destinations:available:' . Carbon::today()->toDateString());

            // 10. All done, commit transaction
            DB::commit();

            // 11. Dispatch email job (after commit)
            SendRegistrationSubmittedEmail::dispatch($registration)->delay(15);

            Log::info('Registration submitted successfully', [
                'registration_id' => $registration->id,
                'form_link_id' => $formLink->id,
                'destination_id' => $data->destination_id,
                'destination_name' => $destination->name,
                'family_count' => $data->family_count,
                'participant_count' => $totalParticipantCount,
                'quota_participant_count' => $quotaParticipantCount,
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
                'class' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }

    protected function checkDuplicates(RegistrationData $data): void
    {
        // Check KK duplicate (only active registrations with submitted/approved form_link)
        if (Registration::withoutTrashed()
            ->where('kk_number', $data->kk_number)
            ->whereHas('formLink', function ($query) {
                $query->whereIn('status', ['pending', 'submitted', 'approved']);
            })
            ->exists()
        ) {
            throw new MudikException(
                ErrorCode::DuplicateKK,
                sprintf(
                    'Nomor Kartu Keluarga (KK) %s sudah terdaftar di sistem.',
                    $data->kk_number
                )
            );
        }

        // Check representative NIK (only active registrations with submitted/approved form_link)
        if (Registration::withoutTrashed()
            ->where('representative_nik', $data->representative_nik)
            ->whereHas('formLink', function ($query) {
                $query->whereIn('status', ['pending', 'submitted', 'approved']);
            })
            ->exists()
        ) {
            throw new MudikException(
                ErrorCode::DuplicateNIK,
                sprintf(
                    'Nomor KTP perwakilan %s sudah terdaftar di sistem.',
                    $data->representative_nik
                )
            );
        }

        // Check all participants NIK/KIA (only active participants with active registration and submitted/approved form_link)
        $duplicateParticipants = [];

        foreach ($data->participants as $index => $participant) {
            if (Participant::withoutTrashed()
                ->where('nik_kia', $participant->nik_kia)
                ->whereHas('registration', function ($query) {
                    $query->withoutTrashed()
                        ->whereHas('formLink', function ($q) {
                            $q->whereIn('status', ['pending', 'submitted', 'approved']);
                        });
                })
                ->exists()
            ) {
                $duplicateParticipants[] = [
                    'peserta_number' => $index + 1,
                    'nik_kia' => $participant->nik_kia,
                    'full_name' => $participant->full_name,
                ];
            }
        }

        if (!empty($duplicateParticipants)) {
            $errorMessage = 'Nomor KTP/KIA peserta berikut sudah terdaftar di sistem:<br/><br/>';
            foreach ($duplicateParticipants as $duplicate) {
                $errorMessage .= sprintf(
                    '- Peserta %d: %s (NIK/KIA: %s)<br/>',
                    $duplicate['peserta_number'],
                    $duplicate['full_name'],
                    $duplicate['nik_kia']
                );
            }
            $errorMessage .= '<br/>Silakan periksa dan gunakan nomor KTP/KIA yang berbeda.';

            throw new MudikException(
                ErrorCode::DuplicateKTPKIAParticipant,
                $errorMessage,
                ['duplicates' => $duplicateParticipants]
            );
        }
    }

    protected function uploadKKDocument($file): string
    {
        // 1. Validasi MIME type real (double check)
        $allowedMimes = ['image/jpeg', 'image/png'];
        $fileMime = $file->getMimeType();

        if (!in_array($fileMime, $allowedMimes)) {
            throw new MudikException(
                ErrorCode::InvalidFile,
                'File harus berupa gambar JPG atau PNG yang valid.'
            );
        }

        // 2. Validasi file adalah image dengan getimagesize
        try {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                throw new MudikException(
                    ErrorCode::InvalidFile,
                    'File yang diupload bukan gambar yang valid.'
                );
            }

            // Validasi MIME dari getimagesize
            $detectedMime = $imageInfo['mime'];
            if (!in_array($detectedMime, $allowedMimes)) {
                throw new MudikException(
                    ErrorCode::InvalidFile,
                    'Tipe gambar tidak didukung.'
                );
            }
        } catch (\Exception $e) {
            // Jika error dari MudikException, re-throw
            if ($e instanceof MudikException) {
                throw $e;
            }
            // Untuk exception lain
            throw new MudikException(
                ErrorCode::InvalidFile,
                'File yang diupload tidak dapat diproses sebagai gambar.'
            );
        }

        // 3. Generate safe filename (hapus original filename untuk security)
        $extension = $file->getClientOriginalExtension();
        $safeFilename = uniqid('kk_', true) . '_' . time() . '.' . $extension;

        // 4. Store dengan nama file yang di-generate
        $path = config('mudik.upload.kk_document_path', 'uploads/kk_documents');
        return $file->storeAs($path, $safeFilename, 'public');
    }
}
