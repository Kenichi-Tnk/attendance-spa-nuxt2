<?php
require 'attendance-api/vendor/autoload.php';
$app = require_once 'attendance-api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== 既存のテストデータを削除 ===\n";
// 外部キー制約のため、deleteを使用
\App\Models\AttendanceCorrect::query()->delete();
echo "削除完了\n\n";

echo "=== 新しいテストデータを作成 ===\n";
$user = \App\Models\User::where('role', 'user')->first();
if (!$user) {
    echo "一般ユーザーが見つかりません\n";
    exit(1);
}

// テストデータ1
$correction1 = new \App\Models\AttendanceCorrect();
$correction1->user_id = $user->id;
$correction1->date = '2025-11-05';
$correction1->check_in = '09:15:00';
$correction1->check_out = '18:30:00';
$correction1->reason = '電車遅延のため15分遅刻しました。実際は9:00に到着予定でした。';
$correction1->status = 'pending';
$correction1->save();

// テストデータ2
$correction2 = new \App\Models\AttendanceCorrect();
$correction2->user_id = $user->id;
$correction2->date = '2025-11-08';
$correction2->check_in = '08:45:00';
$correction2->check_out = '17:45:00';
$correction2->reason = '会議のため早めに出社しました。退勤時刻も調整をお願いします。';
$correction2->status = 'pending';
$correction2->save();

echo "✅ テストデータを作成しました:\n";
echo "ID: {$correction1->id} | Date: {$correction1->date} | 申請者: {$user->name}\n";
echo "ID: {$correction2->id} | Date: {$correction2->date} | 申請者: {$user->name}\n";

echo "\n=== 現在のpending申請一覧 ===\n";
$pending = \App\Models\AttendanceCorrect::with('user')->where('status', 'pending')->get();
foreach ($pending as $req) {
    echo "[ID: {$req->id}] {$req->user->name} - {$req->date} (Status: {$req->status})\n";
    echo "  Check In: {$req->check_in} | Check Out: {$req->check_out}\n";
    echo "  Reason: {$req->reason}\n\n";
}
echo "合計 pending 申請: {$pending->count()}件\n";
