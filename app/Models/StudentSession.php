<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSession extends Model
{
    /** Mọi trạng thái điểm danh hợp lệ */
    public const STATUSES = ['present', 'makeup', 'excused', 'absent'];

    /** Trạng thái có tính tiền buổi đó: có mặt, học bù, vắng không phép. Vắng có phép (excused) được miễn. */
    public const BILLABLE = ['present', 'makeup', 'absent'];

    protected $fillable = [
        'class_session_id', 'student_id', 'status', 'session_units', 'amount',
    ];

    protected $casts = ['session_units' => 'decimal:2'];

    public function classSession() { return $this->belongsTo(ClassSession::class); }
    public function student()      { return $this->belongsTo(Student::class); }
}
