<?php

namespace App\Jobs;

use App\Enums\EmailType;
use App\Models\EmailLog;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRejectionEmail implements ShouldQueue
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
    public function handle(): void
    {
        $subject = 'Pemberitahuan Pendaftaran Mudik Gratis Lebaran 2026';

        // Create email log
        $emailLog = EmailLog::logRejectionEmail(
            $this->registration->form_link_id,
            $this->registration->formLink->email,
            $subject
        );

        try {
            // required data for the email view
            $data = [
                'registration' => $this->registration,
                'rejectionReason' => $this->registration->rejection_reason ?? 'Tidak ada alasan yang diberikan.',
                'websiteUrl' => config('app.url', 'http://localhost:8000'),
            ];

            // Send email
            Mail::send('emails.rejection', $data, function ($message) use ($subject) {
                $message->to($this->registration->formLink->email)
                    ->subject($subject);
            });

            // Mark as sent
            $emailLog->markAsSent();

            Log::info('Rejection email sent successfully', [
                'registration_id' => $this->registration->id,
                'email' => $this->registration->formLink->email,
            ]);

        } catch (\Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send rejection email', [
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
        Log::error('Rejection email job failed permanently', [
            'registration_id' => $this->registration->id,
            'email' => $this->registration->formLink->email,
            'error' => $exception->getMessage(),
        ]);
    }
}