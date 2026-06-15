<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    protected $fillable = [
        'class_id', 'date', 'start_time', 'end_time',
        'type', 'makeup_for_id', 'note', 'attendance_submitted_at',
    ];

    protected $casts = ['date' => 'date', 'attendance_submitted_at' => 'datetime'];

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function studentSessions(): HasMany { return $this->hasMany(StudentSession::class); }
    public function logs(): HasMany { return $this->hasMany(AttendanceLog::class); }

    // Buổi nghỉ gốc mà buổi này bù cho
    public function makeupFor(): BelongsTo { return $this->belongsTo(ClassSession::class, 'makeup_for_id'); }
    // Các buổi bù của buổi nghỉ này
    public function makeups(): HasMany     { return $this->hasMany(ClassSession::class, 'makeup_for_id'); }
}
