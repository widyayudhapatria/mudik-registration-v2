<?php

namespace App\Actions\Registration;

use App\Data\ApproveRegistrationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\Destination;
use App\Models\Registration;
use App\Services\QrCodeService;
use App\Jobs\SendQrCodeEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class ApproveRegistrationAction
{
    use AsAction;

    public function __construct(
        protected QrCodeService $qrCodeService
    ) {}

    public function handle(Registration $registration, Admin $admin, ApproveRegistrationData $data): Registration
    {
        try {
            DB::beginTransaction();

            // CRITICAL: Lock registration FIRST to prevent double-approve -- lock held until commit
            $registration = Registration::where('id', $registration->id)
                ->lockForUpdate()
                ->first();

            if (!$registration) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Error: Registrasi id ' . $registration->id . ' tidak ditemukan'
                );
            }

            // Check if already processed
            if ($registration->isApproved() || $registration->isRejected()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Gagal Approve / Reject: Pendaftaran sudah diproses sebelumnya'
                );
            }

            // CRITICAL: Lock destination and validate remaining quota
            $destination = Destination::where('id', $registration->destination_id)
                ->lockForUpdate()
                ->first();

            if (!$destination) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Error: Destination id ' . $registration->destination_id . ' tidak ditemukan'
                );
            }

            if (!$destination->is_active) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    sprintf('Destination %s tidak aktif', $destination->name)
                );
            }

            // Validate: destination remaining quota must be >= participants_count
            if ($destination->remaining_quota < $registration->participants()->count()) {
                throw new MudikException(
                    ErrorCode::DestinationQuotaFull,
                    sprintf(
                        'Quota destination %s tidak mencukupi. Tersisa: %d orang, Yang Diinputkan: %d orang',
                        $destination->name,
                        $destination->remaining_quota,
                        $registration->participants()->count()
                    )
                );
            }

            // Approve registration and fallback null admin notes
            $notes = $data->admin_notes ?? null;
            $registration->approve($admin->id, $notes);

            // CRITICAL: Update destination used_quota (confirmed booking)
            $destination->used_quota += $registration->participants()->count();
            $destination->save();

            // Generate QR Code
            $qrCode = $this->qrCodeService->generateForRegistration($registration);

            DB::commit();

            Log::info('Registration approved', [
                'registration_id' => $registration->id,
                'approved_by' => $admin->id,
                'destination_id' => $destination->id,
                'destination_name' => $destination->name,
                'participants_count' => $registration->participants()->count(),
                'destination_used_quota' => $destination->used_quota,
                'destination_remaining_quota' => $destination->remaining_quota,
                'qr_code_id' => $qrCode->id,
            ]);

            // Queue QR code email
            dispatch(new SendQrCodeEmail($registration->id));

            return $registration->fresh(['qrCode', 'participants']);
        } catch (MudikException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Registration approval failed', [
                'registration_id' => $registration->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }
}
