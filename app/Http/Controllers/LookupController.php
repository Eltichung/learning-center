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
            return $this->respondError($request, 'code', 'Không tìm thấy học sinh với mã này.');
        }

        return $this->respondOk($request, 'Đã tìm thấy', route('parent.info', $code));
    }

    /* Thông tin học sinh */
    public function show(string $slug)
    {
        $student = $this->resolve($slug);

        // Đếm lượt phụ huynh tra cứu (bỏ qua khi chính GV đang đăng nhập mở preview).
        // increment() = 1 UPDATE atomic, an toàn khi nhiều PH mở cùng lúc.
        if (! auth()->check()) {
            $student->increment('lookup_count', 1, ['last_viewed_at' => now()]);
        }

        $info = $this->studentInfo($student);
        $weeks = $this->buildWeeks($student, 1, 1);

        return view('parent.info', array_merge($info, $this->feeOverview($student), [
            'slug' => $slug,
            'navActive' => 'p-info',
            'stageTitle' => 'Thông tin học sinh',
            'weeks' => $weeks,
            'weekIndex' => 1,
        ]));
    }

    /** Fragment: danh sách chi tiết học phí theo tháng (load thêm khi cuộn). */
    public function feeMonths(Request $request, string $slug)
    {
        $student = $this->resolve($slug);
        $perPage = 5;
        $page = max(0, (int) $request->get('page', 0));

        $alloc = $this->allocateByMonth($this->chargedByMonth($student), $this->paidByMonth($student));
        $monthsDesc = collect($alloc)->keys()->sortDesc()->values();

        $slice = $monthsDesc->slice($page * $perPage, $perPage)->values()->all();
        $months = $this->feeMonthDetail($student, $slice, $alloc);

        return response()->json([
            'html' => view('parent.partials.fee-months', compact('months'))->render(),
            'hasMore' => ($page + 1) * $perPage < $monthsDesc->count(),
        ]);
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

        // Lịch cố định (gộp các lớp), sắp theo thứ — KHÔNG gồm buổi học bù (một lần)
        $schedules = $classes->flatMap(fn ($c) => $c->schedules->map(fn ($s) => (object) [
            'weekday' => (int) $s->weekday,
            'start' => Carbon::parse($s->start_time)->format('H:i'),
            'end' => Carbon::parse($s->end_time)->format('H:i'),
            'class' => $c->name,
        ]))->sortBy('weekday')->values();

        // Buổi học bù sắp tới (one-off, tách riêng khỏi lịch cố định)
        $makeups = ClassSession::whereIn('class_id', $student->classStudents->pluck('class_id'))
            ->where('type', 'makeup')
            ->whereDate('date', '>=', now()->toDateString())
            ->with(['classroom', 'makeupFor'])
            ->orderBy('date')->orderBy('start_time')
            ->get()
            ->map(fn ($s) => (object) [
                'date' => Carbon::parse($s->date),
                'start' => $s->start_time ? Carbon::parse($s->start_time)->format('H:i') : '',
                'end' => $s->end_time ? Carbon::parse($s->end_time)->format('H:i') : '',
                'class' => $s->classroom?->name,
                'forDate' => $s->makeupFor ? Carbon::parse($s->makeupFor->date) : null,
            ]);

        // 3 nhận xét mới nhất của giáo viên
        $comments = $student->comments()->with('type')
            ->orderByDesc('comment_date')->orderByDesc('id')->limit(3)->get();

        // Giáo án tuần này (T2 → CN) — chỉ hiển thị ngày có title hoặc content
        $wkStart = now()->startOfWeek()->toDateString();
        $wkEnd = now()->endOfWeek()->toDateString();
        $lessons = ClassSession::whereIn('class_id', $student->classStudents->pluck('class_id'))
            ->whereBetween('date', [$wkStart, $wkEnd])
            ->where(fn ($q) => $q->whereNotNull('title')->orWhereNotNull('content'))
            ->orderBy('date')
            ->get()
            ->map(fn ($s) => (object) [
                'id' => $s->id,
                'date' => Carbon::parse($s->date),
                'title' => $s->title,
                'content' => $s->content,
                'submitted' => (bool) $s->attendance_submitted_at,
            ]);

        return [
            'student' => $student,
            'className' => $primaryClass?->name,
            'teacherName' => $student->teacher?->name,
            'balance' => $balance,
            'price' => $price,
            'unpaidSessions' => $unpaidSessions,
            'schedules' => $schedules,
            'makeups' => $makeups,
            'payments' => $student->payments,
            'comments' => $comments,
            'lessons' => $lessons,
            'showFees' => (bool) ($student->show_fees ?? true),
        ];
    }

    /* ===================== Học phí theo tháng ===================== */

    /** [Y-m => tiền phát sinh] từ buổi điểm danh (bỏ buổi xoá mềm). */
    private function chargedByMonth(Student $student)
    {
        return $student->studentSessions()
            ->join('class_sessions', 'student_sessions.class_session_id', '=', 'class_sessions.id')
            ->whereNull('class_sessions.deleted_at')
            ->selectRaw("DATE_FORMAT(class_sessions.date, '%Y-%m') ym, COALESCE(SUM(student_sessions.amount),0) amt")
            ->groupBy('ym')->pluck('amt', 'ym');
    }

    /** [Y-m => tiền đã đóng] theo paid_at. */
    private function paidByMonth(Student $student)
    {
        return $student->payments()
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') ym, COALESCE(SUM(amount),0) amt")
            ->groupBy('ym')->pluck('amt', 'ym');
    }

    /**
     * Phân bổ tiền đã đóng theo FIFO (trừ tháng cũ trước) lên các tháng CÓ phát sinh.
     * Trả [Y-m => (charged, paid = phần được trừ, owed)] — tổng owed = công nợ thực lũy kế.
     */
    private function allocateByMonth($charged, $paid): array
    {
        $months = $charged->keys()->sort()->values(); // tháng có phát sinh, cũ → mới
        $pool = (int) $paid->sum();                    // tổng đã đóng, phân bổ dần
        $alloc = [];
        foreach ($months as $ym) {
            $ch = (int) $charged[$ym];
            $covered = (int) min($pool, $ch);
            $alloc[$ym] = (object) ['charged' => $ch, 'paid' => $covered, 'owed' => $ch - $covered];
            $pool -= $covered;
        }

        return $alloc;
    }

    private function ymLabel(string $ym): string
    {
        [$y, $m] = explode('-', $ym);

        return 'Tháng ' . (int) $m . '/' . $y;
    }

    /** Tổng quan học phí (phân bổ FIFO) cho card + trang chi tiết. */
    private function feeOverview(Student $student, int $perPage = 5): array
    {
        $alloc = $this->allocateByMonth($this->chargedByMonth($student), $this->paidByMonth($student));
        $monthsDesc = collect($alloc)->keys()->sortDesc()->values(); // tháng có phát sinh, mới → cũ

        $curYm = now()->format('Y-m');
        $cur = $alloc[$curYm] ?? (object) ['charged' => 0, 'paid' => 0, 'owed' => 0];

        $unpaidMonths = $monthsDesc->filter(fn ($ym) => $alloc[$ym]->owed > 0)
            ->map(fn ($ym) => (object) [
                'ym' => $ym, 'label' => $this->ymLabel($ym),
                'owed' => (int) $alloc[$ym]->owed, 'isCurrent' => $ym === $curYm,
            ])->values();

        return [
            'feeCurrentLabel' => $this->ymLabel($curYm),
            'feeCurrentShort' => (int) explode('-', $curYm)[1] . '/' . explode('-', $curYm)[0],
            'feeCurrentCharged' => (int) $cur->charged,
            'feeCurrentPaid' => (int) $cur->paid,
            'feeCurrentOwed' => (int) $cur->owed,
            'feeUnpaidMonths' => $unpaidMonths,
            'feeTotalOwed' => (int) $unpaidMonths->sum('owed'),
            'feeMonths' => $this->feeMonthDetail($student, $monthsDesc->slice(0, $perPage)->all(), $alloc),
            'feeHasMore' => $monthsDesc->count() > $perPage,
        ];
    }

    /** Chi tiết từng tháng: buổi đi học (ngày/giờ) + buổi nghỉ + tiền (đã phân bổ FIFO). */
    private function feeMonthDetail(Student $student, array $monthKeys, array $alloc): array
    {
        if (empty($monthKeys)) {
            return [];
        }

        $rows = $student->studentSessions()
            ->join('class_sessions', 'student_sessions.class_session_id', '=', 'class_sessions.id')
            ->whereNull('class_sessions.deleted_at')
            ->whereIn(\DB::raw("DATE_FORMAT(class_sessions.date, '%Y-%m')"), $monthKeys)
            ->orderBy('class_sessions.date')->orderBy('class_sessions.start_time')
            ->get([
                'student_sessions.status', 'class_sessions.date',
                'class_sessions.start_time', 'class_sessions.end_time',
            ]);
        $byMonth = $rows->groupBy(fn ($r) => Carbon::parse($r->date)->format('Y-m'));

        $curYm = now()->format('Y-m');
        $out = [];
        foreach ($monthKeys as $ym) {
            $sess = $byMonth->get($ym, collect());
            $attended = $sess->whereIn('status', ['present', 'makeup'])
                ->map(fn ($r) => (object) [
                    'date' => Carbon::parse($r->date),
                    'start' => $r->start_time ? Carbon::parse($r->start_time)->format('H:i') : '',
                    'end' => $r->end_time ? Carbon::parse($r->end_time)->format('H:i') : '',
                    'makeup' => $r->status === 'makeup',
                ])->values();
            $absent = $sess->whereIn('status', ['absent', 'excused'])
                ->map(fn ($r) => (object) [
                    'date' => Carbon::parse($r->date),
                    'start' => $r->start_time ? Carbon::parse($r->start_time)->format('H:i') : '',
                    'excused' => $r->status === 'excused',
                ])->values();
            $a = $alloc[$ym] ?? (object) ['charged' => 0, 'paid' => 0, 'owed' => 0];
            $out[] = (object) [
                'ym' => $ym, 'label' => $this->ymLabel($ym), 'isCurrent' => $ym === $curYm,
                'charged' => (int) $a->charged, 'paid' => (int) $a->paid, 'owed' => (int) $a->owed,
                'attended' => $attended, 'absent' => $absent,
            ];
        }

        return $out;
    }

    /** Sinh dữ liệu lưới tuần (khớp shape parent-week.js). */
    private function buildWeeks(Student $student, int $back, int $fwd): array
    {
        $classIds = $student->classStudents->pluck('class_id');

        // Sessions grouped by date, sort theo giờ để hiển thị ca sớm trước
        $sessByDate = [];
        foreach (ClassSession::whereIn('class_id', $classIds)->orderBy('start_time')->get() as $s) {
            $sessByDate[Carbon::parse($s->date)->toDateString()][] = $s;
        }
        // Attendance keyed theo class_session_id (không phải date) — mỗi ca có bản ghi riêng
        $attBySession = [];
        foreach ($student->studentSessions()->get() as $a) {
            $attBySession[(int) $a->class_session_id] = $a->status;
        }
        // Số ca theo từng weekday để hiển thị "sắp học" đúng số lượng cho ngày tương lai
        $wdCounts = ClassSchedule::whereIn('class_id', $classIds)
            ->get()->groupBy(fn ($s) => (int) $s->weekday)->map->count()->all();
        $firstSched = ClassSchedule::whereIn('class_id', $classIds)->orderBy('start_time')->first();
        $time = $firstSched ? Carbon::parse($firstSched->start_time)->format('H:i') : '';
        $subj = optional($student->classStudents->first()?->classroom)->name ?? '';

        $today = now()->startOfDay();
        $weeks = [];
        for ($w = -$back; $w <= $fwd; $w++) {
            $monday = now()->startOfWeek()->addWeeks($w)->startOfDay();
            $days = $mo = $st = $times = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $monday->copy()->addDays($i);
                $ds = $day->toDateString();
                $days[] = $day->format('d');
                $mo[] = $day->format('m');

                $statuses = [];
                $daySlotTimes = [];
                if (! empty($sessByDate[$ds])) {
                    foreach ($sessByDate[$ds] as $sess) {
                        $statuses[] = match ($sess->type) {
                            'off' => ($sess->off_kind === 'holiday' ? 'holiday' : 'off'),
                            'makeup' => 'makeup',
                            default => match ($attBySession[(int) $sess->id] ?? 'present') {
                                'excused' => 'excused',
                                'absent' => 'absent',
                                default => 'present',
                            },
                        };
                        $daySlotTimes[] = Carbon::parse($sess->start_time)->format('H:i');
                    }
                } elseif ($day->gt($today)) {
                    $wd = $day->dayOfWeekIso;
                    $n = (int) ($wdCounts[$wd] ?? 0);
                    for ($k = 0; $k < $n; $k++) {
                        $statuses[] = 'study';
                    }
                }
                // Rỗng → giữ null để giao diện render ô "không có buổi"
                $st[] = empty($statuses) ? null : $statuses;
                $times[] = $daySlotTimes;
            }
            $weeks[] = [
                'label' => 'Tuần ' . $monday->format('d') . ' – ' . $monday->copy()->addDays(6)->format('d/m/Y'),
                'days' => $days,
                'mo' => $mo,
                'st' => $st,
                'times' => $times,
                'time' => $time,
                'subj' => $subj,
            ];
        }

        return $weeks;
    }
}
