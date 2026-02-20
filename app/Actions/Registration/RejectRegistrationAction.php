<?php

namespace App\Actions\Registration;

use App\Data\RejectRegistrationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\Registration;
use App\Jobs\SendRejectionEmail;
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

            // Reject registration and fallback null admin notes
            $notes = $data->admin_notes ?? null;
            $registration->reject($admin->id, $data->rejection_reason, $notes);
            $registration->formLink->markAsRejected();
            
            // Soft delete participants first (cascading)
            $registration->participants()->delete();

            // Soft delete registration itself
            $registration->delete();

            DB::commit();

            // Queue rejection email (use withTrashed to access soft-deleted data)
            dispatch(new SendRejectionEmail($registration->fresh('formLink')));

            Log::info('Registration rejected and soft deleted', [
                'registration_id' => $registration->id,
                'rejected_by' => $admin->id,
                'reason' => $data->rejection_reason,
                'participants_count' => $registration->participants()->withTrashed()->count(),
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
