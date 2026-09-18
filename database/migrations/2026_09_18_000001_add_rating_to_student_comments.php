<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_comments', function (Blueprint $table) {
            // Mức đánh giá của nhận xét: tot | kha | tb (nullable — không bắt buộc).
            $table->string('rating', 8)->nullable()->after('comment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_comments', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
