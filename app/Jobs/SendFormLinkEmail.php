<?php

namespace App\Jobs;

use App\Enums\EmailType;
use App\Models\EmailLog;
use App\Models\FormLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendFormLinkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FormLink $formLink
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subject = 'Link Pendaftaran Mudik Gratis Lebaran 2026';

        // Create email log
        $emailLog = EmailLog::logFormLinkEmail(
            $this->formLink->id,
            $this->formLink->email,
            $subject
        );

        try {
            // Generate signed URL
            $formUrl = URL::temporarySignedRoute(
                'public.registration.form',
                $this->formLink->expired_at,
                ['token' => $this->formLink->token]
            );

            // Fortmat expired date
            $expiredDate = $this->formLink->expired_at->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm [WIB]');

            // Make sure URL not escaped in email view
            $formUrlUnescaped = html_entity_decode($formUrl);

            // Send email
            Mail::send('emails.form-link', [
                'formLink' => $this->formLink,
                'formUrl' => $formUrlUnescaped,
                'expiredDate' => $expiredDate,
            ], function ($message) use ($subject) {
                $message->to($this->formLink->email)
                    ->subject($subject);
            });

            // Mark as sent
            $emailLog->markAsSent();

            Log::info('Form link email sent successfully', [
                'form_link_id' => $this->formLink->id,
                'email' => $this->formLink->email,
            ]);

        } catch (\Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send form link email', [
                'form_link_id' => $this->formLink->id,
                'email' => $this->formLink->email,
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
        Log::error('Form link email job failed permanently', [
            'form_link_id' => $this->formLink->id,
            'email' => $this->formLink->email,
            'error' => $exception->getMessage(),
        ]);
    }
}