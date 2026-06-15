<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            // Thời điểm điểm danh được submit (null = chưa điểm danh)
            $table->timestamp('attendance_submitted_at')->nullable()->after('note');
        });

        // Nhật ký lịch sử mỗi lần submit điểm danh
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action')->default('submit'); // submit | resubmit
            $table->unsignedInteger('present_count')->default(0);
            $table->unsignedInteger('total_amount')->default(0);
            $table->json('snapshot')->nullable(); // [{student_id,status,amount}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn('attendance_submitted_at');
        });
    }
};
