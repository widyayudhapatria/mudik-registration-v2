<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Jobs\SendFormLinkEmail;
use App\Models\EmailLog;
use App\Models\FormLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmailRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request): View|JsonResponse
    {
        abort_unless(auth('admin')->user()->isSuperAdmin(), 403);

        $query = FormLink::with(['registration', 'emailLogs' => function ($query) {
            $query->where('email_type', 'form_link')->latest();
        }]);

        $this->applyFilters($query, $request);

        $emailRequests = $query->latest('created_at')->paginate(25)->withQueryString();


        if ($request->wantsJson()) {
            return response()->json($emailRequests);
        }

        return view('cms.email-requests.index', [
            'emailRequests' => $emailRequests,
        ]);
    }

    public function show(FormLink $formLink): View|JsonResponse
    {
        abort_unless(auth('admin')->user()->isSuperAdmin(), 403);

        $formLink->load(['registration.participants', 'registration.approvedBy', 'registration.rejectedBy']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $formLink,
            ]);
        }

        return view('cms.email-requests.show', [
            'formLink' => $formLink,
        ]);
    }

    public function resend(FormLink $formLink): JsonResponse
    {
        abort_unless(auth('admin')->user()->isSuperAdmin(), 403);

        try {
            // Get the latest failed form_link email log
            $emailLog = EmailLog::where('form_link_id', $formLink->id)
                ->where('email_type', 'form_link')
                ->where('status', 'failed')
                ->latest('created_at')
                ->first();

            if (!$emailLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada email yang gagal untuk dikirim ulang',
                ], 400);
            }

            // Check retry limit (max 3 retries)
            if (!$emailLog->canRetry(maxRetries: 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah mencapai batas maksimal percobaan pengiriman ulang (3x)',
                ], 400);
            }

            // Increment retry count
            $emailLog->incrementRetry();

            // Dispatch the email job again with delay
            dispatch(new SendFormLinkEmail($formLink))->delay(15);

            Log::info('Form link email resent', [
                'form_link_id' => $formLink->id,
                'email_log_id' => $emailLog->id,
                'resent_by' => auth('admin')->id(),
                'retry_count' => $emailLog->retry_count,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email berhasil dijadwalkan untuk dikirim ulang dalam 15 detik',
                'retry_count' => $emailLog->retry_count,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to resend form link email', [
                'form_link_id' => $formLink->id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ulang email. Silakan coba lagi.',
            ], 500);
        }
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('expired')) {
            if ($request->expired === 'yes') {
                $query->where('expired_at', '<', now());
            } elseif ($request->expired === 'no') {
                $query->where('expired_at', '>=', now());
            }
        }

        if ($request->filled('used')) {
            if ($request->used === 'yes') {
                $query->whereNotNull('used_at');
            } elseif ($request->used === 'no') {
                $query->whereNull('used_at');
            }
        }

        if ($request->filled('email_status')) {
            $status = $request->email_status;
            $sub = EmailLog::select('status')
                ->whereColumn('form_link_id', 'form_links.id')
                ->where('email_type', 'form_link')
                ->orderBy('created_at', 'desc')
                ->limit(1);

            // embed subquery and pass bindings properly
            $query->whereRaw("({$sub->toSql()}) = ?", array_merge($sub->getBindings(), [$status]));
        }
    }
}
