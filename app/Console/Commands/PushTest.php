<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Models\Student;
use Illuminate\Console\Command;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushTest extends Command
{
    protected $signature = 'lopthem:push-test
        {slug : student_code (VD: 0388787438)}
        {--title=🔔 Test thông báo}
        {--body=Bạn đã cài đặt thành công. Đây là noti thử.}';

    protected $description = 'Bắn 1 web-push thử tới tất cả subscription của 1 học sinh.';

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $student = Student::where('student_code', $slug)->first();
        if (! $student) {
            $this->error("Không tìm thấy học sinh với mã: {$slug}");

            return self::FAILURE;
        }

        $subs = $student->pushSubscriptions()->get();
        if ($subs->isEmpty()) {
            $this->warn('Học sinh chưa có thiết bị nào đăng ký thông báo.');

            return self::SUCCESS;
        }

        $vapid = [
            'VAPID' => [
                'subject' => (string) config('webpush.subject'),
                'publicKey' => (string) config('webpush.public_key'),
                'privateKey' => (string) config('webpush.private_key'),
            ],
        ];
        if (empty($vapid['VAPID']['publicKey'])) {
            $this->error('Thiếu VAPID keys trong .env');

            return self::FAILURE;
        }

        $webPush = new WebPush($vapid);
        $payload = json_encode([
            'title' => (string) $this->option('title'),
            'body' => (string) $this->option('body'),
            'url' => url('/search/' . $slug),
            'tag' => 'test-' . time(),
        ]);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh,
                    'authToken' => $sub->auth,
                ]),
                $payload
            );
        }

        $ok = 0;
        $fail = 0;
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();
            if ($report->isSuccess()) {
                $ok++;
                $this->info('✓ ' . substr($endpoint, 0, 60) . '...');
            } else {
                $fail++;
                $code = $report->getResponse() ? $report->getResponse()->getStatusCode() : 0;
                $this->error('✗ [' . $code . '] ' . $report->getReason() . ' — ' . substr($endpoint, 0, 60) . '...');
                if (in_array($code, [404, 410], true)) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                    $this->warn('   → đã xoá subscription hết hạn');
                }
            }
        }

        $this->info("Gửi xong. OK={$ok}, Fail={$fail}");

        return self::SUCCESS;
    }
}
