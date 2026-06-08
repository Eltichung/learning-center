<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'started_at', 'current_period_end',
    ];

    protected $casts = [
        'started_at' => 'date',
        'current_period_end' => 'date',
    ];

    public function teacher() { return $this->belongsTo(User::class, 'tenant_id'); }
    public function plan()    { return $this->belongsTo(Plan::class); }
}
