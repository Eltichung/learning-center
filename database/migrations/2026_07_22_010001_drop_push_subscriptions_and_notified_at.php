<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('push_subscriptions');

        if (Schema::hasColumn('class_sessions', 'notified_at')) {
            Schema::table('class_sessions', function (Blueprint $table) {
                $table->dropIndex(['notified_at']);
                $table->dropColumn('notified_at');
            });
        }
    }

    public function down(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('endpoint', 500)->unique();
            $table->string('p256dh', 255);
            $table->string('auth', 255);
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index('student_id');
        });
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('attendance_submitted_at');
            $table->index('notified_at');
        });
    }
};
