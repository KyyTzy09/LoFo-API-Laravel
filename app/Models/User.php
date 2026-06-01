<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $primaryKey = 'userId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'userId',
        'phone_number',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->userId) {
                $user->userId = (string) Str::ulid();
            }
        });
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'userId');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'user_id', 'userId');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'user_id', 'userId');
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }
}
