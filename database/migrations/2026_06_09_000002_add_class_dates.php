<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('subject');   // ngày bắt đầu (khai giảng)
            $table->date('ended_at')->nullable()->after('status');      // ngày kết thúc (khi tạm dừng)
        });

        // Backfill ngày bắt đầu cho lớp đã có = ngày tạo
        DB::statement('UPDATE classes SET start_date = DATE(created_at) WHERE start_date IS NULL');
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'ended_at']);
        });
    }
};
