<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — LớpThêm
|--------------------------------------------------------------------------
| - Khu giáo viên (admin): yêu cầu đăng nhập (middleware 'auth').
| - Khu phụ huynh: công khai, truy cập bằng mã/slug, không cần đăng nhập.
*/

// Trang chủ -> vào thẳng admin (chưa đăng nhập sẽ bị 'auth' đẩy về /login)
Route::get('/', fn () => redirect()->route('teacher.dashboard'));

/* ---------------- Xác thực (chỉ cho khách chưa đăng nhập) ---------------- */
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
    Route::view('/dashboard', 'teacher.dashboard', ['active' => 'dashboard'])->name('teacher.dashboard');
    Route::view('/classes',   'teacher.classes',   ['active' => 'classes'])->name('teacher.classes');
    Route::view('/students',  'teacher.students',  ['active' => 'students'])->name('teacher.students');
    Route::view('/attendance', 'teacher.attendance', ['active' => 'attendance'])->name('teacher.attendance');
    Route::view('/fees',      'teacher.fees',      ['active' => 'fees'])->name('teacher.fees');
    Route::view('/reports',   'teacher.reports',   ['active' => 'reports'])->name('teacher.reports');

    Route::get('/classes/{id}', fn ($id) => view('teacher.class-detail', ['active' => 'classes', 'id' => $id]))
        ->name('teacher.class');
    Route::get('/students/{id}', fn ($id) => view('teacher.student', ['active' => 'students', 'id' => $id]))
        ->name('teacher.student');
});

/* ---------------- Khu phụ huynh (công khai) ---------------- */
Route::view('/tra-cuu', 'parent.search', ['navActive' => 'p-search', 'stageTitle' => 'Trang tra cứu phụ huynh'])
    ->name('parent.search');

Route::get('/p/{slug}', fn ($slug) => view('parent.info', ['slug' => $slug, 'navActive' => 'p-info', 'stageTitle' => 'Thông tin học sinh']))
    ->name('parent.info');
Route::get('/p/{slug}/lich-su', fn ($slug) => view('parent.history', ['slug' => $slug, 'navActive' => 'p-history', 'stageTitle' => 'Lịch sử học (theo tuần)']))
    ->name('parent.history');
