<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeFacade;

class QrCodeService
{
    public function generateForRegistration(Registration $registration): QrCode
    {
        // Generate unique token
        $token = $this->generateUniqueToken();

        // Set validity period (H day from approval)
        $validFrom = Carbon::now()->startOfDay();
        $validUntil = Carbon::now()->endOfDay();

        // Create QR code record
        $qrCode = QrCode::create([
            'registration_id' => $registration->id,
            'token_qr' => $token,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
        ]);

        return $qrCode;
    }

    protected function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (QrCode::where('token_qr', $token)->exists());

        return $token;
    }

    public function generateImage(QrCode $qrCode): string
    {
        $size = config('mudik.qr_code.size', 300);
        $margin = config('mudik.qr_code.margin', 2);
        $format = config('mudik.qr_code.format', 'png');

        // Generate QR code with token
        $qrCodeImage = QrCodeFacade::size($size)
            ->margin($margin)
            ->format($format)
            ->generate($qrCode->token_qr);

        return $qrCodeImage;
    }

    public function generateBase64Image(QrCode $qrCode): string
    {
        $size = config('mudik.qr_code.size', 300);
        $margin = config('mudik.qr_code.margin', 2);

        // Generate QR code with token as base64
        $qrCodeImage = QrCodeFacade::size($size)
            ->margin($margin)
            ->format('png')
            ->generate($qrCode->token_qr);

        return 'data:image/png;base64,' . base64_encode($qrCodeImage);
    }

    public function validateToken(string $token): array
    {
        $qrCode = QrCode::where('token_qr', $token)
            ->with(['registration.formLink', 'registration.participants'])
            ->first();

        if (!$qrCode) {
            return [
                'valid' => false,
                'message' => 'QR Code tidak ditemukan',
                'qr_code' => null,
            ];
        }

        if (!$qrCode->canBeScanned()) {
            return [
                'valid' => false,
                'message' => $qrCode->getValidationMessage(),
                'qr_code' => $qrCode,
            ];
        }

        return [
            'valid' => true,
            'message' => 'QR Code valid',
            'qr_code' => $qrCode,
        ];
    }

    public function scan(string $token, int $adminId): array
    {
        $validation = $this->validateToken($token);

        if (!$validation['valid']) {
            return $validation;
        }

        $qrCode = $validation['qr_code'];

        // Mark as scanned
        $qrCode->markAsScanned($adminId);

        return [
            'success' => true,
            'message' => 'QR Code berhasil di-scan',
            'qr_code' => $qrCode->fresh(['registration.formLink', 'registration.participants']),
        ];
    }

    public function getStatistics(): array
    {
        return [
            'total_generated' => QrCode::count(),
            'total_scanned' => QrCode::scanned()->count(),
            'total_unscanned' => QrCode::unscanned()->count(),
            'total_valid' => QrCode::valid()->count(),
            'total_expired' => QrCode::expired()->count(),
        ];
    }
}