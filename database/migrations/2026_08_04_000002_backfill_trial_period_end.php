<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trial giờ chỉ miễn phí 2 tháng. Các subscription trial cũ đang để
 * current_period_end = null (vô hạn) → đặt hạn = ngày bắt đầu + 2 tháng.
 */
return new class extends Migration
{
    public function up(): void
    {
        $trialId = DB::table('plans')->where('slug', 'trial')->value('id');
        if (! $trialId) {
            return;
        }

        DB::table('subscriptions')
            ->where('plan_id', $trialId)
            ->whereNull('current_period_end')
            ->orderBy('id')
            ->get()
            ->each(function ($s) {
                $base = $s->started_at ?: $s->created_at;
                $end = Carbon::parse($base)->addMonths(2)->toDateString();
                DB::table('subscriptions')->where('id', $s->id)->update(['current_period_end' => $end]);
            });
    }

    public function down(): void
    {
        // Không revert: không thể phân biệt hạn do backfill với hạn đặt tay.
    }
};
