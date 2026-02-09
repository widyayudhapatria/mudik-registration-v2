<?php

namespace App\Mail;

use App\Models\FormLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class FormLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $formUrl;
    public string $expiredDate;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public FormLink $formLink
    ) {
        // Generate signed URL
        $this->formUrl = URL::temporarySignedRoute(
            'public.registration.form',
            $this->formLink->expired_at,
            ['token' => $this->formLink->token]
        );

        // Unescape URL
        $this->formUrl = html_entity_decode($this->formUrl);

        // Format expired date
        $this->expiredDate = $this->formLink->expired_at
            ->locale('id')
            ->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm [WIB]');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Link Pendaftaran Mudik Gratis Lebaran 2026',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.form-link',
            with: [
                'formLink' => $this->formLink,
                'formUrl' => $this->formUrl,
                'expiredDate' => $this->expiredDate,
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