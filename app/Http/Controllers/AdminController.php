<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Plan;
use App\Models\PlanOrder;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /* ===================== Tổng quan hệ thống ===================== */
    public function dashboard()
    {
        $stats = (object) [
            'teachers' => User::where('role', 'owner')->count(),
            'locked'   => User::where('role', 'owner')->where('status', 'locked')->count(),
            'classes'  => Classroom::count(),
            'students' => Student::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /** Chọn giáo viên để xem dữ liệu trong khu giáo viên (lưu vào session). */
    public function setViewTeacher(Request $request)
    {
        $id = (int) $request->get('teacher');
        if (User::where('id', $id)->where('role', 'owner')->exists()) {
            session(['admin_teacher_id' => $id]);
        }

        return back();
    }

    /* ===================== Danh sách giáo viên ===================== */
    public function teachers(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $status = $request->get('status'); // active | locked

        $query = User::where('role', 'owner')->withCount(['classes', 'students']);
        if ($q !== '') {
            $query->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        if (in_array($status, ['active', 'locked'], true)) {
            $query->where('status', $status);
        }

        $teachers = $query->orderByDesc('id')->paginate(10)
            ->withPath(route('admin.teachers'))->appends($request->query());

        return $request->boolean('partial')
            ? view('admin.partials.teachers-list', compact('teachers'))
            : view('admin.teachers', compact('teachers', 'q', 'status'));
    }

    /* ===================== Chi tiết / quản lý giáo viên ===================== */
    public function teacherShow(int $id)
    {
        $teacher = User::withCount(['classes', 'students'])
            ->with(['subscription.plan'])
            ->findOrFail($id);
        $plans = Plan::active()->orderBy('price')->get();
        $recentOrders = PlanOrder::where('user_id', $teacher->id)->with('plan')->latest('id')->limit(5)->get();

        return view('admin.teacher', compact('teacher', 'plans', 'recentOrders'));
    }

    /** Fragment: thân trang chi tiết GV (AJAX refetch). */
    public function teacherShowPartial(int $id)
    {
        $teacher = User::withCount(['classes', 'students'])
            ->with(['subscription.plan'])
            ->findOrFail($id);
        $plans = Plan::active()->orderBy('price')->get();
        $recentOrders = PlanOrder::where('user_id', $teacher->id)->with('plan')->latest('id')->limit(5)->get();

        return view('admin.partials.teacher-body', compact('teacher', 'plans', 'recentOrders'));
    }

    /** Khoá / mở tài khoản giáo viên. */
    public function toggleStatus(Request $request, int $id)
    {
        $teacher = User::findOrFail($id);
        if ($teacher->id === auth()->id()) {
            return $this->respondError($request, 'email', 'Không thể tự khoá tài khoản của chính mình.');
        }

        $teacher->status = $teacher->status === 'locked' ? 'active' : 'locked';
        $teacher->save();

        return $this->respondOk($request, $teacher->status === 'locked'
            ? 'Đã khoá tài khoản “' . $teacher->name . '”.'
            : 'Đã mở khoá tài khoản “' . $teacher->name . '”.');
    }

    /** Đổi quyền (role) của tài khoản. */
    public function changeRole(Request $request, int $id)
    {
        $teacher = User::findOrFail($id);
        if ($teacher->id === auth()->id()) {
            return $this->respondError($request, 'email', 'Không thể tự đổi quyền của chính mình.');
        }

        $data = $request->validate(['role' => ['required', 'in:owner,super_admin']]);
        $teacher->update(['role' => $data['role']]);

        return $this->respondOk($request, 'Đã đổi quyền “' . $teacher->name . '” thành ' . $data['role'] . '.');
    }

    /** Đặt lại mật khẩu cho giáo viên. */
    public function resetPassword(Request $request, int $id)
    {
        $teacher = User::findOrFail($id);
        $data = $request->validate(['password' => ['required', 'confirmed', Password::min(6)]]);
        $teacher->update(['password' => $data['password']]); // tự hash nhờ cast 'hashed'

        return $this->respondOk($request, 'Đã đặt lại mật khẩu cho “' . $teacher->name . '”.');
    }

    /* ===================== Tạo tài khoản giáo viên mới ===================== */
    public function createTeacher()
    {
        $plans = Plan::active()->orderBy('price')->get();

        return view('admin.teacher-create', compact('plans'));
    }

    public function storeTeacher(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'phone' => ['nullable', 'string', 'max:20'],
            'plan' => ['required', 'string', Rule::exists('plans', 'slug')],
            'months' => ['nullable', 'integer', 'in:1,3,6,12'],
        ]);

        $plan = Plan::where('slug', $data['plan'])->firstOrFail();
        $months = (int) ($data['months'] ?? 1);

        DB::transaction(function () use ($data, $plan, $months, &$teacher) {
            $teacher = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'role' => 'owner',
                'status' => 'active',
            ]);
            $teacher->update(['tenant_id' => $teacher->id]);

            Subscription::create([
                'tenant_id' => $teacher->id,
                'plan_id' => $plan->id,
                'status' => $plan->slug === 'trial' ? 'trial' : 'active',
                'started_at' => now()->toDateString(),
                'current_period_end' => match ($plan->slug) {
                    'vip' => null,                                        // VIP vô hạn
                    'trial' => now()->addMonths(2)->toDateString(),       // Trial free 2 tháng
                    default => now()->addMonths($months)->toDateString(), // gói trả phí
                },
            ]);
        });

        return redirect()->route('admin.teacher', $teacher->id)
            ->with('ok', 'Đã tạo tài khoản “'.$teacher->name.'” với gói '.$plan->name.'.');
    }

    /** Đổi gói cho GV (admin cấp thủ công, không thu tiền). */
    public function setPlan(Request $request, int $id)
    {
        $teacher = User::where('role', 'owner')->findOrFail($id);
        $data = $request->validate([
            'plan' => ['required', 'string', Rule::exists('plans', 'slug')],
            'months' => ['nullable', 'integer', 'in:1,3,6,12'],
        ]);
        $plan = Plan::where('slug', $data['plan'])->firstOrFail();
        $months = (int) ($data['months'] ?? 1);

        $sub = $teacher->subscription ?: new Subscription(['tenant_id' => $teacher->id]);
        $sub->plan_id = $plan->id;
        $sub->status = $plan->slug === 'trial' ? 'trial' : 'active';
        $sub->started_at = $sub->started_at ?: now()->toDateString();
        if ($plan->slug === 'vip') {
            $sub->current_period_end = null;                              // VIP vô hạn
        } elseif ($plan->slug === 'trial') {
            $sub->current_period_end = now()->addMonths(2)->toDateString(); // Trial free 2 tháng
        } else {
            $base = ($sub->current_period_end && Carbon::parse($sub->current_period_end)->isFuture())
                ? Carbon::parse($sub->current_period_end)
                : now();
            $sub->current_period_end = $base->copy()->addMonths($months)->toDateString();
        }
        $sub->save();

        return $this->respondOk($request, 'Đã đặt gói '.$plan->name.' cho “'.$teacher->name.'”.');
    }

    /* ===================== Duyệt thanh toán ===================== */
    public function payments(Request $request)
    {
        $status = $request->get('status', 'pending');
        $q = PlanOrder::with(['user', 'plan'])->latest('id');
        if (in_array($status, PlanOrder::STATUSES, true)) {
            $q->where('status', $status);
        }
        $orders = $q->paginate(20)->withQueryString();

        $pendingCount = PlanOrder::where('status', 'pending')->count();

        return $request->boolean('partial')
            ? view('admin.partials.payments-list', compact('orders', 'pendingCount'))
            : view('admin.payments', compact('orders', 'status', 'pendingCount'));
    }

    public function approvePayment(Request $request, int $id)
    {
        $order = PlanOrder::with(['user', 'plan'])->findOrFail($id);
        if ($order->status !== 'pending') {
            return $this->respondError($request, 'order', 'Đơn không ở trạng thái chờ.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            $sub = $order->user->subscription ?: new Subscription(['tenant_id' => $order->user_id]);
            $sub->plan_id = $order->plan_id;
            $sub->status = 'active';
            $sub->started_at = $sub->started_at ?: now()->toDateString();
            $base = ($sub->current_period_end && Carbon::parse($sub->current_period_end)->isFuture())
                ? Carbon::parse($sub->current_period_end)
                : now();
            $sub->current_period_end = $base->copy()->addMonths((int) $order->months)->toDateString();
            $sub->save();
        });

        return $this->respondOk($request, 'Đã duyệt đơn '.$order->code.' và kích hoạt gói.');
    }

    public function rejectPayment(Request $request, int $id)
    {
        $order = PlanOrder::findOrFail($id);
        if ($order->status !== 'pending') {
            return $this->respondError($request, 'order', 'Đơn không ở trạng thái chờ.');
        }
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);
        $order->update([
            'status' => 'rejected',
            'rejected_reason' => $data['reason'] ?? null,
        ]);

        return $this->respondOk($request, 'Đã từ chối đơn '.$order->code.'.');
    }
}
