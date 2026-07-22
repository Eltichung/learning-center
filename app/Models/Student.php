<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'teacher_id', 'full_name', 'dob', 'school',
        'parent_phone', 'parent_contact', 'student_code', 'status',
    ];

    protected $casts = ['dob' => 'date'];

    public function teacher(): BelongsTo       { return $this->belongsTo(User::class, 'teacher_id'); }
    public function classStudents(): HasMany   { return $this->hasMany(ClassStudent::class); }
    public function studentSessions(): HasMany { return $this->hasMany(StudentSession::class); }
    public function payments(): HasMany        { return $this->hasMany(Payment::class); }
    public function comments(): HasMany        { return $this->hasMany(StudentComment::class); }
    public function statusLogs(): HasMany      { return $this->hasMany(StudentStatusLog::class); }

    /** Công nợ = tổng tiền đã học - tổng đã đóng (tính động, không lưu cứng). */
    public function balanceDue(): int
    {
        $charged = $this->studentSessions()->sum('amount');
        $paid    = $this->payments()->sum('amount');
        return (int) ($charged - $paid);
    }

    /** Viết tắt: "Nguyễn Bảo An" -> "NA" (chữ đầu của từ đầu + từ cuối) */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->full_name)) ?: [];
        if (count($parts) === 0) {
            return '?';
        }
        $first = mb_substr($parts[0], 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first . $last);
    }
}
