<?php

namespace App\Actions\Scanner;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\QrCode;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class ValidateQrCodeAction
{
    use AsAction;

    public function handle(string $tokenQr, ?Admin $admin = null): array
    {
        // Find QR code
        $qrCode = QrCode::where('token_qr', $tokenQr)
            ->with([
                'registration.formLink',
                'registration.destination',
                'registration.participants'
            ])
            ->first();

        if (!$qrCode) {
            // Log not-found failure if admin provided
            if ($admin) {
                try {
                    ScanLog::logFailure(
                        null,
                        $admin->id,
                        sprintf('QR not found: %s', $tokenQr),
                        request()->ip(),
                        request()->userAgent()
                    );
                } catch (Throwable $e) {
                    Log::warning('Failed to log QR not-found attempt', ['error' => $e->getMessage()]);
                }
            }
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

            // Log validation failure if admin provided
            if ($admin) {
                try {
                    $now = Carbon::now();
                    $failureReason = match (true) {
                        $validation['has_been_scanned'] => sprintf(
                            'Already scanned at %s by %s',
                            $qrCode->scanned_at?->toDateTimeString() ?? 'unknown',
                            $qrCode->scannedBy?->name ?? 'unknown'
                        ),
                        !$validation['is_within_valid_period'] => $now->isBefore($qrCode->valid_from)
                            ? 'QR Code is not yet valid'
                            : 'QR Code has expired',
                        default => $errorCode->getMessage(),
                    };

                    ScanLog::logFailure(
                        $qrCode->id,
                        $admin->id,
                        $failureReason,
                        request()->ip(),
                        request()->userAgent()
                    );
                } catch (Throwable $e) {
                    Log::warning('Failed to log validation failure', ['error' => $e->getMessage()]);
                }
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
                    'destination_name' => $qrCode->registration->destination?->name,
                ],
                'participants_summary' => [
                    'total' => $qrCode->registration->participants->count(),
                    'children_under_4' => $qrCode->registration->participants->where('is_child_under_4', true)->count(),
                    'adults' => $qrCode->registration->participants->where('is_child_under_4', false)->count(),
                ],
                'email' => $qrCode->registration->formLink->email,
                'participants' => $qrCode->registration->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'full_name' => $participant->full_name,
                        'nik_kia' => $participant->nik_kia,
                        'birth_date' => $participant->birth_date->format('Y-m-d'),
                        'birth_date_js' => $participant->birth_date->toIso8601String(),
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
