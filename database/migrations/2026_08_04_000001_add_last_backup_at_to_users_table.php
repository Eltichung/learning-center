<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thời điểm sao lưu dữ liệu gần nhất — dùng để giới hạn 1 lần/ngày.
            $table->timestamp('last_backup_at')->nullable()->after('qr_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_backup_at');
        });
    }
};
