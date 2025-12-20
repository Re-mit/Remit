<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lockbox_urls', function (Blueprint $table) {
            $table->id();
            $table->date('start_date')->unique();   // 3일 구간 시작일
            $table->date('end_date');               // 3일 구간 종료일
            $table->text('url');
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lockbox_urls');
    }
};


