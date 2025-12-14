<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 開発用ユーザーを作成
        // 本番環境では実行されません
        $this->call([
            DevelopmentSeeder::class,
        ]);
    }
}
