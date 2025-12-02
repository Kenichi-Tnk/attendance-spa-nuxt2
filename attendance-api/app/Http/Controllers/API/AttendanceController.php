<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get user's attendance status for today
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->with('rests')
            ->first();

        if (!$attendance) {
            return response()->json([
                'status' => 'not_started',
                'attendance' => null,
                'active_rest' => null,
            ], Response::HTTP_OK);
        }

        $activeRest = $attendance->rests()
            ->whereNull('rest_end')
            ->first();

        $status = 'checked_in';
        if ($attendance->check_out) {
            $status = 'checked_out';
        } elseif ($activeRest) {
            $status = 'on_break';
        }

        return response()->json([
            'status' => $status,
            'attendance' => $attendance,
            'active_rest' => $activeRest,
        ], Response::HTTP_OK);
    }

    /**
     * Check in (start work)
     */
    public function checkIn(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Already checked in today'
            ], Response::HTTP_CONFLICT);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => now(),
        ]);

        return response()->json([
            'message' => 'Check-in successful',
            'attendance' => $attendance,
        ], Response::HTTP_CREATED);
    }

    /**
     * Check out (end work)
     */
    public function checkOut(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No check-in record found for today'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'Already checked out today'
            ], Response::HTTP_CONFLICT);
        }

        // End any active rest period
        $activeRest = $attendance->rests()
            ->whereNull('rest_end')
            ->first();

        if ($activeRest) {
            $activeRest->update([
                'rest_end' => now()
            ]);
        }

        $attendance->update([
            'check_out' => now()
        ]);

        return response()->json([
            'message' => 'Check-out successful',
            'attendance' => $attendance->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Start rest (break)
     */
    public function startRest(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No check-in record found for today'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'Cannot start rest after check-out'
            ], Response::HTTP_CONFLICT);
        }

        // Check if already on rest
        $activeRest = $attendance->rests()
            ->whereNull('rest_end')
            ->first();

        if ($activeRest) {
            return response()->json([
                'message' => 'Already on rest'
            ], Response::HTTP_CONFLICT);
        }

        $rest = Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => now(),
        ]);

        return response()->json([
            'message' => 'Rest started successfully',
            'rest' => $rest,
        ], Response::HTTP_CREATED);
    }

    /**
     * End rest (return from break)
     */
    public function endRest(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No check-in record found for today'
            ], Response::HTTP_NOT_FOUND);
        }

        $activeRest = $attendance->rests()
            ->whereNull('rest_end')
            ->first();

        if (!$activeRest) {
            return response()->json([
                'message' => 'No active rest period found'
            ], Response::HTTP_NOT_FOUND);
        }

        $activeRest->update([
            'rest_end' => now()
        ]);

        return response()->json([
            'message' => 'Rest ended successfully',
            'rest' => $activeRest->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Get attendance records
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Attendance::where('user_id', $user->id)
            ->with('rests');

        // 年月でフィルタリング
        if ($request->has('year') && $request->has('month')) {
            $year = $request->input('year');
            $month = str_pad($request->input('month'), 2, '0', STR_PAD_LEFT);
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate)); // 月の最終日

            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate(15);

        // Transform the data to ensure date is returned as string format
        $attendances->getCollection()->transform(function ($attendance) {
            return [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'date' => $attendance->date->format('Y-m-d'),
                'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i:s') : null,
                'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i:s') : null,
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
                'rests' => $attendance->rests->map(function ($rest) {
                    return [
                        'id' => $rest->id,
                        'attendance_id' => $rest->attendance_id,
                        'rest_start' => $rest->rest_start ? $rest->rest_start->format('H:i:s') : null,
                        'rest_end' => $rest->rest_end ? $rest->rest_end->format('H:i:s') : null,
                    ];
                })
            ];
        });

        return response()->json($attendances, Response::HTTP_OK);
    }

    /**
     * Get specific attendance record
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $query = Attendance::with('rests');

        // 管理者以外は自分の勤怠のみ取得可能
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $attendance = $query->find($id);

        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance record not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'date' => $attendance->date->format('Y-m-d'),
            'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i:s') : null,
            'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i:s') : null,
            'created_at' => $attendance->created_at,
            'updated_at' => $attendance->updated_at,
            'rests' => $attendance->rests->map(function ($rest) {
                return [
                    'id' => $rest->id,
                    'rest_start' => $rest->rest_start ? $rest->rest_start->format('H:i:s') : null,
                    'rest_end' => $rest->rest_end ? $rest->rest_end->format('H:i:s') : null,
                ];
            })
        ], Response::HTTP_OK);
    }

    /**
     * Get daily attendance for all staff (Admin only)
     */
    public function getDailyAttendance(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        // 全ユーザーを取得
        $users = \App\Models\User::all();

        // 指定日の勤怠データを取得
        $attendances = Attendance::where('date', $date)
            ->with('rests', 'user')
            ->get()
            ->keyBy('user_id');

        // 各ユーザーの勤怠レコードを生成
        $records = $users->map(function ($user) use ($attendances, $date) {
            $attendance = $attendances->get($user->id);

            if (!$attendance) {
                // 勤怠記録がない場合
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'break_time' => 0,
                    'work_time' => 0,
                    'status' => 'absent'
                ];
            }

            // 休憩時間を計算（分）
            $breakMinutes = 0;
            if ($attendance->rests) {
                foreach ($attendance->rests as $rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        $breakMinutes += $rest->rest_start->diffInMinutes($rest->rest_end);
                    }
                }
            }

            // 勤務時間を計算（分）
            $workMinutes = 0;
            if ($attendance->check_in && $attendance->check_out) {
                $totalMinutes = $attendance->check_in->diffInMinutes($attendance->check_out);
                $workMinutes = $totalMinutes - $breakMinutes;
            }

            // ステータスを判定
            $status = 'absent';
            if ($attendance->check_in) {
                if ($attendance->check_out) {
                    $status = 'completed';
                } else {
                    $status = 'working';
                }
            }

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'check_in_time' => $attendance->check_in ? $attendance->check_in->format('H:i:s') : null,
                'check_out_time' => $attendance->check_out ? $attendance->check_out->format('H:i:s') : null,
                'break_time' => $breakMinutes,
                'work_time' => $workMinutes,
                'status' => $status
            ];
        })->values();

        // 統計を計算
        $statistics = [
            'total' => $records->count(),
            'present' => $records->where('status', 'working')->count(),
            'completed' => $records->where('status', 'completed')->count(),
            'absent' => $records->where('status', 'absent')->count()
        ];

        return response()->json([
            'records' => $records,
            'statistics' => $statistics,
            'date' => $date
        ], Response::HTTP_OK);
    }

    /**
     * Admin update attendance record (Admin only)
     */
    public function adminUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'rests' => 'array',
            'rests.*.id' => 'nullable|exists:rests,id',
            'rests.*.rest_start' => 'required|date_format:H:i',
            'rests.*.rest_end' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $attendance = Attendance::with('rests')->findOrFail($id);
        $date = $attendance->date->format('Y-m-d');

        // 勤怠データを更新
        $attendance->update([
            'check_in' => Carbon::parse($date . ' ' . $request->check_in),
            'check_out' => $request->check_out ? Carbon::parse($date . ' ' . $request->check_out) : null,
        ]);

        // 既存の休憩時間を削除
        $attendance->rests()->delete();

        // 新しい休憩時間を追加
        if ($request->has('rests')) {
            foreach ($request->rests as $rest) {
                if ($rest['rest_start']) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'rest_start' => Carbon::parse($date . ' ' . $rest['rest_start']),
                        'rest_end' => isset($rest['rest_end']) && $rest['rest_end']
                            ? Carbon::parse($date . ' ' . $rest['rest_end'])
                            : null,
                    ]);
                }
            }
        }

        $attendance->refresh();
        $attendance->load('rests');

        return response()->json([
            'message' => '勤怠情報を更新しました',
            'attendance' => $attendance
        ], Response::HTTP_OK);
    }
}
