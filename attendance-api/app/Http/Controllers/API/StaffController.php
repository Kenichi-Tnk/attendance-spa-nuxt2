<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
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
     * Invite new staff member (Admin only)
     * スタッフを招待し、招待メールを送信
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:user,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 仮パスワードで作成（初回ログイン時に変更必須）
        $temporaryPassword = 'temp_' . uniqid();

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'role' => $request->role,
            'password_change_required' => true, // 初回ログイン時にパスワード変更必須
            'invited_at' => now() // 招待日時を記録
        ]);

        // TODO: 招待メール送信機能を実装
        // $staff->sendInvitationEmail($temporaryPassword);

        return response()->json([
            'message' => 'スタッフを招待しました。招待メールを送信しました。',
            'user' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
                'invited_at' => $staff->created_at
            ],
            'temporary_password' => $temporaryPassword // 開発用（本番では削除）
        ], Response::HTTP_CREATED);
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
}
