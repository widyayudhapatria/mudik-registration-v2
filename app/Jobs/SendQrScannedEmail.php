<?php

namespace App\Jobs;

use App\Mail\QrScannedMail;
use App\Models\EmailLog;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Exception;

class SendQrScannedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public int $registrationId
    ) {}

    public function handle(): void
    {
        $registration = Registration::with(['qrCode', 'formLink', 'participants', 'destination'])
            ->findOrFail($this->registrationId);

        if (!$registration->qrCode) {
            Log::error('QR code not found for scanned email', [
                'registration_id' => $this->registrationId,
            ]);
            throw new Exception('QR code tidak ditemukan untuk registrasi ini');
        }

        $subject = 'Tiket Dikonfirmasi - Siap Berangkat! | ' . config('app.name');

        $emailLog = EmailLog::logQrCodeEmail(
            $registration->form_link_id,
            $registration->formLink->email,
            $subject
        );

        try {
            Mail::to($registration->formLink->email)
                ->send(new QrScannedMail(
                    $registration,
                    $registration->qrCode,
                    config('mudik')
                ));

            $emailLog->markAsSent();

            Log::info('QR scanned confirmation email sent', [
                'registration_id' => $this->registrationId,
                'email'           => $registration->formLink->email,
            ]);
        } catch (Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send QR scanned email', [
                'registration_id' => $this->registrationId,
                'email'           => $registration->formLink->email,
                'error'           => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('QR scanned email job failed permanently', [
            'registration_id' => $this->registrationId,
            'error'           => $exception->getMessage(),
        ]);
    }
}