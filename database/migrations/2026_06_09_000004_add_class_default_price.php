<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedInteger('default_price')->default(0)->after('subject'); // đơn giá/buổi mặc định của lớp
        });

        // Backfill: lấy giá enrollment đầu tiên của lớp (nếu có)
        DB::statement('UPDATE classes c SET default_price = COALESCE((SELECT cs.price_per_session FROM class_students cs WHERE cs.class_id = c.id ORDER BY cs.id LIMIT 1), 0)');
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('default_price');
        });
    }
};
