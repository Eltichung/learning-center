<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'price_per_session',
        'joined_at', 'left_at', 'status',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'left_at'   => 'date',
    ];

    public function student()   { return $this->belongsTo(Student::class); }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
}
