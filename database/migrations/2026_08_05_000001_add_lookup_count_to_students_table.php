<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Số lần phụ huynh mở trang tra cứu /search/{mã} + lần xem gần nhất.
            $table->unsignedInteger('lookup_count')->default(0)->after('show_fees');
            $table->timestamp('last_viewed_at')->nullable()->after('lookup_count');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['lookup_count', 'last_viewed_at']);
        });
    }
};
