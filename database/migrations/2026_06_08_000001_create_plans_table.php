<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Free / Pro / Trung tâm
            $table->unsignedInteger('price')->default(0); // VNĐ / tháng
            $table->json('limits')->nullable();           // {max_classes, max_students, ...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
