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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade'); // 방 참조
            $table->dateTime('start_at'); // 예약 시작 시간
            $table->dateTime('end_at'); // 예약 종료 시간
            $table->string('key_code', 10); // 열쇠함 비밀번호 (4자리 숫자 등)
            $table->string('status')->default('confirmed'); // 상태: confirmed, cancelled
            $table->timestamps();
            
            // 인덱스: 동시성 제어 및 검색 성능 향상
            $table->index(['room_id', 'start_at', 'end_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
