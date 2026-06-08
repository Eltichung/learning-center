<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'tenant_id', 'account_prefix', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    // Một giáo viên có nhiều lớp / học sinh / thanh toán
    public function classes(): HasMany   { return $this->hasMany(Classroom::class, 'teacher_id'); }
    public function students(): HasMany  { return $this->hasMany(Student::class, 'teacher_id'); }
    public function payments(): HasMany  { return $this->hasMany(Payment::class, 'teacher_id'); }
    public function subscription()       { return $this->hasOne(Subscription::class, 'tenant_id'); }
}
