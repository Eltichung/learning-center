<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Điểm danh từng học sinh trong mỗi buổi học
        Schema::create('student_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // present | absent | excused | makeup
            $table->string('status')->default('present');
            $table->decimal('session_units', 5, 2)->default(1); // số buổi quy đổi (buổi đôi = 2)
            // amount = session_units * price_per_session, CHỐT lúc điểm danh
            $table->unsignedInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['class_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_sessions');
    }
};
