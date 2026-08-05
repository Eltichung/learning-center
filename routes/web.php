<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — LớpThêm
|--------------------------------------------------------------------------
| - Khu giáo viên (admin): yêu cầu đăng nhập (middleware 'auth'), render DB.
| - Khu phụ huynh: công khai, tra cứu theo mã học sinh, render DB.
*/

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role === 'super_admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('teacher.dashboard');
});

/* PWA manifest — build động để start_url luôn khớp route hiện tại */
Route::get('/manifest.json', function () {
    return response()->json([
        'name' => 'Học Chưa — Tra cứu phụ huynh',
        'short_name' => 'Học Chưa',
        'description' => 'Theo dõi lịch học, học phí và nhận xét của con.',
        'start_url' => route('parent.search', [], false),
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'portrait',
        'background_color' => '#f6f7f9',
        'theme_color' => '#c96442',
        'lang' => 'vi',
        'icons' => [
            ['src' => '/favicon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/favicon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ],
    ])->header('Content-Type', 'application/manifest+json')
        ->header('Cache-Control', 'no-cache, must-revalidate');
})->name('pwa.manifest');

/* ---------------- Xác thực (khách chưa đăng nhập) ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('teacher.login');
    Route::post('/login', [AuthController::class, 'login']);
    // /register bị ẩn: chỉ admin tạo tài khoản trong /admin/users/create
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('teacher.logout');

/* ---------------- Khu giáo viên (admin) — yêu cầu đăng nhập + sub còn hạn cho hành động ghi ---------------- */
Route::middleware(['auth', 'active.sub'])->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');

    Route::get('/classes', [TeacherController::class, 'classes'])->name('teacher.classes');
    Route::get('/classes/partial', [TeacherController::class, 'classesPartial'])->name('teacher.classes.partial');
    Route::post('/classes', [TeacherController::class, 'storeClass'])->name('teacher.classes.store');
    Route::put('/classes/{id}', [TeacherController::class, 'updateClass'])->name('teacher.classes.update');
    Route::post('/classes/{id}/duplicate', [TeacherController::class, 'duplicateClass'])->name('teacher.classes.duplicate');
    Route::get('/classes/{id}', [TeacherController::class, 'classShow'])->name('teacher.class');
    Route::get('/classes/{id}/partial', [TeacherController::class, 'classShowPartial'])->name('teacher.class.partial');
    Route::post('/classes/{id}/students', [TeacherController::class, 'addStudentToClass'])->name('teacher.class.addStudent');
    Route::put('/classes/{id}/students/{studentId}/price', [TeacherController::class, 'updateClassStudentPrice'])->name('teacher.class.student.price');
    Route::get('/classes/{id}/students/{studentId}/price-history', [TeacherController::class, 'classStudentPriceHistory'])->name('teacher.class.student.priceHistory');

    Route::get('/students', [TeacherController::class, 'students'])->name('teacher.students');
    Route::get('/students/partial', [TeacherController::class, 'studentsPartial'])->name('teacher.students.partial');
    Route::post('/students', [TeacherController::class, 'storeStudent'])->name('teacher.students.store');
    Route::put('/students/{id}', [TeacherController::class, 'updateStudent'])->name('teacher.students.update');
    Route::put('/students/{id}/status', [TeacherController::class, 'toggleStudentStatus'])->name('teacher.students.toggleStatus');
    Route::put('/students/{id}/show-fees', [TeacherController::class, 'toggleShowFees'])->name('teacher.students.toggleShowFees');
    Route::get('/students/{id}', [TeacherController::class, 'studentShow'])->name('teacher.student');
    Route::get('/students/{id}/partial', [TeacherController::class, 'studentShowPartial'])->name('teacher.student.partial');
    Route::post('/students/{id}/comments', [TeacherController::class, 'storeComment'])->name('teacher.student.comments.store');
    Route::delete('/students/{id}/comments/{commentId}', [TeacherController::class, 'deleteComment'])->name('teacher.student.comments.delete');

    Route::get('/attendance', [TeacherController::class, 'attendance'])->name('teacher.attendance');
    Route::get('/attendance/partial', [TeacherController::class, 'attendancePartial'])->name('teacher.attendance.partial');
    Route::post('/attendance/{session}', [TeacherController::class, 'submitAttendance'])->name('teacher.attendance.submit');
    Route::post('/attendance/{session}/off', [TeacherController::class, 'markSessionOff'])->name('teacher.attendance.off');
    Route::post('/attendance/{session}/unoff', [TeacherController::class, 'unmarkSessionOff'])->name('teacher.attendance.unoff');
    Route::post('/attendance/{session}/makeup', [TeacherController::class, 'addMakeup'])->name('teacher.attendance.makeup');
    Route::post('/attendance/{session}/no-makeup', [TeacherController::class, 'toggleNoMakeup'])->name('teacher.attendance.noMakeup');
    Route::post('/sessions', [TeacherController::class, 'createSession'])->name('teacher.sessions.create');

    // Giáo án
    Route::get('/lessons', [TeacherController::class, 'lessonsIndex'])->name('teacher.lessons');
    Route::get('/lessons/partial', [TeacherController::class, 'lessonsPartial'])->name('teacher.lessons.partial');
    Route::post('/lessons', [TeacherController::class, 'lessonsBatchSave'])->name('teacher.lessons.save');
    Route::put('/sessions/{session}/lesson', [TeacherController::class, 'updateSessionLesson'])->name('teacher.session.lesson');
    Route::delete('/sessions/{session}/lesson', [TeacherController::class, 'clearSessionLesson'])->name('teacher.session.lesson.clear');

    Route::post('/payments', [TeacherController::class, 'storePayment'])->name('teacher.payments.store');

    Route::get('/fees', [TeacherController::class, 'fees'])->name('teacher.fees');
    Route::get('/fees/partial', [TeacherController::class, 'feesPartial'])->name('teacher.fees.partial');
    Route::get('/reports', [TeacherController::class, 'reports'])->name('teacher.reports');
    Route::get('/reports/partial', [TeacherController::class, 'reportsPartial'])->name('teacher.reports.partial');

    // Cài đặt QR chuyển khoản của giáo viên
    Route::get('/settings/qr', [TeacherController::class, 'qrSettings'])->name('teacher.settings.qr');
    Route::get('/settings/qr/partial', [TeacherController::class, 'qrSettingsPartial'])->name('teacher.settings.qr.partial');
    Route::post('/settings/qr', [TeacherController::class, 'updateQrSettings'])->name('teacher.settings.qr.update');

    // AJAX
    Route::get('/api/students/search', [TeacherController::class, 'searchStudents'])->name('api.students.search');
    Route::get('/api/students/{id}/monthly', [TeacherController::class, 'studentMonthly'])->name('api.student.monthly');
});

/* ---------------- Cài đặt: Sao lưu dữ liệu — cho tải kể cả khi gói hết hạn (data portability) ---------------- */
Route::middleware('auth')->group(function () {
    Route::get('/settings/backup', [TeacherController::class, 'backupSettings'])->name('teacher.settings.backup');
    Route::get('/settings/backup/download', [TeacherController::class, 'exportBackup'])->name('teacher.settings.backup.download');
});

/* ---------------- Mua gói (billing) — luôn cho vào, không cần active.sub ---------------- */
Route::middleware('auth')->prefix('billing')->name('billing.')->group(function () {
    Route::get('/', [\App\Http\Controllers\BillingController::class, 'index'])->name('index');
    Route::post('/order', [\App\Http\Controllers\BillingController::class, 'createOrder'])->name('order.create');
    Route::post('/order/{code}/notify', [\App\Http\Controllers\BillingController::class, 'notifyPaid'])->name('order.notify');
    Route::post('/order/{code}/cancel', [\App\Http\Controllers\BillingController::class, 'cancelOrder'])->name('order.cancel');
});

/* ---------------- Khu quản trị (super_admin) ---------------- */
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/view-as', [AdminController::class, 'setViewTeacher'])->name('viewAs');
    Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers');
    Route::get('/teachers/create', [AdminController::class, 'createTeacher'])->name('teachers.create');
    Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('teachers.store');
    Route::get('/teachers/{id}', [AdminController::class, 'teacherShow'])->name('teacher');
    Route::get('/teachers/{id}/partial', [AdminController::class, 'teacherShowPartial'])->name('teacher.partial');
    Route::put('/teachers/{id}/status', [AdminController::class, 'toggleStatus'])->name('teacher.toggleStatus');
    Route::put('/teachers/{id}/role', [AdminController::class, 'changeRole'])->name('teacher.role');
    Route::put('/teachers/{id}/password', [AdminController::class, 'resetPassword'])->name('teacher.password');
    Route::put('/teachers/{id}/plan', [AdminController::class, 'setPlan'])->name('teacher.plan');

    // Duyệt thanh toán gói
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{id}/approve', [AdminController::class, 'approvePayment'])->name('payment.approve');
    Route::post('/payments/{id}/reject', [AdminController::class, 'rejectPayment'])->name('payment.reject');
});

/* ---------------- Khu phụ huynh (công khai) ---------------- */
Route::get('/search', [LookupController::class, 'search'])->name('parent.search');
Route::post('/search', [LookupController::class, 'find']);
Route::get('/search/{slug}', [LookupController::class, 'show'])->name('parent.info');
Route::get('/search/{slug}/lich-su', [LookupController::class, 'history'])->name('parent.history');
