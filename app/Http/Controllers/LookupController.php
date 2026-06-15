<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LookupController extends Controller
{
    /* Trang tra cứu (nhập mã) */
    public function search()
    {
        return view('parent.search', [
            'navActive' => 'p-search',
            'stageTitle' => 'Trang tra cứu phụ huynh',
        ]);
    }

    public function find(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);
        $code = trim($data['code']);

        $student = Student::where('student_code', $code)->first();
        if (! $student) {
            return back()->withErrors(['code' => 'Không tìm thấy học sinh với mã này.'])->onlyInput('code');
        }

        return redirect()->route('parent.info', $code);
    }

    /* Thông tin học sinh */
    public function show(string $slug)
    {
        $student = $this->resolve($slug);
        $info = $this->studentInfo($student);
        $weeks = $this->buildWeeks($student, 1, 1);

        return view('parent.info', array_merge($info, [
            'slug' => $slug,
            'navActive' => 'p-info',
            'stageTitle' => 'Thông tin học sinh',
            'weeks' => $weeks,
            'weekIndex' => 1,
        ]));
    }

    /* Lịch sử học (theo tuần) */
    public function history(string $slug)
    {
        $student = $this->resolve($slug);
        $info = $this->studentInfo($student);
        $weeks = $this->buildWeeks($student, 3, 1);

        return view('parent.history', array_merge($info, [
            'slug' => $slug,
            'navActive' => 'p-history',
            'stageTitle' => 'Lịch sử học (theo tuần)',
            'weeks' => $weeks,
            'weekIndex' => 3,
        ]));
    }

    /* ===================== Helpers ===================== */

    private function resolve(string $slug): Student
    {
        return Student::where('student_code', $slug)
            ->with(['classStudents.classroom.schedules', 'teacher', 'payments' => fn ($q) => $q->orderByDesc('paid_at')])
            ->firstOrFail();
    }

    /** Thông tin chung dùng cho cả info + history. */
    private function studentInfo(Student $student): array
    {
        $classes = $student->classStudents->map(fn ($cs) => $cs->classroom)->filter();
        $primaryClass = $classes->first();
        $price = (int) ($student->classStudents->min('price_per_session') ?: 0);

        $balance = $student->balanceDue();
        $unpaidSessions = $price > 0 ? (int) round(max($balance, 0) / $price) : 0;

        // Lịch cố định (gộp các lớp), sắp theo thứ
        $schedules = $classes->flatMap(fn ($c) => $c->schedules->map(fn ($s) => (object) [
            'weekday' => (int) $s->weekday,
            'start' => Carbon::parse($s->start_time)->format('H:i'),
            'end' => Carbon::parse($s->end_time)->format('H:i'),
            'class' => $c->name,
        ]))->sortBy('weekday')->values();

        return [
            'student' => $student,
            'className' => $primaryClass?->name,
            'teacherName' => $student->teacher?->name,
            'balance' => $balance,
            'price' => $price,
            'unpaidSessions' => $unpaidSessions,
            'schedules' => $schedules,
            'payments' => $student->payments,
        ];
    }

    /** Sinh dữ liệu lưới tuần (khớp shape parent-week.js). */
    private function buildWeeks(Student $student, int $back, int $fwd): array
    {
        $classIds = $student->classStudents->pluck('class_id');

        $sessByDate = [];
        foreach (ClassSession::whereIn('class_id', $classIds)->get() as $s) {
            $sessByDate[Carbon::parse($s->date)->toDateString()][] = $s;
        }
        $attByDate = [];
        foreach ($student->studentSessions()->with('classSession')->get() as $a) {
            $d = $a->classSession ? Carbon::parse($a->classSession->date)->toDateString() : null;
            if ($d) {
                $attByDate[$d] = $a->status;
            }
        }
        $schedWds = ClassSchedule::whereIn('class_id', $classIds)->pluck('weekday')->map(fn ($w) => (int) $w)->unique()->all();
        $firstSched = ClassSchedule::whereIn('class_id', $classIds)->orderBy('start_time')->first();
        $time = $firstSched ? Carbon::parse($firstSched->start_time)->format('H:i') : '';
        $subj = optional($student->classStudents->first()?->classroom)->name ?? '';

        $today = now()->startOfDay();
        $weeks = [];
        for ($w = -$back; $w <= $fwd; $w++) {
            $monday = now()->startOfWeek()->addWeeks($w)->startOfDay();
            $days = $mo = $st = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $monday->copy()->addDays($i);
                $ds = $day->toDateString();
                $days[] = $day->format('d');
                $mo[] = $day->format('m');

                $status = null;
                if (! empty($sessByDate[$ds])) {
                    $sess = $sessByDate[$ds][0];
                    $status = match ($sess->type) {
                        'off' => 'off',
                        'makeup' => 'makeup',
                        default => match ($attByDate[$ds] ?? 'present') {
                            'excused' => 'excused',
                            'absent' => 'absent',
                            default => 'present',
                        },
                    };
                } elseif ($day->gt($today) && in_array($day->dayOfWeekIso, $schedWds, true)) {
                    $status = 'study';
                }
                $st[] = $status;
            }
            $weeks[] = [
                'label' => 'Tuần ' . $monday->format('d') . ' – ' . $monday->copy()->addDays(6)->format('d/m/Y'),
                'days' => $days,
                'mo' => $mo,
                'st' => $st,
                'time' => $time,
                'subj' => $subj,
            ];
        }

        return $weeks;
    }
}
