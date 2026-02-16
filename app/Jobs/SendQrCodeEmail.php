<?php

namespace App\Jobs;

use App\Mail\QrCodeMail;
use App\Models\EmailLog;
use App\Models\Registration;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendQrCodeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Pass registration ID instead of model instance
     * Ini mencegah stale data dan memastikan fresh data dari database
     */
    public function __construct(
        public int $registrationId
    ) {}

    public function handle(QrCodeService $qrCodeService): void
    {
        // Load fresh data dari database dengan relationships
        $registration = Registration::with(['qrCode', 'formLink'])->findOrFail($this->registrationId);

        // Validasi QR code exists
        if (!$registration->qrCode) {
            Log::error('QR code not found for registration', [
                'registration_id' => $this->registrationId,
            ]);
            throw new \Exception('QR code tidak ditemukan untuk registrasi ini');
        }

        $subject = 'QR Code Tiket - ' . config('app.name');

        $emailLog = EmailLog::logQrCodeEmail(
            $registration->form_link_id,
            $registration->formLink->email,
            $subject
        );

        try {
            Mail::to($registration->formLink->email)
                ->send(
                    new QrCodeMail(
                        $registration,
                        $qrCodeService,
                        config('mudik')
                    )
                );

            $emailLog->markAsSent();

            Log::info('QR code email sent successfully', [
                'registration_id' => $this->registrationId,
                'email' => $registration->formLink->email,
            ]);
        } catch (\Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send QR code email', [
                'registration_id' => $this->registrationId,
                'email' => $registration->formLink->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('QR code email job failed permanently', [
            'registration_id' => $this->registrationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
