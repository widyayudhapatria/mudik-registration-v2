<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\FormLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $query = FormLink::with(['registration']);

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
    }
}