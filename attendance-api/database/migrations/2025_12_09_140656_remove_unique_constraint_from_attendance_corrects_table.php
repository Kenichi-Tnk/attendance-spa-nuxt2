<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueConstraintFromAttendanceCorrectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_corrects', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['user_id']);
            // ユニーク制約を削除
            $table->dropUnique(['user_id', 'date']);
            // 外部キー制約を再作成
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_corrects', function (Blueprint $table) {
            // ロールバック時は逆の順序で処理
            $table->dropForeign(['user_id']);
            $table->unique(['user_id', 'date']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
