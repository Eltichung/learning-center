<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->string('title', 150)->nullable()->after('end_time');
            $table->text('content')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn(['title', 'content']);
        });
    }
};
