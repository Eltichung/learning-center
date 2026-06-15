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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        /* ============ Gói cước ============ */
        $free = Plan::create(['name' => 'Free', 'price' => 0,
            'limits' => ['max_classes' => 1, 'max_students' => 10]]);
        $pro = Plan::create(['name' => 'Pro', 'price' => 149000,
            'limits' => ['max_classes' => null, 'max_students' => null]]);

        /* ============ Giáo viên demo: Cô Lan ============ */
        $teacher = User::create([
            'name' => 'Cô Lan',
            'email' => 'colan@email.com',
            'phone' => '0900000821',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'account_prefix' => 'co-lan',
            'status' => 'active',
        ]);
        $teacher->update(['tenant_id' => $teacher->id]);

        Subscription::create([
            'tenant_id' => $teacher->id,
            'plan_id' => $pro->id,
            'status' => 'active',
            'started_at' => now()->subMonths(3),
            'current_period_end' => now()->addMonth(),
        ]);

        /* ============ Lớp + lịch cố định (weekday 1=T2 ... 7=CN) ============ */
        $classDefs = [
            'toan9a'   => ['Toán 9 — Nhóm A', 'group', 9, 'Toán', 'active', [[1, '17:30', '19:00'], [3, '17:30', '19:00'], [5, '17:30', '19:00']]],
            'ly12b'    => ['Lý 12 — Nhóm B', 'group', 12, 'Lý', 'active', [[1, '19:15', '20:45'], [4, '19:15', '20:45']]],
            'gisuAn'   => ['Gia sư 1-1 — Bé An', 'tutor_1on1', 9, 'Toán', 'active', [[1, '14:00', '15:30'], [6, '14:00', '15:30']]],
            'van8c'    => ['Văn 8 — Nhóm C', 'group', 8, 'Văn', 'active', [[2, '18:00', '19:30'], [5, '18:00', '19:30']]],
            'anh6d'    => ['Anh 6 — Nhóm D', 'group', 6, 'Anh', 'active', [[3, '16:00', '17:30'], [6, '16:00', '17:30']]],
            'gisuMinh' => ['Gia sư 1-1 — Bé Minh', 'tutor_1on1', 11, 'Toán', 'paused', [[7, '09:00', '10:30']]],
        ];

        /** @var array<string,Classroom> $classes */
        $classes = [];
        foreach ($classDefs as $key => [$name, $type, $grade, $subject, $status, $sched]) {
            $c = Classroom::create([
                'teacher_id' => $teacher->id, 'name' => $name, 'type' => $type,
                'grade' => $grade, 'subject' => $subject, 'status' => $status,
            ]);
            foreach ($sched as [$wd, $st, $en]) {
                $c->schedules()->create(['weekday' => $wd, 'start_time' => $st, 'end_time' => $en]);
            }
            $classes[$key] = $c;
        }

        /* ============ Buổi học thực tế (4 tuần gần nhất) cho lớp đang hoạt động ============ */
        $start = now()->subWeeks(4)->startOfWeek();
        $today = now();

        /** @var array<int,Collection> $sessByClassId */
        $sessByClassId = [];
        foreach ($classes as $c) {
            if ($c->status !== 'active') {
                $sessByClassId[$c->id] = collect();
                continue;
            }
            $scheds = $c->schedules()->get()->keyBy('weekday');
            $list = collect();
            for ($d = $start->copy(); $d->lte($today); $d->addDay()) {
                $wd = $d->dayOfWeekIso; // 1=Mon..7=Sun
                if ($scheds->has($wd)) {
                    $sc = $scheds[$wd];
                    $list->push(ClassSession::create([
                        'class_id' => $c->id,
                        'date' => $d->toDateString(),
                        'start_time' => $sc->start_time,
                        'end_time' => $sc->end_time,
                        'type' => 'regular',
                    ]));
                }
            }
            $sessByClassId[$c->id] = $list;
        }

        // Buổi nghỉ + học bù cho Toán 9 — Nhóm A
        $off = ClassSession::create([
            'class_id' => $classes['toan9a']->id,
            'date' => now()->subDays(9)->toDateString(),
            'type' => 'off', 'note' => 'Nghỉ lễ',
        ]);
        $makeup = ClassSession::create([
            'class_id' => $classes['toan9a']->id,
            'date' => now()->subDays(2)->toDateString(),
            'start_time' => '14:00', 'end_time' => '15:30',
            'type' => 'makeup', 'makeup_for_id' => $off->id, 'note' => 'Học bù buổi nghỉ lễ',
        ]);
        $sessByClassId[$classes['toan9a']->id]->push($makeup);

        /* ============ Học sinh + ghi danh (đơn giá/buổi riêng) ============ */
        $debtTargets = [];  // student_id => công nợ mong muốn
        $enrollPrice = [];  // student_id => đơn giá chính (để suy "số buổi chưa đóng")

        $makeStudent = fn (string $name, string $code, string $school, string $phone) => Student::create([
            'teacher_id' => $teacher->id, 'full_name' => $name, 'student_code' => $code,
            'school' => $school ?: null, 'parent_phone' => $phone, 'status' => 'active',
        ]);
        $enroll = fn (Student $s, Classroom $c, int $price) => ClassStudent::create([
            'student_id' => $s->id, 'class_id' => $c->id, 'price_per_session' => $price,
            'joined_at' => now()->subMonths(2), 'status' => 'active',
        ]);

        // --- Học sinh có hồ sơ cụ thể (khớp các màn chi tiết) ---
        $an = $makeStudent('Nguyễn Bảo An', 'an-toan9', 'THCS Lê Quý Đôn', '0900000821');
        $enroll($an, $classes['toan9a'], 120000);
        $enroll($an, $classes['gisuAn'], 200000);
        $debtTargets[$an->id] = 480000; $enrollPrice[$an->id] = 120000;

        $han = $makeStudent('Trần Gia Hân', 'han-9a', '', '0900000312');
        $enroll($han, $classes['toan9a'], 120000);
        $debtTargets[$han->id] = 0; $enrollPrice[$han->id] = 120000;

        $lem = $makeStudent('Lê Minh', 'le-minh', '', '0900000905');
        $enroll($lem, $classes['toan9a'], 100000);
        $debtTargets[$lem->id] = 300000; $enrollPrice[$lem->id] = 100000;

        $duc = $makeStudent('Phạm Đức', 'duc-toan', '', '0900000447');
        $enroll($duc, $classes['toan9a'], 120000);
        $debtTargets[$duc->id] = 0; $enrollPrice[$duc->id] = 120000;

        $khanh = $makeStudent('Vũ Khánh', 'khanh-ly12', '', '0900000158');
        $enroll($khanh, $classes['ly12b'], 150000);
        $debtTargets[$khanh->id] = 600000; $enrollPrice[$khanh->id] = 150000;

        // --- Học sinh tạo hàng loạt cho đủ sĩ số mỗi lớp ---
        $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Phan', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];
        $dem = ['Văn', 'Thị', 'Gia', 'Minh', 'Khánh', 'Hoài', 'Thuỳ', 'Đăng', 'Kim', 'Thái', 'Bảo', 'Ngọc', 'Quốc', 'Hữu'];
        $ten = ['Bình', 'Châu', 'Dũng', 'Giang', 'Hà', 'Khoa', 'Lan', 'Mai', 'Nam', 'Oanh', 'Phúc', 'Quân', 'Sơn', 'Trang', 'Uyên', 'Vy', 'Yến', 'Khôi', 'Ngân', 'Thư', 'Linh', 'Quỳnh', 'Đạt', 'Tú'];
        $gi = 0;

        $fillClass = function (string $classKey, int $count, int $price) use (&$gi, $ho, $dem, $ten, $classes, $makeStudent, $enroll, &$debtTargets, &$enrollPrice) {
            for ($k = 0; $k < $count; $k++, $gi++) {
                $name = $ho[$gi % count($ho)] . ' ' . $dem[($gi * 3 + 1) % count($dem)] . ' ' . $ten[($gi * 5 + 2) % count($ten)];
                $code = Str::slug($name) . '-' . ($gi + 1);
                $phone = '09' . str_pad((string) (($gi * 137 + 311) % 100000000), 8, '0', STR_PAD_LEFT);
                $s = $makeStudent($name, $code, '', $phone);
                $enroll($s, $classes[$classKey], $price);
                // Cứ 3 em có 1 em đã đóng đủ; còn lại nợ 1–4 buổi
                $debtTargets[$s->id] = ($gi % 3 === 0) ? 0 : ((($gi % 4) + 1) * $price);
                $enrollPrice[$s->id] = $price;
            }
        };
        $fillClass('toan9a', 4, 120000);
        $fillClass('ly12b', 5, 150000);
        $fillClass('van8c', 10, 110000);
        $fillClass('anh6d', 9, 100000);
        $fillClass('gisuMinh', 1, 250000);

        /* ============ Điểm danh: mọi enrollment × sessions của lớp ============ */
        $chargedByStudent = [];
        foreach (ClassStudent::all() as $en) {
            foreach (($sessByClassId[$en->class_id] ?? collect()) as $sess) {
                if ($sess->type === 'off') {
                    continue; // buổi nghỉ không tính tiền, không điểm danh
                }
                StudentSession::create([
                    'class_session_id' => $sess->id,
                    'student_id' => $en->student_id,
                    'status' => 'present',
                    'session_units' => 1,
                    'amount' => $en->price_per_session,
                ]);
                $chargedByStudent[$en->student_id] = ($chargedByStudent[$en->student_id] ?? 0) + $en->price_per_session;
            }
        }

        /* ============ Đóng tiền: tạo payment để công nợ = mục tiêu ============ */
        foreach (Student::all() as $s) {
            $charged = $chargedByStudent[$s->id] ?? 0;
            $debt = min($debtTargets[$s->id] ?? 0, $charged);
            $toPay = $charged - $debt;
            if ($toPay <= 0) {
                continue;
            }

            if ($s->id === $an->id) {
                // Tách 2 lần đóng để có lịch sử
                $p1 = (int) (round($toPay * 0.55 / 1000) * 1000);
                Payment::create(['student_id' => $s->id, 'teacher_id' => $teacher->id, 'amount' => $p1,
                    'paid_at' => now()->subMonth(), 'method' => 'transfer', 'note' => 'Học phí tháng trước']);
                Payment::create(['student_id' => $s->id, 'teacher_id' => $teacher->id, 'amount' => $toPay - $p1,
                    'paid_at' => now()->subMonths(2), 'method' => 'cash', 'note' => 'Học phí 2 tháng trước']);
            } else {
                Payment::create(['student_id' => $s->id, 'teacher_id' => $teacher->id, 'amount' => $toPay,
                    'paid_at' => now()->subDays(($s->id * 7) % 38 + 2),
                    'method' => ($s->id % 2 === 0) ? 'transfer' : 'cash', 'note' => 'Học phí']);
            }
        }

        $this->command->info('Demo data đã tạo. Đăng nhập: colan@email.com / password');
    }
}
