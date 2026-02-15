<?php

use App\Http\Controllers\CMS\DashboardController;
use App\Http\Controllers\CMS\QuotaManagementController;
use App\Http\Controllers\CMS\RegistrationManagementController;
use App\Http\Controllers\Public\EmailSubmissionController;
use App\Http\Controllers\Public\QrViewController;
use App\Http\Controllers\Public\RegistrationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('public')->name('public.')->group(function () {

    Route::get('/', function () {
        return view('pages.index');
    })->name('landing');

    Route::post('/submit-email', [EmailSubmissionController::class, 'submit'])
        ->middleware('throttle:5,10')
        ->name('submit-email');

    Route::get('/register/{token}', [RegistrationController::class, 'show'])
        ->middleware(['signed', 'form.link.valid', 'quota.available'])
        ->name('registration.form');

    Route::post('/register/{token}', [RegistrationController::class, 'submit'])
        ->middleware(['form.link.valid', 'quota.available'])
        ->name('registration.submit');
});

Route::prefix('scan')->name('scan.')->group(function () {
    Route::get('/entry', [QrViewController::class, 'entry'])->name('entry');
    Route::get('/{token}', [QrViewController::class, 'view'])->name('view');
});

Route::prefix('cms')->name('cms.')->middleware(['auth:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/', [RegistrationManagementController::class, 'index'])->name('index');
        Route::get('/{registration}', [RegistrationManagementController::class, 'show'])->name('show');
        Route::post('/{registration}/approve', [RegistrationManagementController::class, 'approve'])->name('approve');
        Route::post('/{registration}/reject', [RegistrationManagementController::class, 'reject'])->name('reject');
    });

    Route::prefix('quotas')->name('quotas.')->group(function () {
        Route::get('/', [QuotaManagementController::class, 'index'])->name('index');
        Route::get('/today', [QuotaManagementController::class, 'today'])->name('today');
        Route::post('/', [QuotaManagementController::class, 'store'])->name('store');
    });

    Route::prefix('scanner')->name('scanner.')->middleware(['scanner.permission'])->group(function () {
        Route::get('/', function () {
            return view('cms.scanner.index');
        })->name('index');

        Route::get('/scan/{token}', function ($token) {
            $qrCode = \App\Models\QrCode::where('token_qr', $token)
                ->with(['registration.participants', 'registration.formLink', 'scannedBy'])
                ->firstOrFail();

            return view('cms.scanner.scan', [
                'token' => $token,
                'qrCode' => $qrCode,
            ]);
        })->name('scan');
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

        if (auth('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('cms.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    })->name('login.process')->middleware('guest:admin');

    Route::post('/logout', function (Request $request) {
        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    })->name('logout')->middleware('auth:admin');
});

Route::get('/', function () {
    return redirect()->route('public.landing');
});
