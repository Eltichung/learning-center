<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Bảng nối học sinh <-> lớp, KÈM đơn giá/buổi riêng của từng học sinh
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedInteger('price_per_session')->default(0); // VNĐ / buổi
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['student_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_students');
    }
};
