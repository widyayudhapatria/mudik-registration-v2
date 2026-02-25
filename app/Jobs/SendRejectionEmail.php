<?php

namespace App\Jobs;

use App\Mail\RejectionMail;
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
    public $backoff = [60, 300, 600];


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
        $subject = 'Pemberitahuan Penolakan - ' . config('app.name');

        // Create email log
        $emailLog = EmailLog::logRejectionEmail(
            $this->registration->form_link_id,
            $this->registration->formLink->email,
            $subject
        );

        try {
            Mail::to($this->registration->formLink->email)
                ->send(
                    new RejectionMail(
                        $this->registration,
                        config('mudik')
                    )
                );

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
