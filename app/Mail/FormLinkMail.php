<?php

namespace App\Mail;

use App\Models\FormLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class FormLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $formUrl;
    public string $expiredDate;
    public string $emailDestination;
    public array $mudikConfig;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public FormLink $formLink,
        array $mudikConfig = []
    ) {
        $this->formLink->refresh();
        $this->mudikConfig = $mudikConfig ?: config('mudik');

        Log::info('After dispatching email job', [
            'token' => $this->formLink->token,
            'expired_at' => $this->formLink->expired_at,
        ]);

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

        $this->emailDestination = $this->formLink->email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Link Pendaftaran - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.form-link-v2',
            with: [
                'emailDestination' => $this->emailDestination,
                'formUrl' => $this->formUrl,
                'expiredDate' => $this->expiredDate,
                'mudikConfig' => $this->mudikConfig,
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
