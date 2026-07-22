<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* Bắn noti trước 1 tiếng cho mỗi buổi học — chạy mỗi phút */
Schedule::command('lopthem:notify-upcoming')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
