<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_tokens', function (Blueprint $table) {
            $table->string('token', 64)->primary();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('session_id', 100);
            $table->boolean('ad_watched')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['token', 'expires_at', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_tokens');
    }
};
