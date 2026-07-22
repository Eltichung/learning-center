<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* Bắn noti trước 2 tiếng cho mỗi buổi học — chạy mỗi 30 phút.
 * --window=16 để mọi session luôn khớp ít nhất 1 lần trong 1 chu kỳ 30 phút
 * (cửa sổ [104p, 136p] quanh mốc "còn 2 tiếng"). */
Schedule::command('lopthem:notify-upcoming --window=16')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();
