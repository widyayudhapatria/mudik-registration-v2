<?php

namespace App\Jobs;

use App\Mail\RegistrationSubmittedMail;
use App\Models\EmailLog;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRegistrationSubmittedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public $backoff = [60, 300, 600];

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new RateLimited('email-queue')];
    }

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
        $this->registration->loadMissing('formLink');

        $email   = $this->registration->formLink->email;
        $subject = 'Pendaftaran Berhasil Disubmit - ' . config('app.name');

        $emailLog = EmailLog::logFormLinkEmail(
            $this->registration->form_link_id,
            $email,
            $subject
        );

        try {
            Mail::to($email)->send(
                new RegistrationSubmittedMail(
                    $this->registration,
                    config('mudik')
                )
            );

            $emailLog->markAsSent();

            Log::info('Registration submitted email sent successfully', [
                'registration_id' => $this->registration->id,
                'email'           => $email,
            ]);
        } catch (Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send registration submitted email', [
                'registration_id' => $this->registration->id,
                'email'           => $email,
                'error'           => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Registration submitted email job failed permanently', [
            'registration_id' => $this->registration->id,
            'error'           => $exception->getMessage(),
        ]);
    }
}
