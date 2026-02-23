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
        // get signed url from database
        $this->formUrl = $this->formLink->generated_link;

        // Format expired date for email display
        $this->expiredDate = $this->formLink->expired_at
            ->locale('id')
            ->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm [WIB]');

        $this->emailDestination = $this->formLink->email;

        Log::info('FormLinkMail initialized with signed URL from database', [
            'form_link_id' => $this->formLink->id,
            'token' => $this->formLink->token,
            'expires_at' => $this->formLink->expired_at->toDateTimeString(),
        ]);
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
