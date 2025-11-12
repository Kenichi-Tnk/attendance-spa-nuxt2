<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AttendanceCorrectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/verify', [AuthController::class, 'verifyEmail']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendEmailVerification']);

    // Attendance management routes
    Route::prefix('attendance')->group(function () {
        Route::get('/status', [AttendanceController::class, 'status']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::post('/start-rest', [AttendanceController::class, 'startRest']);
        Route::post('/end-rest', [AttendanceController::class, 'endRest']);
        Route::get('/', [AttendanceController::class, 'index']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
    });

    // Attendance correction request routes
    Route::prefix('correction-requests')->group(function () {
        Route::get('/', [AttendanceCorrectController::class, 'index']); // ユーザーの申請一覧
        Route::post('/', [AttendanceCorrectController::class, 'store']); // 申請作成
        Route::get('/{id}', [AttendanceCorrectController::class, 'show']); // 申請詳細

        // 管理者専用ルート
        Route::middleware(['admin'])->group(function () {
            Route::get('/admin/all', [AttendanceCorrectController::class, 'adminIndex']); // 全申請管理
            Route::get('/admin/pending', [AttendanceCorrectController::class, 'pendingRequests']); // 承認待ち申請
            Route::put('/{id}/approve', [AttendanceCorrectController::class, 'approve']); // 承認
            Route::put('/{id}/reject', [AttendanceCorrectController::class, 'reject']); // 却下
        });
    });
});

// Legacy route for compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
