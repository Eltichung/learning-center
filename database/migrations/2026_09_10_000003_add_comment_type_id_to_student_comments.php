<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_comments', function (Blueprint $table) {
            // Loại nhận xét (tuỳ chọn) — null cho dữ liệu cũ / nhận xét không gắn loại
            $table->foreignId('comment_type_id')->nullable()->after('teacher_id')
                  ->constrained('comment_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('comment_type_id');
        });
    }
};
