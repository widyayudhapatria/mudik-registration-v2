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

    public function __construct(
        public Registration $registration,
        QrCodeService $qrCodeService
    ) {
        // Validasi QR code exists 
        if (!$this->registration->qrCode) {
            throw new \Exception('QR code tidak ditemukan untuk registrasi ini');
        }

        $qrCode = $this->registration->qrCode;
        
        $this->qrCodeImage = $qrCodeService->generateBase64Image($qrCode);
        
        $this->validDate = $qrCode->valid_from
            ->locale('id')
            ->isoFormat('dddd, D MMMM YYYY');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'QR Code Tiket Mudik Gratis Lebaran 2026',
        );
    }

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

    public function attachments(): array
    {
        return [];
    }
}