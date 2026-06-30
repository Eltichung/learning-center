<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Nhật ký sửa đơn giá học sinh trong từng lớp
        Schema::create('class_student_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedInteger('old_price');
            $table->unsignedInteger('new_price');
            $table->timestamps();

            $table->index(['class_id', 'student_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_student_price_logs');
    }
};
