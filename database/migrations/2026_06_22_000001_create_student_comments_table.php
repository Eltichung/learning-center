<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Nhận xét của giáo viên về học sinh, lưu theo từng ngày
        Schema::create('student_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('comment_date');          // ngày của nhận xét
            $table->text('body');                  // nội dung nhận xét
            $table->timestamps();

            $table->index(['student_id', 'comment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_comments');
    }
};
