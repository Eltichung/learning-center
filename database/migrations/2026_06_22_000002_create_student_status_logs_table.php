<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Nhật ký bật/tắt hoạt động của học sinh (ai làm, lúc nào)
        Schema::create('student_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action'); // activate | deactivate
            $table->timestamps();

            $table->index(['student_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_logs');
    }
};
