<?php

namespace App\Actions\Registration;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Jobs\SendQrCodeEmail;
use App\Models\Admin;
use App\Models\EmailLog;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class ResendQrCodeEmailAction
{
    use AsAction;

    public function handle(Registration $registration, Admin $admin): array
    {
        try {
            // Validate registration is approved
            if (!$registration->isApproved()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Pendaftaran harus sudah disetujui untuk dapat mengirim ulang QR code'
                );
            }

            // Check if QR code exists
            if (!$registration->qrCode) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'QR code tidak ditemukan untuk registrasi ini'
                );
            }

            // Find the latest failed QR code email
            $emailLog = EmailLog::where('form_link_id', $registration->form_link_id)
                ->where('email_type', 'qr_code')
                ->latest('created_at')
                ->first();

            // If no previous email log exists, create one so resend can proceed
            if (!$emailLog) {
                $subject = 'QR Code Tiket - ' . config('app.name');
                $emailLog = EmailLog::logQrCodeEmail(
                    $registration->form_link_id,
                    $registration->formLink->email,
                    $subject
                );
            } else {
                // Can only resend if email failed
                if (!$emailLog->isFailed()) {
                    throw new MudikException(
                        ErrorCode::ServerError,
                        'Email sudah terkirim atau masih pending'
                    );
                }

                // Check if can retry (max 3 retries)
                if (!$emailLog->canRetry(maxRetries: 3)) {
                    throw new MudikException(
                        ErrorCode::ServerError,
                        'Sudah mencapai batas maksimal percobaan pengiriman ulang (3x)'
                    );
                }

                // Increment retry count for existing logs
                $emailLog->incrementRetry();
            }

            // Queue the email again
            dispatch(new SendQrCodeEmail($registration->id))->delay(15);

            Log::info('QR code email resent', [
                'registration_id' => $registration->id,
                'email_log_id' => $emailLog->id,
                'resent_by' => $admin->id,
                'retry_count' => $emailLog->retry_count,
            ]);

            return [
                'success' => true,
                'message' => 'Email QR code berhasil dijadwalkan untuk dikirim ulang',
                'email_log' => $emailLog,
            ];
        } catch (MudikException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('QR code email resend failed', [
                'registration_id' => $registration->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }
}
