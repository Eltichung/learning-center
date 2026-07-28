<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassStudentPriceLog extends Model
{
    protected $fillable = ['class_id', 'student_id', 'user_id', 'old_price', 'new_price'];

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function student(): BelongsTo   { return $this->belongsTo(Student::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
}
