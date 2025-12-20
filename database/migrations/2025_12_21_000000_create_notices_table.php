<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_user_id')->constrained('users')->onDelete('cascade');
            $table->string('author_email');
            $table->string('title');
            $table->text('message');
            $table->timestamps();

            $table->index(['author_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};


