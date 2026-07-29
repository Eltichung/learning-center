<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanOrder extends Model
{
    protected $fillable = [
        'code', 'user_id', 'plan_id', 'amount', 'months', 'status',
        'notified_at', 'approved_at', 'approved_by', 'rejected_reason', 'note',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function plan(): BelongsTo     { return $this->belongsTo(Plan::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
