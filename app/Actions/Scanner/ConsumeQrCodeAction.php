<?php

namespace App\Actions\Scanner;

use App\Data\ScanQrData;
use App\Enums\ErrorCode;
use App\Enums\ScanResult;
//use App\Events\ScanPerformed;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\QrCode;
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
                    'registration.participants'
                ])
                ->first();

            if (!$qrCode) {
                $this->logFailedScan(null, $admin, 'QR Code not found');
                throw new MudikException(ErrorCode::QrNotFound, null, [], 404);
            }

            // Validate token
            $this->validateQrCode($qrCode, $admin);

            // Mark as scanned
            $qrCode->markAsScanned($admin->id);

            // Log success
            $scanLog = ScanLog::logSuccess(
                $qrCode->id,
                $admin->id,
                request()->ip(),
                request()->userAgent()
            );

            DB::commit();

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
                    ],
                    'email' => $qrCode->registration->formLink->email,
                    'participants' => $qrCode->registration->participants->map(function ($participant) {
                        return [
                            'full_name' => $participant->full_name,
                            'age' => $participant->getAge(),
                            'is_child_under_4' => $participant->is_child_under_4,
                        ];
                    })->toArray(),
                    'warnings' => $qrCode->registration->has_child_under_4
                        ? ['Anak dibawah 4 tahun wajib dipangku selama perjalanan']
                        : [],
                ],
            ];
        } catch (MudikException $e) {
            DB::rollBack();
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
            $this->logFailedScan($qrCode, $admin, 'QR Code already scanned');
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

            $this->logFailedScan($qrCode, $admin, $reason);

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
