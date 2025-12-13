<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AttendanceCorrectController;
use App\Http\Controllers\API\StaffController;

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
        Route::get('/monthly-summary', [AttendanceController::class, 'getMonthlySummary']);
        Route::get('/', [AttendanceController::class, 'index']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
    });

    // Attendance correction request routes
    Route::prefix('correction-requests')->group(function () {
        Route::get('/', [AttendanceCorrectController::class, 'index']); // ユーザーの申請一覧
        Route::post('/', [AttendanceCorrectController::class, 'store']); // 申請作成
        Route::get('/{id}', [AttendanceCorrectController::class, 'show']); // 申請詳細
        Route::get('/{id}/original-attendance', [AttendanceCorrectController::class, 'getOriginalAttendance']); // 修正前データ取得

        // 管理者専用ルート
        Route::middleware(['admin'])->group(function () {
            Route::get('/admin/all', [AttendanceCorrectController::class, 'adminIndex']); // 全申請管理
            Route::get('/admin/pending', [AttendanceCorrectController::class, 'pendingRequests']); // 承認待ち申請
            Route::put('/{id}/approve', [AttendanceCorrectController::class, 'approve']); // 承認
            Route::put('/{id}/reject', [AttendanceCorrectController::class, 'reject']); // 却下
        });
    });

    // 管理者専用スタッフ管理
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [StaffController::class, 'getDashboardData']); // ダッシュボードデータ取得
        Route::get('/staff/{id}/attendance/monthly', [StaffController::class, 'getMonthlyAttendance']); // 月次勤怠取得
        Route::get('/attendance/daily', [AttendanceController::class, 'getDailyAttendance']); // 日次勤怠一覧
        Route::get('/attendance/daily/{userId}', [AttendanceController::class, 'getDailyAttendanceDetail']); // 日次勤怠詳細
        Route::put('/attendance/{id}', [AttendanceController::class, 'adminUpdate']); // 管理者による勤怠更新
    });

    Route::middleware(['admin'])->prefix('staff')->group(function () {
        Route::get('/', [StaffController::class, 'index']); // スタッフ一覧
        Route::get('/statistics', [StaffController::class, 'statistics']); // 統計情報
        Route::put('/{id}', [StaffController::class, 'update']); // スタッフ更新
        Route::delete('/{id}', [StaffController::class, 'destroy']); // スタッフ削除
        Route::post('/{id}/verify-email', [StaffController::class, 'verifyEmail']); // メール認証
    });
});

// Legacy route for compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
