<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 3번 좌석(단체석) 예약 시 동반 이용자 이메일 목록 (최대 4명)
            $table->json('group_member_emails')->nullable()->after('seat_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('group_member_emails');
        });
    }
};


