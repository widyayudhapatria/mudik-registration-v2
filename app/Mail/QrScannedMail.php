<?php

namespace App\Mail;

use App\Models\QrCode;
use App\Models\Registration;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class QrScannedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $mudikConfig;

    public function __construct(
        public Registration $registration,
        public QrCode $qrCode,
        array $mudikConfig = []
    ) {
        $this->mudikConfig = $mudikConfig ?: config('mudik');
    }

    public function build(): static
    {
        $qrCodeService = app(QrCodeService::class);

        $filename = "qr-codes/{$this->qrCode->id}.png";

        if (!Storage::exists($filename)) {
            $savedPath = $qrCodeService->saveQrCodeAsPng($this->qrCode);
            if (!$savedPath) {
                throw new \Exception('Failed to generate QR code PNG file');
            }
        }

        $qrCodePath = Storage::path($filename);

        if (!file_exists($qrCodePath)) {
            throw new \Exception("QR code file not found at: {$qrCodePath}");
        }

        return $this->subject('Tiket Dikonfirmasi - Siap Berangkat! | ' . config('app.name'))
            ->view('emails.qr-scanned')
            ->with([
                'registration' => $this->registration,
                'qrCode'       => $this->qrCode,
                'qrCodePath'   => $qrCodePath,
                'mudikConfig'  => $this->mudikConfig,
            ]);
    }
}