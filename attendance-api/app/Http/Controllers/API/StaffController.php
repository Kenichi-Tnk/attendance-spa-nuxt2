<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrect;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * Get dashboard data (Admin only)
     */
    public function getDashboardData(Request $request)
    {
        $today = now()->toDateString();

        // 総スタッフ数
        $totalStaff = User::count();

        // 本日出勤中のスタッフ数
        $presentToday = Attendance::where('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->count();

        // 承認待ち申請数
        $pendingRequests = AttendanceCorrect::where('status', 'pending')->count();

        // 最近の活動を取得（本日の勤怠記録）
        $recentActivities = Attendance::where('date', $today)
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attendance) {
                $icon = 'fas fa-user-clock';
                $message = '';
                $time = $attendance->updated_at->diffForHumans();

                if ($attendance->check_out) {
                    $icon = 'fas fa-user-minus';
                    $message = "{$attendance->user->name}さんが退勤しました";
                } elseif ($attendance->check_in) {
                    $icon = 'fas fa-user-plus';
                    $message = "{$attendance->user->name}さんが出勤しました";
                }

                return [
                    'id' => $attendance->id,
                    'icon' => $icon,
                    'message' => $message,
                    'time' => $time
                ];
            })
            ->filter(function ($activity) {
                return !empty($activity['message']);
            })
            ->values();

        // 修正申請の最近の活動も追加
        $recentCorrections = AttendanceCorrect::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($correction) {
                $icon = 'fas fa-edit';
                if ($correction->status === 'approved') {
                    $icon = 'fas fa-check';
                } elseif ($correction->status === 'rejected') {
                    $icon = 'fas fa-times';
                }

                $statusText = '';
                if ($correction->status === 'pending') {
                    $statusText = '修正申請を提出しました';
                } elseif ($correction->status === 'approved') {
                    $statusText = 'の修正申請を承認しました';
                } elseif ($correction->status === 'rejected') {
                    $statusText = 'の修正申請を却下しました';
                }

                return [
                    'id' => 'correction_' . $correction->id,
                    'icon' => $icon,
                    'message' => "{$correction->user->name}さん{$statusText}",
                    'time' => $correction->created_at->diffForHumans()
                ];
            });

        // アクティビティを結合してソート
        $allActivities = $recentActivities->concat($recentCorrections)
            ->sortByDesc('time')
            ->take(10)
            ->values();

        return response()->json([
            'stats' => [
                'totalStaff' => $totalStaff,
                'presentToday' => $presentToday,
                'pendingRequests' => $pendingRequests,
            ],
            'recentActivities' => $allActivities
        ], Response::HTTP_OK);
    }

    /**
     * Get all staff members (Admin only)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // フィルター機能
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        if ($request->has('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verified === 'false') {
                $query->whereNull('email_verified_at');
            }
        }

        // 検索機能
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json($staff, Response::HTTP_OK);
    }

    /**
     * Get staff statistics (Admin only)
     */
    public function statistics()
    {
        $totalStaff = User::count();
        $verifiedStaff = User::whereNotNull('email_verified_at')->count();
        $unverifiedStaff = User::whereNull('email_verified_at')->count();
        $adminStaff = User::where('role', 'admin')->count();
        $userStaff = User::where('role', 'user')->count();

        return response()->json([
            'total' => $totalStaff,
            'verified' => $verifiedStaff,
            'unverified' => $unverifiedStaff,
            'admin' => $adminStaff,
            'user' => $userStaff
        ], Response::HTTP_OK);
    }

    /**
     * Update staff member (Admin only)
     */
    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|required|in:user,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = $request->only(['name', 'email', 'role']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $staff->update($updateData);

        return response()->json([
            'message' => 'スタッフ情報を更新しました',
            'user' => $staff->fresh()
        ], Response::HTTP_OK);
    }

    /**
     * Delete staff member (Admin only)
     */
    public function destroy($id)
    {
        $staff = User::findOrFail($id);

        // 自分自身を削除することを防ぐ
        if ($staff->id === auth()->id()) {
            return response()->json([
                'message' => '自分自身を削除することはできません'
            ], Response::HTTP_FORBIDDEN);
        }

        $staff->delete();

        return response()->json([
            'message' => 'スタッフを削除しました'
        ], Response::HTTP_OK);
    }

    /**
     * Verify staff email (Admin only)
     */
    public function verifyEmail($id)
    {
        $staff = User::findOrFail($id);

        if ($staff->email_verified_at) {
            return response()->json([
                'message' => 'このユーザーは既に認証済みです'
            ], Response::HTTP_CONFLICT);
        }

        $staff->update([
            'email_verified_at' => now()
        ]);

        return response()->json([
            'message' => 'メール認証を完了しました',
            'user' => $staff->fresh()
        ], Response::HTTP_OK);
    }

    /**
     * Get staff monthly attendance data (Admin only)
     */
    public function getMonthlyAttendance($id, Request $request)
    {
        $staff = User::findOrFail($id);

        // 月の指定(デフォルトは当月)
        $month = $request->input('month', now()->format('Y-m'));

        try {
            $date = Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json([
                'message' => '無効な月の形式です。YYYY-MM形式で指定してください。'
            ], Response::HTTP_BAD_REQUEST);
        }

        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // 指定月の勤怠データを取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('rests')
            ->orderBy('date', 'asc')
            ->get();

        // レコードを整形
        $records = $attendances->map(function ($attendance) {
            $workTime = null;
            $restTime = 0;

            if ($attendance->check_out) {
                $checkIn = $attendance->check_in;
                $checkOut = $attendance->check_out;
                $workSeconds = $checkOut->diffInSeconds($checkIn);

                // 休憩時間を計算
                $restTime = $attendance->rests->sum(function ($rest) {
                    if (!$rest->rest_end) {
                        return 0;
                    }
                    $start = $rest->rest_start;
                    $end = $rest->rest_end;
                    return $end->diffInSeconds($start);
                });

                $workSeconds -= $restTime;
                $workTime = round($workSeconds / 3600, 2); // 時間単位
            }

            return [
                'id' => $attendance->id,
                'date' => $attendance->date->format('Y-m-d'),
                'day_of_week' => $attendance->date->locale('ja')->dayName,
                'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i') : null,
                'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i') : null,
                'rest_time' => round($restTime / 3600, 2),
                'work_time' => $workTime,
                'status' => $this->determineStatus($attendance),
            ];
        });

        // 統計情報を計算
        $statistics = [
            'work_days' => $records->whereNotNull('check_in')->count(),
            'total_hours' => round($records->sum('work_time'), 1),
            'late_count' => $records->filter(function ($record) {
                return $record['status'] === '遅刻';
            })->count(),
            'absence_count' => $records->filter(function ($record) {
                return $record['status'] === '欠勤';
            })->count(),
        ];

        return response()->json([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
            ],
            'month' => $month,
            'records' => $records,
            'statistics' => $statistics,
        ], Response::HTTP_OK);
    }

    /**
     * Determine attendance status
     */
    private function determineStatus($attendance)
    {
        if (!$attendance->check_in) {
            return '欠勤';
        }

        $checkIn = $attendance->check_in;
        $standardTime = Carbon::parse($attendance->date->format('Y-m-d') . ' 09:00:00');

        if ($checkIn->greaterThan($standardTime)) {
            return '遅刻';
        }

        if (!$attendance->check_out) {
            return '早退';
        }

        return '出勤';
    }
}
