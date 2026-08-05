<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /** Idempotent: chạy lại nhiều lần không tạo trùng. */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'trial',
                'name' => 'Dùng thử',
                'price' => 0,
                'limits' => ['classes' => 1, 'students' => 10],
                'is_public' => true,
            ],
            [
                'slug' => 'plus',
                'name' => 'Plus',
                'price' => 79000,
                'limits' => ['classes' => 3, 'students' => 30],
                'is_public' => true,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'price' => 179000,
                'limits' => ['classes' => 10, 'students' => 100],
                'is_public' => true,
            ],
            [
                'slug' => 'vip',
                'name' => 'Full VIP',
                'price' => 0, // không mua được, chỉ admin cấp
                'limits' => ['classes' => null, 'students' => null], // null = không giới hạn
                'is_public' => false,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'limits' => $p['limits'],
                    'is_active' => true,
                    'is_public' => $p['is_public'],
                ]
            );
        }
    }
}
