<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentSession;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Gói cước ----
        $free = Plan::create(['name' => 'Free', 'price' => 0,
            'limits' => ['max_classes' => 1, 'max_students' => 10]]);
        $pro  = Plan::create(['name' => 'Pro', 'price' => 149000,
            'limits' => ['max_classes' => null, 'max_students' => null]]);

        // ---- Giáo viên demo: Cô Lan ----
        $teacher = User::create([
            'name' => 'Cô Lan',
            'email' => 'colan@email.com',
            'phone' => '0900000821',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'account_prefix' => 'co-lan',
            'status' => 'active',
        ]);
        $teacher->update(['tenant_id' => $teacher->id]); // owner tự trỏ về chính mình

        Subscription::create([
            'tenant_id' => $teacher->id,
            'plan_id' => $pro->id,
            'status' => 'active',
            'started_at' => now()->subMonths(3),
            'current_period_end' => now()->addMonth(),
        ]);

        // ---- Lớp Toán 9 — Nhóm A ----
        $class = Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => 'Toán 9 — Nhóm A',
            'type' => 'group',
            'grade' => 9,
            'subject' => 'Toán',
            'status' => 'active',
        ]);
        // Lịch cố định: T2, T4, T6 (1=T2 ... 7=CN)
        foreach ([1, 3, 5] as $wd) {
            $class->schedules()->create(['weekday' => $wd, 'start_time' => '17:30', 'end_time' => '19:00']);
        }

        // ---- Học sinh + ghi danh (đơn giá/buổi riêng) ----
        $seed = [
            ['Nguyễn Bảo An', 'an-toan9', 120000],
            ['Trần Gia Hân',  'han-9a',   120000],
            ['Lê Minh',       'le-minh',  100000],
            ['Phạm Đức',      'duc-toan', 120000],
        ];
        $students = [];
        foreach ($seed as [$name, $code, $price]) {
            $st = Student::create([
                'teacher_id' => $teacher->id,
                'full_name' => $name,
                'student_code' => $code,
                'parent_phone' => '09xxxxx' . rand(100, 999),
                'status' => 'active',
            ]);
            ClassStudent::create([
                'student_id' => $st->id,
                'class_id' => $class->id,
                'price_per_session' => $price,
                'joined_at' => now()->subMonths(2),
                'status' => 'active',
            ]);
            $students[] = $st;
        }

        // ---- Vài buổi học + điểm danh ----
        $dates = [now()->subDays(6), now()->subDays(4), now()->subDays(2)];
        foreach ($dates as $dt) {
            $session = ClassSession::create([
                'class_id' => $class->id,
                'date' => $dt->toDateString(),
                'start_time' => '17:30',
                'end_time' => '19:00',
                'type' => 'regular',
            ]);
            foreach ($students as $st) {
                $price = ClassStudent::where('student_id', $st->id)
                    ->where('class_id', $class->id)->value('price_per_session');
                // cho Lê Minh nghỉ phép buổi giữa để có dữ liệu công nợ đa dạng
                $absent = ($st->full_name === 'Lê Minh' && $dt->equalTo($dates[1]));
                StudentSession::create([
                    'class_session_id' => $session->id,
                    'student_id' => $st->id,
                    'status' => $absent ? 'excused' : 'present',
                    'session_units' => 1,
                    'amount' => $absent ? 0 : $price,
                ]);
            }
        }

        // ---- Một buổi nghỉ + buổi học bù ----
        $off = ClassSession::create([
            'class_id' => $class->id, 'date' => now()->subDays(8)->toDateString(),
            'type' => 'off', 'note' => 'Nghỉ lễ',
        ]);
        ClassSession::create([
            'class_id' => $class->id, 'date' => now()->subDays(1)->toDateString(),
            'start_time' => '14:00', 'end_time' => '15:30',
            'type' => 'makeup', 'makeup_for_id' => $off->id, 'note' => 'Học bù buổi nghỉ lễ',
        ]);

        // ---- Lịch sử đóng tiền ----
        Payment::create([
            'student_id' => $students[0]->id, 'teacher_id' => $teacher->id,
            'amount' => 1200000, 'paid_at' => now()->subMonth(),
            'method' => 'transfer', 'note' => 'Học phí tháng trước',
        ]);

        $this->command->info('Demo data đã tạo. Đăng nhập: colan@email.com / password');
    }
}
