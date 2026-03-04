<?php

namespace App\Actions\Registration;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Jobs\SendSeatAllocationEmailJob;
use App\Models\Admin;
use App\Models\EmailLog;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class ResendSeatAllocationEmailAction
{
    use AsAction;

    public function handle(Registration $registration, Admin $admin): array
    {
        try {
            // Validate registration is approved
            if (!$registration->isApproved()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Pendaftaran harus sudah disetujui untuk dapat mengirim ulang email kursi'
                );
            }

            // Check if QR code has been scanned
            if (!$registration->qrCode || !$registration->qrCode->scanned_at) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'QR code harus sudah di-scan sebelum mengirim email alokasi kursi'
                );
            }

            // Check if seat allocations exist
            $seatAllocations = $registration->seatAllocations()
                ->with('destination')
                ->get();

            if ($seatAllocations->isEmpty()) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Alokasi kursi belum tersedia untuk registrasi ini'
                );
            }

            // Find the latest email log
            $emailLog = EmailLog::where('form_link_id', $registration->form_link_id)
                ->where('email_type', 'seat_allocation')
                ->latest('created_at')
                ->first();

            // If no previous email log exists, create one
            if (!$emailLog) {
                $subject = '🎫 E-Ticket Mudik Gratis 2026 - ' . $registration->destination->name;
                $emailLog = EmailLog::logSeatAllocationEmail(
                    $registration->form_link_id,
                    $registration->formLink->email,
                    $subject
                );
            } else {
                // Check retry limit to prevent abuse
                if ($emailLog->retry_count >= 10) {
                    throw new MudikException(
                        ErrorCode::ServerError,
                        'Sudah mencapai batas maksimal percobaan pengiriman ulang (10x)'
                    );
                }

                // Increment retry count for existing logs
                $emailLog->incrementRetry();
            }

            // Queue the email again with 15 second delay
            dispatch(new SendSeatAllocationEmailJob($registration, $seatAllocations))
                ->delay(15);

            Log::info('Seat allocation email resent', [
                'registration_id' => $registration->id,
                'email_log_id' => $emailLog->id,
                'resent_by' => $admin->id,
                'retry_count' => $emailLog->retry_count,
            ]);

            return [
                'success' => true,
                'message' => 'Email alokasi kursi berhasil dijadwalkan untuk dikirim ulang',
                'email_log' => $emailLog,
            ];
        } catch (MudikException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Seat allocation email resend failed', [
                'registration_id' => $registration->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ServerError);
        }
    }
}
