<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'price', 'limits'];

    protected $casts = ['limits' => 'array'];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
