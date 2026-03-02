<?php

namespace App\Actions\Registration;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Jobs\SendRejectionEmail;
use App\Models\Admin;
use App\Models\EmailLog;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class ResendRejectionEmailAction
{
    use AsAction;

    private const MAX_RETRY = 10;

    public function handle(Registration $registration, Admin $admin): array
    {
        try {
            if (!$registration->isRejected()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Pendaftaran harus berstatus ditolak untuk mengirim ulang email penolakan'
                );
            }

            $emailLog = EmailLog::where('form_link_id', $registration->form_link_id)
                ->where('email_type', 'rejection')
                ->latest('created_at')
                ->first();

            if (!$emailLog) {
                $emailLog = EmailLog::logRejectionEmail(
                    $registration->form_link_id,
                    $registration->formLink->email,
                    'Pemberitahuan Penolakan - ' . config('app.name')
                );
            } else {
                if ($emailLog->retry_count >= self::MAX_RETRY) {
                    throw new MudikException(
                        ErrorCode::ServerError,
                        'Sudah mencapai batas maksimal percobaan pengiriman ulang (' . self::MAX_RETRY . 'x)'
                    );
                }

                $emailLog->incrementRetry();
            }

            dispatch(new SendRejectionEmail($registration, $emailLog->id))->delay(15);

            Log::info('Rejection email resent', [
                'registration_id' => $registration->id,
                'email_log_id'    => $emailLog->id,
                'resent_by'       => $admin->id,
                'retry_count'     => $emailLog->retry_count,
            ]);

            return [
                'success'   => true,
                'message'   => 'Email penolakan berhasil dijadwalkan untuk dikirim ulang',
                'email_log' => $emailLog,
            ];
        } catch (MudikException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Rejection email resend failed', [
                'registration_id' => $registration->id,
                'admin_id'        => $admin->id,
                'error'           => $e->getMessage(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }
}