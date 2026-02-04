<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\ScanLog;
use Illuminate\Support\Facades\Broadcast;
use App\Events\ScanPerformed;
use App\Events\StatisticsUpdated;

class BroadcastService
{
    public function broadcastScan(ScanLog $scanLog): void
    {
        if (!$this->isPusherEnabled()) {
            return;
        }

        $channel = config('mudik.pusher.scan_channel', 'scan-monitoring');
        $event = config('mudik.pusher.scan_event', 'ScanPerformed');

        $data = $this->prepareScanData($scanLog);

        broadcast(new ScanPerformed($data))
            ->toOthers();
    }

    protected function prepareScanData(ScanLog $scanLog): array
    {
        $scanLog->load(['qrCode.registration.formLink', 'admin']);

        return [
            'scan_id' => $scanLog->id,
            'qr_code_id' => $scanLog->qr_code_id,
            'admin_name' => $scanLog->admin->name,
            'scan_result' => $scanLog->scan_result,
            'failure_reason' => $scanLog->failure_reason,
            'scanned_at' => $scanLog->scanned_at->toDateTimeString(),
            'registration' => [
                'id' => $scanLog->qrCode->registration->id,
                'representative_name' => $scanLog->qrCode->registration->representative_name,
                'kk_number' => $scanLog->qrCode->registration->kk_number,
                'family_count' => $scanLog->qrCode->registration->family_count,
            ],
        ];
    }

    public function broadcastStatistics(array $stats): void
    {
        if (!$this->isPusherEnabled()) {
            return;
        }

        broadcast(new StatisticsUpdated($stats))
            ->toOthers();
    }

    protected function isPusherEnabled(): bool
    {
        return config('mudik.pusher.enabled', false);
    }

    public function getRealtimeScanCount(): int
    {
        return ScanLog::success()->count();
    }

    public function getRecentScans(int $limit = 10): array
    {
        $scans = ScanLog::with(['qrCode.registration', 'admin'])
            ->latest('scanned_at')
            ->limit($limit)
            ->get();

        return $scans->map(function ($scanLog) {
            return $this->prepareScanData($scanLog);
        })->toArray();
    }
}