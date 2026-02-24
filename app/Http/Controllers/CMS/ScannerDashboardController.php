<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\ScanLog;
use App\Models\Destination;
use App\Models\Participant;
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
            $totalParticipants = Registration::approved()->withCount('participants')->get()->sum('participants_count');
            $totalParticipantsApprovedAdult = Participant::whereHas('registration', function ($q) {
                $q->approved();
            })->where('is_child_under_4', false)->count();
            $totalParticipantsApprovedChild = Participant::whereHas('registration', function ($q) {
                $q->approved();
            })->where('is_child_under_4', true)->count();

            // Scanned participants: sum participants_count where registration has a scanned QR
            // --- adult
            $scannedParticipantsAdult = Participant::whereHas('registration', function ($q) {
                $q->approved()->whereHas('qrCode', function ($q2) {
                    $q2->whereNotNull('scanned_at');
                });
            })->where('is_child_under_4', false)->count();
            // --- child
            $scannedParticipantsChild = Participant::whereHas('registration', function ($q) {
                $q->approved()->whereHas('qrCode', function ($q2) {
                    $q2->whereNotNull('scanned_at');
                });
            })->where('is_child_under_4', true)->count();
            // --- total
            $scannedParticipants = $scannedParticipantsAdult + $scannedParticipantsChild;

            // Unscanned breakdown by adult / child (use approved participant totals)
            $unscannedParticipantsAdult = max(0, $totalParticipantsApprovedAdult - $scannedParticipantsAdult);
            $unscannedParticipantsChild = max(0, $totalParticipantsApprovedChild - $scannedParticipantsChild);
            $unscannedParticipants = $unscannedParticipantsAdult + $unscannedParticipantsChild;

            $completionPercentage = $totalParticipants > 0
                ? round(($scannedParticipants / $totalParticipants) * 100, 2)
                : 0;

            // $scannedParticipants = Registration::whereHas('qrCode', function ($q) {
            //     $q->whereNotNull('scanned_at');
            // })->approved()->withCount('participants')->get()->sum('participants_count');
            // $unscannedParticipants = $totalParticipants - $scannedParticipants;
            // $completionPercentage = $totalParticipants > 0
            //     ? round(($scannedParticipants / $totalParticipants) * 100, 2)
            //     : 0;



            $todayScans = ScanLog::where('scan_result', 'success')
                ->whereDate('scanned_at', today())
                ->count();

            return [
                // total participant all age
                'total_participants' => $totalParticipants,
                'total_participants_adult' => $totalParticipantsApprovedAdult,
                'total_participants_child' => $totalParticipantsApprovedChild,
                // scanned participant all age
                'scanned_participants' => $scannedParticipants,
                'scanned_participants_adult' => $scannedParticipantsAdult,
                'scanned_participants_child' => $scannedParticipantsChild,
                //unscanned participant all age
                'unscanned_participants' => $unscannedParticipants,
                'unscanned_participants_adult' => $unscannedParticipantsAdult,
                'unscanned_participants_child' => $unscannedParticipantsChild,
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
                    ->whereIn('scan_result', ['success', 'failed'])
                    ->with(['qrCode.registration', 'admin'])
                    ->latest('scanned_at')
                    ->paginate($perPage);

                $formatted = $logs->map(function ($log) {
                    $reg = $log->qrCode?->registration;
                    $destinationName = $reg?->destination?->name ?? null;
                    return [
                        'id' => $log->id,
                        'nama' => $reg?->representative_name ?? ($log->token_scanned ?? 'N/A'),
                        'destination' => $destinationName ?? 'N/A',
                        'kk' => $reg?->kk_number ?? 'N/A',
                        'jumlah' => $reg?->family_count ?? 0,
                        'petugas' => $log->admin?->name ?? 'System',
                        'waktu' => $log->scanned_at?->format('d M Y H:i') ?? '-',
                        'status' => $log->isFailed() ? 'Gagal' : 'Berhasil',
                        'failure_reason' => $log->isFailed() ? $log->failure_reason : null,
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
            ->whereIn('scan_result', ['success', 'failed'])
            ->with(['qrCode.registration', 'admin'])
            ->latest('scanned_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $formatted = $logs->map(function ($log) {
            $reg = $log->qrCode?->registration;
            $destinationName = $reg?->destination?->name ?? null;
            // family count should be from participants count
            $jumlah = $reg?->participants?->count() ?? 0;

            return [
                'id' => $log->id,
                'nama' => $reg?->representative_name ?? ($log->token_scanned ?? 'N/A'),
                'destination' => $destinationName ?? 'N/A',
                'kk' => $reg?->kk_number ?? 'N/A',
                'jumlah' => $jumlah,
                'petugas' => $log->admin?->name ?? 'System',
                'waktu' => $log->scanned_at?->format('d M Y H:i') ?? '-',
                'status' => $log->isFailed() ? 'Gagal' : 'Berhasil',
                'failure_reason' => $log->isFailed() ? $log->failure_reason : null,
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
                    $query->approved()->with(['participants', 'qrCode']);
                }])
                ->get();

            return $destinations->map(function ($dest) {
                $approvedRegs = $dest->registrations ?? collect();

                $totalParticipantsAdult = 0;
                $totalParticipantsChild = 0;
                $scannedParticipantsAdult = 0;
                $scannedParticipantsChild = 0;

                foreach ($approvedRegs as $reg) {
                    $participants = $reg->participants ?? collect();
                    $adultCount = $participants->where('is_child_under_4', false)->count();
                    $childCount = $participants->where('is_child_under_4', true)->count();

                    $totalParticipantsAdult += $adultCount;
                    $totalParticipantsChild += $childCount;

                    $qrCode = $reg->qrCode;
                    if ($qrCode && $qrCode->isScanned()) {
                        $scannedParticipantsAdult += $adultCount;
                        $scannedParticipantsChild += $childCount;
                    }
                }

                $totalParticipants = $totalParticipantsAdult + $totalParticipantsChild;
                $scannedParticipants = $scannedParticipantsAdult + $scannedParticipantsChild;
                $unscannedParticipantsAdult = max(0, $totalParticipantsAdult - $scannedParticipantsAdult);
                $unscannedParticipantsChild = max(0, $totalParticipantsChild - $scannedParticipantsChild);
                $unscannedParticipants = $unscannedParticipantsAdult + $unscannedParticipantsChild;

                $percentage = $totalParticipants > 0
                    ? round(($scannedParticipants / $totalParticipants) * 100, 2)
                    : 0;

                return [
                    'id' => $dest->id,
                    'kota' => $dest->name ?? 'N/A',
                    'total_quota' => $dest->total_quota ?? 0,
                    // totals split by adult / child
                    'total_peserta' => $totalParticipants,
                    'total_peserta_adult' => $totalParticipantsAdult,
                    'total_peserta_child' => $totalParticipantsChild,
                    // scanned split by adult / child
                    'sudah_scan' => $scannedParticipants,
                    'sudah_scan_adult' => $scannedParticipantsAdult,
                    'sudah_scan_child' => $scannedParticipantsChild,
                    // unscanned split by adult / child
                    'belum_scan' => $unscannedParticipants,
                    'belum_scan_adult' => $unscannedParticipantsAdult,
                    'belum_scan_child' => $unscannedParticipantsChild,
                    'persentase' => $percentage,
                    'registrasi' => $approvedRegs->count(),
                ];
            })->sortByDesc('persentase')->values();
        });

        return response()->json(['success' => true, 'data' => $breakdown]);
    }
}
