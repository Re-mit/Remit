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
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null'); // 예약과 연결
            $table->dateTime('entry_at'); // 입실 시간
            $table->dateTime('exit_at')->nullable(); // 퇴실 시간 (null이면 아직 사용 중)
            $table->integer('duration')->nullable(); // 사용 시간 (초 단위)
            $table->timestamps();
            
            // 인덱스: 사용자별 사용 시간 조회 성능 향상
            $table->index(['user_id', 'entry_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
