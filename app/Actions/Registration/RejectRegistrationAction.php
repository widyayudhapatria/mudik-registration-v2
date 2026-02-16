<?php

namespace App\Actions\Registration;

use App\Data\RejectRegistrationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\DailyQuota;
use App\Models\Registration;
use App\Jobs\SendRejectionEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RejectRegistrationAction
{
    use AsAction;

    public function handle(Registration $registration, Admin $admin, RejectRegistrationData $data): Registration
    {
        try {
            DB::beginTransaction();

            // Check if already processed
            if ($registration->isApproved() || $registration->isRejected()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Pendaftaran sudah diproses sebelumnya'
                );
            }

            // Reject registration
            $notes = $data->admin_notes instanceof \Spatie\LaravelData\Optional
                ? null
                : $data->admin_notes;

            $registration->reject($admin->id, $data->rejection_reason, $notes);


            DB::commit();

            // Queue rejection email
            dispatch(new SendRejectionEmail($registration->fresh('formLink')));

            Log::info('Registration rejected', [
                'registration_id' => $registration->id,
                'rejected_by' => $admin->id,
                'reason' => $data->rejection_reason,
            ]);

            return $registration->fresh();
        } catch (MudikException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Registration rejection failed', [
                'registration_id' => $registration->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }
}
