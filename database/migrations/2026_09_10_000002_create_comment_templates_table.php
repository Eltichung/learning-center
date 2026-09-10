<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comment_templates', function (Blueprint $table) {
            $table->id();
            // null = mẫu hệ thống; có giá trị = mẫu riêng của giáo viên
            $table->foreignId('teacher_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('comment_type_id')->nullable()->constrained('comment_types')->nullOnDelete();
            $table->string('key', 60)->nullable();          // định danh mẫu hệ thống (seed idempotent)
            $table->text('body');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['teacher_id', 'comment_type_id']);
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_templates');
    }
};
