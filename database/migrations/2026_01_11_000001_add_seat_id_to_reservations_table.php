<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('seat_id')->nullable()->after('room_id')->constrained('seats')->nullOnDelete();
            $table->index(['room_id', 'seat_id', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['room_id', 'seat_id', 'start_at', 'end_at']);
            $table->dropConstrainedForeignId('seat_id');
        });
    }
};


