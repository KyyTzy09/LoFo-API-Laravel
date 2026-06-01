<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeviceToken extends Model
{
    use HasFactory;

        protected static function booted()
    {
        static::creating(function ($DeviceToken) {
            if (!$DeviceToken->id) {
                $DeviceToken->id = (string) Str::ulid();
            }
        });
    }

    // Kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * Relasi balik ke model User
     * (Setiap token ini dimiliki oleh satu User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


