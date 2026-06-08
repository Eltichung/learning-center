<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'teacher_id', 'full_name', 'dob', 'school',
        'parent_phone', 'student_code', 'status',
    ];

    protected $casts = ['dob' => 'date'];

    public function teacher(): BelongsTo       { return $this->belongsTo(User::class, 'teacher_id'); }
    public function classStudents(): HasMany   { return $this->hasMany(ClassStudent::class); }
    public function studentSessions(): HasMany { return $this->hasMany(StudentSession::class); }
    public function payments(): HasMany        { return $this->hasMany(Payment::class); }

    /** Công nợ = tổng tiền đã học - tổng đã đóng (tính động, không lưu cứng). */
    public function balanceDue(): int
    {
        $charged = $this->studentSessions()->sum('amount');
        $paid    = $this->payments()->sum('amount');
        return (int) ($charged - $paid);
    }
}
