<?php

namespace App\Mail;

use App\Models\Registration;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $qrCodeImage;
    public string $validDate;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Registration $registration,
        QrCodeService $qrCodeService
    ) {
        $qrCode = $this->registration->qrCode;
        
        // Generate QR code image
        $this->qrCodeImage = $qrCodeService->generateBase64Image($qrCode);
        
        // Format valid date
        $this->validDate = $qrCode->valid_from
            ->locale('id')
            ->isoFormat('dddd, D MMMM YYYY');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'QR Code Tiket Mudik Gratis Lebaran 2026',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.qr-code',
            with: [
                'registration' => $this->registration,
                'qrCode' => $this->registration->qrCode,
                'qrCodeImage' => $this->qrCodeImage,
                'validDate' => $this->validDate,
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