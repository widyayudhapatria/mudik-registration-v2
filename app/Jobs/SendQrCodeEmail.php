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
     * Create a new job instance.
     */
    public function __construct(
        public Registration $registration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(QrCodeService $qrCodeService): void
    {
        $subject = 'QR Code Tiket Mudik Gratis Lebaran 2026';

        // Create email log
        $emailLog = EmailLog::logQrCodeEmail(
            $this->registration->form_link_id,
            $this->registration->formLink->email,
            $subject
        );

        try {
            Mail::to($this->registration->formLink->email)
                ->send(new QrCodeMail($this->registration, $qrCodeService));

            // Mark as sent
            $emailLog->markAsSent();

            Log::info('QR code email sent successfully', [
                'registration_id' => $this->registration->id,
                'email' => $this->registration->formLink->email,
            ]);

        } catch (\Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send QR code email', [
                'registration_id' => $this->registration->id,
                'email' => $this->registration->formLink->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('QR code email job failed permanently', [
            'registration_id' => $this->registration->id,
            'email' => $this->registration->formLink->email,
            'error' => $exception->getMessage(),
        ]);
    }
}