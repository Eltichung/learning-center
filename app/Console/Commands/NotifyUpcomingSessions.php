<?php

namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Models\PushSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class NotifyUpcomingSessions extends Command
{
    protected $signature = 'lopthem:notify-upcoming
        {--window=10 : Cửa sổ ± phút quanh mốc "còn 1 tiếng"}
        {--dry : Chỉ in ra, không gửi thật}';

    protected $description = 'Gửi web-push tới phụ huynh cho các buổi học bắt đầu sau ~60 phút.';

    public function handle(): int
    {
        $win = max(1, (int) $this->option('window'));
        $dry = (bool) $this->option('dry');

        // Cửa sổ: bây giờ + (60 - win) đến bây giờ + (60 + win) phút
        $now = now();
        $lower = $now->copy()->addMinutes(60 - $win);
        $upper = $now->copy()->addMinutes(60 + $win);

        // Query các session hôm nay chưa gửi và giờ bắt đầu rơi trong cửa sổ
        $sessions = ClassSession::whereIn('type', ['regular', 'makeup'])
            ->whereNull('notified_at')
            ->whereDate('date', $now->toDateString())
            ->with(['classroom' => fn ($q) => $q->with('teacher')])
            ->get()
            ->filter(function ($s) use ($lower, $upper) {
                $start = Carbon::parse($s->date->format('Y-m-d') . ' ' . $s->start_time);

                return $start->betweenIncluded($lower, $upper);
            });

        if ($sessions->isEmpty()) {
            $this->info('Không có buổi nào trong cửa sổ. (' . $lower->format('H:i') . ' – ' . $upper->format('H:i') . ')');

            return self::SUCCESS;
        }

        $vapid = [
            'VAPID' => [
                'subject' => (string) config('webpush.subject'),
                'publicKey' => (string) config('webpush.public_key'),
                'privateKey' => (string) config('webpush.private_key'),
            ],
        ];
        if (empty($vapid['VAPID']['publicKey']) || empty($vapid['VAPID']['privateKey'])) {
            $this->error('Thiếu VAPID keys trong .env');

            return self::FAILURE;
        }

        $webPush = $dry ? null : new WebPush($vapid);
        $sentTotal = 0;
        $sessionsNotified = collect();

        foreach ($sessions as $session) {
            $class = $session->classroom;
            if (! $class) {
                continue;
            }
            $students = $class->classStudents()->with('student.pushSubscriptions')->get()->pluck('student')->filter();
            $subs = $students->flatMap(fn ($s) => $s->pushSubscriptions)->unique('endpoint');
            if ($subs->isEmpty()) {
                $sessionsNotified->push($session);

                continue;
            }

            $start = Carbon::parse($session->start_time)->format('H:i');
            $end = Carbon::parse($session->end_time)->format('H:i');
            $title = ($class->name ?? 'Lớp học') . ' — Còn 1 tiếng nữa vào học';
            $body = 'Buổi ' . $start . '–' . $end . ' hôm nay (' . Carbon::parse($session->date)->format('d/m') . ')';

            foreach ($subs as $sub) {
                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => url('/search/' . optional($sub->student)->student_code),
                    'tag' => 'session-' . $session->id,
                ]);
                if ($dry) {
                    $this->line('[DRY] → ' . $sub->endpoint . ' :: ' . $title . ' | ' . $body);

                    continue;
                }
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'publicKey' => $sub->p256dh,
                        'authToken' => $sub->auth,
                    ]),
                    $payload
                );
            }
            $sessionsNotified->push($session);
        }

        if (! $dry && $webPush) {
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getEndpoint();
                if ($report->isSuccess()) {
                    $sentTotal++;
                    PushSubscription::where('endpoint', $endpoint)->update(['last_seen_at' => now()]);
                } else {
                    $code = $report->getResponse() ? $report->getResponse()->getStatusCode() : 0;
                    // 404/410 = subscription hết hạn → xoá
                    if (in_array($code, [404, 410], true)) {
                        PushSubscription::where('endpoint', $endpoint)->delete();
                        $this->warn('Xoá subscription hết hạn: ' . $endpoint);
                    } else {
                        Log::warning('Web push failed', ['endpoint' => $endpoint, 'code' => $code, 'reason' => $report->getReason()]);
                    }
                }
            }
        }

        foreach ($sessionsNotified as $s) {
            $s->forceFill(['notified_at' => now()])->save();
        }

        $this->info(($dry ? '[DRY] ' : '') . 'Đã xử lý ' . $sessionsNotified->count() . ' buổi · gửi ' . $sentTotal . ' noti.');

        return self::SUCCESS;
    }
}
