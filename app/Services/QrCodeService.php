<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

class QrCodeService
{
    public function generateForRegistration(Registration $registration): QrCode
    {
        $token = $this->generateUniqueToken();
        $validFrom = Carbon::parse(config('mudik.schedule.qr_code_valid_from'));
        $validUntil = Carbon::parse(config('mudik.schedule.qr_code_valid_until'));

        // Encode full URL in QR code data
        $qrData = route('scan.entry', ['t' => $token], true);

        $qrCode = QrCode::create([
            'registration_id' => $registration->id,
            'token_qr' => $token,
            'qr_data' => $qrData,
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
        return $this->generateSvgImage($qrCode->qr_data);
    }

    public function saveQrCodeAsPng(QrCode $qrCode): ?string
    {
        $size = config('mudik.qr_code.size', 300);
        $filename = "qr-codes/{$qrCode->token_qr}.png";
        $qrCodesDir = 'qr-codes';

        try {
            Log::info('saveQrCodeAsPng: Starting', [
                'qr_code_id' => $qrCode->id,
                'filename' => $filename,
                'disk_root' => config('filesystems.disks.local.root'),
            ]);

            // create directory if not exists
            if (!Storage::exists($qrCodesDir)) {
                Log::info('saveQrCodeAsPng: Creating directory');
                $created = Storage::makeDirectory($qrCodesDir);
                if (!$created) {
                    throw new \Exception('Failed to create qr-codes directory');
                }
                Log::info('saveQrCodeAsPng: Directory created');
            }

            // Verify directory exists and is writable
            $fullDirPath = storage_path('app/private/qr-codes');
            if (!is_dir($fullDirPath)) {
                throw new \Exception("Directory does not exist: {$fullDirPath}");
            }

            if (!is_writable($fullDirPath)) {
                throw new \Exception("Directory not writable: {$fullDirPath} - Run: sudo chown -R www-data:www-data {$fullDirPath} && sudo chmod -R 755 {$fullDirPath}");
            }

            Log::info('saveQrCodeAsPng: Directory verified', [
                'path' => $fullDirPath,
                'permissions' => substr(sprintf('%o', fileperms($fullDirPath)), -4),
            ]);


            $qr = EndroidQrCode::create($qrCode->qr_data)
                ->setSize($size)
                ->setMargin(10)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

            Log::info('saveQrCodeAsPng: QR object created');

            // Generate PNG
            $writer = new PngWriter();
            $result = $writer->write($qr);

            // Get PNG binary data
            $pngData = $result->getString();

            // Verify it's a valid PNG
            if (substr($pngData, 0, 4) !== "\x89PNG") {
                Log::error('Generated data is not a valid PNG');
                return null;
            }

            // Save to storage
            Storage::put($filename, $pngData);

            // Verify file exists
            if (!Storage::exists($filename)) {
                Log::error('File not saved to storage', ['filename' => $filename]);
                return null;
            }

            $fileSize = Storage::size($filename);
            Log::info('QR code saved as PNG file', [
                'path' => $filename,
                'size' => $fileSize,
                'qr_code_id' => $qrCode->id
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to save QR code as PNG', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'qr_code_id' => $qrCode->id
            ]);
            return null;
        }
    }

    public function generateBase64Image(QrCode $qrCode): string
    {
        // Try to use saved file first
        $filename = "qr-codes/{$qrCode->token_qr}.png";

        if (Storage::exists($filename)) {
            $pngData = Storage::get($filename);
            return 'data:image/png;base64,' . base64_encode($pngData);
        }

        // Try to create and save
        $savedPath = $this->saveQrCodeAsPng($qrCode);

        if ($savedPath && Storage::exists($savedPath)) {
            $pngData = Storage::get($savedPath);
            return 'data:image/png;base64,' . base64_encode($pngData);
        }

        // Direct generation as fallback
        try {
            $size = config('mudik.qr_code.size', 300);

            $qr = EndroidQrCode::create($qrCode->qr_data)
                ->setSize($size)
                ->setMargin(10)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

            $writer = new PngWriter();
            $result = $writer->write($qr);
            $pngData = $result->getString();

            return 'data:image/png;base64,' . base64_encode($pngData);
        } catch (\Exception $e) {
            Log::warning('Could not generate PNG, using SVG', [
                'error' => $e->getMessage()
            ]);
            return $this->generateBase64Svg($qrCode);
        }
    }

    protected function generateSvgImage(string $data): string
    {
        $size = config('mudik.qr_code.size', 300);

        try {
            // Use Endroid for SVG too
            $qr = EndroidQrCode::create($data)
                ->setSize($size)
                ->setMargin(10)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

            $writer = new SvgWriter();
            $result = $writer->write($qr);

            return $result->getString();
        } catch (\Exception $e) {
            Log::error('Failed to generate SVG', ['error' => $e->getMessage()]);
            return '<svg></svg>';
        }
    }

    public function generateBase64Svg(QrCode $qrCode): string
    {
        $svgData = $this->generateSvgImage($qrCode->qr_data);
        return 'data:image/svg+xml;base64,' . base64_encode($svgData);
    }

    public function validateToken(string $token): array
    {
        $qrCode = QrCode::where('token_qr', $token)
            ->with(['registration.formLink', 'registration.participants'])
            ->first();

        if (!$qrCode) {
            return ['valid' => false, 'message' => 'QR Code tidak ditemukan', 'qr_code' => null];
        }

        if (!$qrCode->canBeScanned()) {
            return ['valid' => false, 'message' => $qrCode->getValidationMessage(), 'qr_code' => $qrCode];
        }

        return ['valid' => true, 'message' => 'QR Code valid', 'qr_code' => $qrCode];
    }

    public function scan(string $token, int $adminId): array
    {
        $validation = $this->validateToken($token);
        if (!$validation['valid']) return $validation;

        $qrCode = $validation['qr_code'];
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
