<?php

namespace App\Mail;

use App\Models\Registration;
use App\Models\QrCode;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $validDate;
    public Registration $registration;
    public QrCode $qrCode;
    public string $qrCodePath;

    public function __construct(
        Registration $registration,
        QrCodeService $qrCodeService
    ) {
        if (!$registration->qrCode) {
            throw new \Exception('QR code tidak ditemukan untuk registrasi ini');
        }

        $this->registration = $registration;
        $this->qrCode = $this->registration->qrCode;
        
        // Generate/get PNG file path
        $filename = "qr-codes/{$this->qrCode->id}.png";
        
        // Generate if not exists
        if (!Storage::exists($filename)) {
            $savedPath = $qrCodeService->saveQrCodeAsPng($this->qrCode);
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
        
        // Format valid date
        $this->validDate = $this->qrCode->valid_from
            ->locale('id')
            ->isoFormat('dddd, D MMMM YYYY');
    }

    public function build()
    {
        return $this->subject('QR Code Tiket Mudik Gratis Lebaran 2026')
            ->view('emails.qr-code')
            ->with([
                'registration' => $this->registration,
                'qrCode' => $this->qrCode,
                'validDate' => $this->validDate,
                'qrCodePath' => $this->qrCodePath,
            ]);
    }
}