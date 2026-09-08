<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            // Loại nghỉ khi type='off': 'normal' (nghỉ thường) | 'holiday' (nghỉ lễ). Null = nghỉ thường (dữ liệu cũ).
            $table->string('off_kind', 20)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn('off_kind');
        });
    }
};
