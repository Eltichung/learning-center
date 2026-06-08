<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            // owner | assistant | super_admin
            $table->string('role')->default('owner')->after('phone');
            // tenant_id = id của giáo viên chủ tài khoản (chính nó nếu là owner)
            $table->foreignId('tenant_id')->nullable()->after('role')
                  ->constrained('users')->nullOnDelete();
            // tiền tố URL trang phụ huynh, vd: co-lan  => lopthem.vn/co-lan/{student_code}
            $table->string('account_prefix')->nullable()->unique()->after('tenant_id');
            $table->string('status')->default('active')->after('account_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['phone', 'role', 'account_prefix', 'status']);
        });
    }
};
