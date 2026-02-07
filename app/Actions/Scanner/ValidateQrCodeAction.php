<?php

namespace App\Actions\Scanner;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\QrCode;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ValidateQrCodeAction
{
    use AsAction;

    public function handle(string $tokenQr): array
    {
        // Find QR code
        $qrCode = QrCode::where('token_qr', $tokenQr)
            ->with([
                'registration.formLink',
                'registration.participants'
            ])
            ->first();

        if (!$qrCode) {
            throw new MudikException(
                ErrorCode::QrNotFound,
                null,
                [],
                404
            );
        }

        // Get validation status
        $validation = $this->getValidationStatus($qrCode);

        // If not valid, throw appropriate error
        if (!$validation['is_valid']) {
            $errorCode = match (true) {
                $validation['has_been_scanned'] => ErrorCode::QrAlreadyScanned,
                !$validation['is_within_valid_period'] => ErrorCode::QrInvalidDate,
                default => ErrorCode::InvalidQR,
            };

            $additionalData = [
                'valid_from' => $qrCode->valid_from->toISOString(),
                'valid_until' => $qrCode->valid_until->toISOString(),
                'current_time' => Carbon::now()->toISOString(),
            ];

            if ($qrCode->scanned_at) {
                $additionalData['scanned_at'] = $qrCode->scanned_at->toISOString();
                $additionalData['scanned_by'] = $qrCode->scannedBy?->name;
            }

            throw new MudikException(
                $errorCode,
                null,
                $additionalData,
                422
            );
        }

        // Return success with data
        return [
            'success' => true,
            'message' => 'QR Code valid',
            'data' => [
                'qr_code' => [
                    'id' => $qrCode->id,
                    'token_qr' => $qrCode->token_qr,
                    'valid_from' => $qrCode->valid_from->toISOString(),
                    'valid_until' => $qrCode->valid_until->toISOString(),
                    'is_scanned' => $qrCode->isScanned(),
                    'scanned_at' => $qrCode->scanned_at?->toISOString(),
                ],
                'registration' => [
                    'id' => $qrCode->registration->id,
                    'representative_name' => $qrCode->registration->representative_name,
                    'representative_nik' => $qrCode->registration->representative_nik,
                    'family_count' => $qrCode->registration->family_count,
                    'kk_number' => $qrCode->registration->kk_number,
                    'has_child_under_4' => $qrCode->registration->has_child_under_4,
                ],
                'email' => $qrCode->registration->formLink->email,
                'participants' => $qrCode->registration->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'full_name' => $participant->full_name,
                        'nik_kia' => $participant->nik_kia,
                        'birth_date' => $participant->birth_date->format('Y-m-d'),
                        'age' => $participant->getAge(),
                        'is_child_under_4' => $participant->is_child_under_4,
                    ];
                })->toArray(),
                'validation' => $validation,
            ],
        ];
    }

    protected function getValidationStatus(QrCode $qrCode): array
    {
        $now = Carbon::now();
        $isWithinValidPeriod = $now->between($qrCode->valid_from, $qrCode->valid_until);
        $hasBeenScanned = $qrCode->isScanned();
        $isToday = $now->isSameDay($qrCode->valid_from);

        return [
            'is_valid' => $isWithinValidPeriod && !$hasBeenScanned,
            'is_today' => $isToday,
            'is_within_valid_period' => $isWithinValidPeriod,
            'has_been_scanned' => $hasBeenScanned,
        ];
    }
}