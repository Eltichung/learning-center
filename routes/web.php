<?php

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

Route::get('/', fn () => redirect()->route('teacher.dashboard'));

/* ---------------- Xác thực (khách chưa đăng nhập) ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('teacher.login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('teacher.register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('teacher.logout');

/* ---------------- Khu giáo viên (admin) — yêu cầu đăng nhập ---------------- */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');

    Route::get('/classes', [TeacherController::class, 'classes'])->name('teacher.classes');
    Route::post('/classes', [TeacherController::class, 'storeClass'])->name('teacher.classes.store');
    Route::put('/classes/{id}', [TeacherController::class, 'updateClass'])->name('teacher.classes.update');
    Route::post('/classes/{id}/duplicate', [TeacherController::class, 'duplicateClass'])->name('teacher.classes.duplicate');
    Route::get('/classes/{id}', [TeacherController::class, 'classShow'])->name('teacher.class');
    Route::post('/classes/{id}/students', [TeacherController::class, 'addStudentToClass'])->name('teacher.class.addStudent');
    Route::put('/classes/{id}/students/{studentId}/price', [TeacherController::class, 'updateClassStudentPrice'])->name('teacher.class.student.price');
    Route::get('/classes/{id}/students/{studentId}/price-history', [TeacherController::class, 'classStudentPriceHistory'])->name('teacher.class.student.priceHistory');

    Route::get('/students', [TeacherController::class, 'students'])->name('teacher.students');
    Route::post('/students', [TeacherController::class, 'storeStudent'])->name('teacher.students.store');
    Route::put('/students/{id}', [TeacherController::class, 'updateStudent'])->name('teacher.students.update');
    Route::put('/students/{id}/status', [TeacherController::class, 'toggleStudentStatus'])->name('teacher.students.toggleStatus');
    Route::get('/students/{id}', [TeacherController::class, 'studentShow'])->name('teacher.student');
    Route::post('/students/{id}/comments', [TeacherController::class, 'storeComment'])->name('teacher.student.comments.store');
    Route::delete('/students/{id}/comments/{commentId}', [TeacherController::class, 'deleteComment'])->name('teacher.student.comments.delete');

    Route::get('/attendance', [TeacherController::class, 'attendance'])->name('teacher.attendance');
    Route::post('/attendance/{session}', [TeacherController::class, 'submitAttendance'])->name('teacher.attendance.submit');
    Route::post('/attendance/{session}/off', [TeacherController::class, 'markSessionOff'])->name('teacher.attendance.off');
    Route::post('/attendance/{session}/unoff', [TeacherController::class, 'unmarkSessionOff'])->name('teacher.attendance.unoff');
    Route::post('/attendance/{session}/makeup', [TeacherController::class, 'addMakeup'])->name('teacher.attendance.makeup');
    Route::post('/attendance/{session}/no-makeup', [TeacherController::class, 'toggleNoMakeup'])->name('teacher.attendance.noMakeup');
    Route::post('/sessions', [TeacherController::class, 'createSession'])->name('teacher.sessions.create');

    // Giáo án
    Route::get('/lessons', [TeacherController::class, 'lessonsIndex'])->name('teacher.lessons');
    Route::post('/lessons', [TeacherController::class, 'lessonsBatchSave'])->name('teacher.lessons.save');
    Route::put('/sessions/{session}/lesson', [TeacherController::class, 'updateSessionLesson'])->name('teacher.session.lesson');
    Route::delete('/sessions/{session}/lesson', [TeacherController::class, 'clearSessionLesson'])->name('teacher.session.lesson.clear');

    Route::post('/payments', [TeacherController::class, 'storePayment'])->name('teacher.payments.store');

    Route::get('/fees', [TeacherController::class, 'fees'])->name('teacher.fees');
    Route::get('/reports', [TeacherController::class, 'reports'])->name('teacher.reports');

    // Cài đặt QR chuyển khoản của giáo viên
    Route::get('/settings/qr', [TeacherController::class, 'qrSettings'])->name('teacher.settings.qr');
    Route::post('/settings/qr', [TeacherController::class, 'updateQrSettings'])->name('teacher.settings.qr.update');

    // AJAX
    Route::get('/api/students/search', [TeacherController::class, 'searchStudents'])->name('api.students.search');
    Route::get('/api/students/{id}/monthly', [TeacherController::class, 'studentMonthly'])->name('api.student.monthly');
});

/* ---------------- Khu phụ huynh (công khai) ---------------- */
Route::get('/search', [LookupController::class, 'search'])->name('parent.search');
Route::post('/search', [LookupController::class, 'find']);
Route::get('/search/{slug}', [LookupController::class, 'show'])->name('parent.info');
Route::get('/search/{slug}/lich-su', [LookupController::class, 'history'])->name('parent.history');
Route::post('/search/{slug}/push/subscribe', [LookupController::class, 'pushSubscribe'])->name('parent.push.subscribe');
Route::post('/search/{slug}/push/unsubscribe', [LookupController::class, 'pushUnsubscribe'])->name('parent.push.unsubscribe');
