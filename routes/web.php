<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — LớpThêm (Bước 1: chỉ giao diện, dữ liệu tĩnh)
|--------------------------------------------------------------------------
| Tất cả route hiện trả thẳng view tĩnh. Bước sau sẽ thay bằng Controller
| + truy vấn DB. ID/slug để placeholder cho khớp với cấu trúc thật.
*/

// Trang chủ -> đăng nhập giáo viên
Route::get('/', fn () => redirect()->route('teacher.login'));

/* ---------------- Khu giáo viên (desktop) ---------------- */
Route::view('/login', 'teacher.login')->name('teacher.login');

Route::view('/dashboard', 'teacher.dashboard', ['active' => 'dashboard'])->name('teacher.dashboard');
Route::view('/classes',   'teacher.classes',   ['active' => 'classes'])->name('teacher.classes');
Route::view('/students',  'teacher.students',  ['active' => 'students'])->name('teacher.students');
Route::view('/attendance','teacher.attendance',['active' => 'attendance'])->name('teacher.attendance');
Route::view('/fees',      'teacher.fees',      ['active' => 'fees'])->name('teacher.fees');
Route::view('/reports',   'teacher.reports',   ['active' => 'reports'])->name('teacher.reports');


Route::get('/classes/{id}', fn ($id) => view('teacher.class-detail', ['active' => 'classes', 'id' => $id]))
    ->name('teacher.class');
Route::get('/students/{id}', fn ($id) => view('teacher.student', ['active' => 'students', 'id' => $id]))
    ->name('teacher.student');


Route::view('/tra-cuu', 'parent.search')->name('parent.search');

Route::get('/p/{slug}', fn ($slug) => view('parent.info', ['slug' => $slug]))
    ->name('parent.info');
Route::get('/p/{slug}/lich-su', fn ($slug) => view('parent.history', ['slug' => $slug]))
    ->name('parent.history');
