<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentComment extends Model
{
    protected $fillable = ['student_id', 'teacher_id', 'comment_type_id', 'rating', 'comment_date', 'body'];

    protected $casts = ['comment_date' => 'date'];

    /** Mức đánh giá: key => [nhãn, class chip màu (g/b/a)]. */
    public const RATINGS = [
        'tot' => ['label' => 'Tốt', 'chip' => 'g'],
        'kha' => ['label' => 'Khá', 'chip' => 'b'],
        'tb' => ['label' => 'Trung bình', 'chip' => 'a'],
    ];

    public function ratingLabel(): ?string { return self::RATINGS[$this->rating]['label'] ?? null; }

    public function ratingChip(): ?string { return self::RATINGS[$this->rating]['chip'] ?? null; }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }

    public function type(): BelongsTo { return $this->belongsTo(CommentType::class, 'comment_type_id'); }
}
