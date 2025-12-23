<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->integer('views_count');
            $table->decimal('cpm_rate', 10, 2);
            $table->decimal('amount', 15, 2);
            $table->date('calculation_date');
            $table->timestamps();
            $table->unique(['video_id', 'calculation_date']);
            $table->index(['user_id', 'calculation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
