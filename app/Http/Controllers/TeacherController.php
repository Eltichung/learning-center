<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\ClassStudent;
use App\Models\ClassStudentPriceLog;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentComment;
use App\Models\StudentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    private function tid(): int
    {
        return (int) auth()->id();
    }

    /* ===================== Tổng quan ===================== */
    public function dashboard()
    {
        $tid = $this->tid();

        $classesActive = Classroom::where('teacher_id', $tid)->where('status', 'active')->count();
        $studentsCount = Student::where('teacher_id', $tid)->count();

        // Buổi hôm nay: lớp active có lịch cố định trùng thứ hôm nay HOẶC có buổi (bù/thường) đúng ngày hôm nay
        // (chỉ tính lớp đã khai giảng: start_date <= hôm nay)
        $todayWd = now()->dayOfWeekIso;
        $todayDate = now()->toDateString();
        $todayClasses = Classroom::where('teacher_id', $tid)->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', $todayDate))
            ->where(function ($q) use ($todayWd, $todayDate) {
                $q->whereHas('schedules', fn ($x) => $x->where('weekday', $todayWd))
                  ->orWhereHas('sessions', fn ($x) => $x->whereDate('date', $todayDate));
            })
            ->with(['schedules' => fn ($q) => $q->where('weekday', $todayWd)])
            ->withCount('classStudents')
            ->get()
            ->map(function ($c) use ($todayDate) {
                // "Đã điểm danh" = đã submit (attendance_submitted_at), KHÔNG phải chỉ tồn tại bản ghi buổi
                // (bản ghi buổi tự sinh khi mở trang điểm danh nên không phản ánh việc đã điểm danh).
                $session = ClassSession::where('class_id', $c->id)->whereDate('date', $todayDate)->first();
                $sc = $c->schedules->first();

                // Ưu tiên giờ từ session (buổi bù có giờ riêng), fallback về lịch cố định
                $start = $session?->start_time ?: $sc?->start_time;
                $end = $session?->end_time ?: $sc?->end_time;

                return (object) [
                    'class' => $c,
                    'start' => $start,
                    'end' => $end,
                    'count' => $c->class_students_count,
                    'done' => (bool) ($session && $session->attendance_submitted_at),
                    'off' => (bool) ($session && $session->type === 'off'),
                    'makeup' => (bool) ($session && $session->type === 'makeup'),
                    'session_id' => $session?->id,
                ];
            });

        $revenueMonth = (int) $this->charged($tid)
            ->whereYear('class_sessions.date', now()->year)
            ->whereMonth('class_sessions.date', now()->month)
            ->sum('student_sessions.amount');

        $balances = $this->balances($tid);
        $debtTotal = (int) $balances->filter(fn ($b) => $b > 0)->sum();
        $debtorCount = $balances->filter(fn ($b) => $b > 0)->count();
        $notDoneToday = $todayClasses->filter(fn ($r) => ! $r->done && ! $r->off)->count();

        // Buổi đã báo nghỉ nhưng chưa xếp lịch học bù (bỏ qua buổi được đánh dấu không cần bù)
        $pendingMakeups = ClassSession::where('type', 'off')
            ->where('no_makeup', false)
            ->whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->whereDoesntHave('makeups')
            ->with('classroom')
            ->orderBy('date', 'desc')
            ->get();
        $pendingMakeupCount = $pendingMakeups->count();

        // Buổi học đã qua ngày nhưng CHƯA điểm danh (chưa sinh tiền -> bỏ sót doanh thu)
        $missedAttendance = ClassSession::whereIn('type', ['regular', 'makeup'])
            ->whereDate('date', '<', $todayDate)
            ->whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->whereDoesntHave('studentSessions')
            ->with('classroom')
            ->orderBy('date', 'desc')
            ->get();
        $missedAttendanceCount = $missedAttendance->count();

        return view('teacher.dashboard', compact(
            'classesActive', 'studentsCount', 'todayClasses',
            'revenueMonth', 'debtTotal', 'debtorCount', 'notDoneToday',
            'pendingMakeups', 'pendingMakeupCount',
            'missedAttendance', 'missedAttendanceCount'
        ));
    }

    /* ===================== Danh sách lớp ===================== */
    public function classes(Request $request)
    {
        $tid = $this->tid();
        $q = trim((string) $request->get('q'));
        $grade = (int) $request->get('grade');
        $type = $request->get('type');      // group | tutor_1on1
        $status = $request->get('status');  // active | paused

        $query = Classroom::where('teacher_id', $tid)
            ->with('schedules')
            ->withCount([
                'classStudents',
                'sessions',
                // Lock chỉ khi đã có buổi ĐÃ điểm danh — session tự sinh khi mở trang chưa tính
                'sessions as submitted_count' => fn ($q) => $q->whereNotNull('attendance_submitted_at'),
            ]);
        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }
        if ($grade) {
            $query->where('grade', $grade);
        }
        if (in_array($type, ['group', 'tutor_1on1'], true)) {
            $query->where('type', $type);
        }

        // Đếm "đang hoạt động" theo bộ lọc khác (không tính lọc trạng thái) cho khỏi mâu thuẫn
        $activeCount = (clone $query)->where('status', 'active')->count();

        if (in_array($status, ['active', 'paused', 'ended'], true)) {
            $query->where('status', $status);
        }
        $classes = $query->orderBy('id')->paginate(10)->withQueryString();

        return view('teacher.classes', compact('classes', 'activeCount', 'q', 'grade', 'type', 'status'));
    }

    /* ===================== Chi tiết lớp ===================== */
    public function classShow(int $id, Request $request)
    {
        $tid = $this->tid();
        $class = Classroom::where('teacher_id', $tid)->with('schedules')
            ->withCount(['sessions as submitted_count' => fn ($q) => $q->whereNotNull('attendance_submitted_at')])
            ->findOrFail($id);

        $students = $class->students()->get()->map(function ($s) use ($class) {
            $alloc = $this->allocateByClass($s);

            return (object) [
                'student' => $s,
                'price' => (int) $s->pivot->price_per_session,
                'balanceClass' => (int) ($alloc[$class->id]['owed'] ?? 0),
            ];
        });

        // Filter buổi theo tuần / tháng
        $period = $request->get('period') === 'week' ? 'week' : 'month';
        if ($period === 'week') {
            $from = now()->startOfWeek();
            $to = now()->endOfWeek();
            $periodLabel = 'Tuần ' . $from->format('d/m') . ' – ' . $to->format('d/m/Y');
        } else {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
            $periodLabel = 'Tháng ' . now()->format('m/Y');
        }

        $sessions = ClassSession::where('class_id', $class->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')->get();
        $taught = $sessions->whereIn('type', ['regular', 'makeup'])->count();
        $offs = $sessions->where('type', 'off');
        $makeups = $sessions->where('type', 'makeup');

        // Giá mặc định của lớp: ưu tiên giá lưu trên lớp, rồi tới giá HS hiện có, cuối cùng 120k
        $classDefaultPrice = (int) ($class->default_price ?: ($class->classStudents()->value('price_per_session') ?: 120000));

        return view('teacher.class-detail', compact(
            'class', 'students', 'taught', 'offs', 'makeups', 'period', 'periodLabel', 'sessions', 'classDefaultPrice'
        ));
    }

    /* ===================== Danh sách học sinh ===================== */
    public function students(Request $request)
    {
        $tid = $this->tid();
        $balances = $this->balances($tid);

        $classId = (int) $request->get('class_id');
        $payStatus = $request->get('pay_status'); // paid | unpaid
        $status = $request->has('status') ? $request->get('status') : 'active'; // active | inactive | '' (tất cả)
        $q = trim((string) $request->get('q'));

        $query = Student::where('teacher_id', $tid)->with(['classStudents.classroom']);
        if ($classId) {
            $query->whereHas('classStudents', fn ($x) => $x->where('class_id', $classId));
        }
        if ($status === 'active' || $status === 'inactive') {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(fn ($x) => $x->where('full_name', 'like', "%{$q}%")->orWhere('student_code', 'like', "%{$q}%"));
        }

        $students = $query->orderBy('full_name')->get()
            ->map(function ($s) use ($balances) {
                $classes = $s->classStudents->map(fn ($cs) => $cs->classroom)->filter();

                $bal = (int) ($balances[$s->id] ?? 0);

                return (object) [
                    'student' => $s,
                    'classes' => $classes,
                    'grade' => optional($classes->first())->grade,
                    'balance' => $bal,
                    'paid' => $bal <= 0,
                ];
            });

        if ($payStatus === 'paid') {
            $students = $students->where('paid', true)->values();
        } elseif ($payStatus === 'unpaid') {
            $students = $students->where('paid', false)->values();
        }

        // Công nợ tính sau truy vấn nên phân trang thủ công trên collection (10 bản ghi/trang)
        $total = $students->count();
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 10;
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $students->forPage($page, $perPage)->values(),
            $total, $perPage, $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        $classList = Classroom::where('teacher_id', $tid)->orderBy('id')->get();

        return view('teacher.students', compact('students', 'classList', 'classId', 'status', 'payStatus', 'q'));
    }

    /* ===================== Hồ sơ học sinh ===================== */
    public function studentShow(int $id)
    {
        $tid = $this->tid();
        $student = Student::where('teacher_id', $tid)
            ->with(['classStudents.classroom', 'payments' => fn ($q) => $q->orderByDesc('paid_at')])
            ->findOrFail($id);

        $enrollments = $student->classStudents->map(fn ($cs) => (object) [
            'name' => optional($cs->classroom)->name,
            'price' => (int) $cs->price_per_session,
        ]);

        $balance = (int) ($this->balances($tid)[$student->id] ?? 0);
        $primaryPrice = (int) ($student->classStudents->min('price_per_session') ?: 0);
        $unpaidSessions = $primaryPrice > 0 ? (int) round($balance / $primaryPrice) : 0;
        $grade = optional($student->classStudents->first()?->classroom)->grade;
        $prefix = auth()->user()->account_prefix;

        // Lịch sử điểm danh (bảng chấm công): buổi nào có mặt/vắng
        $attendance = $student->studentSessions()
            ->with('classSession.classroom')->get()
            ->sortByDesc(fn ($ss) => optional($ss->classSession)->date?->toDateString())
            ->map(fn ($ss) => (object) [
                'date' => optional($ss->classSession)->date,
                'class' => optional(optional($ss->classSession)->classroom)->name,
                'status' => $ss->status,
                'amount' => (int) $ss->amount,
            ])->values();
        $attSummary = (object) [
            'present' => $attendance->where('status', 'present')->count(),
            'makeup' => $attendance->where('status', 'makeup')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
        ];

        // Nhận xét của giáo viên, mới nhất trước
        $comments = $student->comments()
            ->orderByDesc('comment_date')->orderByDesc('id')->get();

        // Nhật ký bật/tắt hoạt động, mới nhất trước
        $statusLogs = $student->statusLogs()->with('user')->latest('id')->get();

        return view('teacher.student', compact(
            'student', 'enrollments', 'balance', 'unpaidSessions', 'grade', 'primaryPrice', 'prefix',
            'attendance', 'attSummary', 'comments', 'statusLogs'
        ));
    }

    /** Thêm nhận xét cho học sinh (lưu theo ngày). */
    public function storeComment(Request $request, int $id)
    {
        $tid = $this->tid();
        $student = Student::where('teacher_id', $tid)->findOrFail($id);

        $data = $request->validate([
            'comment_date' => ['required', 'date'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $student->comments()->create([
            'teacher_id' => $tid,
            'comment_date' => $data['comment_date'],
            'body' => $data['body'],
        ]);

        return $this->respondOk($request, 'Đã lưu nhận xét.', route('teacher.student', $student->id));
    }

    /** Xoá một nhận xét. */
    public function deleteComment(Request $request, int $id, int $commentId)
    {
        $tid = $this->tid();
        $student = Student::where('teacher_id', $tid)->findOrFail($id);
        $student->comments()->whereKey($commentId)->delete();

        return $this->respondOk($request, 'Đã xoá nhận xét.', route('teacher.student', $student->id));
    }

    /* ===================== Điểm danh ===================== */
    public function attendance(Request $request)
    {
        $tid = $this->tid();
        $classList = Classroom::where('teacher_id', $tid)->where('status', 'active')
            ->with('schedules')->orderBy('id')->get();

        $classId = (int) ($request->get('class_id') ?: $classList->first()?->id);
        $class = $classList->firstWhere('id', $classId) ?: $classList->first();

        // Tuần đang xem
        $weekStart = $request->get('week')
            ? Carbon::parse($request->get('week'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $weekLabel = 'Tuần ' . $weekStart->format('d/m') . ' – ' . $weekEnd->format('d/m/Y');

        $sessions = collect();
        $session = null;
        $rows = collect();
        $logs = collect();
        if ($class) {
            // Tự sinh buổi học theo lịch cố định cho tuần đang xem (tới hết tuần hiện tại)
            $genLimit = now()->endOfWeek();
            foreach ($class->schedules as $sc) {
                $day = $weekStart->copy()->addDays(((int) $sc->weekday) - 1);
                if ($class->start_date && $day->lt($class->start_date->copy()->startOfDay())) {
                    continue;
                }
                if ($day->gt($genLimit)) {
                    continue;
                }
                ClassSession::firstOrCreate(
                    ['class_id' => $class->id, 'date' => $day->toDateString()],
                    ['start_time' => $sc->start_time, 'end_time' => $sc->end_time, 'type' => 'regular']
                );
            }

            $sessions = ClassSession::where('class_id', $class->id)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->withCount('makeups')
                ->orderBy('date')->orderBy('start_time')->get();

            $sessionId = (int) ($request->get('session_id') ?: $sessions->first()?->id);
            $session = $sessions->firstWhere('id', $sessionId) ?: $sessions->first();

            if ($session) {
                $existing = StudentSession::where('class_session_id', $session->id)->pluck('status', 'student_id');
                // Chỉ hiển thị học sinh đang hoạt động (đã ngừng hoạt động thì không điểm danh)
                $rows = $class->students()->where('students.status', 'active')->get()->map(fn ($s) => (object) [
                    'student' => $s,
                    'price' => (int) $s->pivot->price_per_session,
                    'status' => $existing[$s->id] ?? 'present',
                ]);
                $logs = $session->logs()->with('user')->latest('id')->get();
            }
        }
        $total = $rows->whereIn('status', StudentSession::BILLABLE)->sum('price');

        return view('teacher.attendance', compact(
            'classList', 'class', 'sessions', 'session', 'rows', 'total',
            'weekStart', 'weekEnd', 'weekLabel', 'logs'
        ));
    }

    /** Lưu điểm danh: cập nhật student_sessions, ghi submitted_at + log lịch sử */
    public function submitAttendance(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->with('classroom')->findOrFail($sessionId);

        $statuses = (array) $request->input('status', []);
        // Chỉ điểm danh học sinh đang hoạt động — bỏ qua học sinh đã ngừng hoạt động
        $prices = ClassStudent::where('class_id', $session->class_id)
            ->whereHas('student', fn ($q) => $q->where('status', 'active'))
            ->pluck('price_per_session', 'student_id');

        $wasSubmitted = ! is_null($session->attendance_submitted_at);
        $presentCount = 0;
        $totalAmount = 0;
        $snapshot = [];

        foreach ($prices as $studentId => $price) {
            $st = $statuses[$studentId] ?? 'present';
            if (! in_array($st, StudentSession::STATUSES, true)) {
                $st = 'present';
            }
            $amount = in_array($st, StudentSession::BILLABLE, true) ? (int) $price : 0;

            StudentSession::updateOrCreate(
                ['class_session_id' => $session->id, 'student_id' => $studentId],
                ['status' => $st, 'session_units' => 1, 'amount' => $amount]
            );

            if ($amount > 0) {
                $presentCount++;
            }
            $totalAmount += $amount;
            $snapshot[] = ['student_id' => (int) $studentId, 'status' => $st, 'amount' => $amount];
        }

        $session->update(['attendance_submitted_at' => now()]);

        AttendanceLog::create([
            'class_session_id' => $session->id,
            'user_id' => $tid,
            'action' => $wasSubmitted ? 'resubmit' : 'submit',
            'present_count' => $presentCount,
            'total_amount' => $totalAmount,
            'snapshot' => $snapshot,
        ]);

        $redirectUrl = route('teacher.attendance', [
            'class_id' => $session->class_id,
            'week' => Carbon::parse($session->date)->startOfWeek()->toDateString(),
            'session_id' => $session->id,
        ]);
        return $this->respondOk(
            $request,
            'Đã lưu điểm danh lúc ' . now()->format('H:i d/m/Y') . ($wasSubmitted ? ' (cập nhật lại)' : '') . '.',
            $redirectUrl
        );
    }

    /** Báo cả lớp nghỉ một buổi: chuyển type=off (không tính tiền), tuỳ chọn tạo buổi học bù */
    public function markSessionOff(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->findOrFail($sessionId);

        $redirectUrl = route('teacher.attendance', [
            'class_id' => $session->class_id,
            'week' => Carbon::parse($session->date)->startOfWeek()->toDateString(),
            'session_id' => $session->id,
        ]);

        // Đã điểm danh thì không cho báo nghỉ — phải bỏ điểm danh trước (tránh sai lệch tiền đã chốt)
        if ($session->attendance_submitted_at) {
            return $this->respondError($request, 'off', 'Buổi này đã điểm danh nên không thể báo nghỉ.', $redirectUrl);
        }
        if ($session->type === 'off') {
            return $this->respondError($request, 'off', 'Buổi này đã là buổi nghỉ.', $redirectUrl);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'makeup_date' => ['nullable', 'date'],
        ]);

        // Ngày học bù (nếu có) phải trống lịch để tránh trùng buổi đang có
        $makeup = null;
        if (! empty($data['makeup_date'])) {
            $exists = ClassSession::where('class_id', $session->class_id)
                ->whereDate('date', $data['makeup_date'])->exists();
            if ($exists) {
                return $this->respondError($request, 'off', 'Ngày học bù đã có buổi học khác — chọn ngày khác.', $redirectUrl);
            }
        }

        DB::transaction(function () use ($session, $data, &$makeup) {
            // Phòng xa: dọn mọi bản ghi điểm danh lỡ tạo cho buổi này
            StudentSession::where('class_session_id', $session->id)->delete();

            $session->update([
                'type' => 'off',
                'note' => $data['reason'] ?? $session->note,
                'attendance_submitted_at' => null,
            ]);

            if (! empty($data['makeup_date'])) {
                $makeup = ClassSession::create([
                    'class_id' => $session->class_id,
                    'date' => $data['makeup_date'],
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'type' => 'makeup',
                    'makeup_for_id' => $session->id,
                ]);
            }
        });

        $msg = 'Đã báo cả lớp nghỉ buổi ' . Carbon::parse($session->date)->format('d/m/Y') . ' (không tính tiền).';
        if ($makeup) {
            $msg .= ' Đã tạo buổi học bù ngày ' . Carbon::parse($makeup->date)->format('d/m/Y') . '.';
        }

        return $this->respondOk($request, $msg, $redirectUrl);
    }

    /** Hoàn tác báo nghỉ: chuyển buổi off về regular, xoá buổi bù chưa điểm danh (nếu có) */
    public function unmarkSessionOff(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->findOrFail($sessionId);

        $redirectUrl = route('teacher.attendance', [
            'class_id' => $session->class_id,
            'week' => Carbon::parse($session->date)->startOfWeek()->toDateString(),
            'session_id' => $session->id,
        ]);

        if ($session->type !== 'off') {
            return $this->respondError($request, 'off', 'Buổi này không phải buổi nghỉ.', $redirectUrl);
        }

        DB::transaction(function () use ($session) {
            // Xoá buổi học bù gắn với buổi nghỉ này nếu buổi bù chưa điểm danh
            ClassSession::where('makeup_for_id', $session->id)
                ->whereNull('attendance_submitted_at')
                ->delete();

            $session->update(['type' => 'regular']);
        });

        return $this->respondOk(
            $request,
            'Đã hoàn tác — buổi ' . Carbon::parse($session->date)->format('d/m/Y') . ' trở lại buổi học bình thường.',
            $redirectUrl
        );
    }

    /** Thêm buổi học bù cho một buổi đã báo nghỉ (xếp lịch bù sau khi nghỉ) */
    public function addMakeup(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->findOrFail($sessionId);

        $redirectUrl = route('teacher.attendance', [
            'class_id' => $session->class_id,
            'week' => Carbon::parse($session->date)->startOfWeek()->toDateString(),
            'session_id' => $session->id,
        ]);

        if ($session->type !== 'off') {
            return $this->respondError($request, 'off', 'Chỉ tạo buổi học bù cho buổi đã báo nghỉ.', $redirectUrl);
        }
        if ($session->makeups()->exists()) {
            return $this->respondError($request, 'off', 'Buổi nghỉ này đã có buổi học bù.', $redirectUrl);
        }

        $data = $request->validate(['makeup_date' => ['required', 'date']]);

        $exists = ClassSession::where('class_id', $session->class_id)
            ->whereDate('date', $data['makeup_date'])->exists();
        if ($exists) {
            return $this->respondError($request, 'off', 'Ngày học bù đã có buổi học khác — chọn ngày khác.', $redirectUrl);
        }

        $makeup = ClassSession::create([
            'class_id' => $session->class_id,
            'date' => $data['makeup_date'],
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'type' => 'makeup',
            'makeup_for_id' => $session->id,
        ]);

        return $this->respondOk(
            $request,
            'Đã tạo buổi học bù ngày ' . Carbon::parse($makeup->date)->format('d/m/Y') . ' (tính tiền như buổi học bình thường).',
            $redirectUrl
        );
    }

    /** Đánh dấu buổi nghỉ này KHÔNG cần học bù (coi như bỏ hẳn). Toggle qua tham số state. */
    public function toggleNoMakeup(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))
            ->findOrFail($sessionId);

        $redirectUrl = route('teacher.attendance', [
            'class_id' => $session->class_id,
            'week' => Carbon::parse($session->date)->startOfWeek()->toDateString(),
            'session_id' => $session->id,
        ]);

        if ($session->type !== 'off') {
            return $this->respondError($request, 'off', 'Chỉ áp dụng cho buổi đã báo nghỉ.', $redirectUrl);
        }
        if ($session->makeups()->exists()) {
            return $this->respondError($request, 'off', 'Buổi này đã có buổi học bù — xoá buổi bù trước.', $redirectUrl);
        }

        $session->update(['no_makeup' => ! $session->no_makeup]);

        $msg = $session->no_makeup
            ? 'Đã đánh dấu buổi ' . Carbon::parse($session->date)->format('d/m/Y') . ' không cần học bù.'
            : 'Đã bỏ đánh dấu — buổi ' . Carbon::parse($session->date)->format('d/m/Y') . ' cần xếp học bù lại.';

        return $this->respondOk($request, $msg, $redirectUrl);
    }

    /* ===================== Học phí & công nợ ===================== */
    public function fees(Request $request)
    {
        $tid = $this->tid();

        $collectedMonth = (int) Payment::where('teacher_id', $tid)
            ->whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->sum('amount');

        $balances = $this->balances($tid);
        $outstanding = (int) $balances->filter(fn ($b) => $b > 0)->sum();
        $debtorCount = $balances->filter(fn ($b) => $b > 0)->count();

        $priceMap = $this->primaryPriceMap($tid);
        $lastPay = Payment::where('teacher_id', $tid)
            ->selectRaw('student_id, MAX(paid_at) last_paid')->groupBy('student_id')->pluck('last_paid', 'student_id');

        // Filters: lớp / trạng thái / tìm kiếm
        $classId = (int) $request->get('class_id');
        // Mặc định lọc "Chưa đóng" khi mới vào (tối ưu load); chọn "Tất cả" (status rỗng) thì tôn trọng.
        $status = $request->has('status') ? $request->get('status') : 'unpaid';
        $q = trim((string) $request->get('q'));

        $query = Student::where('teacher_id', $tid);
        if ($classId) {
            $query->whereHas('classStudents', fn ($x) => $x->where('class_id', $classId));
        }
        if ($q !== '') {
            $query->where(fn ($x) => $x->where('full_name', 'like', "%{$q}%")->orWhere('student_code', 'like', "%{$q}%"));
        }

        $rows = $query->orderBy('full_name')->get()->map(function ($s) use ($balances, $priceMap, $lastPay) {
            $bal = (int) ($balances[$s->id] ?? 0);
            $price = (int) ($priceMap[$s->id] ?? 0);

            return (object) [
                'student' => $s,
                'balance' => $bal,
                'paid' => $bal <= 0,
                'sessions' => $price > 0 ? (int) round(max($bal, 0) / $price) : 0,
                'lastPaid' => $lastPay[$s->id] ?? null,
            ];
        });
        if ($status === 'paid') {
            $rows = $rows->where('paid', true);
        } elseif ($status === 'unpaid') {
            $rows = $rows->where('paid', false);
        }
        $rows = $rows->sortByDesc('balance')->values();

        $classList = Classroom::where('teacher_id', $tid)->orderBy('id')->get();

        return view('teacher.fees', compact(
            'collectedMonth', 'outstanding', 'debtorCount', 'rows', 'classList', 'classId', 'status', 'q'
        ));
    }

    /* ===================== Chi tiết nợ theo tháng (AJAX) ===================== */
    public function studentMonthly(int $id)
    {
        $tid = $this->tid();
        $student = Student::where('teacher_id', $tid)->findOrFail($id);

        $months = [];
        $totalCharged = 0;
        $activeMonths = 0;
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->subMonths($i);
            $charged = (int) StudentSession::where('student_id', $student->id)
                ->whereHas('classSession', fn ($q) => $q->whereYear('date', $m->year)->whereMonth('date', $m->month))
                ->sum('amount');
            $paid = (int) Payment::where('student_id', $student->id)
                ->whereYear('paid_at', $m->year)->whereMonth('paid_at', $m->month)->sum('amount');
            $months[] = ['label' => 'Tháng ' . $m->format('m/Y'), 'charged' => $charged, 'paid' => $paid, 'owed' => max(0, $charged - $paid)];
            $totalCharged += $charged;
            if ($charged > 0) {
                $activeMonths++;
            }
        }
        $balance = (int) ($this->balances($tid)[$student->id] ?? 0);
        $avg = $activeMonths > 0 ? $totalCharged / $activeMonths : 0;
        $monthsBehind = ($avg > 0 && $balance > 0) ? (int) ceil($balance / $avg) : 0;

        // Thống kê theo lớp (sắp theo thứ tự ghi danh = lớp đầu tiên trước)
        $classRows = $student->classStudents()->with('classroom')->orderBy('id')->get()->map(function ($cs) use ($student) {
            $allTime = (int) StudentSession::where('student_id', $student->id)
                ->whereHas('classSession', fn ($q) => $q->where('class_id', $cs->class_id))
                ->sum('amount');
            // Số buổi + tiền học của THÁNG HIỆN TẠI cho lớp này
            $m = StudentSession::where('student_id', $student->id)
                ->whereHas('classSession', fn ($q) => $q->where('class_id', $cs->class_id)
                    ->whereYear('date', now()->year)->whereMonth('date', now()->month))
                ->selectRaw('COALESCE(SUM(CASE WHEN amount > 0 THEN 1 ELSE 0 END),0) cnt, COALESCE(SUM(amount),0) total')->first();

            return [
                'name' => optional($cs->classroom)->name ?? '—',
                'price' => (int) $cs->price_per_session,
                'charged' => $allTime,                       // tổng đã học (cho tổng ở trên)
                'sessionsMonth' => (int) ($m->cnt ?? 0),     // số buổi tháng này
                'chargedMonth' => (int) ($m->total ?? 0),    // tiền học tháng này
            ];
        })->all();

        $totalPaid = (int) $student->payments()->sum('amount');

        return response()->json([
            'name' => $student->full_name,
            'code' => $student->student_code,
            'balance' => $balance,
            'monthsBehind' => $monthsBehind,
            'month' => (int) now()->month,
            'totalCharged' => (int) array_sum(array_column($classRows, 'charged')),
            'totalPaid' => $totalPaid,
            'classes' => array_values($classRows),
            'months' => $months,
        ]);
    }

    /* ===================== Sửa thông tin học sinh ===================== */
    public function updateStudent(Request $request, int $id)
    {
        $tid = $this->tid();
        $student = Student::where('teacher_id', $tid)->findOrFail($id);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:20'],
            'parent_contact' => ['nullable', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'max:100',
                Rule::unique('students')->where(fn ($q) => $q->where('teacher_id', $tid))->ignore($student->id)],
        ]);

        $student->update($data);

        return $this->respondOk($request, 'Đã cập nhật thông tin học sinh “' . $student->full_name . '”.');
    }

    /** Bật/tắt trạng thái hoạt động của học sinh. Ngừng hoạt động => không điểm danh nữa. */
    public function toggleStudentStatus(Request $request, int $id)
    {
        $tid = $this->tid();
        $student = Student::where('teacher_id', $tid)->findOrFail($id);

        $student->status = $student->status === 'active' ? 'inactive' : 'active';
        $student->save();

        // Ghi nhật ký: ai bật/tắt, lúc nào
        $student->statusLogs()->create([
            'user_id' => $tid,
            'action' => $student->status === 'active' ? 'activate' : 'deactivate',
        ]);

        $msg = $student->status === 'active'
            ? 'Đã kích hoạt lại học sinh “' . $student->full_name . '”.'
            : 'Đã ngừng hoạt động học sinh “' . $student->full_name . '” — sẽ không xuất hiện khi điểm danh.';

        return $this->respondOk($request, $msg);
    }

    /* ===================== Ghi nhận đóng tiền ===================== */
    public function storePayment(Request $request)
    {
        $tid = $this->tid();
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:cash,transfer'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $student = Student::where('teacher_id', $tid)->findOrFail($data['student_id']);

        Payment::create([
            'student_id' => $student->id,
            'teacher_id' => $tid,
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'],
            'method' => $data['method'],
            'note' => $data['note'] ?? null,
        ]);

        $balance = (int) ($this->balances($tid)[$student->id] ?? 0);
        $msg = 'Đã ghi nhận ' . \App\Support\Money::vnd($data['amount']) . ' từ ' . $student->full_name
            . ' · công nợ còn lại: ' . ($balance > 0 ? \App\Support\Money::vnd($balance) : 'đã đóng đủ ✓');

        return $this->respondOk($request, $msg);
    }

    /* ===================== AJAX: tìm học sinh ===================== */
    public function searchStudents(Request $request)
    {
        $tid = $this->tid();
        $q = trim((string) $request->get('q'));
        $excludeClass = (int) $request->get('exclude_class');

        $query = Student::where('teacher_id', $tid);
        if ($q !== '') {
            $query->where(fn ($x) => $x->where('full_name', 'like', "%{$q}%")->orWhere('student_code', 'like', "%{$q}%"));
        }
        if ($excludeClass) {
            $query->whereDoesntHave('classStudents', fn ($x) => $x->where('class_id', $excludeClass));
        }

        $items = $query->orderBy('full_name')->limit(20)->get()
            ->map(fn ($s) => ['id' => $s->id, 'label' => $s->full_name . ' · ' . $s->student_code]);

        return response()->json($items);
    }

    /* ===================== Thêm lớp ===================== */
    public function storeClass(Request $request)
    {
        $tid = $this->tid();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:group,tutor_1on1'],
            'grade' => ['required', 'integer', 'between:1,12'],
            'subject' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,paused'],
            'start_date' => ['required', 'date'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'time_start' => ['array'],
            'time_start.*' => ['nullable', 'date_format:H:i'],
            'time_end' => ['array'],
            'time_end.*' => ['nullable', 'date_format:H:i'],
            'students' => ['array'],
            'students.*' => ['integer'],
            'price_per_session' => ['nullable', 'integer', 'min:0'],
        ]);

        $class = Classroom::create([
            'teacher_id' => $tid,
            'name' => $data['name'],
            'type' => $data['type'],
            'grade' => $data['grade'] ?? null,
            'subject' => $data['subject'] ?? null,
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'default_price' => (int) ($data['price_per_session'] ?? 0),
        ]);
        foreach (($data['weekdays'] ?? []) as $wd) {
            $class->schedules()->create([
                'weekday' => $wd,
                'start_time' => $data['time_start'][$wd] ?? '17:30',
                'end_time' => $data['time_end'][$wd] ?? '19:00',
            ]);
        }

        // Ghi danh các học sinh đã chọn (nếu có)
        $price = (int) ($data['price_per_session'] ?? 0);
        foreach (($data['students'] ?? []) as $sid) {
            if (Student::where('teacher_id', $tid)->whereKey($sid)->exists()) {
                ClassStudent::firstOrCreate(
                    ['student_id' => $sid, 'class_id' => $class->id],
                    ['price_per_session' => $price, 'joined_at' => now(), 'status' => 'active']
                );
            }
        }

        return $this->respondOk($request, 'Đã tạo lớp “' . $class->name . '”.', route('teacher.classes'));
    }

    /* ===================== Sửa lớp (dùng chung form tạo) ===================== */
    public function updateClass(Request $request, int $id)
    {
        $tid = $this->tid();
        $class = Classroom::where('teacher_id', $tid)
            ->withCount(['sessions as submitted_count' => fn ($q) => $q->whereNotNull('attendance_submitted_at')])
            ->findOrFail($id);
        // Chỉ khoá khi lớp đã có buổi ĐÃ điểm danh (tránh khoá vì session tự sinh chưa dùng)
        $canEditAll = (int) $class->submitted_count === 0;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,paused'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'time_start' => ['array'],
            'time_start.*' => ['nullable', 'date_format:H:i'],
            'time_end' => ['array'],
            'time_end.*' => ['nullable', 'date_format:H:i'],
        ];
        if ($canEditAll) {
            $rules['type'] = ['required', 'in:group,tutor_1on1'];
            $rules['grade'] = ['required', 'integer', 'between:1,12'];
            $rules['subject'] = ['required', 'string', 'max:100'];
            $rules['start_date'] = ['required', 'date'];
        }
        $data = $request->validate($rules);

        $oldStatus = $class->status;
        $oldStartDate = optional($class->start_date)->toDateString();

        $update = ['name' => $data['name'], 'status' => $data['status']];
        if ($canEditAll) {
            $update['type'] = $data['type'];
            $update['grade'] = $data['grade'];
            $update['subject'] = $data['subject'];
            $update['start_date'] = $data['start_date'];
        }
        // Tạm dừng -> lưu ngày kết thúc; kích hoạt lại -> xoá ngày kết thúc
        $update['ended_at'] = $data['status'] === 'paused' ? now()->toDateString() : null;

        $class->update($update);

        // Đổi ngày khai giảng: xoá các buổi cũ (tự sinh) để trang điểm danh sinh lại theo mốc mới.
        // Chỉ chạy khi canEditAll = true, tức lớp chưa có buổi nào đã điểm danh.
        if ($canEditAll && $oldStartDate !== $data['start_date']) {
            $class->sessions()->delete();
        }

        // Dựng lại lịch cố định theo các thứ + giờ riêng từng buổi (áp dụng cho các buổi tạo MỚI;
        // các buổi đã tạo/đã điểm danh giữ nguyên). Xoá hết rồi tạo lại cho khớp lựa chọn.
        $class->schedules()->delete();
        foreach ($data['weekdays'] as $wd) {
            $class->schedules()->create([
                'weekday' => $wd,
                'start_time' => $data['time_start'][$wd] ?? '17:30',
                'end_time' => $data['time_end'][$wd] ?? '19:00',
            ]);
        }

        // Thông báo theo thay đổi thực tế: ưu tiên báo trạng thái nếu nó đổi
        if ($data['status'] === 'paused' && $oldStatus !== 'paused') {
            $msg = 'Đã tạm dừng lớp “' . $class->name . '” · kết thúc ' . now()->format('d/m/Y') . '.';
        } elseif ($data['status'] === 'active' && $oldStatus === 'paused') {
            $msg = 'Đã kích hoạt lại lớp “' . $class->name . '”.';
        } else {
            $msg = 'Đã cập nhật lớp “' . $class->name . '”.';
        }

        return $this->respondOk($request, $msg, route('teacher.classes'));
    }

    /* ===================== Nhân bản lớp ===================== */
    public function duplicateClass(Request $request, int $id)
    {
        $tid = $this->tid();
        $class = Classroom::where('teacher_id', $tid)
            ->with(['schedules', 'classStudents'])->findOrFail($id);

        $new = DB::transaction(function () use ($class, $tid) {
            $copy = Classroom::create([
                'teacher_id' => $tid,
                'name' => $class->name . ' (sao chép)',
                'type' => $class->type,
                'grade' => $class->grade,
                'subject' => $class->subject,
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'default_price' => $class->default_price,
            ]);

            // Sao chép lịch cố định (giữ giờ riêng từng thứ)
            foreach ($class->schedules as $s) {
                $copy->schedules()->create([
                    'weekday' => $s->weekday,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                ]);
            }

            // Sao chép danh sách học sinh kèm đơn giá riêng từng em
            foreach ($class->classStudents as $cs) {
                $copy->classStudents()->create([
                    'student_id' => $cs->student_id,
                    'price_per_session' => $cs->price_per_session,
                    'joined_at' => now()->toDateString(),
                    'status' => 'active',
                ]);
            }

            return $copy;
        });

        return $this->respondOk(
            $request,
            'Đã nhân bản lớp “' . $class->name . '”. Hãy sửa lại tên/giờ rồi lưu.',
            route('teacher.classes', ['edit' => $new->id])
        );
    }

    /* ===================== Thêm học sinh vào lớp ===================== */
    public function addStudentToClass(Request $request, int $id)
    {
        $tid = $this->tid();
        $class = Classroom::where('teacher_id', $tid)->findOrFail($id);
        $data = $request->validate([
            'students' => ['required', 'array', 'min:1'],
            'students.*' => ['integer'],
            'price_per_session' => ['required', 'integer', 'min:0'],
        ]);

        $count = 0;
        foreach ($data['students'] as $sid) {
            if (Student::where('teacher_id', $tid)->whereKey($sid)->exists()) {
                $cs = ClassStudent::firstOrCreate(
                    ['student_id' => $sid, 'class_id' => $class->id],
                    ['price_per_session' => $data['price_per_session'], 'joined_at' => now(), 'status' => 'active']
                );
                if ($cs->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $this->respondOk($request, 'Đã thêm ' . $count . ' học sinh vào lớp.', route('teacher.class', $class->id));
    }

    /* ===================== Sửa đơn giá học sinh trong lớp ===================== */
    public function updateClassStudentPrice(Request $request, int $id, int $studentId)
    {
        $tid = $this->tid();
        $class = Classroom::where('teacher_id', $tid)->findOrFail($id);
        $data = $request->validate([
            'price_per_session' => ['required', 'integer', 'min:0'],
        ]);

        $cs = ClassStudent::where('class_id', $class->id)
            ->where('student_id', $studentId)
            ->whereHas('student', fn ($q) => $q->where('teacher_id', $tid))
            ->firstOrFail();

        $oldPrice = (int) $cs->price_per_session;
        $newPrice = (int) $data['price_per_session'];

        if ($oldPrice !== $newPrice) {
            $cs->update(['price_per_session' => $newPrice]);
            ClassStudentPriceLog::create([
                'class_id' => $class->id,
                'student_id' => $studentId,
                'user_id' => $tid,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ]);
        }

        return $this->respondOk($request, 'Đã cập nhật đơn giá.', route('teacher.class', $class->id));
    }

    /* ===================== Lịch sử sửa đơn giá (JSON) ===================== */
    public function classStudentPriceHistory(int $id, int $studentId)
    {
        $tid = $this->tid();
        $class = Classroom::where('teacher_id', $tid)->findOrFail($id);
        $student = Student::where('teacher_id', $tid)->findOrFail($studentId);

        $logs = ClassStudentPriceLog::where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->with('user:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($l) => [
                'old_price' => (int) $l->old_price,
                'new_price' => (int) $l->new_price,
                'user' => optional($l->user)->name,
                'at' => optional($l->created_at)->format('H:i d/m/Y'),
            ]);

        return response()->json([
            'student' => $student->full_name,
            'logs' => $logs,
        ]);
    }

    /* ===================== Thêm học sinh mới ===================== */
    public function storeStudent(Request $request)
    {
        $tid = $this->tid();
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'max:100', Rule::unique('students')->where(fn ($q) => $q->where('teacher_id', $tid))],
            'parent_phone' => ['required', 'string', 'max:20'],
            'parent_contact' => ['nullable', 'string', 'max:255'],
            'class_id' => ['required', 'integer'],
            'price_per_session' => ['required', 'integer', 'min:0'],
        ]);

        $student = Student::create([
            'teacher_id' => $tid,
            'full_name' => $data['full_name'],
            'student_code' => $data['student_code'],
            'parent_phone' => $data['parent_phone'],
            'parent_contact' => $data['parent_contact'] ?? null,
            'status' => 'active',
        ]);

        $class = Classroom::where('teacher_id', $tid)->find($data['class_id']);
        if ($class) {
            ClassStudent::create([
                'student_id' => $student->id, 'class_id' => $class->id,
                'price_per_session' => $data['price_per_session'],
                'joined_at' => now(), 'status' => 'active',
            ]);
        }

        return $this->respondOk($request, 'Đã thêm học sinh ' . $student->full_name . '.', route('teacher.student', $student->id));
    }

    /* ===================== Giáo án ===================== */
    /** Trang giáo án tuần cho 1 lớp */
    public function lessonsIndex(Request $request)
    {
        $tid = $this->tid();
        $classList = Classroom::where('teacher_id', $tid)->where('status', 'active')
            ->with('schedules')->orderBy('id')->get();

        $classId = (int) ($request->get('class_id') ?: $classList->first()?->id);
        $class = $classList->firstWhere('id', $classId) ?: $classList->first();

        $weekStart = $request->get('week')
            ? Carbon::parse($request->get('week'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $days = collect();
        if ($class) {
            $weekdays = $class->schedules->pluck('weekday')->map(fn ($w) => (int) $w)->unique()->values();
            $sessions = ClassSession::where('class_id', $class->id)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()->keyBy(fn ($s) => Carbon::parse($s->date)->toDateString());

            for ($i = 0; $i < 7; $i++) {
                $day = $weekStart->copy()->addDays($i);
                $wd = $day->dayOfWeekIso;
                $hasSchedule = $weekdays->contains($wd);
                $session = $sessions[$day->toDateString()] ?? null;
                if (! $hasSchedule && ! $session) {
                    continue;
                }
                $days->push((object) [
                    'date' => $day,
                    'session_id' => $session?->id,
                    'title' => $session?->title,
                    'content' => $session?->content,
                    'submitted' => (bool) $session?->attendance_submitted_at,
                    'type' => $session?->type ?? 'regular',
                ]);
            }
        }

        return view('teacher.lessons', compact('classList', 'class', 'weekStart', 'weekEnd', 'days'));
    }

    /** Batch save giáo án cả tuần: pre-create session nếu ngày đó chưa có */
    public function lessonsBatchSave(Request $request)
    {
        $tid = $this->tid();
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'week' => ['required', 'date'],
            'lessons' => ['array'],
            'lessons.*.date' => ['required', 'date'],
            'lessons.*.title' => ['nullable', 'string', 'max:100'],
            'lessons.*.content' => ['nullable', 'string', 'max:5000'],
        ]);
        $class = Classroom::where('teacher_id', $tid)->findOrFail($data['class_id']);

        $default = $class->schedules->first();
        foreach (($data['lessons'] ?? []) as $row) {
            $session = ClassSession::firstOrCreate(
                ['class_id' => $class->id, 'date' => $row['date']],
                [
                    'start_time' => optional($default)->start_time,
                    'end_time' => optional($default)->end_time,
                    'type' => 'regular',
                ]
            );
            $session->update([
                'title' => $row['title'] ?? null,
                'content' => $row['content'] ?? null,
            ]);
        }

        return $this->respondOk($request, 'Đã lưu giáo án tuần.', route('teacher.lessons', ['class_id' => $class->id, 'week' => $data['week']]));
    }

    /** Sửa bài học của 1 buổi cụ thể (không đụng điểm danh) */
    public function updateSessionLesson(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))->findOrFail($sessionId);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string', 'max:5000'],
        ]);
        $session->update([
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
        ]);
        return $this->respondOk($request, 'Đã lưu bài học.');
    }

    /** Xoá nội dung bài học của 1 buổi (không đụng điểm danh) */
    public function clearSessionLesson(Request $request, int $sessionId)
    {
        $tid = $this->tid();
        $session = ClassSession::whereHas('classroom', fn ($q) => $q->where('teacher_id', $tid))->findOrFail($sessionId);
        $session->update(['title' => null, 'content' => null]);
        return $this->respondOk($request, 'Đã xoá bài học.');
    }

    /* ===================== Cài đặt QR chuyển khoản ===================== */
    public function qrSettings()
    {
        return view('teacher.settings-qr', ['me' => auth()->user()]);
    }

    public function updateQrSettings(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'qr_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_qr_image' => ['nullable', 'in:1'],
        ]);

        $update = [];

        if (! empty($data['remove_qr_image']) && $user->qr_image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->qr_image_path);
            $update['qr_image_path'] = null;
        }
        if ($request->hasFile('qr_image')) {
            if ($user->qr_image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->qr_image_path);
            }
            $update['qr_image_path'] = $request->file('qr_image')->store('qr', 'public');
        }

        if ($update) {
            $user->update($update);
        }

        return $this->respondOk($request, 'Đã lưu cài đặt QR chuyển khoản.', route('teacher.settings.qr'));
    }

    /* ===================== Báo cáo ===================== */
    public function reports(Request $request)
    {
        $tid = $this->tid();

        // Filter tháng (YYYY-MM) + lớp
        $monthStr = $request->get('month') ?: now()->format('Y-m');
        try {
            $month = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
            $monthStr = $month->format('Y-m');
        }
        $classList = Classroom::where('teacher_id', $tid)->orderBy('id')->get();
        $classId = (int) $request->get('class_id');
        // Mặc định lọc lớp đầu tiên (tối ưu load) khi mới vào — chưa chọn gì.
        // Người dùng chủ động chọn "Tất cả lớp" (class_id rỗng) thì vẫn tôn trọng.
        if (! $request->has('class_id') && $classList->isNotEmpty()) {
            $classId = (int) $classList->first()->id;
        }

        $balances = $this->balances($tid);
        $studentIdsAll = Student::where('teacher_id', $tid)->pluck('id');
        $paidAll = Payment::whereIn('student_id', $studentIdsAll)
            ->selectRaw('student_id, SUM(amount) amt')->groupBy('student_id')->pluck('amt', 'student_id');

        // Các lớp đưa vào báo cáo
        $classesQuery = Classroom::where('teacher_id', $tid)->with('students');
        if ($classId) {
            $classesQuery->where('id', $classId);
        }
        $classes = $classesQuery->orderBy('id')->get();

        // Mỗi lớp -> danh sách học sinh kèm tiền
        $report = $classes->map(function ($class) use ($month, $balances, $paidAll) {
            $aggMap = StudentSession::whereHas('classSession', function ($q) use ($class, $month) {
                    $q->where('class_id', $class->id)
                        ->whereYear('date', $month->year)->whereMonth('date', $month->month);
                })
                ->selectRaw('student_id, COALESCE(SUM(amount),0) amt, COALESCE(SUM(CASE WHEN amount>0 THEN 1 ELSE 0 END),0) cnt')
                ->groupBy('student_id')->get()->keyBy('student_id');

            $rows = $class->students->map(fn ($s) => (object) [
                'student' => $s,
                'price' => (int) $s->pivot->price_per_session,
                'sessionsMonth' => (int) (optional($aggMap->get($s->id))->cnt ?? 0),
                'chargedMonth' => (int) (optional($aggMap->get($s->id))->amt ?? 0),
                'paid' => (int) ($paidAll[$s->id] ?? 0),
                'balance' => (int) ($balances[$s->id] ?? 0),
            ])->values();

            return (object) [
                'class' => $class,
                'rows' => $rows,
                'chargedMonth' => (int) $rows->sum('chargedMonth'),
                'owed' => (int) $rows->sum(fn ($r) => max(0, $r->balance)),
            ];
        });

        // Thẻ tổng (theo phạm vi lọc, học sinh distinct)
        $scopeIds = $classes->flatMap(fn ($c) => $c->students->pluck('id'))->unique();
        $cardCharged = (int) $report->sum('chargedMonth');
        $cardCollected = (int) Payment::whereIn('student_id', $scopeIds)
            ->whereYear('paid_at', $month->year)->whereMonth('paid_at', $month->month)->sum('amount');
        $cardOwed = (int) $scopeIds->sum(fn ($id) => max(0, (int) ($balances[$id] ?? 0)));

        return view('teacher.reports', compact(
            'report', 'classList', 'classId', 'monthStr', 'month',
            'cardCharged', 'cardCollected', 'cardOwed'
        ));
    }

    /* ===================== Helpers ===================== */

    /** Query base: student_sessions join class_sessions + classes, lọc theo teacher. */
    private function charged(int $tid)
    {
        return StudentSession::query()
            ->join('class_sessions', 'student_sessions.class_session_id', '=', 'class_sessions.id')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $tid);
    }

    /** [student_id => công nợ] cho mọi học sinh của giáo viên. */
    private function balances(int $tid): Collection
    {
        $ids = Student::where('teacher_id', $tid)->pluck('id');
        $charged = StudentSession::whereIn('student_id', $ids)
            ->selectRaw('student_id, SUM(amount) amt')->groupBy('student_id')->pluck('amt', 'student_id');
        $paid = Payment::whereIn('student_id', $ids)
            ->selectRaw('student_id, SUM(amount) amt')->groupBy('student_id')->pluck('amt', 'student_id');

        return $ids->mapWithKeys(fn ($id) => [$id => (int) (($charged[$id] ?? 0) - ($paid[$id] ?? 0))]);
    }

    /**
     * Phân bổ tiền đã đóng của 1 học sinh theo lớp — ưu tiên trừ vào lớp ghi danh trước.
     * Trả [class_id => ['charged' => , 'collected' => , 'owed' => ]].
     */
    private function allocateByClass(Student $student): array
    {
        $rows = $student->classStudents()->orderBy('id')->get();
        $charged = [];
        foreach ($rows as $cs) {
            $charged[$cs->class_id] = (int) StudentSession::where('student_id', $student->id)
                ->whereHas('classSession', fn ($q) => $q->where('class_id', $cs->class_id))
                ->sum('amount');
        }
        $remaining = (int) $student->payments()->sum('amount');
        $result = [];
        foreach ($rows as $cs) {
            $ch = $charged[$cs->class_id] ?? 0;
            $collected = min($remaining, $ch);
            $result[$cs->class_id] = ['charged' => $ch, 'collected' => (int) $collected, 'owed' => (int) ($ch - $collected)];
            $remaining -= $collected;
        }

        return $result;
    }

    /** [student_id => đơn giá thấp nhất] để suy số buổi chưa đóng. */
    private function primaryPriceMap(int $tid): Collection
    {
        return \App\Models\ClassStudent::whereHas('student', fn ($q) => $q->where('teacher_id', $tid))
            ->selectRaw('student_id, MIN(price_per_session) p')->groupBy('student_id')->pluck('p', 'student_id');
    }
}
