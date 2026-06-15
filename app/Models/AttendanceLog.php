<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'class_session_id', 'user_id', 'action',
        'present_count', 'total_amount', 'snapshot',
    ];

    protected $casts = ['snapshot' => 'array'];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
