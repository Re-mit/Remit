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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'reservation_reminder', 'notice' 등
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('related_id')->nullable(); // reservation_id 등
            $table->string('related_type')->nullable(); // 'App\Models\Reservation' 등
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
