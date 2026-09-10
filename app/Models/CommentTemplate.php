<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentTemplate extends Model
{
    protected $fillable = ['teacher_id', 'comment_type_id', 'key', 'body', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function type(): BelongsTo { return $this->belongsTo(CommentType::class, 'comment_type_id'); }

    /** Mẫu dùng được cho 1 giáo viên = mẫu hệ thống (teacher_id null) + mẫu của chính GV đó. */
    public function scopeUsableBy(Builder $q, ?int $tid): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('teacher_id')
                ->when($tid, fn ($x) => $x->orWhere('teacher_id', $tid)));
    }
}
