<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\FormLink;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Exception;

class EmailService
{
    public function sendFormLinkEmail(FormLink $formLink): bool
    {
        try {
            $subject = 'Link Pendaftaran Mudik Gratis Lebaran 2026';
            
            // Create email log
            $emailLog = EmailLog::logFormLinkEmail(
                $formLink->id,
                $formLink->email,
                $subject
            );

            // Generate signed URL
            $formUrl = $this->generateFormUrl($formLink);

            // Send email (implement mail class separately)
            // Mail::to($formLink->email)->send(new FormLinkMail($formLink, $formUrl));

            // Mark as sent
            $emailLog->markAsSent();

            return true;
        } catch (Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            
            return false;
        }
    }

    public function sendQrCodeEmail(Registration $registration): bool
    {
        try {
            $subject = 'QR Code Tiket Mudik Gratis Lebaran 2026';
            
            // Create email log
            $emailLog = EmailLog::logQrCodeEmail(
                $registration->form_link_id,
                $registration->formLink->email,
                $subject
            );

            // Send email with QR code (implement mail class separately)
            // Mail::to($registration->formLink->email)->send(new QrCodeMail($registration));

            // Mark as sent
            $emailLog->markAsSent();

            return true;
        } catch (Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            
            return false;
        }
    }

    public function sendRejectionEmail(Registration $registration, string $reason): bool
    {
        try {
            $subject = 'Pemberitahuan Pendaftaran Mudik Gratis Lebaran 2026';
            
            // Create email log
            $emailLog = EmailLog::logRejectionEmail(
                $registration->form_link_id,
                $registration->formLink->email,
                $subject
            );

            // Send email (implement mail class separately)
            // Mail::to($registration->formLink->email)->send(new RejectionMail($registration, $reason));

            // Mark as sent
            $emailLog->markAsSent();

            return true;
        } catch (Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            
            return false;
        }
    }

    protected function generateFormUrl(FormLink $formLink): string
    {
        return URL::temporarySignedRoute(
            'registration.form',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );
    }

    public function retryFailedEmails(): int
    {
        $maxRetries = config('mudik.email.max_retry', 3);
        $failedEmails = EmailLog::canRetry($maxRetries)->get();
        
        $retried = 0;

        foreach ($failedEmails as $emailLog) {
            $emailLog->incrementRetry();

            // Retry based on email type
            $success = match ($emailLog->email_type) {
                'form_link' => $this->retryFormLinkEmail($emailLog),
                'qr_code' => $this->retryQrCodeEmail($emailLog),
                'rejection' => $this->retryRejectionEmail($emailLog),
                default => false,
            };

            if ($success) {
                $retried++;
            }
        }

        return $retried;
    }

    protected function retryFormLinkEmail(EmailLog $emailLog): bool
    {
        if (!$emailLog->formLink) {
            return false;
        }

        return $this->sendFormLinkEmail($emailLog->formLink);
    }

    protected function retryQrCodeEmail(EmailLog $emailLog): bool
    {
        if (!$emailLog->formLink || !$emailLog->formLink->registration) {
            return false;
        }

        return $this->sendQrCodeEmail($emailLog->formLink->registration);
    }

    protected function retryRejectionEmail(EmailLog $emailLog): bool
    {
        if (!$emailLog->formLink || !$emailLog->formLink->registration) {
            return false;
        }

        $reason = $emailLog->formLink->registration->rejection_reason ?? 'Tidak memenuhi syarat';
        
        return $this->sendRejectionEmail($emailLog->formLink->registration, $reason);
    }
}