<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * 開発・テスト用のユーザーを作成します。
     * 本番環境では実行できません。
     *
     * @return void
     */
    public function run()
    {
        // 本番環境での実行を防止
        if (app()->environment('production')) {
            $this->command->error('⛔ Cannot run development seeder in production environment!');
            return;
        }

        $this->command->info('🌱 Creating development users...');

        // ========================================
        // メール認証済みユーザー（勤怠機能テスト用）
        // ========================================

        $adminVerified = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->command->info('✅ Created: Test Admin (verified)');

        $userVerified = User::create([
            'name' => 'Test User',
            'email' => 'user@test.local',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $this->command->info('✅ Created: Test User (verified)');

        // ========================================
        // メール未認証ユーザー（メール認証フローテスト用）
        // ========================================

        $adminUnverified = User::create([
            'name' => 'Unverified Admin',
            'email' => 'unverified-admin@test.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => null, // 未認証
        ]);
        $this->command->info('✅ Created: Unverified Admin (needs email verification)');

        $userUnverified = User::create([
            'name' => 'Unverified User',
            'email' => 'unverified-user@test.local',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => null, // 未認証
        ]);
        $this->command->info('✅ Created: Unverified User (needs email verification)');

        $this->command->newLine();
        $this->command->info('✨ Development users created successfully!');
        $this->command->newLine();
        $this->command->warn('⚠️  These accounts are for DEVELOPMENT ONLY!');
        $this->command->warn('⚠️  Default password: password');
    }
}
