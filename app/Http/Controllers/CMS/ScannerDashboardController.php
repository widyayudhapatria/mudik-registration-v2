<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\ScanLog;
use App\Models\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ScannerDashboardController extends Controller
{
    /**
     * Display dashboard page.
     */
    public function index()
    {
        return view('cms.scanner.dashboard');
    }

    /**
     * Get overall statistics.
     */
    public function statistics(): JsonResponse
    {
        $data = Cache::remember('cms.scanner.statistics', 15, function () {
            // Total participants from approved registrations
            $totalParticipants = Registration::approved()->sum('family_count');

            // Scanned participants: sum family_count where registration has a scanned QR
            $scannedParticipants = Registration::whereHas('qrCode', function ($q) {
                $q->whereNotNull('scanned_at');
            })->approved()->sum('family_count');

            $unscannedParticipants = $totalParticipants - $scannedParticipants;
            $completionPercentage = $totalParticipants > 0
                ? round(($scannedParticipants / $totalParticipants) * 100, 2)
                : 0;

            $todayScans = ScanLog::where('scan_result', 'success')
                ->whereDate('scanned_at', today())
                ->count();

            return [
                'total_participants' => $totalParticipants,
                'scanned_participants' => $scannedParticipants,
                'unscanned_participants' => $unscannedParticipants,
                'completion_percentage' => $completionPercentage,
                'today_scans' => $todayScans,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get scan logs with pagination.
     */
    public function scanLogs(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = 15;
        // Cache only page 1 (short TTL) because logs are high-churn
        if ((int)$page === 1) {
            $cacheKey = 'cms.scanner.scan_logs.page.1';
            $cached = Cache::remember($cacheKey, 10, function () use ($perPage) {
                $logs = ScanLog::query()
                    ->where('scan_result', 'success')
                    ->with(['qrCode.registration', 'admin'])
                    ->latest('scanned_at')
                    ->paginate($perPage);

                $formatted = $logs->map(function ($log) {
                    $reg = $log->qrCode?->registration;
                    $destinationName = $reg?->destination?->name ?? null;
                    return [
                        'id' => $log->id,
                        'nama' => $reg?->representative_name ?? 'N/A',
                        'destination' => $destinationName ?? 'N/A',
                        'kk' => $reg?->kk_number ?? 'N/A',
                        'jumlah' => $reg?->family_count ?? 0,
                        'petugas' => $log->admin?->name ?? 'System',
                        'waktu' => $log->scanned_at->format('d M Y H:i'),
                        'status' => 'Berhasil'
                    ];
                });

                return [
                    'success' => true,
                    'data' => $formatted,
                    'pagination' => [
                        'current_page' => $logs->currentPage(),
                        'total' => $logs->total(),
                        'per_page' => $logs->perPage(),
                        'last_page' => $logs->lastPage(),
                        'has_more' => $logs->hasMorePages(),
                    ]
                ];
            });

            return response()->json($cached);
        }

        // Other pages: do not cache (higher latencies acceptable for paged history)
        $logs = ScanLog::query()
            ->where('scan_result', 'success')
            ->with(['qrCode.registration', 'admin'])
            ->latest('scanned_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $formatted = $logs->map(function ($log) {
            $reg = $log->qrCode?->registration;
            $destinationName = $reg?->destination?->name ?? null;
            return [
                'id' => $log->id,
                'nama' => $reg?->representative_name ?? 'N/A',
                'destination' => $destinationName ?? 'N/A',
                'kk' => $reg?->kk_number ?? 'N/A',
                'jumlah' => $reg?->family_count ?? 0,
                'petugas' => $log->admin?->name ?? 'System',
                'waktu' => $log->scanned_at->format('d M Y H:i'),
                'status' => 'Berhasil'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'last_page' => $logs->lastPage(),
                'has_more' => $logs->hasMorePages(),
            ]
        ]);
    }

    /**
     * Get scan breakdown by destination/kota.
     */
    public function scanByDestination(): JsonResponse
    {
        $breakdown = Cache::remember('cms.scanner.scan_by_destination', 30, function () {
            $destinations = Destination::active()
                ->with(['registrations' => function ($query) {
                    $query->approved();
                }])
                ->get();

            return $destinations->map(function ($dest) {
                $approvedRegs = $dest->registrations ?? collect();
                $totalParticipants = $approvedRegs->sum('family_count');

                // Count scanned QRs for this destination
                $scannedParticipants = 0;
                foreach ($approvedRegs as $reg) {
                    $qrCode = $reg->qrCode;
                    if ($qrCode && $qrCode->isScanned()) {
                        $scannedParticipants += $reg->family_count;
                    }
                }

                $percentage = $totalParticipants > 0
                    ? round(($scannedParticipants / $totalParticipants) * 100, 2)
                    : 0;

                return [
                    'id' => $dest->id,
                    'kota' => $dest->name ?? 'N/A',
                    'total_peserta' => $totalParticipants,
                    'sudah_scan' => $scannedParticipants,
                    'belum_scan' => $totalParticipants - $scannedParticipants,
                    'persentase' => $percentage,
                    'registrasi' => $approvedRegs->count(),
                ];
            })->sortByDesc('persentase')->values();
        });

        return response()->json(['success' => true, 'data' => $breakdown]);
    }
}
