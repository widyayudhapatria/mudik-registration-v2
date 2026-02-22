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

        Log::info('FormLinkMail constructor started', [
            'token' => $this->formLink->token,
            'expired_at' => $this->formLink->expired_at,
            'app_url' => config('app.url'),
        ]);

        // CRITICAL: Force HTTPS scheme if APP_URL uses HTTPS
        // This ensures signed URL signature matches during validation
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            Log::info('Forced HTTPS scheme for URL generation');
        }

        // Handle expiry time with proper fallback
        $expiryTime = $this->formLink->expired_at;
        $needsDbUpdate = false;

        // Get config expiry days (same as SubmitEmailAction)
        $expiryDays = config('mudik.form_link_expiry_days', 3);

        // Case 1: expired_at is null
        if (!$expiryTime) {
            $expiryTime = now()->addDays($expiryDays);
            $needsDbUpdate = true;
            Log::warning('FormLink expired_at is NULL, using config fallback', [
                'token' => $this->formLink->token,
                'fallback_days' => $expiryDays,
                'fallback_expiry' => $expiryTime->toDateTimeString(),
            ]);
        }

        // Case 2: expired_at is in the past
        if ($expiryTime->isPast()) {
            $oldExpiry = $expiryTime->toDateTimeString();
            $expiryTime = now()->addDays($expiryDays);
            $needsDbUpdate = true;
            Log::warning('FormLink expired_at is in the PAST, using config fallback', [
                'token' => $this->formLink->token,
                'fallback_days' => $expiryDays,
                'old_expiry' => $oldExpiry,
                'new_expiry' => $expiryTime->toDateTimeString(),
            ]);
        }

        // CRITICAL: Update database if fallback was used
        if ($needsDbUpdate) {
            $this->formLink->expired_at = $expiryTime;
            $this->formLink->save();
            Log::info('Updated FormLink expired_at in database', [
                'token' => $this->formLink->token,
                'new_expired_at' => $expiryTime->toDateTimeString(),
            ]);
        }

        // Generate signed URL with proper expiry
        $this->formUrl = URL::temporarySignedRoute(
            'public.registration.form',
            $expiryTime,
            ['token' => $this->formLink->token]
        );

        // Unescape URL (for HTML entities in query params)
        $this->formUrl = html_entity_decode($this->formUrl);

        Log::info('Generated signed URL successfully', [
            'token' => $this->formLink->token,
            'url_scheme' => parse_url($this->formUrl, PHP_URL_SCHEME),
            'url_length' => strlen($this->formUrl),
            'expires_at' => $expiryTime->toDateTimeString(),
        ]);

        // Format expired date for email display
        $this->expiredDate = $expiryTime
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
