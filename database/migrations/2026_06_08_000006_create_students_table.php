<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('full_name');
            $table->date('dob')->nullable();
            $table->string('school')->nullable();
            $table->string('parent_phone')->nullable();
            // mã tra cứu phụ huynh dùng — unique theo từng giáo viên
            $table->string('student_code');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['teacher_id', 'student_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
