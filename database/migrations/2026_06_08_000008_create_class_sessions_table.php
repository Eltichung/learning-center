<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Mỗi buổi/tiết học THỰC TẾ của một lớp theo ngày
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            // regular | makeup | off (off = buổi nghỉ, không tính tiền)
            $table->string('type')->default('regular');
            // buổi học bù trỏ về buổi nghỉ tương ứng (tự tham chiếu)
            $table->foreignId('makeup_for_id')->nullable()
                  ->constrained('class_sessions')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
