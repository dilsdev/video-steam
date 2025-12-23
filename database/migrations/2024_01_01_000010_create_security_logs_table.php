<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('ip_address', 45);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('details')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');
            $table->index(['event_type', 'ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
