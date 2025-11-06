<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $startTime = microtime(true);
        Log::info('Registration started', [
            'timestamp' => now()->toISOString(),
            'email' => $request->email,
            'start_time' => $startTime
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            $endTime = microtime(true);
            Log::error('Registration validation failed', [
                'timestamp' => now()->toISOString(),
                'email' => $request->email,
                'duration' => ($endTime - $startTime) * 1000 . 'ms',
                'errors' => $validator->errors()
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $beforeCreate = microtime(true);
        Log::info('Creating user', [
            'timestamp' => now()->toISOString(),
            'email' => $request->email,
            'validation_duration' => ($beforeCreate - $startTime) * 1000 . 'ms'
        ]);

        /** @var User $user */
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        $afterCreate = microtime(true);
        Log::info('User created, sending email verification', [
            'timestamp' => now()->toISOString(),
            'email' => $request->email,
            'user_id' => $user->id,
            'create_duration' => ($afterCreate - $beforeCreate) * 1000 . 'ms'
        ]);

        // メール認証通知を送信
        $user->sendEmailVerificationNotification();

        $afterEmail = microtime(true);
        Log::info('Email sent, creating token', [
            'timestamp' => now()->toISOString(),
            'email' => $request->email,
            'user_id' => $user->id,
            'email_duration' => ($afterEmail - $afterCreate) * 1000 . 'ms'
        ]);

        $token = $user->createToken('attendance-app')->plainTextToken;

        $endTime = microtime(true);
        Log::info('Registration completed successfully', [
            'timestamp' => now()->toISOString(),
            'email' => $request->email,
            'user_id' => $user->id,
            'total_duration' => ($endTime - $startTime) * 1000 . 'ms',
            'token_duration' => ($endTime - $afterEmail) * 1000 . 'ms'
        ]);

        return response()->json([
            'message' => 'User registered successfully. Please check your email for verification.',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('attendance-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], Response::HTTP_OK);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ], Response::HTTP_OK);
    }

    /**
     * Send email verification notification
     */
    public function sendEmailVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], Response::HTTP_OK);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent'
        ], Response::HTTP_OK);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request, $id = null, $hash = null)
    {
        // URLパラメータまたはリクエストボディから値を取得
        $userId = $id ?? $request->input('id');
        $emailHash = $hash ?? $request->input('hash');

        $validator = Validator::make([
            'id' => $userId,
            'hash' => $emailHash
        ], [
            'id' => 'required|integer',
            'hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::findOrFail($userId);

        if (!hash_equals(sha1($user->email), $emailHash)) {
            return response()->json([
                'message' => 'Invalid verification link'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], Response::HTTP_OK);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully'
        ], Response::HTTP_OK);
    }
}
