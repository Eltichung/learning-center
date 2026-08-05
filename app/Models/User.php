<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'tenant_id', 'account_prefix', 'status',
        'qr_image_path', 'last_backup_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'last_backup_at' => 'datetime'];
    }

    /** Đã sao lưu dữ liệu trong hôm nay chưa (theo giờ VN)? */
    public function backedUpToday(): bool
    {
        return $this->last_backup_at !== null && $this->last_backup_at->isToday();
    }

    // Một giáo viên có nhiều lớp / học sinh / thanh toán
    public function classes(): HasMany   { return $this->hasMany(Classroom::class, 'teacher_id'); }
    public function students(): HasMany  { return $this->hasMany(Student::class, 'teacher_id'); }
    public function payments(): HasMany  { return $this->hasMany(Payment::class, 'teacher_id'); }
    public function subscription()       { return $this->hasOne(Subscription::class, 'tenant_id'); }
    public function planOrders(): HasMany { return $this->hasMany(PlanOrder::class); }

    /* ===================== Role ===================== */
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }

    /* ===================== Plan & subscription ===================== */

    /** Plan hiện tại (theo subscription còn hạn); fallback Trial. */
    public function currentPlan(): Plan
    {
        $sub = $this->subscription;
        if ($sub && $sub->plan) {
            // VIP luôn còn hạn; Trial + gói trả phí phải còn ngày (current_period_end).
            if ($sub->plan->slug === 'vip' || $this->subscriptionActive()) {
                return $sub->plan;
            }
        }

        return Plan::where('slug', 'trial')->firstOrFail();
    }

    /** Subscription còn hạn không (Trial/VIP → luôn true; plan trả phí → theo current_period_end). */
    public function subscriptionActive(): bool
    {
        $sub = $this->subscription;
        if (! $sub || ! $sub->plan) {
            return true; // chưa có sub → treat như trial mặc định
        }
        if ($sub->plan->slug === 'vip') {
            return true; // chỉ VIP là vô hạn; Trial nay có hạn 2 tháng
        }
        if (! $sub->current_period_end) {
            return false;
        }

        return Carbon::parse($sub->current_period_end)->endOfDay()->isFuture();
    }

    /** Số ngày còn lại (null nếu trial/VIP hoặc không xác định). */
    public function subscriptionDaysLeft(): ?int
    {
        $sub = $this->subscription;
        if (! $sub || ! $sub->plan || $sub->plan->slug === 'vip') {
            return null; // VIP vô hạn; Trial nay cũng đếm ngày còn lại như gói trả phí
        }
        if (! $sub->current_period_end) {
            return null;
        }
        $days = (int) now()->startOfDay()->diffInDays(Carbon::parse($sub->current_period_end)->endOfDay(), false);

        return $days;
    }

    /* ===================== Usage & quota ===================== */

    public function usage(): array
    {
        return [
            'classes'  => (int) Classroom::where('teacher_id', $this->id)->where('status', 'active')->count(),
            'students' => (int) Student::where('teacher_id', $this->id)->where('status', 'active')->count(),
        ];
    }

    public function canCreateClass(): bool
    {
        $plan = $this->currentPlan();
        if ($plan->isUnlimited('classes')) return true;

        return $this->usage()['classes'] < (int) ($plan->limits['classes'] ?? 0);
    }

    public function canAddStudent(): bool
    {
        $plan = $this->currentPlan();
        if ($plan->isUnlimited('students')) return true;

        return $this->usage()['students'] < (int) ($plan->limits['students'] ?? 0);
    }
}
