<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();                       // HC-PLUS-15-1728394857
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->unsignedInteger('amount');                          // VNĐ tổng đơn
            $table->unsignedTinyInteger('months')->default(1);
            // pending | approved | rejected | cancelled
            $table->string('status', 16)->default('pending');
            $table->timestamp('notified_at')->nullable();               // user báo đã CK
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->string('rejected_reason', 255)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_orders');
    }
};
