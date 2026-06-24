<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /** Idempotent: chạy lại nhiều lần không tạo trùng. */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Quản trị hệ thống',
                'password' => 'trangvachung', // tự hash nhờ cast 'hashed'
                'role'     => 'super_admin',
                'status'   => 'active',
            ]
        );

        // Đảm bảo đúng quyền/trạng thái kể cả khi tài khoản đã tồn tại
        $admin->update(['role' => 'super_admin', 'status' => 'active']);
    }
}
