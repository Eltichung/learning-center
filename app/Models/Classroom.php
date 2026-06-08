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
}
