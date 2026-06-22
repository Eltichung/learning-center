<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentComment extends Model
{
    protected $fillable = ['student_id', 'teacher_id', 'comment_date', 'body'];

    protected $casts = ['comment_date' => 'date'];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
}
