<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedTinyInteger('seat_no')->default(1)->after('room_id');
            $table->index(['room_id', 'seat_no', 'start_at', 'end_at'], 'reservations_room_seat_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_room_seat_time_idx');
            $table->dropColumn('seat_no');
        });
    }
};
