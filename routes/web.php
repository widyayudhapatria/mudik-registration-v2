<?php

use App\Http\Controllers\CMS\DashboardController;
use App\Http\Controllers\CMS\AdminManagementController;
use App\Http\Controllers\CMS\DestinationManagementController;
use App\Http\Controllers\CMS\EmailRequestController;
use App\Http\Controllers\CMS\QuotaManagementController;
use App\Http\Controllers\CMS\RegistrationManagementController;
use App\Http\Controllers\CMS\SeatManifestController;
use App\Http\Controllers\CMS\BypassRegistrationController;
use App\Http\Controllers\Public\EmailSubmissionController;
use App\Http\Controllers\Public\QrViewController;
use App\Http\Controllers\Public\RegistrationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::prefix('public')->name('public.')->group(function () {

    Route::get('/', function () {
        return view('pages.index');
    })->name('landing');

    Route::post('/submit-email', [EmailSubmissionController::class, 'submit'])
        ->middleware('throttle:5,10', 'registration.period')
        ->name('submit-email');

    Route::get('/register/{token}', [RegistrationController::class, 'show'])
        ->middleware(['signed.form', 'form.link.valid', 'quota.available'])
        ->name('registration.form');

    Route::post('/register/{token}', [RegistrationController::class, 'submit'])
        ->middleware(['signed.form', 'form.link.valid', 'quota.available'])
        ->name('registration.submit');
});

Route::prefix('scan')->name('scan.')->group(function () {
    Route::get('/entry', [QrViewController::class, 'entry'])->name('entry');
    Route::get('/{token}', [QrViewController::class, 'view'])->name('view');
});

Route::prefix('cms')->name('cms.')->middleware(['auth:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin-management')->name('admin-management.')->middleware('super.admin')->group(function () {
        Route::get('/', [AdminManagementController::class, 'index'])->name('index');
        Route::get('/create', [AdminManagementController::class, 'create'])->name('create');
        Route::post('/', [AdminManagementController::class, 'store'])->name('store');
        Route::get('/{admin}/edit', [AdminManagementController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [AdminManagementController::class, 'update'])->name('update');
        Route::post('/{admin}/toggle-active', [AdminManagementController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])->name('destroy');
    });

    // Change password - accessible by all authenticated admins (bisa ubah diri sendiri)
    Route::prefix('admin-management')->name('admin-management.')->group(function () {
        Route::get('/{admin}/change-password', [AdminManagementController::class, 'showChangePassword'])->name('change-password.show');
        Route::put('/{admin}/change-password', [AdminManagementController::class, 'updatePassword'])->name('change-password.update');
    });

    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/', [RegistrationManagementController::class, 'index'])->name('index');
        Route::get('/export', [RegistrationManagementController::class, 'export'])->name('export');
        Route::get('/{registration}', [RegistrationManagementController::class, 'show'])->withTrashed()->name('show');
        Route::post('/{registration}/approve', [RegistrationManagementController::class, 'approve'])->name('approve');
        Route::post('/{registration}/reject', [RegistrationManagementController::class, 'reject'])->name('reject');
        Route::post('/{registration}/resend-qr-code', [RegistrationManagementController::class, 'resendQrCode'])->name('resend-qr-code');
    });

    Route::prefix('seat-manifest')->name('seat-manifest.')->group(function () {
        Route::get('/', [SeatManifestController::class, 'index'])->name('index');
    });

    Route::prefix('bypass-registrations')->name('bypass-registrations.')->group(function () {
        Route::get('/form', [BypassRegistrationController::class, 'showForm'])->name('form');
        Route::post('/validate', [BypassRegistrationController::class, 'validateFile'])->name('validate');
        Route::get('/preview', [BypassRegistrationController::class, 'showPreview'])->name('preview');
        Route::post('/confirm', [BypassRegistrationController::class, 'confirmImport'])->name('confirm');
        Route::get('/results/{importId}', [BypassRegistrationController::class, 'showResults'])->name('results');
        Route::get('/history', [BypassRegistrationController::class, 'history'])->name('history');
        Route::get('/download-template', [BypassRegistrationController::class, 'downloadTemplate'])->name('download-template');
    });

    Route::prefix('destinations')->name('destinations.')->group(function () {
        Route::get('/', [DestinationManagementController::class, 'index'])->name('index');
        Route::get('/{destination}', [DestinationManagementController::class, 'show'])->name('show');
        Route::post('/', [DestinationManagementController::class, 'store'])->name('store');
        Route::put('/{destination}', [DestinationManagementController::class, 'update'])->name('update');
        Route::delete('/{destination}', [DestinationManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{destination}/toggle-active', [DestinationManagementController::class, 'toggleActive'])->name('toggle-active');
    });

    Route::prefix('quotas')->name('quotas.')->group(function () {
        Route::get('/', [QuotaManagementController::class, 'index'])->name('index');
        Route::get('/today', [QuotaManagementController::class, 'today'])->name('today');
        Route::get('/destination/{destination}', [QuotaManagementController::class, 'getByDestination'])->name('by-destination');
        Route::get('/destination/{destination}/detail', [QuotaManagementController::class, 'destinationDetail'])->name('destination.detail');
        Route::post('/', [QuotaManagementController::class, 'store'])->name('store');
    });

    Route::prefix('email-requests')->name('email-requests.')->group(function () {
        Route::get('/', [EmailRequestController::class, 'index'])->name('index');
        Route::get('/{formLink}', [EmailRequestController::class, 'show'])->name('show');
    });

    Route::prefix('scanner')->name('scanner.')->middleware(['scanner.permission'])->group(function () {
        Route::get('/', function () {
            return view('cms.scanner.index-spa');
        })->name('index');

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\CMS\ScannerDashboardController::class, 'index'])->name('dashboard');

        // Scanner API Endpoints (moved from api.php for session support)
        Route::get('/api/validate', [\App\Http\Controllers\CMS\ScannerController::class, 'validateQrCode'])->name('api.validate');
        Route::post('/api/consume', [\App\Http\Controllers\CMS\ScannerController::class, 'consumeQrCode'])->name('api.consume');
        Route::get('/api/history', [\App\Http\Controllers\CMS\ScannerController::class, 'scanHistory'])->name('api.history');
        Route::get('/api/statistics', [\App\Http\Controllers\CMS\ScannerDashboardController::class, 'statistics'])->name('api.statistics');
        Route::get('/api/scan-logs', [\App\Http\Controllers\CMS\ScannerDashboardController::class, 'scanLogs'])->name('api.scan-logs');
        Route::get('/api/scan-by-destination', [\App\Http\Controllers\CMS\ScannerDashboardController::class, 'scanByDestination'])->name('api.scan-by-destination');

        Route::get('/scan/{token}', function ($token) {
            $qrCode = \App\Models\QrCode::where('token_qr', $token)
                ->with(['registration.participants', 'registration.formLink', 'scannedBy'])
                ->first();

            if (!$qrCode) {
                // Better error handling with debug info
                Log::warning('QR Code not found in scanner', [
                    'token_searched' => $token,
                    'token_length' => strlen($token),
                ]);

                return response()->view('errors.qr-not-found', [
                    'token' => $token,
                    'message' => 'QR Code tidak ditemukan di database.',
                    'debug' => [
                        'token_length' => strlen($token),
                        'searched_token' => substr($token, 0, 32) . '...',
                    ]
                ], 404);
            }

            return view('cms.scanner.scan', [
                'token' => $token,
                'qrCode' => $qrCode,
            ]);
        })->name('scan')->where('token', '.+');;
    });
});

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login')->middleware('guest:admin');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login timestamp
            $admin = Auth::guard('admin')->user();
            $admin->update(['last_login_at' => now()]);

            return redirect()->intended(route('cms.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    })->name('login.process')->middleware('guest:admin');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    })->name('logout')->middleware('auth:admin');
});

Route::get('/', function () {
    return redirect()->route('public.landing');
});
