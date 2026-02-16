<?php

namespace App\Actions\Registration;

use App\Data\ApproveRegistrationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
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

            // Check if already processed
            if ($registration->isApproved() || $registration->isRejected()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Gagal Approve / Reject: Pendaftaran sudah diproses sebelumnya'
                );
            }

            // Approve registration
            $notes = $data->admin_notes instanceof \Spatie\LaravelData\Optional
                ? null
                : $data->admin_notes;

            $registration->approve($admin->id, $notes);

            // Generate QR Code
            $qrCode = $this->qrCodeService->generateForRegistration($registration);

            DB::commit();

            Log::info('Registration approved', [
                'registration_id' => $registration->id,
                'approved_by' => $admin->id,
                'qr_code_id' => $qrCode->id,
            ]);

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
