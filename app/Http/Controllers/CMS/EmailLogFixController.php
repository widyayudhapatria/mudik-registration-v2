<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Services\EmailLogFixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailLogFixController extends Controller
{
    protected EmailLogFixService $emailLogFixService;

    public function __construct(EmailLogFixService $emailLogFixService)
    {
        $this->emailLogFixService = $emailLogFixService;

        // Only super admin can access this feature
        $this->middleware(function ($request, $next) {
            if (!auth('admin')->user()->isSuperAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Show import form
     */
    public function showForm()
    {
        return view('cms.email-log-fix.form');
    }

    /**
     * Handle file upload & validation
     */
    public function validateFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB
        ]);

        $file = $request->file('file');

        // Process and validate Excel
        $result = $this->emailLogFixService->processFile($file);

        if (!$result['success']) {
            // If not AJAX, redirect back with error
            if (!$request->ajax() && !$request->wantsJson()) {
                return redirect()->back()->with('error', $result['error'] ?? 'Validasi gagal');
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Validasi gagal',
            ], 422);
        }

        // Count statuses
        $statusCounts = [
            'can_fix' => 0,
            'already_failed' => 0,
            'pending' => 0,
            'no_log' => 0,
        ];

        foreach ($result['email_statuses'] as $status) {
            if ($status['can_fix']) {
                $statusCounts['can_fix']++;
            } elseif ($status['latest_status'] === 'failed') {
                $statusCounts['already_failed']++;
            } elseif ($status['latest_status'] === 'pending') {
                $statusCounts['pending']++;
            } elseif ($status['latest_status'] === 'no_log') {
                $statusCounts['no_log']++;
            }
        }

        // Store in session for confirmation
        session()->put('email_log_fix_data', [
            'filename' => $result['filename'],
            'total_emails' => $result['total_emails'],
            'email_statuses' => $result['email_statuses'],
            'emails' => $result['emails'],
            'status_counts' => $statusCounts,
        ]);

        // If not AJAX, redirect to preview page
        if (!$request->ajax() && !$request->wantsJson()) {
            return redirect()->route('cms.email-log-fix.preview');
        }

        return response()->json([
            'success' => true,
            'preview' => [
                'total_emails' => $result['total_emails'],
                'status_counts' => $statusCounts,
            ],
            'email_statuses' => array_slice($result['email_statuses'], 0, 10), // First 10 for preview
            'has_more' => count($result['email_statuses']) > 10,
        ]);
    }

    /**
     * Show preview & confirmation
     */
    public function showPreview()
    {
        $importData = session('email_log_fix_data');

        if (!$importData) {
            return redirect()->route('cms.email-log-fix.form')
                ->with('error', 'Session expired, silahkan upload ulang.');
        }

        return view('cms.email-log-fix.preview', [
            'importData' => $importData,
        ]);
    }

    /**
     * Confirm & process email log fixes
     */
    public function confirmFix(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        $importData = session('email_log_fix_data');

        if (!$importData) {
            return response()->json([
                'success' => false,
                'error' => 'Session expired'
            ], 400);
        }

        // Process only emails that can be fixed (status = sent)
        $emailsToFix = array_filter($importData['emails'], function ($email) use ($importData) {
            foreach ($importData['email_statuses'] as $status) {
                if ($status['email'] === $email && $status['can_fix']) {
                    return true;
                }
            }
            return false;
        });

        // Execute the fix
        $result = $this->emailLogFixService->processEmailFixes($emailsToFix);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Proses gagal',
            ], 500);
        }

        // Store results in session
        session()->put('email_log_fix_results', $result['results']);
        session()->forget('email_log_fix_data');

        Log::info('EmailLogFix: Process completed', [
            'admin_id' => auth('admin')->id(),
            'total' => $result['results']['total'],
            'updated' => $result['results']['updated'],
            'skipped' => $result['results']['skipped'],
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('cms.email-log-fix.results'),
        ]);
    }

    /**
     * Show processing results
     */
    public function showResults()
    {
        $results = session('email_log_fix_results');

        if (!$results) {
            return redirect()->route('cms.email-log-fix.form')
                ->with('error', 'No results found');
        }

        return view('cms.email-log-fix.results', [
            'results' => $results,
        ]);
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $tempFile = $this->emailLogFixService->generateTemplate();

        return response()->download(
            $tempFile,
            'email-log-fix-template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
}
