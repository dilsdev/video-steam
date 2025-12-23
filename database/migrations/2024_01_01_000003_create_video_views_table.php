<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45);
            $table->string('session_id', 100);
            $table->string('user_agent')->nullable();
            $table->string('country', 5)->nullable();
            $table->boolean('is_member_view')->default(false);
            $table->boolean('is_counted')->default(false);
            $table->timestamp('created_at');
            $table->index(['video_id', 'is_counted', 'is_member_view']);
            $table->index(['ip_address', 'session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_views');
    }
};
