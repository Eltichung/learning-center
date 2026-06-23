<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStatusLog extends Model
{
    protected $fillable = ['student_id', 'user_id', 'action'];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
