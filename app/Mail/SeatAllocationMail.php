<?php

namespace App\Mail;

use App\Models\Registration;
use App\Models\QrCode;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SeatAllocationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Registration $registration;
    public Collection $seatAllocations;

    public QrCode $qrCode;
    public string $qrCodePath = '';

    public array $mudikConfig;

    /**
     * Create a new message instance.
     */
    public function __construct(Registration $registration, Collection $seatAllocations, QrCodeService $qrCodeService, array $mudikConfig = [])
    {
        $this->registration = $registration;
        $this->seatAllocations = $seatAllocations;
        $this->qrCode = $this->registration->qrCode;
        $this->mudikConfig = $mudikConfig ?: config('mudik');

        // Load QR code relationship
        if (!$this->registration->relationLoaded('qrCode')) {
            $this->registration->load('qrCode');
        }

        // Generate/get QR code PNG file
        if ($this->registration->qrCode) {
            $filename = "qr-codes/{$this->registration->qrCode->id}.png";

            // Generate if not exists
            if (!Storage::exists($filename)) {
                $savedPath = $qrCodeService->saveQrCodeAsPng($this->registration->qrCode);
                if (!$savedPath) {
                    throw new \Exception('Failed to generate QR code PNG file');
                }
            }

            // Store full path for embedding
            $this->qrCodePath = Storage::path($filename);

            // Verify file exists
            if (!file_exists($this->qrCodePath)) {
                throw new \Exception("QR code file not found at: {$this->qrCodePath}");
            }
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-Ticket - ' . config('app.name')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.seat-allocation-v2',
            with: [
                'registration' => $this->registration,
                'seatAllocations' => $this->seatAllocations,
                'destination' => $this->registration->destination,
                'email' => $this->registration->formLink->email,
                'qrCodePath' => $this->qrCodePath,
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
