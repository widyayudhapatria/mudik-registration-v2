<?php

namespace App\Actions\Scanner;

use App\Data\ScanQrData;
use App\Enums\ErrorCode;
use App\Enums\ScanResult;
use App\Enums\RegistrationStatus;
//use App\Events\ScanPerformed;
use App\Exceptions\MudikException;
use App\Jobs\SendSeatAllocationEmailJob;
use App\Models\Admin;
use App\Models\QrCode;
use App\Models\Registration;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class ConsumeQrCodeAction
{
    use AsAction;

    public function handle(ScanQrData $data, Admin $admin): array
    {
        try {
            DB::beginTransaction();

            // Lock QR code FOR UPDATE
            $qrCode = QrCode::where('token_qr', $data->token_qr)
                ->lockForUpdate()
                ->with([
                    'registration.formLink',
                    'registration.participants',
                    'registration.destination'
                ])
                ->first();

            if (!$qrCode) {
                throw new MudikException(ErrorCode::QrNotFound, null, [], 404);
            }

            // CRITICAL: Lock registration FOR UPDATE
            // Must lock AFTER QR code to prevent deadlock
            $registration = Registration::where('id', $qrCode->registration_id)
                ->lockForUpdate()
                ->first();

            if (!$registration) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Registration tidak ditemukan'
                );
            }

            // Validate QR code - WITH BOTH LOCKS ACTIVE
            $this->validateQrCode($qrCode, $admin);

            //Validate registration status - WITH BOTH LOCKS ACTIVE
            if ($registration->approved_at === null) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    sprintf(
                        'Pendaftaran status adalah %s, harus APPROVED untuk scanning',
                        $registration->status
                    )
                );
            }

            // Mark as scanned
            $qrCode->markAsScanned($admin->id);

            // Log success
            $scanLog = ScanLog::logSuccess(
                $qrCode->id,
                $admin->id,
                request()->ip(),
                request()->userAgent()
            );

            // Allocate seats for participants
            $seatAllocations = AllocateSeatsAction::run(
                $qrCode->registration,
                $scanLog,
                $admin
            );

            // All done, commit transaction - RELEASE BOTH LOCKS
            DB::commit();

            // Send email with seat allocations (outside transaction)
            dispatch(new SendSeatAllocationEmailJob(
                $qrCode->registration,
                $seatAllocations
            ));

            // Invalidate related dashboard caches
            Cache::forget('cms.scanner.statistics');
            Cache::forget('cms.scanner.scan_by_destination');
            Cache::forget('cms.scanner.scan_logs.page.1');

            // broadcasting disabled (no Pusher configured)
            //broadcast(new ScanPerformed($this->prepareScanData($scanLog, $qrCode)));

            Log::info('QR Code scanned successfully', [
                'qr_code_id' => $qrCode->id,
                'admin_id' => $admin->id,
                'registration_id' => $qrCode->registration_id,
                'seats_allocated' => $seatAllocations->filter(fn($s) => !$s->isNoSeat())->count(),
            ]);

            // Prepare response
            return [
                'success' => true,
                'message' => 'QR Code successfully scanned',
                'data' => [
                    'scan_id' => $scanLog->id,
                    'scanned_at' => $qrCode->scanned_at->toISOString(),
                    'scanned_by' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                    ],
                    'registration' => [
                        'id' => $qrCode->registration->id,
                        'representative_name' => $qrCode->registration->representative_name,
                        'representative_nik' => $qrCode->registration->representative_nik,
                        'family_count' => $qrCode->registration->family_count,
                        'kk_number' => $qrCode->registration->kk_number,
                        'has_child_under_4' => $qrCode->registration->has_child_under_4,
                        'destination_name' => $qrCode->registration->destination?->name,
                        'destination_code' => $qrCode->registration->destination?->code,
                    ],
                    'participants_summary' => [
                        'total' => $qrCode->registration->participants->count(),
                        'children_under_4' => $qrCode->registration->participants->where('is_child_under_4', true)->count(),
                        'adults' => $qrCode->registration->participants->where('is_child_under_4', false)->count(),
                    ],
                    'email' => $qrCode->registration->formLink->email,
                    'participants' => $qrCode->registration->participants->map(function ($participant) {
                        return [
                            'full_name' => $participant->full_name,
                            'birth_date' => $participant->birth_date->toIso8601String(),
                            'age' => $participant->getAge(),
                            'is_child_under_4' => $participant->is_child_under_4,
                        ];
                    })->toArray(),
                    'seat_allocations' => $seatAllocations->map(function ($seat) {
                        return [
                            'participant_name' => $seat->participant->full_name,
                            'participant_age' => $seat->participant->getAge(),
                            'seat_code' => $seat->seat_code,
                            'bus_name' => $seat->bus_name,       
                            'seat_label' => $seat->seat_label,   
                            'is_no_seat' => $seat->isNoSeat(),
                        ];
                    })->toArray(),
                    'warnings' => $qrCode->registration->has_child_under_4
                        ? ['Anak dibawah 4 tahun wajib dipangku selama perjalanan']
                        : [],
                ],
            ];
        } catch (MudikException $e) {
            DB::rollBack();
            // Persist failed scan log AFTER rollback so it isn't rolled back.
            try {
                // Prefer a detailed failure reason when available
                $failureReason = $e->getMessage();
                try {
                    $code = $e->getErrorCode();
                    $additional = $e->getAdditionalData();

                    if (isset($qrCode) && $qrCode && $code === ErrorCode::QrAlreadyScanned) {
                        $scannedAt = $qrCode->scanned_at?->toDateTimeString() ?? ($additional['scanned_at'] ?? 'unknown');
                        $scannedBy = $qrCode->scannedBy?->name ?? ($additional['scanned_by'] ?? 'unknown');
                        $failureReason = sprintf('Already scanned at %s by %s', $scannedAt, $scannedBy);
                    } elseif ($code === ErrorCode::QrInvalidDate) {
                        $failureReason = $additional['reason'] ?? $failureReason;
                    } elseif ($code === ErrorCode::QrNotFound) {
                        $failureReason = sprintf('QR not found: %s', $data->token_qr);
                    }
                } catch (Throwable $_ignore) {
                    // fallback to exception message
                }

                $qrCodeId = isset($qrCode) && $qrCode ? $qrCode->id : null;

                ScanLog::logFailure(
                    $qrCodeId,
                    $admin->id,
                    $failureReason,
                    request()->ip(),
                    request()->userAgent()
                );
            } catch (Throwable $logEx) {
                Log::warning('Failed to persist scan failure log', [
                    'error' => $logEx->getMessage(),
                    'original_error' => $e->getMessage(),
                ]);
            }

            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('QR Code scan failed', [
                'token_qr' => $data->token_qr,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new MudikException(ErrorCode::ScanFailed);
        }
    }

    protected function validateQrCode(QrCode $qrCode, Admin $admin): void
    {
        // Check if already scanned
        if ($qrCode->isScanned()) {
            throw new MudikException(
                ErrorCode::QrAlreadyScanned,
                null,
                [
                    'scanned_at' => $qrCode->scanned_at->toISOString(),
                    'scanned_by' => $qrCode->scannedBy?->name,
                ],
                422
            );
        }

        // Check valid date
        $now = Carbon::now();
        if (!$now->between($qrCode->valid_from, $qrCode->valid_until)) {
            $reason = $now->isBefore($qrCode->valid_from)
                ? 'QR Code is not yet valid'
                : 'QR Code has expired';

            throw new MudikException(
                ErrorCode::QrInvalidDate,
                null,
                [
                    'valid_from' => $qrCode->valid_from->toISOString(),
                    'valid_until' => $qrCode->valid_until->toISOString(),
                    'current_time' => $now->toISOString(),
                    'reason' => $reason,
                ],
                422
            );
        }
    }

    protected function logFailedScan(?QrCode $qrCode, Admin $admin, string $reason): void
    {
        if ($qrCode) {
            ScanLog::logFailure(
                $qrCode->id,
                $admin->id,
                $reason,
                request()->ip(),
                request()->userAgent()
            );
        }
    }

    protected function prepareScanData(ScanLog $scanLog, QrCode $qrCode): array
    {
        return [
            'scan_id' => $scanLog->id,
            'qr_code_id' => $qrCode->id,
            'admin_name' => $scanLog->admin->name,
            'scan_result' => ScanResult::Success->value,
            'scanned_at' => $scanLog->scanned_at->toDateTimeString(),
            'registration' => [
                'id' => $qrCode->registration->id,
                'representative_name' => $qrCode->registration->representative_name,
                'kk_number' => $qrCode->registration->kk_number,
                'family_count' => $qrCode->registration->family_count,
            ],
        ];
    }
}
