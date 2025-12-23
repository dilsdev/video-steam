<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('payment_method');
            $table->string('payment_account');
            $table->string('payment_name');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('proof_file')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
