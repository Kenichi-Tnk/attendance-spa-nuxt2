<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrect;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceCorrectController extends Controller
{
    /**
     * Get user's correction requests
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $corrections = AttendanceCorrect::where('user_id', $user->id)
            ->with('rests')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Transform the data to ensure date is returned as string format
        $corrections->getCollection()->transform(function ($correction) {
            return [
                'id' => $correction->id,
                'user_id' => $correction->user_id,
                'date' => $correction->date->format('Y-m-d'),
                'check_in' => $correction->check_in,
                'check_out' => $correction->check_out,
                'reason' => $correction->reason,
                'status' => $correction->status,
                'created_at' => $correction->created_at,
                'updated_at' => $correction->updated_at,
                'rests' => $correction->rests
            ];
        });

        return response()->json($corrections, Response::HTTP_OK);
    }

    /**
     * Store a new correction request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|before_or_equal:today',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'reason' => 'required|string|max:500',
            'rests' => 'nullable|array',
            'rests.*.rest_start' => 'required_with:rests|date_format:H:i',
            'rests.*.rest_end' => 'required_with:rests|date_format:H:i|after:rests.*.rest_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '入力データが正しくありません',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $request->user();
        $date = $request->date;

        // 既に同じ日付で申請が存在するかチェック
        $existingCorrection = AttendanceCorrect::where('user_id', $user->id)
            ->where('date', $date)
            ->where('status', 'pending')
            ->first();

        if ($existingCorrection) {
            return response()->json([
                'message' => 'この日付の修正申請は既に申請済みです'
            ], Response::HTTP_CONFLICT);
        }

        // 該当日の勤怠データが存在するかチェック
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'この日付の勤怠データが存在しません'
            ], Response::HTTP_NOT_FOUND);
        }

        // 修正申請を作成
        $correction = AttendanceCorrect::create([
            'user_id' => $user->id,
            'date' => $date,
            'check_in' => $request->check_in . ':00', // 秒を追加
            'check_out' => $request->check_out ? $request->check_out . ':00' : null,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        // 休憩時間の修正申請がある場合
        if ($request->has('rests') && is_array($request->rests)) {
            foreach ($request->rests as $rest) {
                $correction->rests()->create([
                    'rest_start' => $rest['rest_start'] . ':00',
                    'rest_end' => $rest['rest_end'] . ':00'
                ]);
            }
        }

        // 作成した申請を関連データと一緒に取得
        $correction->load('rests');

        return response()->json([
            'message' => '修正申請を受け付けました',
            'correction' => $correction
        ], Response::HTTP_CREATED);
    }

    /**
     * Get specific correction request
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $correction = AttendanceCorrect::where('user_id', $user->id)
            ->where('id', $id)
            ->with('rests')
            ->first();

        if (!$correction) {
            return response()->json([
                'message' => '申請が見つかりません'
            ], Response::HTTP_NOT_FOUND);
        }

        // フォーマット済みデータを返す
        return response()->json([
            'correction' => [
                'id' => $correction->id,
                'user_id' => $correction->user_id,
                'date' => $correction->date,
                'check_in' => $correction->check_in ? $correction->check_in->format('H:i:s') : null,
                'check_out' => $correction->check_out ? $correction->check_out->format('H:i:s') : null,
                'reason' => $correction->reason,
                'status' => $correction->status,
                'reject_reason' => $correction->reject_reason,
                'created_at' => $correction->created_at,
                'updated_at' => $correction->updated_at,
                'rests' => $correction->rests->map(function ($rest) {
                    return [
                        'id' => $rest->id,
                        'rest_start' => $rest->rest_start ? $rest->rest_start->format('H:i:s') : null,
                        'rest_end' => $rest->rest_end ? $rest->rest_end->format('H:i:s') : null,
                    ];
                })
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Approve correction request (Admin only)
     */
    public function approve(Request $request, $id)
    {
        try {
            Log::info("承認処理開始 - ID: {$id}");

            $correction = AttendanceCorrect::findOrFail($id);
            Log::info("修正申請データ取得成功", ['correction' => $correction->toArray()]);

            if ($correction->status !== 'pending') {
                return response()->json([
                    'message' => 'この申請は既に処理済みです'
                ], Response::HTTP_CONFLICT);
            }

            // 承認処理：元の勤怠データを修正申請内容で更新または新規作成
            $attendance = Attendance::where('user_id', $correction->user_id)
                ->where('date', $correction->date)
                ->first();

            Log::info("勤怠データ検索結果", ['attendance' => $attendance ? $attendance->toArray() : 'null']);

            if ($attendance) {
                // 既存データを更新
                $attendance->update([
                    'check_in' => $correction->check_in,
                    'check_out' => $correction->check_out
                ]);
                Log::info("既存勤怠データを更新");
            } else {
                // 新規データを作成
                $attendance = Attendance::create([
                    'user_id' => $correction->user_id,
                    'date' => $correction->date,
                    'check_in' => $correction->check_in,
                    'check_out' => $correction->check_out
                ]);
                Log::info("新規勤怠データを作成", ['attendance' => $attendance->toArray()]);
            }

            // 休憩時間も更新
            if ($correction->rests->count() > 0) {
                // 既存の休憩データを削除
                $attendance->rests()->delete();

                // 新しい休憩データを作成
                foreach ($correction->rests as $rest) {
                    $attendance->rests()->create([
                        'rest_start' => $rest->rest_start,
                        'rest_end' => $rest->rest_end
                    ]);
                }
                Log::info("休憩データを更新");
            }

            // 申請ステータスを承認済みに更新
            $correction->update(['status' => 'approved']);
            Log::info("申請ステータスを承認済みに更新");

            return response()->json([
                'message' => '申請を承認しました',
                'correction' => $correction->fresh('rests')
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("承認処理エラー", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => '承認処理中にエラーが発生しました: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reject correction request (Admin only)
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reject_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '却下理由が必要です',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $correction = AttendanceCorrect::findOrFail($id);

        if ($correction->status !== 'pending') {
            return response()->json([
                'message' => 'この申請は既に処理済みです'
            ], Response::HTTP_CONFLICT);
        }

        // 申請ステータスを却下に更新（却下理由も保存）
        $correction->update([
            'status' => 'rejected',
            'reject_reason' => $request->reject_reason
        ]);

        return response()->json([
            'message' => '申請を却下しました',
            'correction' => $correction->fresh('rests')
        ], Response::HTTP_OK);
    }

    /**
     * Get all pending correction requests (Admin only)
     */
    public function pendingRequests(Request $request)
    {
        $corrections = AttendanceCorrect::with(['user', 'rests'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return response()->json($corrections, Response::HTTP_OK);
    }

    /**
     * Get all correction requests (Admin only)
     */
    public function adminIndex(Request $request)
    {
        $query = AttendanceCorrect::with(['user', 'rests'])
            ->orderBy('created_at', 'desc');

        // ステータスでフィルタリング
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // ユーザーでフィルタリング
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $corrections = $query->paginate(15);

        return response()->json($corrections, Response::HTTP_OK);
    }

    /**
     * Get original attendance data for correction request
     */
    public function getOriginalAttendance(Request $request, $id)
    {
        $user = $request->user();

        // 修正申請を取得
        $correction = AttendanceCorrect::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$correction) {
            return response()->json([
                'message' => '申請が見つかりません'
            ], Response::HTTP_NOT_FOUND);
        }

        // 修正前の勤怠データを取得
        $originalAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $correction->date)
            ->with('rests')
            ->first();

        if (!$originalAttendance) {
            return response()->json([
                'message' => '元の勤怠データが見つかりません',
                'original_attendance' => null
            ], Response::HTTP_OK);
        }

        // フォーマット済みデータを返す
        return response()->json([
            'original_attendance' => [
                'id' => $originalAttendance->id,
                'user_id' => $originalAttendance->user_id,
                'date' => $originalAttendance->date->format('Y-m-d'),
                'check_in' => $originalAttendance->check_in ? $originalAttendance->check_in->format('H:i:s') : null,
                'check_out' => $originalAttendance->check_out ? $originalAttendance->check_out->format('H:i:s') : null,
                'rests' => $originalAttendance->rests->map(function ($rest) {
                    return [
                        'id' => $rest->id,
                        'attendance_id' => $rest->attendance_id,
                        'rest_start' => $rest->rest_start ? $rest->rest_start->format('H:i:s') : null,
                        'rest_end' => $rest->rest_end ? $rest->rest_end->format('H:i:s') : null,
                    ];
                })
            ]
        ], Response::HTTP_OK);
    }
}
