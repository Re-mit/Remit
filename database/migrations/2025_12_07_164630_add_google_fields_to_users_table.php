<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('id');
            $table->string('role')->default('user')->after('email'); // user, admin
            $table->integer('warning')->default(0)->after('role'); // 경고 횟수
            
            // password를 nullable로 변경 (Google OAuth 사용 시 비밀번호 불필요)
            $table->string('password')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'role', 'warning']);
        });

    }
};
