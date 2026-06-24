<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
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

        $teachers = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('admin.teachers', compact('teachers', 'q', 'status'));
    }

    /* ===================== Chi tiết / quản lý giáo viên ===================== */
    public function teacherShow(int $id)
    {
        $teacher = User::withCount(['classes', 'students'])->findOrFail($id);

        return view('admin.teacher', compact('teacher'));
    }

    /** Khoá / mở tài khoản giáo viên. */
    public function toggleStatus(int $id)
    {
        $teacher = User::findOrFail($id);
        if ($teacher->id === auth()->id()) {
            return back()->withErrors(['email' => 'Không thể tự khoá tài khoản của chính mình.']);
        }

        $teacher->status = $teacher->status === 'locked' ? 'active' : 'locked';
        $teacher->save();

        return back()->with('ok', $teacher->status === 'locked'
            ? 'Đã khoá tài khoản “' . $teacher->name . '”.'
            : 'Đã mở khoá tài khoản “' . $teacher->name . '”.');
    }

    /** Đổi quyền (role) của tài khoản. */
    public function changeRole(Request $request, int $id)
    {
        $teacher = User::findOrFail($id);
        if ($teacher->id === auth()->id()) {
            return back()->withErrors(['email' => 'Không thể tự đổi quyền của chính mình.']);
        }

        $data = $request->validate(['role' => ['required', 'in:owner,super_admin']]);
        $teacher->update(['role' => $data['role']]);

        return back()->with('ok', 'Đã đổi quyền “' . $teacher->name . '” thành ' . $data['role'] . '.');
    }

    /** Đặt lại mật khẩu cho giáo viên. */
    public function resetPassword(Request $request, int $id)
    {
        $teacher = User::findOrFail($id);
        $data = $request->validate(['password' => ['required', 'confirmed', Password::min(6)]]);
        $teacher->update(['password' => $data['password']]); // tự hash nhờ cast 'hashed'

        return back()->with('ok', 'Đã đặt lại mật khẩu cho “' . $teacher->name . '”.');
    }
}
