<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSession extends Model
{
    protected $fillable = [
        'class_session_id', 'student_id', 'status', 'session_units', 'amount',
    ];

    protected $casts = ['session_units' => 'decimal:2'];

    public function classSession() { return $this->belongsTo(ClassSession::class); }
    public function student()      { return $this->belongsTo(Student::class); }
}
