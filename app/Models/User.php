<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'phone_number',
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
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'user_id', 'id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'user_id', 'id');
    }

}
