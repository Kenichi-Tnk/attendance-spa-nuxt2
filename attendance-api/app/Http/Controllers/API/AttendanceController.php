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
            'check_in' => now()->toTimeString(),
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
                'rest_end' => now()->toTimeString()
            ]);
        }

        $attendance->update([
            'check_out' => now()->toTimeString()
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
            'rest_start' => now()->toTimeString(),
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
            'rest_end' => now()->toTimeString()
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

        $attendances = Attendance::where('user_id', $user->id)
            ->with('rests')
            ->orderBy('date', 'desc')
            ->paginate(15);

        return response()->json($attendances, Response::HTTP_OK);
    }

    /**
     * Get specific attendance record
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('id', $id)
            ->with('rests')
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance record not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'attendance' => $attendance
        ], Response::HTTP_OK);
    }
}
