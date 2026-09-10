<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comment_types', function (Blueprint $table) {
            $table->id();
            // null = loại hệ thống (dùng chung); có giá trị = loại riêng của giáo viên
            $table->foreignId('teacher_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('key', 40)->nullable();          // định danh loại hệ thống (seed idempotent)
            $table->string('name', 60);
            $table->string('icon', 8)->nullable();          // emoji
            $table->string('color', 12)->default('n');      // hậu tố class chip: b/a/g/p/r/n
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['teacher_id', 'is_active']);
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_types');
    }
};
