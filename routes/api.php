<?php

use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\CMS\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::prefix('destinations')->name('destinations.')->group(function () {
    Route::get('/available', [DestinationController::class, 'available'])->name('available');
});

Route::prefix('cms')->name('cms.api.')->middleware(['auth:admin'])->group(function () {
    // Scanner endpoints moved to web.php for session support
    Route::get('/statistics', [DashboardController::class, 'statistics'])->name('statistics');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'Mudik Lebaran 2026 API',
    ]);
})->name('health');

Route::get('/version', function () {
    return response()->json([
        'version' => '1.0.0',
        'api_version' => 'v1',
        'app_name' => 'Mudik Lebaran 2026',
        'environment' => app()->environment(),
    ]);
})->name('version');

Route::middleware('auth:admin')->get('/me', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => [
            'id' => $request->user('admin')->id,
            'name' => $request->user('admin')->name,
            'email' => $request->user('admin')->email,
            'role' => $request->user('admin')->role,
            'can_scan' => $request->user('admin')->can_scan,
            'is_active' => $request->user('admin')->is_active,
        ],
    ]);
})->name('me');
