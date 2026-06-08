<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    protected $fillable = ['class_id', 'weekday', 'start_time', 'end_time'];

    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
}
