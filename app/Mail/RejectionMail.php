<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $rejectionReason;
    public string $websiteUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Registration $registration
    ) {
        $this->rejectionReason = $this->registration->rejection_reason 
            ?? 'Tidak ada alasan yang diberikan.';
            
        $this->websiteUrl = config('app.url', 'http://localhost:8000');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Pendaftaran Mudik Gratis Lebaran 2026',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.rejection',
            with: [
                'registration' => $this->registration,
                'rejectionReason' => $this->rejectionReason,
                'websiteUrl' => $this->websiteUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}