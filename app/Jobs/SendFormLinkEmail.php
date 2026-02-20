<?php

namespace App\Jobs;

use App\Mail\FormLinkMail;
use App\Models\EmailLog;
use App\Models\FormLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $subject = 'Link Pendaftaran - ' . config('app.name');

        // Create email log
        $emailLog = EmailLog::logFormLinkEmail(
            $this->formLink->id,
            $this->formLink->email,
            $subject
        );

        try {
            Mail::to($this->formLink->email)
                ->send(
                    new FormLinkMail(
                        $this->formLink,
                        config('mudik')
                    )
                );

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
