<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $representativeName;
    public string $emailDestination;
    public array $mudikConfig;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Registration $registration,
        array $mudikConfig = []
    ) {
        $this->registration->loadMissing('formLink');
        $this->mudikConfig = $mudikConfig ?: config('mudik');

        $this->representativeName = $this->registration->representative_name;
        $this->emailDestination   = $this->registration->formLink->email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Berhasil Disubmit - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-submitted',
            with: [
                'representativeName' => $this->representativeName,
                'emailDestination'   => $this->emailDestination,
                'mudikConfig'        => $this->mudikConfig,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}