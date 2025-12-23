<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('slug', 10)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filename');
            $table->string('original_name')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('mime_type')->default('video/mp4');
            $table->bigInteger('file_size')->default(0);
            $table->integer('duration')->default(0);
            $table->string('status')->default('ready');
            $table->bigInteger('total_views')->default(0);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status', 'is_public']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
