<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommentType extends Model
{
    protected $fillable = ['teacher_id', 'key', 'name', 'icon', 'color', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function templates(): HasMany { return $this->hasMany(CommentTemplate::class); }

    /** Biến CSS (--cb nền, --cf chữ) cho chip loại — map theo hậu tố màu. */
    public function paletteStyle(): string
    {
        $map = [
            'b' => ['#e8f0fc', '#2f6fd6'], 'a' => ['#fdf2db', '#c8860a'], 'g' => ['#e3f4ec', '#1f9d72'],
            'p' => ['#f3e5f5', '#6a1b9a'], 'r' => ['#fbe7e7', '#d64545'], 'n' => ['#eef0f3', '#6b7280'],
        ];
        [$bg, $fg] = $map[$this->color] ?? $map['n'];

        return "--cb:{$bg};--cf:{$fg}";
    }

    /** Loại dùng được cho 1 giáo viên = loại hệ thống (teacher_id null) + loại của chính GV đó. */
    public function scopeUsableBy(Builder $q, ?int $tid): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('teacher_id')
                ->when($tid, fn ($x) => $x->orWhere('teacher_id', $tid)));
    }
}
