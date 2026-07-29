<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'price', 'limits', 'is_active', 'is_public'];

    protected $casts = ['limits' => 'array', 'is_active' => 'boolean', 'is_public' => 'boolean'];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopePublic($q) { return $q->where('is_public', true); }

    /** Gói không giới hạn (limits[key] = null hoặc thiếu) cho VIP. */
    public function isUnlimited(string $key): bool
    {
        return array_key_exists($key, $this->limits ?? [])
            ? is_null($this->limits[$key])
            : false;
    }
}
