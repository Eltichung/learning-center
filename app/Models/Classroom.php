<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model cho bảng "classes".
 * Đặt tên Classroom vì "Class" là từ khoá của PHP, không dùng làm tên class được.
 */
class Classroom extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'teacher_id', 'name', 'type', 'grade', 'subject', 'status', 'note',
        'start_date', 'ended_at', 'default_price',
    ];

    protected $casts = [
        'start_date' => 'date',
        'ended_at' => 'date',
    ];

    public function teacher(): BelongsTo  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function schedules(): HasMany  { return $this->hasMany(ClassSchedule::class, 'class_id'); }
    public function sessions(): HasMany   { return $this->hasMany(ClassSession::class, 'class_id'); }
    public function classStudents(): HasMany { return $this->hasMany(ClassStudent::class, 'class_id'); }

    // Học sinh trong lớp (qua bảng nối class_students), kèm đơn giá/buổi
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_students', 'class_id', 'student_id')
                    ->withPivot(['price_per_session', 'joined_at', 'left_at', 'status'])
                    ->withTimestamps();
    }

    /* ---------- Nhãn hiển thị ---------- */

    public const WEEKDAYS = [1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN'];

    public static function weekdayLabel(int $w): string
    {
        return self::WEEKDAYS[$w] ?? '?';
    }

    public function typeLabel(): string
    {
        return $this->type === 'tutor_1on1' ? 'Gia sư' : 'Học thêm';
    }

    public function typeChip(): string
    {
        return $this->type === 'tutor_1on1' ? 'b' : 'n';
    }

    public function gradeLabel(): string
    {
        return $this->grade ? 'Lớp ' . $this->grade : '—';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paused' => 'Tạm dừng',
            'ended'  => 'Kết thúc',
            default  => 'Hoạt động',
        };
    }

    public function statusChip(): string
    {
        return match ($this->status) {
            'paused' => 'a',
            'ended'  => 'n',
            default  => 'g',
        };
    }

    /** "T2, T4, T6 · 17:30" từ các lịch cố định */
    public function scheduleLabel(): string
    {
        $items = $this->schedules->sortBy('weekday');
        if ($items->isEmpty()) {
            return '—';
        }
        $days = $items->map(fn ($s) => self::weekdayLabel((int) $s->weekday))->implode(', ');
        $time = \Illuminate\Support\Carbon::parse($items->first()->start_time)->format('H:i');

        return $days . ' · ' . $time;
    }
}
